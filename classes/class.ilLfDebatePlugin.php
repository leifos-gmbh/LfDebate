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

include_once("./Services/Repository/classes/class.ilRepositoryObjectPlugin.php");

use ILIAS\DI\Container;
use Leifos\Debate\RepoFactory;
use Leifos\Debate\DataFactory;
use ILIAS\UI\Implementation\DebateUIRenderer;
use Leifos\Debate\DomainFactory;
use Leifos\Debate\GUIFactory;

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
        // TODO?                                     hier wahrscheinlich DB Tabellen löschen
    }

    public function allowCopy(): bool
    {
        return true;
    }

    public function data(): DataFactory
    {
        return self::$finstance["data"] ??
            self::$finstance["data"] = new DataFactory();
    }

    public function repo(): RepoFactory
    {
        global $DIC;

        return self::$finstance["repo"] ??
            self::$finstance["repo"] = new RepoFactory(
                $this->data(),
                $DIC->database()
            );
    }

    public function domain(): DomainFactory
    {
        global $DIC;

        return self::$finstance["domain"] ??
            self::$finstance["domain"] = new DomainFactory(
                $DIC,
                $this->data(),
                $this->repo(),
                $this
            );
    }

    public function gui(): GUIFactory
    {
        global $DIC;

        return self::$finstance["gui"] ??
            self::$finstance["gui"] = new GUIFactory(
                $DIC,
                $this->data(),
                $this->domain()
            );
    }

    public function exchangeUIRendererAfterInitialization(Container $dic): Closure
    {
        $renderer = $dic->raw('ui.renderer');

        if (!$this->isActive()) {
            return $renderer;
        }

        return function () use ($dic) {
            return new DebateUIRenderer($dic["ui.component_renderer_loader"], $dic);
        };
    }
}
