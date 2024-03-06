<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

declare(strict_types=1);

namespace Leifos\Debate;

class AttachmentDBRepo
{
    /**
     * @var DataFactory
     */
    protected $data;
    /**
     * @var \ilDBInterface
     */
    protected $db;

    public function __construct(
        DataFactory $data,
        \ilDBInterface $db
    )
    {
        $this->data = $data;
        $this->db = $db;
    }

    protected function getFromRecord(array $rec): Attachment
    {
        return $this->data->attachment(
            (int) $rec["id"],
            (int) $rec["posting_id"],
            (string) $rec["rid"],
            (int) $rec["create_version"],
            (int) $rec["delete_version"],
        );
    }

    public function getAttachment(int $id): ?Attachment
    {
        $set = $this->db->queryF("SELECT * FROM xdbt_posting_att " .
            " WHERE id = %s",
            ["integer"],
            [$id]
        );

        if ($rec = $this->db->fetchAssoc($set)) {
            return $this->getFromRecord($rec);
        }

        throw new \ilPostingNotFoundException("Attachment with ID $id not found.");
    }

    /**
     * @param int $posting_id
     * @return Attachment[]
     */
    public function getAttachmentsForPosting(int $posting_id, int $version = 0): array
    {
        $set = $this->db->queryF("SELECT * FROM xdbt_posting_att " .
            " WHERE posting_id = %s AND create_version = %s",
            ["integer", "integer"],
            [$posting_id, $version]
        );

        $atts = [];
        while ($rec = $this->db->fetchAssoc($set)) {
            $atts[] = $this->getFromRecord($rec);
        }

        return $atts;
    }

    public function create(
        int $posting_id,
        string $rid
    ): void
    {
        $id = $this->db->nextId("xdbt_posting_att");
        $this->db->insert("xdbt_posting_att", [
            "id" => ["integer", $id],
            "posting_id" => ["integer", $posting_id],
            "rid" => ["text", $rid],
            "create_version" => ["integer", 0]
        ]);
    }

    public function update(
        Posting $posting,
        int $max_post_version
    ): void
    {
        $this->db->update("xdbt_posting_att", [
            "create_version" => ["integer", $max_post_version]
        ], [    // where
                "posting_id" => ["integer", $posting->getId()],
                "create_version" => ["integer", 0],
            ]
        );

        $atts = $this->getAttachmentsForPosting($posting->getId(), $max_post_version);
        foreach ($atts as $att) {
            $id = $this->db->nextId("xdbt_posting_att");
            $this->db->insert("xdbt_posting_att", [
                "id" => ["integer", $id],
                "posting_id" => ["integer", $att->getPostingId()],
                "rid" => ["text", $att->getRid()],
                "create_version" => ["integer", 0]
            ]);
        }
    }

    public function delete(int $id): void
    {
        $this->db->manipulateF(
            "DELETE FROM xdbt_posting_att WHERE id = %s",
            ["integer"],
            [$id]
        );
    }

    public function deleteForPosting(int $posting_id): void
    {
        $this->db->manipulateF(
            "DELETE FROM xdbt_posting_att WHERE posting_id = %s",
            ["integer"],
            [$posting_id]
        );
    }

    public function deleteAll(int $obj_id): void
    {
        $this->db->manipulateF(
            "DELETE FROM xdbt_posting_att " .
            " WHERE posting_id IN (SELECT child FROM xdbt_post_tree WHERE xdbt_obj_id = %s)",
            ["integer"],
            [$obj_id]
        );
    }
}
