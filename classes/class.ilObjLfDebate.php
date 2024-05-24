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
class ilObjLfDebate extends ilObjectPlugin
{
    protected int $default_sortation = 0;
    protected bool $online = false;

    public function __construct(int $a_ref_id = 0)
    {
        parent::__construct($a_ref_id);
    }

    final protected function initType(): void
    {
        $this->setType(ilLfDebatePlugin::ID);
    }

    protected function doCreate(bool $clone_mode = false): void
    {
        $ilDB = $this->db;

        $ilDB->manipulate("INSERT INTO xdbt_data " .
            "(obj_id, is_online, default_sortation) VALUES (" .
            $ilDB->quote($this->getId(), "integer") . "," .
            $ilDB->quote(0, "integer") . "," .
            $ilDB->quote(1, "integer") .
            ")");
    }

    protected function doRead(): void
    {
        $ilDB = $this->db;

        $set = $ilDB->query("SELECT * FROM xdbt_data " .
            " WHERE obj_id = " . $ilDB->quote($this->getId(), "integer")
        );
        while ($rec = $ilDB->fetchAssoc($set)) {
            $this->setOnline((bool) $rec["is_online"]);
            $this->setDefaultSortation((int) $rec["default_sortation"]);
        }
    }

    protected function doUpdate(): void
    {
        $ilDB = $this->db;

        $ilDB->manipulate("UPDATE xdbt_data SET " .
            " is_online = " . $ilDB->quote($this->isOnline(), "integer") . ", " .
            " default_sortation = " . $ilDB->quote($this->getDefaultSortation(), "integer") . " " .
            " WHERE obj_id = " . $ilDB->quote($this->getId(), "integer")
        );
    }

    protected function beforeDelete(): bool
    {
        /** @var \Leifos\Debate\PostingManager $posting_manager */
        $posting_manager = $this->getPlugin()->domain()->posting($this->getId());

        $posting_manager->deleteAll();

        return parent::beforeDelete();
    }

    protected function doDelete(): void
    {
        $ilDB = $this->db;

        $ilDB->manipulate("DELETE FROM xdbt_data WHERE " .
            " obj_id = " . $ilDB->quote($this->getId(), "integer")
        );
    }

    protected function doCloneObject(ilObject2 $new_obj, int $a_target_id, ?int $a_copy_id = null): void
    {
        $new_obj->setOnline($this->isOnline());
        $new_obj->setDefaultSortation($this->getDefaultSortation());
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

    public function setDefaultSortation(int $a_val): void
    {
        $this->default_sortation = $a_val;
    }

    public function getDefaultSortation(): int
    {
        return $this->default_sortation;
    }

}
