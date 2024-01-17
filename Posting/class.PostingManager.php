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
use Leifos\Debate\RepoFactory;
use Leifos\Debate\Posting\Posting;

class PostingManager
{
    /**
     * @var DataFactory
     */
    protected $repo;
    /**
     * @var DataFactory
     */
    protected $data;
    /**
     * @var \ilDBInterface
     */
    protected $db;

    /**
     * @var int repository object id
     */
    protected $obj_id;

    public function __construct(
        DataFactory $data,
        DataFactory $repo,
        int $obj_id
    )
    {
        $this->data = $data;
        $this->repo = $repo;
        $this->obj_id = $obj_id;
    }

    /**
     * @return Posting[]
     */
    public function getTopPostings() : array
    {
        return $this->repo->posting()->getTopPostings($this->obj_id);
    }


}