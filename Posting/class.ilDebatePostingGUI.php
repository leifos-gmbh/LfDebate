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

use Leifos\Debate\CommentLightUI;
use Leifos\Debate\CommentUI;
use Leifos\Debate\DebateAccess;
use Leifos\Debate\GUIFactory;
use Leifos\Debate\Posting;
use Leifos\Debate\PostingLightUI;
use Leifos\Debate\PostingUI;
use Leifos\Debate\PostingManager;
use ILIAS\ResourceStorage\Services as ResourceStorage;
use ILIAS\UI;
use ILIAS\UI\Component\Input\Container\Form\Form;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @author Thomas Famula <famula@leifos.de>
 *
 * @ilCtrl_isCalledBy ilDebatePostingGUI: ilObjLfDebateGUI
 */
class ilDebatePostingGUI
{
    /**
     * @var ilCtrl
     */
    protected $ctrl;
    /**
     * @var ilLanguage
     */
    protected $lng;
    /**
     * @var ilTabsGUI
     */
    protected $tabs;
    /**
     * @var ilGlobalTemplateInterface
     */
    protected $tpl;
    /**
     * @var ilLocatorGUI
     */
    protected $locator;
    /**
     * @var UI\Factory
     */
    protected $ui_fac;
    /**
     * @var UI\Renderer
     */
    protected $ui_ren;
    /**
     * @var ServerRequestInterface
     */
    protected $request;
    /**
     * @var ResourceStorage
     */
    protected $res_storage;
    /**
     * @var PostingManager
     */
    protected $posting_manager;
    /**
     * @var DebateAccess
     */
    protected $access_wrapper;
    /**
     * @var GUIFactory
     */
    protected $gui;
    /**
     * @var ilObjLfDebate
     */
    protected $dbt_object;
    /**
     * @var ilLfDebatePlugin
     */
    protected $dbt_plugin;
    /**
     * @var Posting
     */
    protected $posting;
    /**
     * @var UI\Component\Component[]
     */
    protected $ui_comps = [];

    public function __construct(ilLfDebatePlugin $dbt_plugin, ilObjLfDebate $dbt_obj)
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->tabs = $DIC->tabs();
        $this->tpl = $DIC["tpl"];
        $this->locator = $DIC["ilLocator"];

        $this->ui_fac = $DIC->ui()->factory();
        $this->ui_ren = $DIC->ui()->renderer();
        $this->request = $DIC->http()->request();
        $this->res_storage = $DIC->resourceStorage();

        $this->dbt_object = $dbt_obj;
        $this->dbt_plugin = $dbt_plugin;
        $this->gui = $dbt_plugin->gui();
        $this->posting_manager = $dbt_plugin->domain()->posting($dbt_obj->getId());
        $this->access_wrapper = $dbt_plugin->domain()->accessWrapper((int) $dbt_obj->getRefId());
        $this->posting = $this->posting_manager->getPosting($this->gui->request()->getPostingId());
    }

    public function executeCommand(): void
    {
        $this->prepareOutput();

        $next_class = $this->ctrl->getNextClass($this);
        switch ($next_class) {
            case "ildebatepostinguploadhandlergui":
                $post_upl_gui = new ilDebatePostingUploadHandlerGUI();
                $this->ctrl->forwardCommand($post_upl_gui);
                break;
            default:
                $cmd = $this->ctrl->getCmd("showPosting");
                $this->$cmd();
                break;
        }
        $this->tpl->printToStdout();
    }

    protected function prepareOutput(): void
    {
        $this->tpl->loadStandardTemplate();
        $this->tpl->setTitle($this->dbt_object->getTitle());
        $this->tpl->setTitleIcon(ilObject::_getIcon($this->dbt_object->getId()));
        $this->locator->addRepositoryItems($this->dbt_object->getRefId());
        $this->locator->addItem(
            $this->dbt_object->getTitle(),
            $this->ctrl->getLinkTarget($this, "returnToDebate")
        );
        $this->tpl->setLocator();
    }

    protected function returnToDebate(): void
    {
        $this->ctrl->redirectByClass("ilobjlfdebategui", "showAllPostings");
    }

    protected function showPosting(): void
    {
        $this->tabs->clearTargets();
        $this->tabs->setBackTarget(
            $this->dbt_plugin->txt("posting_overview"),
            $this->ctrl->getLinkTargetByClass("ilobjlfdebategui", "showAllPostings")
        );

        $html = "";

        $posting_ui = $this->getPostingUI();
        $html .= $posting_ui->render();

        $add_comment_button = $this->ui_fac->button()->standard(
            $this->dbt_plugin->txt("add_comment"),
            $this->ctrl->getLinkTarget($this, "addComment")
        );
        $html .= "<div class='debate-reply'>" . $this->ui_ren->render($add_comment_button) ."</div>";

        foreach ($this->posting_manager->getCommentsOfPosting($this->posting->getId()) as $comment) {
            $comments_ui = $this->getCommentUI($comment);
            $sub_html = "";
            foreach ($this->posting_manager->getSubCommentsOfComment($comment->getId()) as $sub_comment) {
                $sub_comments_ui = $this->getCommentUI($sub_comment, true);
                $sub_html .= $sub_comments_ui->render();
            }
            $comments_ui = $comments_ui->withSubComments($sub_html);
            $html .= $comments_ui->render();
        }
        // add modals
        $html .= $this->ui_ren->render($this->ui_comps);

        $this->tpl->setContent($html);
    }

    protected function getPostingUI(): PostingUI
    {
        $posting_ui = $this->getPostingBasics($this->posting);

        $actions = $this->getPostingActions();
        $posting_ui = $posting_ui->withActions($actions);

        return $posting_ui;
    }

    protected function getCommentUI(Posting $comment, bool $sub = false): CommentUI
    {
        $comments_ui = $this->getPostingBasics($comment, true);

        $actions = [];
        $this->ctrl->setParameter($this, "cmt_id", $comment->getId());
        if (!$sub && $this->access_wrapper->canAddComments()) {
            $actions[] = $this->ui_fac->button()->shy(
                $this->dbt_plugin->txt("add_comment"),
                $this->ctrl->getLinkTarget($this, "addComment")
            );
        }
        $actions = $this->getCommentDefaultActions($actions, $comment);
        $this->ctrl->clearParameterByClass(self::class, "cmt_id");
        $comments_ui = $comments_ui->withActions($actions);

        return $comments_ui;
    }

    /**
     * @return PostingLightUI|CommentLightUI
     */
    protected function getModalPostingUI(Posting $posting, bool $comment = false)
    {
        $pos_type = $comment ? "commentLight" : "postingLight";
        $posting_ui = $this->gui->$pos_type(
            $this->dbt_plugin,
            $posting->getType(),
            $posting->getCreateDate(),
            $posting->getTitle(),
            $posting->getDescription()
        );

        $attachments = $this->getAttachments($posting);
        $posting_ui = $posting_ui->withAttachments($attachments);

        return $posting_ui;
    }

    /**
     * @return PostingUI|CommentUI
     */
    protected function getPostingBasics(Posting $posting, bool $comment = false)
    {
        $user = new ilObjUser($posting->getUserId());
        $name = $user->getPublicName();
        $avatar = $user->getAvatar();
        $initial_creation = $this->posting_manager->getInitialCreation($posting->getId());
        $last_edit = "";
        if ($initial_creation !== $posting->getCreateDate()) {
            $last_edit = $posting->getCreateDate();
        }
        if ($comment) {
            $posting_ui = $this->gui->comment(
                $this->dbt_plugin,
                $posting->getType(),
                $avatar,
                $name,
                $initial_creation,
                $last_edit,
                $posting->getTitle(),
                $posting->getDescription()
            );
        } else {
            $posting_ui = $this->gui->posting(
                $this->dbt_plugin,
                $posting->getType(),
                $avatar,
                $name,
                $initial_creation,
                $last_edit,
                $posting->getTitle(),
                $posting->getDescription(),
                "",
                true
            );
        }

        $attachments = $this->getAttachments($posting);
        $posting_ui = $posting_ui->withAttachments($attachments);

        return $posting_ui;
    }

    /**
     * @return UI\Component\Button\Shy[]
     */
    protected function getPostingActions(): array
    {
        $actions = [];
        $this->ctrl->setParameterByClass("ilobjlfdebategui", "post_id", $this->posting->getId());
        $this->ctrl->setParameterByClass("ilobjlfdebategui", "post_mode", 1);
        if ($this->access_wrapper->canEditPosting($this->posting)) {
            $actions[] = $this->ui_fac->button()->shy(
                $this->lng->txt("edit"),
                $this->ctrl->getLinkTargetByClass("ilobjlfdebategui", "editPosting")
            );
        }
        if ($this->access_wrapper->canReadPostingHistory($this->posting)
            && !empty($old_postings = $this->posting_manager->getOlderVersionsOfPosting($this->posting->getId()))
        ) {
            $modal_html = "";
            foreach ($old_postings as $posting) {
                $posting_ui = $this->getModalPostingUI($posting);
                $modal_html .= $posting_ui->render();
            }
            $modal = $this->ui_fac->modal()->roundtrip($this->dbt_plugin->txt("older_versions"), $this->ui_fac->legacy($modal_html));
            $this->ui_comps[] = $modal;
            $actions[] = $this->ui_fac->button()->shy($this->dbt_plugin->txt("show_older_versions"), "")
                                      ->withOnClick($modal->getShowSignal());
        }
        if ($this->access_wrapper->canDeletePostings()) {
            $item = $this->ui_fac->modal()->interruptiveItem((string) $this->posting->getId(), $this->posting->getTitle());
            $delete_modal = $this->ui_fac->modal()->interruptive(
                $this->dbt_plugin->txt("confirm_deletion"),
                $this->dbt_plugin->txt("confirm_deletion_posting"),
                $this->ctrl->getFormActionByClass("ilobjlfdebategui", "deletePosting")
            )->withAffectedItems([$item]);
            $this->ui_comps[] = $delete_modal;
            $actions[] = $this->ui_fac->button()->shy($this->lng->txt("delete"), "")
                                      ->withOnClick($delete_modal->getShowSignal());
        }
        $this->ctrl->clearParameterByClass("ilobjlfdebategui", "post_id");
        $this->ctrl->clearParameterByClass("ilobjlfdebategui", "post_mode");

        return $actions;
    }

    /**
     * @return UI\Component\Button\Shy[]
     */
    protected function getCommentDefaultActions(array $actions, Posting $comment): array
    {
        if ($this->access_wrapper->canEditPosting($comment)) {
            $actions[] = $this->ui_fac->button()->shy(
                $this->lng->txt("edit"),
                $this->ctrl->getLinkTarget($this, "editComment")
            );
        }
        if ($this->access_wrapper->canReadPostingHistory($comment)
            && !empty($old_postings = $this->posting_manager->getOlderVersionsOfPosting($comment->getId()))
        ) {
            $modal_html = "";
            foreach ($old_postings as $posting) {
                $posting_ui = $this->getModalPostingUI($posting, true);
                $modal_html .= $posting_ui->render();
            }
            $modal = $this->ui_fac->modal()->roundtrip($this->dbt_plugin->txt("older_versions"), $this->ui_fac->legacy($modal_html));
            $this->ui_comps[] = $modal;
            $actions[] = $this->ui_fac->button()->shy($this->dbt_plugin->txt("show_older_versions"), "")
                                      ->withOnClick($modal->getShowSignal());
        }
        if ($this->access_wrapper->canDeletePostings()) {
            $item = $this->ui_fac->modal()->interruptiveItem((string) $comment->getId(), $comment->getTitle());
            $delete_modal = $this->ui_fac->modal()->interruptive(
                $this->dbt_plugin->txt("confirm_deletion"),
                $this->dbt_plugin->txt("confirm_deletion_comment"),
                $this->ctrl->getFormAction($this, "deleteComment")
            )->withAffectedItems([$item]);
            $this->ui_comps[] = $delete_modal;
            $actions[] = $this->ui_fac->button()->shy($this->lng->txt("delete"), "")
                                      ->withOnClick($delete_modal->getShowSignal());
        }

        return $actions;
    }

    /**
     * @return UI\Component\Link\Link[]
     */
    protected function getAttachments(Posting $posting): array
    {
        $attachments = [];
        foreach ($this->posting_manager->getAttachments($posting->getId(), $posting->getVersion()) as $att) {
            if (($rid = $att->getRid()) &&
                ($identification = $this->res_storage->manage()->find($rid))) {
                $this->ctrl->setParameter($this, "rid", $rid);
                $title = $this->res_storage->manage()->getCurrentRevision($identification)->getTitle();
                $attachments[] = $this->ui_fac->link()->standard(
                    $title,
                    $this->ctrl->getLinkTarget($this, "downloadAttachment")
                );
                $this->ctrl->clearParameterByClass(self::class, "rid");
            }
        }

        return $attachments;
    }

    protected function addComment(): void
    {
        if (!$this->access_wrapper->canAddComments()) {
            return;
        }

        $this->addOrEditComment();
    }

    protected function editComment(): void
    {
        $comment_id = $this->gui->request()->getCommentId();
        $comment = $this->posting_manager->getPosting($comment_id);
        if (!$this->access_wrapper->canEditPosting($comment)) {
            return;
        }

        $this->addOrEditComment(true);
    }

    protected function addOrEditComment(bool $edit = false): void
    {
        $this->tabs->clearTargets();
        $this->tabs->setBackTarget(
            $this->lng->txt("back"),
            $this->ctrl->getLinkTarget($this, "showPosting")
        );

        $this->tpl->setContent($this->ui_ren->render($this->initCommentForm($edit)));
    }

    protected function initCommentForm(bool $edit = false): Form
    {
        $comment_id = $this->gui->request()->getCommentId();
        if ($edit) {
            $comment = $this->posting_manager->getPosting($comment_id);
        }

        $title = $this->ui_fac->input()->field()->text($this->lng->txt("title"))->withRequired(true);
        if ($edit) {
            $title = $title->withValue($comment->getTitle());
        }

        $description = $this->ui_fac->input()->field()->textarea($this->lng->txt("description"));
        if ($edit) {
            $description = $description->withValue($comment->getDescription());
        }

        $type = $this->ui_fac->input()->field()->radio($this->lng->txt("type"))
            ->withOption(CommentUI::TYPE_INITIAL, $this->dbt_plugin->txt("neutral"))
            ->withOption(CommentUI::TYPE_PRO, $this->dbt_plugin->txt("pro"), $this->dbt_plugin->txt("pro_info"))
            ->withOption(CommentUI::TYPE_EXCLAMATION, $this->dbt_plugin->txt("exclamation"), $this->dbt_plugin->txt("exclamation_info"))
            ->withOption(CommentUI::TYPE_CONTRA, $this->dbt_plugin->txt("contra"), $this->dbt_plugin->txt("contra_info"))
            ->withOption(CommentUI::TYPE_QUESTION, $this->dbt_plugin->txt("question"), $this->dbt_plugin->txt("question_info"));
        if ($edit) {
            $type = $type->withValue($comment->getType());
        } else {
            $type = $type->withValue(CommentUI::TYPE_INITIAL);
        }

        $files = $this->ui_fac->input()->field()->file(
            new ilDebatePostingUploadHandlerGUI(),
            $this->lng->txt("attachment"),
            $this->lng->txt("attachment_info") // Info mit unterstützten Dateiformaten?
        );
        //->withAcceptedMimeTypes() // ILIAS whitelist oder manuell?

        $section_title = $edit ? $this->dbt_plugin->txt("update_comment") : $this->dbt_plugin->txt("add_comment");
        $section_inputs = ["title" => $title,
                           "description" => $description];
        if (!$edit) {
            $section_inputs["type"] = $type;
            $section_inputs["files"] = $files;
        }
        $section = $this->ui_fac->input()->field()->section(
            $section_inputs,
            $section_title
        );

        $this->ctrl->setParameter($this, "cmt_id", $comment_id);
        if ($edit) {
            $form_action = $this->ctrl->getFormAction($this, "updateComment");
        } else {
            $form_action = $this->ctrl->getFormAction($this, "createComment");
        }
        $this->ctrl->clearParameterByClass(self::class, "cmt_id");

        return $this->ui_fac->input()->container()->form()->standard($form_action, ["props" => $section]);
    }

    protected function createComment(): void
    {
        $this->saveComment();
    }

    protected function updateComment(): void
    {
        $this->saveComment(true);
    }

    protected function saveComment(bool $edit = false): void
    {
        $form = $this->initCommentForm();
        if ($this->request->getMethod() === "POST") {
            $form = $form->withRequest($this->request);
            $data = $form->getData();
            if (isset($data["props"]) && is_array($data["props"])) {
                $props = $data["props"];
                $comment_id = $this->gui->request()->getCommentId();
                if ($edit) {
                    $comment = $this->posting_manager->getPosting($comment_id);
                    $this->posting_manager->editPosting(
                        $comment,
                        $props["title"],
                        $props["description"]
                    );
                    $this->tpl->setOnScreenMessage("success", $this->dbt_plugin->txt("comment_updated"), true);
                } else {
                    $parent_id = $comment_id ?: $this->posting->getId();
                    $this->posting_manager->createCommentPosting(
                        $parent_id,
                        $props["title"],
                        $props["description"],
                        $props["type"],
                        $props["files"][0] ?? ""
                    );
                    $this->tpl->setOnScreenMessage("success", $this->dbt_plugin->txt("comment_created"), true);
                }
            } else {
                $this->tpl->setContent($this->ui_ren->render($form));
                $this->tabs->clearTargets();
                $this->tabs->setBackTarget(
                    $this->lng->txt("back"),
                    $this->ctrl->getLinkTarget($this, "showPosting")
                );
                return;
            }
        }
        $this->ctrl->redirect($this, "showPosting");
    }

    protected function deleteComment()
    {
        $comment_id = $this->gui->request()->getCommentId();
        if (!$this->access_wrapper->canDeletePostings()) {
            return;
        }
        $this->posting_manager->deleteComment($comment_id);

        $this->tpl->setOnScreenMessage("success", $this->dbt_plugin->txt("comment_deleted"), true);
        $this->ctrl->redirect($this, "showPosting");
    }

    protected function downloadAttachment(): void
    {
        $rid = $this->gui->request()->getResourceID();
        if ($identification = $this->res_storage->manage()->find($rid)) {
            $this->res_storage->consume()->download($identification)->run();
        }
    }
}
