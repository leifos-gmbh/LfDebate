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

namespace Leifos\Debate\Posting;

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

    /**
     * @return Posting[]
     */
    public function getTopPostings(int $obj_id) : array
    {
        $set = $this->db->queryF("SELECT * FROM xdbt_posting p JOIN xdbt_post_tree t ON (t.child = p.id) " .
            " WHERE t.xdbt_obj_id = %s AND p.version = %s AND t.parent = %s",
            ["integer","integer","integer"],
            [$obj_id,0,0]
        );
        $postings = [];
        while ($rec = $this->db->fetchAssoc($set)) {
            $postings[] = $this->data->posting(
                (int) $rec["xdbt_obj_id"],
                (int) $rec["id"],
                (int) $rec["version"],
                (string) $rec["title"]
            );
        }

        return $postings;
    }

}