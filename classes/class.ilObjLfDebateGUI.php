<?php

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
    protected function afterConstructor(): void
    {

    }

    protected function uiTest()
    {
        global $DIC;

        $f = $DIC->ui()->factory();
        $avatar = $f->symbol()->avatar()->letter("Test User");
        //$avatar = $f->symbol()->avatar()->picture("./templates/default/images/HeaderIcon.svg" ,"ILIAS");
        $posting_ui = new \Leifos\Debate\Posting\PostingUI(
            $this->plugin,
            \Leifos\Debate\Posting\PostingUI::TYPE_INITIAL,
            $avatar,
            "Test User",
            "12. Oct 2024",
            "First Posting",
            "This is the first posting",
            ""
        );
        $posting_ui = $posting_ui->withActions([
            $f->button()->shy("Edit", "#"),
            $f->button()->shy("Delete", "#")
        ]);
        $this->tpl->setContent($posting_ui->render());
        $this->tpl->printToStdout();
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
            case "updateProperties":
            case "saveProperties":
                $this->checkPermission("write");
                $this->$cmd();
                break;
            case "showContent":
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
        return "showContent";
    }

    public function setTabs(): void
    {
        $ilCtrl = $this->ctrl;
        $ilAccess = $this->access;

        if ($ilAccess->checkAccess("read", "", $this->object->getRefId())) {
            $this->tabs->addTab(
                "content", $this->txt("content"), $ilCtrl->getLinkTarget($this, "showContent")
            );
        }

        $this->addInfoTab();

        if ($ilAccess->checkAccess("write", "", $this->object->getRefId())) {
            $this->tabs->addTab(
                "properties", $this->txt("properties"), $ilCtrl->getLinkTarget($this, "editProperties")
            );
        }

        $this->addPermissionTab();
        //$this->activateTab();
    }

    protected function editProperties(): void
    {
        $this->tabs->activateTab("properties");
        $form = $this->initPropertiesForm();
        $this->addValuesToForm($form);
        $this->tpl->setContent($form->getHTML());
    }

    protected function initPropertiesForm(): ilPropertyFormGUI
    {
        $form = new ilPropertyFormGUI();
        $form->setTitle($this->plugin->txt("obj_xdbt"));

        $title = new ilTextInputGUI($this->txt("title"), "title");
        $title->setRequired(true);
        $form->addItem($title);

        $description = new ilTextInputGUI($this->txt("description"), "description");
        $form->addItem($description);

        $online = new ilCheckboxInputGUI($this->txt("online"), "online");
        $form->addItem($online);

        $form->setFormAction($this->ctrl->getFormAction($this, "saveProperties"));
        $form->addCommandButton("saveProperties", $this->txt("update"));

        return $form;
    }

    protected function addValuesToForm(ilPropertyFormGUI $form): void
    {
        /** @var ilObjLfDebate $object */
        $object = $this->object;
        $form->setValuesByArray([
            "title" => $object->getTitle(),
            "description" => $object->getDescription(),
            "online" => $object->isOnline(),
        ]);
    }

    protected function saveProperties(): void
    {
        $form = $this->initPropertiesForm();
        $form->setValuesByPost();
        if ($form->checkInput()) {
            /** @var ilObjLfDebate $object */
            $object = $this->object;
            $this->fillObject($object, $form);
            $object->update();
            ilUtil::sendSuccess($this->txt("msg_object_modified"), true);
            $this->ctrl->redirect($this, "editProperties");
        }
        $this->tpl->setContent($form->getHTML());
    }

    protected function showContent(): void
    {
        $ilCtrl = $this->ctrl;
        $ilToolbar = $this->toolbar;

        $ilToolbar->addButton("Beitrag hinzufügen", $ilCtrl->getLinkTarget($this, "addPost"));

        $this->tabs->activateTab("content");

        /** @var ilObjLfDebate $object */
        $object = $this->object;

        $form = new ilPropertyFormGUI();
        $form->setTitle($object->getTitle());

        $i = new ilNonEditableValueGUI($this->txt("title"));
        $i->setInfo($object->getTitle());
        $form->addItem($i);

        $i = new ilNonEditableValueGUI($this->txt("description"));
        $i->setInfo($object->getDescription());
        $form->addItem($i);

        $i = new ilNonEditableValueGUI($this->txt("online"));
        $i->setInfo($object->isOnline() ? "Online" : "Offline");
        $form->addItem($i);

        $this->tpl->setContent($form->getHTML());
    }

    protected function fillObject(ilObjLfDebate $object, ilPropertyFormGUI $form): void
    {
        $object->setTitle($form->getInput("title"));
        $object->setDescription($form->getInput("description"));
        $object->setOnline($form->getInput("online"));
    }

    protected function addPost(): void
    {
        $ilCtrl = $this->ctrl;

        ilUtil::sendInfo("Post created", true);
        $ilCtrl->redirect($this, "showContent");
    }
}
