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

class Posting
{
    /**
     * @var string
     */
    protected $title;
    /**
     * @var int
     */
    protected $version;
    /**
     * @var int
     */
    protected $id;
    /**
     * @var int
     */
    protected $obj_id;

    public function __construct(
        int $obj_id,
        int $id,
        int $version,
        string $title
    )
    {
        $this->obj_id = $obj_id;
        $this->id = $id;
        $this->version = $version;
        $this->title = $title;
    }

    public function getObjId() : int
    {
        return $this->obj_id;
    }
    public function getId() : int
    {
        return $this->id;
    }
    public function getVersion() : int
    {
        return $this->version;
    }
    public function getTitle() : string
    {
        return $this->title;
    }
}