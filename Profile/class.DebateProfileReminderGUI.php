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

class DebateProfileReminderGUI
{
    /**
     * @var \Leifos\Debate\DomainFactory
     */
    protected $domain;
    /**
     * @var ilLfDebatePlugin
     */
    protected $plugin;

    /**
     * @var \Leifos\Debate\GUIFactory
     */
    protected $gui;

    public function __construct(
        \Leifos\Debate\DomainFactory $domain,
        \Leifos\Debate\GUIFactory $gui,
        \ilLfDebatePlugin $plugin) {
        $this->gui = $gui;
        $this->plugin = $plugin;
        $this->domain = $domain;
    }

    public function render() : string
    {
        $checker = $this->domain->profileChecker();
        if ($checker->hasPublicProfile()) {
            return "";
        }
        if ($checker->hasBeenReminded()) {
            return "";
        }

        $checker->setTimestamp();

        $f = $this->gui->ui()->factory();
        $r = $this->gui->ui()->renderer();
        $main_tpl = $this->gui->ui()->mainTemplate();
        $ctrl = $this->gui->ctrl();

        $modal = $f->modal()->interruptive(
            $this->plugin->txt("profile_reminder"),
            $this->plugin->txt("profile_reminder_long"),
            $ctrl->getLinkTargetByClass(ilObjLfDebateGUI::class, "openProfileSettings"))
            ->withActionButtonLabel("actButtonLabel");
        $signal = $modal->getShowSignal();

        $html = $r->render([$modal]);
        $html = str_replace("-actButtonLabel-", $this->plugin->txt("open_profile"), $html);
        $html = str_replace("<form ", "<form target='_blank' ", $html);

        $main_tpl->addOnloadCode(
            "$(document).trigger('" . $signal . "', {'id': '" . $signal . "','triggerer':$(this), 'options': JSON.parse('[]')});"
        );

        return $html;
    }
}
