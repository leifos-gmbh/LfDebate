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

use ILIAS\ResourceStorage;

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
     * @var ResourceStorage\Services
     */
    protected $resource_storage;
    /**
     * @var DomainFactory
     */
    protected $domain;

    /**
     * @var int repository object id
     */
    protected $obj_id;
    /**
     * @var PostingStakeHolder
     */
    protected $stakeholder;

    public function __construct(
        DataFactory $data,
        RepoFactory $repo,
        ResourceStorage\Services $resource_storage,
        DomainFactory $domain,
        int $obj_id
    )
    {
        $this->data = $data;
        $this->repo = $repo;
        $this->resource_storage = $resource_storage;
        $this->domain = $domain;
        $this->obj_id = $obj_id;
        $this->stakeholder = new PostingStakeHolder();
    }

    /**
     * @return Posting[]
     */
    public function getTopPostings(int $sorting): array
    {
        $post_repo = $this->repo->posting();
        $all = $post_repo->getTopEntries($this->obj_id);
        $all_arr = [];
        foreach ($all as $posting) {
            $parr = [];
            $parr["posting"] = $posting;
            $parr["create_date"] = $posting->getCreateDate();
            $parr["initial_creation"] = $post_repo->getInitialCreation($posting->getId());
            $parr["name"] = \ilUserUtil::getNamePresentation($posting->getUserId());
            $parr["nr_comments"] = $post_repo->getNrOfComments($posting->getId());
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

    public function getNumberOfCommentsAndSubComments(): int
    {
        $all = [];
        foreach ($this->getTopPostings(0) as $posting) {
            foreach ($this->getCommentsOfPosting($posting->getId()) as $comment) {
                $all[] = $comment;
                foreach ($this->getSubCommentsOfComment($comment->getId()) as $sub_comment) {
                    $all[] = $sub_comment;
                }
            }
        }

        return count($all);
    }

    public function getNumberOfCommentsAndSubCommentsOfPosting(int $id): int
    {
        $posting_all = [];
        foreach ($this->getCommentsOfPosting($id) as $comment) {
            $posting_all[] = $comment;
            foreach ($this->getSubCommentsOfComment($comment->getId()) as $sub_comment) {
                $posting_all[] = $sub_comment;
            }
        }

        return count($posting_all);
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

    /**
     * @param string[]  $file_ids
     */
    public function createTopPosting(
        string $title,
        string $description,
        array $file_ids
    ): void {
        $user_id = $this->domain->user()->getId();
        $posting_id = $this->repo->posting()->create(
            $user_id,
            $title,
            $description,
            CommentUI::TYPE_INITIAL,
            \ilUtil::now()
        );

        foreach ($file_ids as $file_id) {
            $this->repo->attachment()->create(
                $posting_id,
                $file_id
            );
        }

        $this->repo->posting()->addToTree(
            $this->obj_id,
            $posting_id
        );
    }

    /**
     * @param string[]  $file_ids
     */
    public function createCommentPosting(
        int $parent_id,
        string $title,
        string $description,
        string $type,
        array $file_ids
    ): void {
        $user_id = $this->domain->user()->getId();
        $posting_id = $this->repo->posting()->create(
            $user_id,
            $title,
            $description,
            $type,
            \ilUtil::now()
        );

        foreach ($file_ids as $file_id) {
            $this->repo->attachment()->create(
                $posting_id,
                $file_id
            );
        }

        $this->repo->posting()->addToTree(
            $this->obj_id,
            $posting_id,
            $parent_id
        );
    }

    /**
     * @param string[]   $new_file_ids
     */
    public function editPosting(
        Posting $posting,
        string $new_title,
        string $new_description,
        array $new_file_ids
    ): void {
        $this->repo->posting()->createNewVersion(
            $posting->getId(),
            $posting->getUserId(),
            $new_title,
            $new_description,
            $posting->getType(),
            \ilUtil::now()
        );

        $max_post_version = $this->getMaxVersionOfPosting($posting->getId());
        $this->repo->attachment()->update(
            $posting,
            $max_post_version
        );

        foreach ($new_file_ids as $new_file_id) {
            $this->repo->attachment()->create(
                $posting->getId(),
                $new_file_id
            );
        }
    }

    protected function getMaxVersionOfPosting(int $id): int
    {
        return $this->repo->posting()->getMaxVersion($id);
    }

    public function getAttachment(int $id): Attachment
    {
        return $this->repo->attachment()->getAttachment($id);
    }

    /**
     * @return Attachment[]
     */
    public function getAttachmentsForPosting(int $posting_id, int $version = 0): array
    {
        return $this->repo->attachment()->getAttachmentsForPosting($posting_id, $version);
    }

    public function deleteTopPosting(int $id): void
    {
        foreach ($this->getCommentsOfPosting($id) as $comment) {
            foreach ($this->getSubCommentsOfComment($comment->getId()) as $sub_comment) {
                $this->deleteAttachmentFilesForPosting($sub_comment->getId());
                $this->repo->attachment()->deleteForPosting($sub_comment->getId());
                $this->repo->posting()->delete($sub_comment->getId());
            }
            $this->deleteAttachmentFilesForPosting($comment->getId());
            $this->repo->attachment()->deleteForPosting($comment->getId());
            $this->repo->posting()->delete($comment->getId());
            $this->repo->posting()->removeChildsFromTree($comment->getId());
        }
        $this->deleteAttachmentFilesForPosting($id);
        $this->repo->attachment()->deleteForPosting($id);
        $this->repo->posting()->delete($id);
        $this->repo->posting()->removeChildsFromTree($id);
        $this->repo->posting()->removeFromTree($id);
    }

    public function deleteComment(int $id): void
    {
        foreach ($this->getSubCommentsOfComment($id) as $sub_comment) {
            $this->deleteAttachmentFilesForPosting($sub_comment->getId());
            $this->repo->attachment()->deleteForPosting($sub_comment->getId());
            $this->repo->posting()->delete($sub_comment->getId());
        }
        $this->deleteAttachmentFilesForPosting($id);
        $this->repo->attachment()->deleteForPosting($id);
        $this->repo->posting()->delete($id);
        $this->repo->posting()->removeChildsFromTree($id);
        $this->repo->posting()->removeFromTree($id);
    }

    public function deleteAll(): void
    {
        $this->deleteAttachmentFilesForDebate();
        $this->repo->attachment()->deleteAll($this->obj_id);
        $this->repo->posting()->deleteAll($this->obj_id);
        $this->repo->posting()->removeAllFromTree($this->obj_id);
    }

    protected function deleteAttachmentFilesForPosting(int $posting_id): void
    {
        foreach ($this->repo->attachment()->getAttachmentsForAllPostingVersions($posting_id) as $att) {
            $id = $this->resource_storage->manage()->find($att->getRid());
            if ($id !== null) {
                $this->resource_storage->manage()->remove($id, $this->stakeholder);
            }
        }
    }

    protected function deleteAttachmentFilesForDebate(): void
    {
        foreach ($this->repo->attachment()->getAttachmentsForDebate($this->obj_id) as $att) {
            $id = $this->resource_storage->manage()->find($att->getRid());
            if ($id !== null) {
                $this->resource_storage->manage()->remove($id, $this->stakeholder);
            }
        }
    }

    public function getContributors(): array
    {
        $post_repo = $this->repo->posting();
        $contribs = [];
        foreach ($post_repo->getContributorIds($this->obj_id) as $id) {
            $name = \ilObjUser::_lookupName($id);
            $contribs[] = [
                "id" => $id,
                "firstname" => $name["firstname"],
                "lastname" => $name["lastname"],
                "sort" => $name["lastname"] . $name["firstname"]
            ];
        }
        $contribs = \ilUtil::sortArray($contribs, "sort", "asc");
        return $contribs;
    }

    public function exportContributor(int $user_id) : void
    {
        $name = \ilObjUser::_lookupName($user_id);

        //echo "<pre>" . $this->getContributionsOfUserAsText($user_id) . "</pre>";
        //exit;

        \ilUtil::deliverData(
            $this->getContributionsOfUserAsText($user_id),
            $name["lastname"] . "_" . $name["firstname"] . ".txt"
        );
    }

    protected function getContributionsOfUserAsText(int $user_id) : string
    {
        $plugin = $this->domain->plugin();
        $post_repo = $this->repo->posting();

        $name = \ilObjUser::_lookupName($user_id);
        $name_line = $name["lastname"] . ", " . $name["firstname"];
        $text = "";

        /** @var Posting $p */
        foreach ($post_repo->getContributionsOfUser($this->obj_id, $user_id) as $p) {

            $pad = "";
            $reference = "";
            $posting_type = $plugin->txt("posting");
            if ($p->getParent() > 0) {
                $pad = str_pad(" ", 4);
                $posting_type = $plugin->txt("comment")." (1)";
                $parent = $this->repo->posting()->getPosting($this->obj_id, $p->getParent(), 0);
                $parent_name = \ilObjUser::_lookupName($parent->getUserId());
                $parent_name = $parent_name["lastname"] . ", " . $parent_name["firstname"];
                $reference = $plugin->txt("reference"). ": " . str_replace("%1", $parent_name, $plugin->txt("posting_of"));
                if ($parent->getParent() > 0) {
                    $pad = str_pad(" ", 8);
                    $posting_type = $plugin->txt("comment")." (2)";
                    $grand_parent = $this->repo->posting()->getPosting($this->obj_id, $parent->getParent(), 0);
                    $grand_parent_name = \ilObjUser::_lookupName($grand_parent->getUserId());
                    $grand_parent_name = $grand_parent_name["lastname"] . ", " . $grand_parent_name["firstname"];
                    $reference = $plugin->txt("reference"). ": " .
                        str_replace("%1", $parent_name,
                            str_replace("%2", $grand_parent_name, $plugin->txt("comment_of_to_posting")));
                }
                $reference.= "\n";
            }

            $desc = str_replace("\n", " ", $p->getDescription());
            $desc = str_replace("\r", " ", $desc);
            $desc = str_replace("  ", " ", $desc);
            $desc = str_replace("  ", " ", $desc);
            $desc = str_replace("</li>", "</li>   ", $desc);
            $desc = str_replace("<li>", "<li>* ", $desc);
            $desc = strip_tags($desc);
            $text .= $pad.$posting_type . ": " . $name_line;
            $dt = new \ilDateTime($p->getCreateDate(), IL_CAL_DATETIME);
            \ilDatePresentation::setUseRelativeDates(false);
            $dt = \ilDatePresentation::formatDate($dt);
            $text .= " [" . $dt . "]\n";
            if ($reference !== "") {
                $text .= $pad.$reference;
            }
            $text .= $pad."Titel: " . $p->getTitle() . "\n";
            if ($pad !== "") {
                $text .= $pad."Reaktion: " . $this->getTypeTitle($p->getType()) . "\n";
            }
            $text .= $pad.$desc . "\n";
            $text .= "\n";
        }

        return $text;
    }

    protected function getTypeTitle(string $type) : string
    {
        $plugin = $this->domain->plugin();
        switch ($type) {
            case CommentUI::TYPE_INITIAL: return $plugin->txt("neutral");
            case CommentUI::TYPE_PRO: return $plugin->txt("pro");
            case CommentUI::TYPE_EXCLAMATION: return $plugin->txt("exclamation");
            case CommentUI::TYPE_CONTRA: return $plugin->txt("contra");
            case CommentUI::TYPE_QUESTION: return $plugin->txt("question");
        }
        return "";
    }
}
