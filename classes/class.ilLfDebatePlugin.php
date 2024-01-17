<?php

include_once("./Services/Repository/classes/class.ilRepositoryObjectPlugin.php");

use Leifos\Debate\RepoFactory;
use Leifos\Debate\DataFactory;

/**
 * @author Thomas Famula <famula@leifos.de>
 */
class ilLfDebatePlugin extends ilRepositoryObjectPlugin
{
    public const ID = "xdbt";
    protected static $finstance = [];

    public function getPluginName(): string
    {
        return "LfDebate";
    }

    protected function uninstallCustom(): void
    {
        // TODO?
    }

    public function allowCopy(): bool
    {
        return true;
    }

    protected function data() : DataFactory
    {
        global $DIC;
        return self::$finstance["data"] ??
            self::$finstance["data"] = new DataFactory();
    }

    protected function repo() : RepoFactory
    {
        global $DIC;
        return self::$finstance["repo"] ??
            self::$finstance["repo"] = new RepoFactory(
                $this->data(),
                $DIC->database()
            );
    }
}
