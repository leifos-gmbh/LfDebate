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

class Attachment
{
    /**
     * @var int
     */
    protected $id;
    /**
     * @var int
     */
    protected $posting_id;
    /**
     * @var string
     */
    protected $rid = "";
    /**
     * @var int
     */
    protected $create_version = 0;

    public function __construct(
        int $id,
        int $posting_id,
        string $rid,
        int $create_version
    ) {
        $this->id = $id;
        $this->posting_id = $posting_id;
        $this->rid = $rid;
        $this->create_version = $create_version;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getPostingId(): int
    {
        return $this->posting_id;
    }

    public function getRid(): string
    {
        return $this->rid;
    }

    public function getCreateVersion(): int
    {
        return $this->create_version;
    }
}
