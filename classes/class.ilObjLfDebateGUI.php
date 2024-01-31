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

use Leifos\Debate\DebateAccess;
use Leifos\Debate\PostingManager;
use Leifos\Debate\GUIFactory;
use ILIAS\UI;
use ILIAS\UI\Component\Input\Container\Form\Form;
use Psr\Http\Message\ServerRequestInterface;

include_once("./Services/Repository/classes/class.ilObjectPluginGUI.php");
require_once("./Customizing/global/plugins/Services/Repository/RepositoryObject/LfDebate/classes/class.ilLfDebatePlugin.php");

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

        /** @var ilLfDebatePlugin $plugin */
        $plugin = $this->plugin;
        $this->gui = $plugin->gui();

        if ($this->object) {
            $this->posting_manager = $plugin->domain()->posting($this->object->getId());
            $this->access_wrapper = $plugin->domain()->accessWrapper((int) $this->object->getRefId());
        }
    }

    protected function afterConstructor(): void
    {

    }

    final public function getType(): string
    {
        return ilLfDebatePlugin::ID;
    }

    public function executeCommand(): void
    {
        $next_class = $this->ctrl->getNextClass($this);
        switch ($next_class) {
            case "ildebatepostinggui":
                $dbt_pos = new ilDebatePostingGUI($this->plugin, $this->object);
                $this->ctrl->setReturn($this, "showAllPostings");
                //$this->ctrl->setParameterByClass("ildebatepostinggui", "post_id", $this->gui->request()->getPostingId());
                $this->ctrl->saveParameterByClass("ildebatepostinggui", "post_id");
                //$this->ctrl->saveParameterByClass("ildebatepostinggui", "cmt_id");
                $this->ctrl->forwardCommand($dbt_pos);
                break;
            default:
                parent::executeCommand();
                break;
        }
    }

    /**
     * Handles all commands of this class, centralizes permission checks
     * @throws ilObjectException
     */
    public function performCommand($cmd): void
    {
        switch ($cmd) {
            case "editProperties":
                if (!$this->access_wrapper->canEditProperties()) {
                    return;
                }
                $this->$cmd();
                break;
            case "showAllPostings":
            default:
                if (!$this->access_wrapper->canReadPostings()) {
                    return;
                }
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
        if ($this->access_wrapper->canReadPostings()) {
            $this->tabs->addTab(
                "content", $this->txt("Beitragsübersicht"), $this->ctrl->getLinkTarget($this, "showAllPostings")
            );
        }

        $this->addInfoTab();

        if ($this->access_wrapper->canEditProperties()) {
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

        $form = $this->ui_fac->input()->container()->form()->standard(
            $this->ctrl->getFormAction($this, "editProperties"),
            ["section_props" => $section_properties]
        );

        if ($this->request->getMethod() === "POST") {
            if (!$this->access_wrapper->canEditProperties()) {
                return;
            }

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
        if ($this->access_wrapper->canAddPostings()) {
            $add_post_button = ilLinkButton::getInstance();
            $add_post_button->setCaption("Beitrag hinzufügen");
            $add_post_button->setUrl($this->ctrl->getLinkTarget($this, "addPosting"));
            $this->toolbar->addButtonInstance($add_post_button);
        }

        $this->tabs->activateTab("content");

        /** @var ilLfDebatePlugin $dbt_plugin */
        $dbt_plugin = $this->plugin;
        $html = "";
        foreach ($this->posting_manager->getTopPostings() as $top_posting) {
            $avatar = ilObjUser::_getAvatar($top_posting->getUserId());
            $posting_ui = $this->gui->posting(
                $dbt_plugin,
                $top_posting->getType(),
                $avatar,
                ilObjUser::_lookupFullname($top_posting->getUserId()),
                $top_posting->getCreateDate(),
                $top_posting->getTitle(),
                $top_posting->getDescription()
            );
            $actions = [];
            $this->ctrl->setParameterByClass("ildebatepostinggui", "post_id", $top_posting->getId());
            $actions[] = $this->ui_fac->button()->shy(
                "Öffnen",
                $this->ctrl->getLinkTargetByClass("ildebatepostinggui", "showPosting")
            );
            $this->ctrl->clearParameterByClass("ildebatepostinggui", "post_id");
            $this->ctrl->setParameter($this, "post_id", $top_posting->getId());
            if ($this->access_wrapper->canEditPosting($top_posting)) {
                $actions[] = $this->ui_fac->button()->shy(
                    "Bearbeiten",
                    $this->ctrl->getLinkTarget($this, "editPosting")
                );
            }
            if ($this->access_wrapper->canDeletePosting($top_posting) || $this->access_wrapper->canDeletePostings()) {
                $actions[] = $this->ui_fac->button()->shy(
                    "Löschen",
                    $this->ctrl->getLinkTarget($this, "confirmDeletePosting")
                );
            }
            $this->ctrl->clearParameterByClass(self::class, "post_id");

            $posting_ui = $posting_ui->withActions($actions);

            $html .= $posting_ui->render();
        }

        $this->tpl->setContent($html);
    }

    protected function addPosting(): void
    {
        if (!$this->access_wrapper->canAddPostings()) {
            return;
        }

        $this->addOrEditPosting();
    }

    protected function editPosting(): void
    {
        $posting_id = $this->gui->request()->getPostingId();
        $posting = $this->posting_manager->getPosting($posting_id);
        if (!$this->access_wrapper->canEditPosting($posting)) {
            return;
        }

        $this->addOrEditPosting(true);
    }

    protected function addOrEditPosting(bool $edit = false): void
    {
        $posting_id = $this->gui->request()->getPostingId();
        $posting_mode = $this->gui->request()->getPostingMode();
        $this->tabs->clearTargets();
        if ($posting_mode) {
            $this->ctrl->setParameterByClass("ildebatepostinggui", "post_id", $posting_id);
            $this->tabs->setBackTarget(
                $this->lng->txt("back"),
                $this->ctrl->getLinkTargetByClass("ildebatepostinggui", "showPosting")
            );
            $this->ctrl->clearParameterByClass("ildebatepostinggui", "post_id");
        } else {
            $this->tabs->setBackTarget(
                $this->lng->txt("back"),
                $this->ctrl->getLinkTarget($this, "showAllPostings")
            );
        }

        $this->tpl->setContent($this->ui_ren->render($this->initPostingForm($edit)));
    }

    protected function initPostingForm(bool $edit = false): Form
    {
        $posting_id = $this->gui->request()->getPostingId();
        $posting_mode = $this->gui->request()->getPostingMode();
        if ($edit) {
            $posting = $this->posting_manager->getPosting($posting_id);
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
            $this->ctrl->setParameter($this, "post_mode", $posting_mode);
            $form_action = $this->ctrl->getFormAction($this, "updatePosting");
            $this->ctrl->clearParameterByClass(self::class, "post_id");
            $this->ctrl->clearParameterByClass(self::class, "post_mode");
        } else {
            $form_action = $this->ctrl->getFormAction($this, "createPosting");
        }

        return $this->ui_fac->input()->container()->form()->standard($form_action, ["props" => $section]);
    }

    protected function createPosting(): void
    {
        if (!$this->access_wrapper->canAddPostings()) {
            return;
        }

        $this->savePosting();
    }

    protected function updatePosting(): void
    {
        $posting_id = $this->gui->request()->getPostingId();
        $posting = $this->posting_manager->getPosting($posting_id);
        if (!$this->access_wrapper->canEditPosting($posting)) {
            return;
        }

        $this->savePosting(true);
    }

    protected function savePosting(bool $edit = false): void
    {
        $form = $this->initPostingForm();
        $posting_id = $this->gui->request()->getPostingId();
        $posting_mode = $this->gui->request()->getPostingMode();
        if ($this->request->getMethod() === "POST") {
            $form = $form->withRequest($this->request);
            $data = $form->getData();
            if (isset($data["props"]) && is_array($data["props"])) {
                $props = $data["props"];
                if ($edit) {
                    $posting = $this->posting_manager->getPosting($posting_id);
                    $this->posting_manager->editPosting(
                        $posting->getId(),
                        $props["title"],
                        $props["description"]
                    );
                } else {
                    $this->posting_manager->createTopPosting(
                        $props["title"],
                        $props["description"]
                    );
                }

                $this->tpl->setOnScreenMessage("success", $this->lng->txt("msg_obj_modified"), true);
            } else {
                $this->tpl->setContent($this->ui_ren->render($form));
                $this->tabs->clearTargets();
                if ($posting_mode) {
                    $this->ctrl->setParameterByClass("ildebatepostinggui", "post_id", $posting_id);
                    $this->tabs->setBackTarget(
                        $this->lng->txt("back"),
                        $this->ctrl->getLinkTargetByClass("ildebatepostinggui", "showPosting")
                    );
                    $this->ctrl->clearParameterByClass("ildebatepostinggui", "post_id");
                } else {
                    $this->tabs->setBackTarget(
                        $this->lng->txt("back"),
                        $this->ctrl->getLinkTarget($this, "showAllPostings")
                    );
                }
                return;
            }
        }
        if ($posting_mode) {
            $this->ctrl->setParameterByClass("ildebatepostinggui", "post_id", $posting_id);
            $this->ctrl->redirectByClass("ildebatepostinggui", "showPosting");
            $this->ctrl->clearParameterByClass("ildebatepostinggui", "post_id");
        } else {
            $this->ctrl->redirect($this, "showAllPostings");
        }
    }

    protected function confirmDeletePosting()
    {
        $this->ctrl->redirect($this, "showAllPostings");   // zu implementieren
    }
}
