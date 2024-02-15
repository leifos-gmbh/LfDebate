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

use Leifos\Debate\DataFactory;

class PostingDBRepo
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

    protected function getFromRecord(array $rec, int $obj_id): Posting
    {
        return $this->data->posting(
            $obj_id,
            (int) $rec["id"],
            (int) $rec["user_id"],
            (string) $rec["title"],
            (string) $rec["description"],
            (string) $rec["type"],
            (string) $rec["create_date"],
            (int) $rec["version"],
        );
    }

    /**
     * @return Posting[]
     */
    public function getTopEntries(int $obj_id): array
    {
        $set = $this->db->queryF("SELECT * FROM xdbt_posting p JOIN xdbt_post_tree t ON (t.child = p.id) " .
            " WHERE t.xdbt_obj_id = %s AND t.parent = %s AND p.version = %s ORDER BY p.id, p.version DESC",
            ["integer", "integer", "integer"],
            [$obj_id, 0, 0]
        );
        $postings = [];
        while ($rec = $this->db->fetchAssoc($set)) {
            $postings[] = $this->getFromRecord($rec, $obj_id);
        }

        return $postings;
    }

    /**
     * @return Posting[]
     */
    public function getSubEntries(int $obj_id, int $id): array
    {
        $set = $this->db->queryF("SELECT * FROM xdbt_posting p JOIN xdbt_post_tree t ON (t.child = p.id) " .
            " WHERE t.xdbt_obj_id = %s AND t.parent = %s AND p.version = %s ORDER BY p.id, p.version DESC",
            ["integer", "integer", "integer"],
            [$obj_id, $id, 0]
        );
        $postings = [];
        while ($rec = $this->db->fetchAssoc($set)) {
            $postings[] = $this->getFromRecord($rec, $obj_id);
        }

        return $postings;
    }

    public function getPosting(int $obj_id, int $id, int $version): ?Posting
    {
        $set = $this->db->queryF("SELECT * FROM xdbt_posting " .
            " WHERE id = %s AND version = %s",
            ["integer", "integer"],
            [$id, $version]
        );

        if ($rec = $this->db->fetchAssoc($set)) {
            return $this->getFromRecord($rec, $obj_id);
        }

        throw new \ilPostingNotFoundException("Posting with ID $id not found.");
    }

    public function create(
        int $user_id,
        string $title,
        string $description,
        string $type,
        string $date
    ): int
    {
        $id = $this->db->nextId("xdbt_posting");
        $this->db->insert("xdbt_posting", [
            "id" => ["integer", $id],
            "user_id" => ["integer", $user_id],
            "title" => ["text", $title],
            "description" => ["clob", $description],
            "type" => ["text", $type],
            "create_date" => ["date", $date],
            "version" => ["integer", 0]
        ]);

        return (int) $id;
    }

    public function createNewVersion(
        int $id,
        int $user_id,
        string $title,
        string $description,
        string $type,
        string $date
    ): void {
        $max_version = $this->getMaxVersion($id);
        $this->db->update("xdbt_posting", [
            "version" => ["integer", $max_version + 1]
        ], [    // where
                "id" => ["integer", $id],
                "version" => ["integer", 0],
            ]
        );

        $this->db->insert("xdbt_posting", [
            "id" => ["integer", $id],
            "user_id" => ["integer", $user_id],
            "title" => ["text", $title],
            "description" => ["clob", $description],
            "type" => ["text", $type],
            "create_date" => ["date", $date],
            "version" => ["integer", 0]
        ]);
    }

    public function addToTree(
        int $obj_id,
        int $id,
        int $parent_id = 0
    ): void
    {
        $this->db->insert("xdbt_post_tree", [
            "xdbt_obj_id" => ["integer", $obj_id],
            "child" => ["text", $id],
            "parent" => ["clob", $parent_id]
        ]);
    }

    public function getCurrentType(int $id): string
    {
        $version = $this->getMaxVersion($id);
        $set = $this->db->queryF("SELECT type FROM xdbt_posting " .
            " WHERE id = %s AND version = %s",
            ["integer", "integer"],
            [$id, $version]
        );

        if ($rec = $this->db->fetchAssoc($set)) {
            return $rec["type"];
        }

        return "";
    }

    public function getMaxVersion(int $id): int
    {
        $set = $this->db->queryF("SELECT MAX(version) as max_version FROM xdbt_posting " .
            " WHERE id = %s",
            ["integer"],
            [$id]
        );

        if ($rec = $this->db->fetchAssoc($set)) {
            return (int) $rec["max_version"];
        }

        return 0;
    }

    public function getInitialCreation(int $id)
    {
        $set = $this->db->queryF("SELECT MIN(create_date) as cd FROM xdbt_posting " .
            " WHERE id = %s",
            ["integer"],
            [$id]
        );
        $rec = $this->db->fetchAssoc($set);
        return $rec["cd"] ?? "";
    }

    public function getNrOfComments(int $id) : int
    {
        $set = $this->db->queryF("SELECT child FROM xdbt_post_tree " .
            " WHERE parent = %s",
            ["integer"],
            [$id]
        );
        $childs = [];
        while ($rec = $this->db->fetchAssoc($set)) {
            $childs[$rec["child"]] = $rec["child"];
        }
        $set2 = $this->db->queryF("SELECT count(*) as cnt FROM xdbt_post_tree " .
            " WHERE parent = " . $this->db->in("parent", $childs, false, "integer"),
            [],
            []
        );
        $rec2 = $this->db->fetchAssoc($set2);
        return count($childs) + (int) ($rec2["cnt"] ?? 0);
    }

    public function delete(int $id): void
    {
        $this->db->manipulateF(
            "DELETE FROM xdbt_posting WHERE id = %s",
            ["integer"],
            [$id]
        );
    }

    public function removeFromTree(int $id): void
    {
        $this->db->manipulateF(
            "DELETE FROM xdbt_post_tree " .
            " WHERE child = %s",
            ["integer"],
            [$id]
        );
    }

    public function removeChildsFromTree(int $parent_id): void
    {
        $this->db->manipulateF(
            "DELETE FROM xdbt_post_tree " .
            " WHERE parent = %s",
            ["integer"],
            [$parent_id]
        );
    }

    public function deleteAll(int $obj_id): void
    {
        $this->db->manipulateF(
            "DELETE FROM xdbt_posting " .
            " WHERE id IN (SELECT child FROM xdbt_post_tree WHERE xdbt_obj_id = %s)",
            ["integer"],
            [$obj_id]
        );
    }

    public function removeAllFromTree(int $obj_id): void
    {
        $this->db->manipulateF(
            "DELETE FROM xdbt_post_tree " .
            " WHERE xdbt_obj_id = %s",
            ["integer"],
            [$obj_id]
        );
    }
}
