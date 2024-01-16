<?php

include_once("./Services/Repository/classes/class.ilObjectPluginAccess.php");

/**
 * @author Thomas Famula <famula@leifos.de>
 */
class ilObjLfDebateAccess extends ilObjectPluginAccess //implements ilConditionHandling          -> wofür ist das?
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
                if (!self::checkOnline($a_obj_id) &&
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
            "SELECT is_online FROM rep_robj_xdbt_data " .
            " WHERE id = " . $ilDB->quote($id, "integer")
        );
        $rec = $ilDB->fetchAssoc($set);
        return (bool) $rec["is_online"];
    }
}
