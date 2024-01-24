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

include_once("./Services/Repository/classes/class.ilObjectPluginAccess.php");

/**
 * @author Thomas Famula <famula@leifos.de>
 */
class ilObjLfDebateAccess extends ilObjectPluginAccess //implements ilConditionHandling            -> wofür ist das?
{

    /**
     * @inheritdoc
     */
    public function _checkAccess($a_cmd, $a_permission, $a_ref_id, $a_obj_id, $a_user_id = 0): bool
    {
        global $DIC;

        $ilUser = $DIC->user();
        $ilAccess = $DIC->access();

        if ($a_user_id === 0) {
            $a_user_id = $ilUser->getId();
        }

        switch ($a_permission) {
            case "read":
                if (!self::checkOnline((int) $a_obj_id) &&
                    !$ilAccess->checkAccessOfUser($a_user_id, "write", "", $a_ref_id)) {
                    return false;
                }
                break;
        }

        return true;
    }

    public static function checkOnline(int $id): bool
    {
        global $DIC;
        $ilDB = $DIC->database();

        $set = $ilDB->query(
            "SELECT is_online FROM xdbt_data " .
            " WHERE obj_id = " . $ilDB->quote($id, "integer")
        );
        $rec = $ilDB->fetchAssoc($set);
        return (bool) $rec["is_online"];
    }
}
