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

class Posting
{
    protected int $obj_id;
    protected int $id;
    protected int $user_id;
    protected string $title;
    protected string $description;
    protected string $type;
    protected string $create_date;
    protected int $version;
    protected int $parent;

    public function __construct(
        int $obj_id,
        int $id,
        int $user_id,
        string $title,
        string $description,
        string $type,
        string $create_date,
        int $version,
        int $parent = 0
    ) {
        $this->obj_id = $obj_id;
        $this->id = $id;
        $this->user_id = $user_id;
        $this->title = $title;
        $this->description = $description;
        $this->type = $type;
        $this->create_date = $create_date;
        $this->version = $version;
        $this->parent = $parent;
    }

    public function getObjId(): int
    {
        return $this->obj_id;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getUserId(): int
    {
        return $this->user_id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getCreateDate(): string
    {
        return $this->create_date;
    }

    public function getVersion(): int
    {
        return $this->version;
    }

    public function getParent(): int
    {
        return $this->parent;
    }
}
