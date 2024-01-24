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
                "cmd" => "showAllPostings",
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
        if (!ilObjLfDebateAccess::checkOnline((int) $this->obj_id)) {
            $props[] = [
                "alert" => true,
                "property" => $this->txt("status"),
                "value" => $this->txt("offline")
            ];
        }

        return $props;
    }
}
