<?php

include_once "./Services/Repository/classes/class.ilObjectPluginListGUI.php";

/**
 * @author Thomas Famula <famula@leifos.de>
 */
class ilObjLfDebateListGUI extends ilObjectPluginListGUI
{
    public function initType(): void
    {
        $this->setType(ilLfDebatePlugin::ID);
    }

    public function getGuiClass(): string
    {
        return "ilObjLfDebateGUI";
    }

    public function initCommands(): array
    {
        return
        [
            [
                "permission" => "read",
                "cmd" => "showContent",
                "default" => true
            ],
            [
                "permission" => "write",
                "cmd" => "editProperties",
                "txt" => $this->txt("edit"),
                "default" => false
            ]
        ];
    }

    public function getProperties(): array
    {
        $props = [];

        $this->plugin->includeClass("class.ilObjLfDebateAccess.php");
        if (!ilObjLfDebateAccess::checkOnline($this->obj_id)) {
            $props[] = [
                "alert" => true,
                "property" => $this->txt("status"),
                "value" => $this->txt("offline")
            ];
        }

        return $props;
    }
}
