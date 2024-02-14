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

namespace Leifos\Debate;

use ILIAS\UI\Component\Symbol\Avatar\Avatar;

class PostingUI
{
    /**
     * @var \ilLfDebatePlugin
     */
    protected $plugin;
    /**
     * @var string
     */
    protected $type = "";
    /**
     * @var Avatar
     */
    protected $avatar;
    /**
     * @var string
     */
    protected $name = "";
    /**
     * @var string
     */
    protected $create_date = "";
    /**
     * @var string
     */
    protected $last_edit = "";
    /**
     * @var string
     */
    protected $title = "";
    /**
     * @var string
     */
    protected $text = "";
    /**
     * @var array
     */
    protected $actions = [];
    /**
     * @var string
     */
    protected $glyph = "";
    /**
     * @var \ilLanguage
     */
    protected $lng;
    /**
     * @var \ILIAS\UI\Factory
     */
    protected $ui_fac;
    /**
     * @var \ILIAS\UI\Renderer
     */
    protected $ui_ren;
    /**
     * @var \ilTemplate
     */
    protected $main_tpl;

    public function __construct(
        \ilLfDebatePlugin $plugin,
        string $type,
        Avatar $avatar,
        string $name,
        string $create_date,
        string $last_edit,
        string $title,
        string $text
    ) {
        global $DIC;

        $this->plugin = $plugin;
        $this->type = $type;
        $this->avatar = $avatar;
        $this->name = $name;
        $this->create_date = $create_date;
        $this->last_edit = $last_edit;
        $this->title = $title;
        $this->text = $text;

        $this->lng = $DIC->language();
        $this->ui_fac = $DIC->ui()->factory();
        $this->ui_ren = $DIC->ui()->renderer();
        $this->main_tpl = $DIC->ui()->mainTemplate();
    }

    public function withActions(array $actions): self
    {
        $clone = clone($this);
        $clone->actions = $actions;
        return $clone;
    }

    public function render(): string
    {
        $tpl = $this->plugin->getTemplate("tpl.debate_item.html", true, true);

        $this->fillHTML($tpl);
        $this->maybeSetActions($tpl);

        return $tpl->get();
    }

    protected function fillHTML(\ilTemplate $tpl): void
    {
        $tpl->setVariable("TYPE", $this->type);
        $tpl->setVariable("AVATAR", $this->ui_ren->render($this->avatar));
        $tpl->setVariable("NAME", $this->name);
        $tpl->setVariable("DATE", $this->create_date);
        if ($this->last_edit !== "") {
            $tpl->setCurrentBlock("edit");
            $tpl->setVariable("EDIT", $this->lng->txt("last_change") . " " . $this->last_edit);
            $tpl->parseCurrentBlock();
        }
        $tpl->setVariable("TITLE", $this->glyph . $this->title);
        $tpl->setVariable("TEXT", nl2br($this->text));
    }

    protected function maybeSetActions(\ilTemplate $tpl): void
    {
        if (count($this->actions) > 0) {
            $action_html = "";
            foreach ($this->actions as $c) {
                if ($action_html !== "") {
                    $action_html .= trim($this->ui_ren->render($this->ui_fac->divider()->vertical()));
                }
                $action_html .= trim($this->ui_ren->render($c));
            }
            $tpl->setCurrentBlock("actions");
            $tpl->setVariable("ACTIONS", $action_html);
            $tpl->parseCurrentBlock();
        }
    }
}
