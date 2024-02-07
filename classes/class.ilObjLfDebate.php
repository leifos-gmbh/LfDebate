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

include_once("./Services/Repository/classes/class.ilObjectPlugin.php");

/**
 * @author Thomas Famula <famula@leifos.de>
 */
class ilObjLfDebate extends ilObjectPlugin
{
    /**
     * @var bool
     */
    protected $online = false;

    public function __construct(int $a_ref_id = 0)
    {
        parent::__construct($a_ref_id);
    }

    final public function initType(): void
    {
        $this->setType(ilLfDebatePlugin::ID);
    }

    public function doCreate(): void
    {
        $ilDB = $this->db;

        $ilDB->manipulate("INSERT INTO xdbt_data " .
            "(obj_id, is_online) VALUES (" .
            $ilDB->quote($this->getId(), "integer") . "," .
            $ilDB->quote(0, "integer") .
            ")");
    }

    public function doRead(): void
    {
        $ilDB = $this->db;

        $set = $ilDB->query("SELECT * FROM xdbt_data " .
            " WHERE obj_id = " . $ilDB->quote($this->getId(), "integer")
        );
        while ($rec = $ilDB->fetchAssoc($set)) {
            $this->setOnline((bool) $rec["is_online"]);
        }
    }

    public function doUpdate(): void
    {
        $ilDB = $this->db;

        $ilDB->manipulate("UPDATE xdbt_data SET " .
            " is_online = " . $ilDB->quote($this->isOnline(), "integer") . " " .
            " WHERE obj_id = " . $ilDB->quote($this->getId(), "integer")
        );
    }

    public function beforeDelete(): bool
    {
        /** @var \Leifos\Debate\PostingManager $posting_manager */
        $posting_manager = $this->getPlugin()->domain()->posting($this->getId());

        $posting_manager->deleteAll();

        return parent::beforeDelete();
    }

    public function doDelete(): void
    {
        $ilDB = $this->db;

        $ilDB->manipulate("DELETE FROM xdbt_data WHERE " .
            " obj_id = " . $ilDB->quote($this->getId(), "integer")
        );
    }

    public function doCloneObject($new_obj, $a_target_id, $a_copy_id = null): void
    {
        $new_obj->setOnline($this->isOnline());
        $new_obj->update();
    }

    public function setOnline(bool $val): void
    {
        $this->online = $val;
    }

    public function isOnline(): bool
    {
        return $this->online;
    }
}
