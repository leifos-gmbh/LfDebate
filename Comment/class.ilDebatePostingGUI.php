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

use Leifos\Debate\CommentUI;
use Leifos\Debate\DebateAccess;
use Leifos\Debate\GUIFactory;
use Leifos\Debate\Posting;
use Leifos\Debate\PostingManager;
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

    protected function returnToDebate()
    {
        $this->ctrl->redirectByClass("ilobjlfdebategui", "showAllPostings");
    }

    protected function showPosting(): void
    {
        $this->tabs->clearTargets();
        $this->tabs->setBackTarget(
            "Beitragsübersicht",
            $this->ctrl->getLinkTargetByClass("ilobjlfdebategui", "showAllPostings")
        );

        $html = "";
        $avatar = ilObjUser::_getAvatar($this->posting->getUserId());
        $posting_ui = $this->gui->posting(
            $this->dbt_plugin,
            $this->posting->getType(),
            $avatar,
            ilObjUser::_lookupFullname($this->posting->getUserId()),
            $this->posting->getCreateDate(),
            $this->posting->getTitle(),
            $this->posting->getDescription()
        );
        $actions = [];
        $this->ctrl->setParameterByClass("ilobjlfdebategui", "post_id", $this->posting->getId());
        $this->ctrl->setParameterByClass("ilobjlfdebategui", "post_mode", 1);
        if ($this->access_wrapper->canEditPosting($this->posting)) {
            $actions[] = $this->ui_fac->button()->shy(
                "Bearbeiten",
                $this->ctrl->getLinkTargetByClass("ilobjlfdebategui", "editPosting")
            );
        }
        if ($this->access_wrapper->canDeletePosting($this->posting) || $this->access_wrapper->canDeletePostings()) {
            $actions[] = $this->ui_fac->button()->shy(
                "Löschen",
                $this->ctrl->getLinkTargetByClass("ilobjlfdebategui", "confirmDeletePosting")
            );
        }
        $this->ctrl->clearParameterByClass("ilobjlfdebategui", "post_id");
        $this->ctrl->clearParameterByClass("ilobjlfdebategui", "post_mode");

        $posting_ui = $posting_ui->withActions($actions);
        $html .= $posting_ui->render();

        $add_comment_button = $this->ui_fac->button()->standard(
            "Kommentar hinzufügen",
            $this->ctrl->getLinkTarget($this, "addComment")
        );
        $html .= $this->ui_ren->render($add_comment_button);

        foreach ($this->posting_manager->getCommentsOfTopPosting($this->posting->getId()) as $comment) {
            $avatar = ilObjUser::_getAvatar($comment->getUserId());
            $comments_ui = $this->gui->comment(
                $this->dbt_plugin,
                $comment->getType(),
                $avatar,
                ilObjUser::_lookupFullname($comment->getUserId()),
                $comment->getCreateDate(),
                $comment->getTitle(),
                $comment->getDescription()
            );

            $actions = [];
            $this->ctrl->setParameter($this, "cmt_id", $comment->getId());
            if ($this->access_wrapper->canAddComments()) {
                $actions[] = $this->ui_fac->button()->shy(
                    "Kommentar hinzufügen",
                    $this->ctrl->getLinkTarget($this, "addComment")
                );
            }
            if ($this->access_wrapper->canEditPosting($comment)) {
                $actions[] = $this->ui_fac->button()->shy(
                    "Bearbeiten",
                    $this->ctrl->getLinkTarget($this, "editComment")
                );
            }
            if ($this->access_wrapper->canDeletePosting($comment) || $this->access_wrapper->canDeletePostings()) {
                $actions[] = $this->ui_fac->button()->shy(
                    "Löschen",
                    $this->ctrl->getLinkTarget($this, "confirmDeleteComment")
                );
            }
            $this->ctrl->clearParameterByClass(self::class, "cmt_id");
            $comments_ui = $comments_ui->withActions($actions);

            $sub_html = "";
            foreach ($this->posting_manager->getSubCommentsOfComment($comment->getId()) as $sub_comment) {
                $sub_avatar = ilObjUser::_getAvatar($sub_comment->getUserId());
                $sub_comments_ui = $this->gui->comment(
                    $this->dbt_plugin,
                    $sub_comment->getType(),
                    $sub_avatar,
                    ilObjUser::_lookupFullname($sub_comment->getUserId()),
                    $sub_comment->getCreateDate(),
                    $sub_comment->getTitle(),
                    $sub_comment->getDescription()
                );

                $sub_actions = [];
                $this->ctrl->setParameter($this, "cmt_id", $sub_comment->getId());
                if ($this->access_wrapper->canEditPosting($sub_comment)) {
                    $sub_actions[] = $this->ui_fac->button()->shy(
                        "Bearbeiten",
                        $this->ctrl->getLinkTarget($this, "editComment")
                    );
                }
                if ($this->access_wrapper->canDeletePosting($sub_comment) || $this->access_wrapper->canDeletePostings()) {
                    $sub_actions[] = $this->ui_fac->button()->shy(
                        "Löschen",
                        $this->ctrl->getLinkTarget($this, "confirmDeleteComment")
                    );
                }
                $this->ctrl->clearParameterByClass(self::class, "cmt_id");
                $sub_comments_ui = $sub_comments_ui->withActions($sub_actions);
                $sub_html .= $sub_comments_ui->render();
            }
            $comments_ui = $comments_ui->withSubComments($sub_html);

            $html .= $comments_ui->render();
        }

        $this->tpl->setContent($html);
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
            ->withOption(CommentUI::TYPE_INITIAL, $this->lng->txt("Neutral"))
            ->withOption(CommentUI::TYPE_PRO, $this->lng->txt("Zustimmung"))
            ->withOption(CommentUI::TYPE_CONTRA, $this->lng->txt("Ablehnung"))
            ->withOption(CommentUI::TYPE_QUESTION, $this->lng->txt("Rückfrage"))
            ->withOption(CommentUI::TYPE_EXCLAMATION, $this->lng->txt("Bestärkung"));
        if ($edit) {
            $type = $type->withValue($comment->getType());
        } else {
            $type = $type->withValue(CommentUI::TYPE_INITIAL);
        }

        $section_title = $edit ? $this->lng->txt("update_comment") : $this->lng->txt("add_comment");
        $section_inputs = ["title" => $title,
                           "description" => $description];
        if (!$edit) {
            $section_inputs["type"] = $type;
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
                        $comment->getId(),
                        $props["title"],
                        $props["description"]
                    );
                } else {
                    $parent_id = $comment_id ?: $this->posting->getId();
                    $this->posting_manager->createCommentPosting(
                        $parent_id,
                        $props["title"],
                        $props["description"],
                        $props["type"]
                    );
                }

                $this->tpl->setOnScreenMessage("success", $this->lng->txt("msg_obj_modified"), true);
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

    protected function confirmDeleteComment()
    {
        $this->ctrl->redirect($this, "showPosting");    // zu implementieren
    }
}
