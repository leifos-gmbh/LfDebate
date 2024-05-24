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

class RepoFactory
{
    protected DataFactory $data;
    protected \ilDBInterface $db;

    public function __construct(
        DataFactory $data,
        \ilDBInterface $db
    )
    {
        $this->data = $data;
        $this->db = $db;
    }

    public function posting(): PostingDBRepo
    {
        return new PostingDBRepo(
            $this->data,
            $this->db
        );
    }

    public function attachment(): AttachmentDBRepo
    {
        return new AttachmentDBRepo(
            $this->data,
            $this->db
        );
    }
}
