<?php

include_once("./Services/Repository/classes/class.ilRepositoryObjectPlugin.php");

/**
 * @author Thomas Famula <famula@leifos.de>
 */
class ilLfDebatePlugin extends ilRepositoryObjectPlugin
{
    public const ID = "xdbt";

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

}
