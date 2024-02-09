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
use Leifos\Debate\Posting;
use Leifos\Debate\PostingLightUI;
use Leifos\Debate\PostingManager;
use Leifos\Debate\PostingUI;
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
    /**
     * @var UI\Component\Component[]
     */
    protected $ui_comps = [];

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
                "content", $this->txt("posting_overview"), $this->ctrl->getLinkTarget($this, "showAllPostings")
            );
        }

        $this->addInfoTab();

        if ($this->access_wrapper->canEditProperties()) {
            $this->tabs->addTab(
                "properties", $this->lng->txt("properties"), $this->ctrl->getLinkTarget($this, "editProperties")
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

        // inputs
        $title = $this->ui_fac->input()->field()->text($this->lng->txt("title"))
                                                ->withValue($object->getTitle())
                                                ->withRequired(true);

        $description = $this->ui_fac->input()->field()->textarea($this->lng->txt("description"))
                                                      ->withValue($object->getDescription());

        $online = $this->ui_fac->input()->field()->checkbox(
            $this->lng->txt("online")
        )->withValue($object->isOnline());

        // section
        $section_properties = $this->ui_fac->input()->field()->section(
            ["title" => $title,
             "description" => $description,
             "online" => $online],
            $this->txt("obj_xdbt")
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

                $this->tpl->setOnScreenMessage("success", $this->txt("saved_successfully"), true);
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
            $add_post_button->setCaption($this->txt("add_posting"), false);
            $add_post_button->setUrl($this->ctrl->getLinkTarget($this, "addPosting"));
            $this->toolbar->addButtonInstance($add_post_button);
        }

        $this->tabs->activateTab("content");

        $html = "";
        foreach ($this->posting_manager->getLatestTopPostings() as $top_posting) {
            $posting_ui = $this->getPostingUI($top_posting);
            $html .= $posting_ui->render();
        }
        // add modals
        $html .= $this->ui_ren->render($this->ui_comps);

        $this->tpl->setContent($html);
    }

    protected function getPostingUI(Posting $posting): PostingUI
    {
        /** @var ilLfDebatePlugin $dbt_plugin */
        $dbt_plugin = $this->plugin;
        $user = new ilObjUser($posting->getUserId());
        $name = $user->getPublicName();
        $avatar = $user->getAvatar();
        $first_post = $this->posting_manager->getPosting($posting->getId(), 0);
        $create_date = $first_post->getCreateDate();
        $last_edit = "";
        if ($posting->getVersion() !== 0) {
            $last_edit = $posting->getCreateDate();
        }
        $posting_ui = $this->gui->posting(
            $dbt_plugin,
            $posting->getType(),
            $avatar,
            $name,
            $create_date,
            $last_edit,
            $posting->getTitle(),
            $posting->getDescription()
        );

        $actions = $this->getActions($posting);
        $posting_ui = $posting_ui->withActions($actions);

        return $posting_ui;
    }

    protected function getModalPostingUI(Posting $posting): PostingLightUI
    {
        /** @var ilLfDebatePlugin $dbt_plugin */
        $dbt_plugin = $this->plugin;
        $posting_ui = $this->gui->postingLight(
            $dbt_plugin,
            $posting->getType(),
            $posting->getCreateDate(),
            $posting->getTitle(),
            $posting->getDescription()
        );

        return $posting_ui;
    }

    /**
     * @return UI\Component\Button\Shy[]
     */
    protected function getActions(Posting $top_posting): array
    {
        $actions = [];
        $this->ctrl->setParameterByClass("ildebatepostinggui", "post_id", $top_posting->getId());
        if ($this->access_wrapper->canReadPostings()) {
            $actions[] = $this->ui_fac->button()->shy(
                $this->lng->txt("open"),
                $this->ctrl->getLinkTargetByClass("ildebatepostinggui", "showPosting")
            );
        }
        $this->ctrl->clearParameterByClass("ildebatepostinggui", "post_id");
        $this->ctrl->setParameter($this, "post_id", $top_posting->getId());
        if ($this->access_wrapper->canEditPosting($top_posting)) {
            $actions[] = $this->ui_fac->button()->shy(
                $this->lng->txt("edit"),
                $this->ctrl->getLinkTarget($this, "editPosting")
            );
        }
        if ($this->access_wrapper->canReadPostingHistory($top_posting)
            && !empty($old_postings = $this->posting_manager->getOlderVersionsOfPosting($top_posting->getId()))
        ) {
            $modal_html = "";
            foreach ($old_postings as $posting) {
                $posting_ui = $this->getModalPostingUI($posting);
                $modal_html .= $posting_ui->render();
            }
            $modal = $this->ui_fac->modal()->roundtrip($this->txt("older_versions"), $this->ui_fac->legacy($modal_html));
            $this->ui_comps[] = $modal;
            $actions[] = $this->ui_fac->button()->shy($this->txt("show_older_versions"), "")
                                      ->withOnClick($modal->getShowSignal());
        }
        if ($this->access_wrapper->canDeletePostings()) {
            $item = $this->ui_fac->modal()->interruptiveItem((string) $top_posting->getId(), $top_posting->getTitle());
            $delete_modal = $this->ui_fac->modal()->interruptive(
                $this->txt("confirm_deletion"),
                $this->txt("confirm_deletion_posting"),
                $this->ctrl->getFormAction($this, "deletePosting")
            )->withAffectedItems([$item]);
            $this->ui_comps[] = $delete_modal;
            $actions[] = $this->ui_fac->button()->shy($this->lng->txt("delete"), "")
                                      ->withOnClick($delete_modal->getShowSignal());
        }
        $this->ctrl->clearParameterByClass(self::class, "post_id");

        return $actions;
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

        $section_title = $edit ? $this->txt("update_posting") : $this->txt("add_posting");
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
                        $posting,
                        $props["title"],
                        $props["description"]
                    );
                    $this->tpl->setOnScreenMessage("success", $this->txt("posting_updated"), true);
                } else {
                    $this->posting_manager->createTopPosting(
                        $props["title"],
                        $props["description"]
                    );
                    $this->tpl->setOnScreenMessage("success", $this->txt("posting_created"), true);
                }
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

    protected function deletePosting()
    {
        $posting_id = $this->gui->request()->getPostingId();
        if (!$this->access_wrapper->canDeletePostings()) {
            return;
        }
        $this->posting_manager->deleteTopPosting($posting_id);

        $this->tpl->setOnScreenMessage("success", $this->txt("posting_deleted"), true);
        $this->ctrl->redirect($this, "showAllPostings");
    }
}
