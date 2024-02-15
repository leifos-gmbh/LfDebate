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
    public function getTopPostings(int $sorting): array
    {
        $repo = $this->repo->posting();
        $all = $repo->getTopEntries($this->obj_id);
        $all_arr = [];
        foreach ($all as $posting) {
            $parr = [];
            $parr["posting"] = $posting;
            $parr["create_date"] = $posting->getCreateDate();
            $parr["initial_creation"] = $repo->getInitialCreation($posting->getId());
            $parr["name"] = \ilUserUtil::getNamePresentation($posting->getUserId());
            $parr["nr_comments"] = $repo->getNrOfComments($posting->getId());
            $all_arr[] = $parr;
        }
        switch ($sorting) {
            case Sorting::NAME_ASC:
                $all_arr = \ilUtil::sortArray($all_arr, "name", "asc");
                break;
            case Sorting::NAME_DESC:
                $all_arr = \ilUtil::sortArray($all_arr, "name", "desc");
                break;
            case Sorting::CREATION_ASC:
                $all_arr = \ilUtil::sortArray($all_arr, "initial_creation", "asc");
                break;
            case Sorting::CREATION_DESC:
                $all_arr = \ilUtil::sortArray($all_arr, "initial_creation", "desc");
                break;
            case Sorting::UPDATE_ASC:
                $all_arr = \ilUtil::sortArray($all_arr, "create_date", "asc");
                break;
            case Sorting::UPDATE_DESC:
                $all_arr = \ilUtil::sortArray($all_arr, "create_date", "desc");
                break;
            case Sorting::COMMENTS_ASC:
                $all_arr = \ilUtil::sortArray($all_arr, "nr_comments", "asc");
                break;
            case Sorting::COMMENTS_DESC:
                $all_arr = \ilUtil::sortArray($all_arr, "nr_comments", "desc");
                break;
        }
        return array_map(static function ($item) {
            return $item["posting"];
        }, $all_arr);
    }

    public function getInitialCreation(int $posting_id) : string
    {
        return $this->repo->posting()->getInitialCreation($posting_id);
    }

    /**
     * @return Posting[]
     */
    public function getCommentsOfPosting(int $id): array
    {
        return $this->repo->posting()->getSubEntries($this->obj_id, $id);
    }

    /**
     * @return Posting[]
     */
    public function getSubCommentsOfComment(int $id): array
    {
        return $this->getCommentsOfPosting($id);
    }

    public function getPosting(int $id, ?int $version = null): Posting
    {
        if ($version === null) {
            $version = 0;
        }
        return $this->repo->posting()->getPosting($this->obj_id, $id, $version);
    }

    /**
     * Get all former versions of a Posting
     *
     * @return Posting[]
     */
    public function getOlderVersionsOfPosting(int $id): array
    {
        $version = $this->getMaxVersionOfPosting($id);
        if ($version === 0) {
            return [];
        }
        $postings = [];
        while ($version > 0) {
            $postings[] = $this->getPosting($id, $version--);
        }

        return $postings;
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
        Posting $posting,
        string $new_title,
        string $new_description
    ): void {
        $this->repo->posting()->createNewVersion(
            $posting->getId(),
            $posting->getUserId(),
            $new_title,
            $new_description,
            $posting->getType(),
            \ilUtil::now()
        );
    }

    protected function getMaxVersionOfPosting(int $id): int
    {
        return $this->repo->posting()->getMaxVersion($id);
    }

    public function deleteTopPosting(int $id): void
    {
        foreach ($this->getCommentsOfPosting($id) as $comment) {
            foreach ($this->getSubCommentsOfComment($comment->getId()) as $sub_comment) {
                $this->repo->posting()->delete($sub_comment->getId());
            }
            $this->repo->posting()->delete($comment->getId());
            $this->repo->posting()->removeChildsFromTree($comment->getId());
        }
        $this->repo->posting()->delete($id);
        $this->repo->posting()->removeChildsFromTree($id);
        $this->repo->posting()->removeFromTree($id);
    }

    public function deleteComment(int $id): void
    {
        foreach ($this->getSubCommentsOfComment($id) as $sub_comment) {
            $this->repo->posting()->delete($sub_comment->getId());
        }
        $this->repo->posting()->delete($id);
        $this->repo->posting()->removeChildsFromTree($id);
        $this->repo->posting()->removeFromTree($id);
    }

    public function deleteAll(): void
    {
        $this->repo->posting()->deleteAll($this->obj_id);
        $this->repo->posting()->removeAllFromTree($this->obj_id);
    }
}
