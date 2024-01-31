<?php

declare(strict_types=1);

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
 ********************************************************************
 */

namespace Leifos\Debate;

/**
 * Debate access wrapper
 * @author Thomas Famula <famula@leifos.de>
 */
class DebateAccess
{
    /**
     * @var \ilRbacSystem
     */
    protected $access;
    /**
     * @var int
     */
    protected $ref_id = 0;
    /**
     * @var int
     */
    protected $user_id = 0;

    public function __construct(\ilRbacSystem $access, int $ref_id, int $user_id)
    {
        $this->access = $access;
        $this->ref_id = $ref_id;
        $this->user_id = $user_id;
    }

    public function canReadPostings(int $user_id = 0): bool
    {
        if ($user_id === 0) {
            $user_id = $this->user_id;
        }

        return $this->access->checkAccessOfUser($user_id, "read", $this->ref_id);
    }

    public function canAddPostings(int $user_id = 0): bool
    {
        if ($user_id === 0) {
            $user_id = $this->user_id;
        }

        return $this->access->checkAccessOfUser($user_id, "read", $this->ref_id);
    }

    public function canAddComments(int $user_id = 0): bool
    {
        return $this->canAddPostings($user_id);
    }

    public function canReadPostingHistory(Posting $posting, int $user_id = 0): bool
    {
        if ($user_id === 0) {
            $user_id = $this->user_id;
        }
        if ($user_id === $posting->getUserId()) {
            return true;
        }

        return $this->access->checkAccessOfUser($user_id, "write", $this->ref_id);
    }

    public function canEditPosting(Posting $posting, int $user_id = 0): bool
    {
        if ($user_id === 0) {
            $user_id = $this->user_id;
        }
        if ($user_id === $posting->getUserId()) {
            return true;
        }

        return false;
    }

    public function canEditProperties(int $user_id = 0): bool
    {
        if ($user_id === 0) {
            $user_id = $this->user_id;
        }

        return $this->access->checkAccessOfUser($user_id, "write", $this->ref_id);
    }

    public function canDeletePostings(int $user_id = 0): bool
    {
        if ($user_id === 0) {
            $user_id = $this->user_id;
        }

        return $this->access->checkAccessOfUser($user_id, "write", $this->ref_id);
    }

    public function canDeletePosting(Posting $posting, int $user_id = 0): bool
    {
        if ($user_id === 0) {
            $user_id = $this->user_id;
        }
        if ($user_id === $posting->getUserId()) {
            return true;
        }

        return false;
    }
}
