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

class DataFactory
{
    public function __construct()
    {
    }

    public function posting(
        int $obj_id,
        int $id,
        int $user_id,
        string $title,
        string $description,
        string $type,
        string $create_date,
        int $version = 0,
        int $parent = 0
    ): Posting
    {
        return new Posting(
            $obj_id,
            $id,
            $user_id,
            $title,
            $description,
            $type,
            $create_date,
            $version,
            $parent
        );
    }

    public function attachment(
        int $id,
        int $posting_id,
        string $rid,
        int $create_version
    ): Attachment
    {
        return new Attachment(
            $id,
            $posting_id,
            $rid,
            $create_version
        );
    }
}
