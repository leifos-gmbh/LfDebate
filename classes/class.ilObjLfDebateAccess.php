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

/**
 * @author Thomas Famula <famula@leifos.de>
 */
class ilObjLfDebateAccess extends ilObjectPluginAccess //implements ilConditionHandling            // -> wofür ist das?
{
    /**
     * @inheritdoc
     */
    public function _checkAccess(string $cmd, string $permission, int $ref_id, int $obj_id, ?int $user_id = null): bool
    {
        global $DIC;

        $ilUser = $DIC->user();
        $ilAccess = $DIC->access();

        if ($user_id === 0) {
            $user_id = $ilUser->getId();
        }

        switch ($permission) {
            case "read":
                if (!self::checkOnline($obj_id) &&
                    !$ilAccess->checkAccessOfUser($user_id, "write", "", $ref_id)) {
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
