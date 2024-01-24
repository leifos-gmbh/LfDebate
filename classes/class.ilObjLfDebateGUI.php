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

use Leifos\Debate\PostingManager;
use Leifos\Debate\GUIFactory;
use ILIAS\UI;
use ILIAS\UI\Component\Input\Container\Form\Form;
use Psr\Http\Message\ServerRequestInterface;

include_once("./Services/Repository/classes/class.ilObjectPluginGUI.php");
require_once("./Customizing/global/plugins/Services/Repository/RepositoryObject/LfDebate/classes/class.ilLfDebatePlugin.php");
require_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
require_once("./Services/Form/classes/class.ilTextInputGUI.php");
require_once("./Services/Form/classes/class.ilCheckboxInputGUI.php");
require_once("./Services/Form/classes/class.ilNonEditableValueGUI.php");

/**
 * @author Thomas Famula <famula@leifos.de>
 *
 * @ilCtrl_isCalledBy ilObjLfDebateGUI: ilRepositoryGUI, ilAdministrationGUI, ilObjPluginDispatchGUI
 * @ilCtrl_Calls      ilObjLfDebateGUI: ilPermissionGUI, ilInfoScreenGUI, ilObjectCopyGUI, ilCommonActionDispatcherGUI
 */
class ilObjLfDebateGUI extends ilObjectPluginGUI
{
    /**
     * @var PostingManager
     */
    protected $manager;

    /**
     * @var GUIFactory
     */
    protected $gui;

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

    public function __construct($a_ref_id = 0, $a_id_type = self::REPOSITORY_NODE_ID, $a_parent_node_id = 0)
    {
        global $DIC;

        parent::__construct($a_ref_id, $a_id_type, $a_parent_node_id);

        $this->ui_fac = $DIC->ui()->factory();
        $this->ui_ren = $DIC->ui()->renderer();
        $this->request = $DIC->http()->request();

        $plugin = new ilLfDebatePlugin();
        $this->gui = $plugin->gui();

        if ($this->object) {
            $this->manager = $plugin->domain()->posting($this->object->getId());
        }
    }

    protected function afterConstructor(): void
    {

    }

    public function executeCommand()
    {
        //$this->uiTest(); return;
        parent::executeCommand();
    }

    final public function getType(): string
    {
        return ilLfDebatePlugin::ID;
    }

    /**
     * Handles all commands of this class, centralizes permission checks
     * @throws ilObjectException
     */
    public function performCommand($cmd): void
    {
        switch ($cmd) {
            case "editProperties":
                $this->checkPermission("write");
                $this->$cmd();
                break;
            case "showAllPostings":
            default:
                $this->checkPermission("read");
                $this->$cmd();
                break;
        }
    }

    public function getAfterCreationCmd(): string
    {
        return "editProperties";
    }

    public function getStandardCmd(): string
    {
        return "showAllPostings";
    }

    public function setTabs(): void
    {
        if ($this->access->checkAccess("read", "", $this->object->getRefId())) {
            $this->tabs->addTab(
                "content", $this->txt("Beitragsübersicht"), $this->ctrl->getLinkTarget($this, "showAllPostings")
            );
        }

        $this->addInfoTab();

        if ($this->access->checkAccess("write", "", $this->object->getRefId())) {
            $this->tabs->addTab(
                "properties", $this->txt("properties"), $this->ctrl->getLinkTarget($this, "editProperties")
            );
        }

        $this->addPermissionTab();
        //$this->activateTab();
    }

    protected function editProperties(): void
    {
        /** @var ilObjLfDebate $object */
        $object = $this->object;

        $this->tabs->activateTab("properties");

        //inputs
        $title = $this->ui_fac->input()->field()->text($this->txt("title"))
                                                ->withValue($object->getTitle())
                                                ->withRequired(true);

        $description = $this->ui_fac->input()->field()->textarea($this->txt("description"))
                                                      ->withValue($object->getDescription());

        $online = $this->ui_fac->input()->field()->checkbox(
            $this->txt("online")
        )->withValue($object->isOnline());

        //section
        $section_properties = $this->ui_fac->input()->field()->section(
            ["title" => $title,
             "description" => $description,
             "online" => $online],
            $this->plugin->txt("obj_xdbt")
        );

        // form and form action handling
        $this->ctrl->setParameterByClass(
            "ilobjlfdebategui",
            "debate_props",
            "debate_props_config"
        );

        $form = $this->ui_fac->input()->container()->form()->standard(
            $this->ctrl->getFormAction($this, "editProperties"),
            ["section_props" => $section_properties]
        );

        if ($this->request->getMethod() === "POST"
            && $this->request->getQueryParams()["debate_props"] === "debate_props_config") {     //in GUIRequest aufnehmen
            $this->checkPermission("write");

            $form = $form->withRequest($this->request);
            $data = $form->getData();

            if (isset($data["section_props"]) && is_array($data["section_props"])) {
                $props = $data["section_props"];
                $object->setTitle($props["title"]);
                $object->setDescription($props["description"]);
                $object->setOnline((bool) $props["online"]);
                $object->update();

                $this->tpl->setOnScreenMessage("success", $this->txt("msg_object_modified"), true);
            } else {
                $this->tpl->setContent($this->ui_ren->render($form));
                return;
            }

            $this->ctrl->redirect($this, "editProperties");
        }

        $this->tpl->setContent($this->ui_ren->render([$form]));
    }

    protected function showAllPostings(): void
    {
        $add_post_button = ilLinkButton::getInstance();
        $add_post_button->setCaption("Beitrag hinzufügen");
        $add_post_button->setUrl($this->ctrl->getLinkTarget($this, "addPosting"));
        $this->toolbar->addButtonInstance($add_post_button);

        $this->tabs->activateTab("content");

        $html = "";
        foreach ($this->manager->getTopPostings() as $top_posting) {
            $avatar = ilObjUser::_getAvatar($top_posting->getUserId());
            $posting_ui = $this->gui->posting(
                $this->plugin,
                $top_posting->getType(),
                $avatar,
                ilObjUser::_lookupFullname($top_posting->getUserId()),
                $top_posting->getCreateDate(),
                $top_posting->getTitle(),
                $top_posting->getDescription(),
                //""
            );
            $this->ctrl->setParameter($this, "post_id", $top_posting->getId());
            $posting_ui = $posting_ui->withActions([
                $this->ui_fac->button()->shy("Öffnen", $this->ctrl->getLinkTarget($this, "showPosting")),
                $this->ui_fac->button()->shy("Bearbeiten", $this->ctrl->getLinkTarget($this, "editPosting")),
                $this->ui_fac->button()->shy("?Löschen?", "#")
            ]);
            //$this->ctrl->setParameter($this, "post_id", "");
            $this->ctrl->clearParameterByClass(self::class, "post_id");

            $html .= $posting_ui->render();
        }

        $this->tpl->setContent($html);
        //$this->tpl->printToStdout();
    }

    protected function showPosting(): void
    {
        $this->tabs->clearTargets();
        $this->tabs->setBackTarget(
            "Beitragsübersicht",
            $this->ctrl->getLinkTarget($this, "showAllPostings")
        );

        $posting_id = $this->gui->request()->getPostingId();
        //var_dump($posting_id); exit;
        $top_posting = $this->manager->getPosting($posting_id);

        $html = "";
        $avatar = ilObjUser::_getAvatar($top_posting->getUserId());
        $posting_ui = $this->gui->posting(
            $this->plugin,
            $top_posting->getType(),
            $avatar,
            ilObjUser::_lookupFullname($top_posting->getUserId()),
            $top_posting->getCreateDate(),
            $top_posting->getTitle(),
            $top_posting->getDescription(),
        //""
        );
        $this->ctrl->setParameter($this, "post_id", $top_posting->getId());
        $posting_ui = $posting_ui->withActions([
            $this->ui_fac->button()->shy("Bearbeiten", $this->ctrl->getLinkTarget($this, "editComment")),
            $this->ui_fac->button()->shy("?Löschen?", "#")
        ]);
        $this->ctrl->clearParameterByClass(self::class, "post_id");

        $html .= $posting_ui->render();

        $add_comment_button = $this->ui_fac->button()->standard("Kommentar hinzufügen (aktuell ohne Funktion)", "#");
        $html .= $this->ui_ren->render($add_comment_button);

        foreach ($this->manager->getCommentsOfTopPosting($posting_id) as $comment) {
            $avatar = ilObjUser::_getAvatar($comment->getUserId());
            $comments_ui = $this->gui->posting(
                $this->plugin,
                $comment->getType(),
                $avatar,
                ilObjUser::_lookupFullname($comment->getUserId()),
                $comment->getCreateDate(),
                $comment->getTitle(),
                $comment->getDescription(),
            //""
            );
            $this->ctrl->setParameter($this, "comment_id", $comment->getId());
            //$this->ctrl->setParameter($this, "post_id", $top_posting->getId());
            $comments_ui = $comments_ui->withActions([
                $this->ui_fac->button()->shy("Kommentar hinzufügen", $this->ctrl->getLinkTarget($this, "addComment")),
                $this->ui_fac->button()->shy("Bearbeiten", $this->ctrl->getLinkTarget($this, "editComment")),
                $this->ui_fac->button()->shy("?Löschen?", "#")
            ]);
            $this->ctrl->clearParameterByClass(self::class, "comment_id");
            //$this->ctrl->clearParameterByClass(self::class, "post_id");

            $html .= $comments_ui->render();
        }

        $this->tpl->setContent($html);
        //$this->tpl->printToStdout();
    }

    protected function addPosting(): void
    {
        $this->addOrEditPosting();
    }

    protected function editPosting(): void
    {
        $this->addOrEditPosting(true);
    }

    protected function addOrEditPosting(bool $edit = false, int $parent_id = 0): void
    {
        $this->tabs->clearTargets();
        if ($parent_id > 0) {
            $this->ctrl->setParameter($this, "post_id", $parent_id);
            $this->tabs->setBackTarget(
                $this->lng->txt("back"),
                $this->ctrl->getLinkTarget($this, "showPosting")
            );
            $this->ctrl->clearParameterByClass(self::class, "post_id");
        } else {
            $this->tabs->setBackTarget(
                $this->lng->txt("back"),
                $this->ctrl->getLinkTarget($this, "showAllPostings")
            );
        }
        $this->checkPermission("write");

        $this->tpl->setContent($this->ui_ren->render($this->initPostingForm($edit)));
    }

    protected function editComment()
    {
        $parent_id = $this->gui->request()->getPostingId();
        $this->addOrEditPosting(true, $parent_id);
    }

    protected function initPostingForm(bool $edit = false): Form
    {
        if ($edit) {
            $posting_id = $this->gui->request()->getPostingId();
            $posting = $this->manager->getPosting($posting_id);
        }

        $title = $this->ui_fac->input()->field()->text($this->lng->txt("title"))->withRequired(true);
        if ($edit) {
            $title = $title->withValue($posting->getTitle());
        }

        $description = $this->ui_fac->input()->field()->textarea($this->lng->txt("description"));
        if ($edit) {
            $description = $description->withValue($posting->getDescription());
        }

        $section_title = $edit ? $this->txt("update_posting") : $this->lng->txt("add_posting");
        $section = $this->ui_fac->input()->field()->section(
            ["title" => $title,
             "description" => $description],
            $section_title
        );

        if ($edit) {
            $this->ctrl->setParameter($this, "post_id", $posting_id);
            $form_action = $this->ctrl->getLinkTarget($this, "updatePosting");
            $this->ctrl->clearParameterByClass(self::class, "post_id");
        } else {
            $form_action = $this->ctrl->getLinkTarget($this, "createPosting");
        }

        return $this->ui_fac->input()->container()->form()->standard($form_action, ["props" => $section]);
    }

    protected function savePosting(bool $edit = false): void
    {
        $form = $this->initPostingForm();
        if ($this->request->getMethod() === "POST") {
            $form = $form->withRequest($this->request);
            $data = $form->getData();
            if (isset($data["props"]) && is_array($data["props"])) {
                $props = $data["props"];
                if ($edit) {
                    $posting_id = $this->gui->request()->getPostingId();
                    $posting = $this->manager->getPosting($posting_id);
                    $this->manager->editPosting(
                        $posting->getId(),
                        $props["title"],
                        $props["description"]
                    );
                } else {
                    $this->manager->createTopPosting(
                        $props["title"],
                        $props["description"]
                    );
                }

                $this->tpl->setOnScreenMessage("success", $this->lng->txt("msg_obj_modified"), true);
            } else {
                $this->tpl->setContent($this->ui_ren->render($form));
                $this->tabs->clearTargets();
                //if ($parent_id > 0) {
                //    $this->ctrl->setParameter($this, "post_id", $parent_id);
                //    $this->tabs->setBackTarget(
                //        $this->lng->txt("back"),
                //        $this->ctrl->getLinkTarget($this, "showPosting")
                //    );
                //    $this->ctrl->clearParameterByClass(self::class, "post_id");
                //} else {
                    $this->tabs->setBackTarget(
                        $this->lng->txt("back"),
                        $this->ctrl->getLinkTarget($this, "showAllPostings")
                    );
                //}
                return;
            }
        }
        if ($edit) {
            $this->ctrl->setParameter($this, "post_id", $posting_id);
            $this->ctrl->redirect($this, "showPosting");
            $this->ctrl->clearParameterByClass(self::class, "post_id");
        }
        $this->ctrl->redirect($this, "showAllPostings");
    }

    protected function createPosting(): void
    {
        $this->savePosting();
    }

    protected function updatePosting(): void
    {
        $this->savePosting(true);
    }
}
