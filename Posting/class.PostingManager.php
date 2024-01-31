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

class PostingManager
{
    /**
     * @var DataFactory
     */
    protected $data;
    /**
     * @var RepoFactory
     */
    protected $repo;
    /**
     * @var DomainFactory
     */
    protected $domain;

    /**
     * @var int repository object id
     */
    protected $obj_id;

    public function __construct(
        DataFactory $data,
        RepoFactory $repo,
        DomainFactory $domain,
        int $obj_id
    )
    {
        $this->data = $data;
        $this->repo = $repo;
        $this->domain = $domain;
        $this->obj_id = $obj_id;
    }

    /**
     * @return Posting[]
     */
    public function getTopPostings(): array
    {
        return $this->repo->posting()->getTopEntries($this->obj_id);
    }

    /**
     * @return Posting[]
     */
    public function getCommentsOfTopPosting(int $id): array
    {
        return $this->repo->posting()->getSubEntries($this->obj_id, $id);
    }

    /**
     * @return Posting[]
     */
    public function getSubCommentsOfComment(int $id): array
    {
        return $this->getCommentsOfTopPosting($id);
    }

    public function getPosting(int $id, ?int $version = null): Posting
    {
        if (!$version) {
            $version = $this->getCurrentVersionOfPosting($id);
        }
        return $this->repo->posting()->getPosting($this->obj_id, $id, $version);
    }

    public function createTopPosting(string $title, string $description): void {
        $user_id = $this->domain->user()->getId();
        $posting_id = $this->repo->posting()->create(
            $user_id,
            $title,
            $description,
            CommentUI::TYPE_INITIAL,
            \ilUtil::now()
        );

        $this->repo->posting()->addToTree(
            $this->obj_id,
            $posting_id
        );
    }

    public function createCommentPosting(
        int $parent_id,
        string $title,
        string $description,
        string $type
    ): void {
        $user_id = $this->domain->user()->getId();
        $posting_id = $this->repo->posting()->create(
            $user_id,
            $title,
            $description,
            $type,
            \ilUtil::now()
        );

        $this->repo->posting()->addToTree(
            $this->obj_id,
            $posting_id,
            $parent_id
        );
    }

    public function editPosting(
        int $id,
        string $new_title,
        string $new_description
    ): void {
        $user_id = $this->domain->user()->getId();
        $this->repo->posting()->createNewVersion(
            $id,
            $user_id,
            $new_title,
            $new_description,
            $this->getCurrentTypeOfPosting($id),                           // soll der Typ editierbar sein?
            \ilUtil::now(),
            $this->getCurrentVersionOfPosting($id) + 1
        );
    }

    protected function getCurrentTypeOfPosting(int $id): string
    {
        return $this->repo->posting()->getCurrentType($id);
    }

    protected function getCurrentVersionOfPosting(int $id): int
    {
        return $this->repo->posting()->getMaxVersion($id);
    }
}
