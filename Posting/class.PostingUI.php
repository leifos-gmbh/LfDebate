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

use ILIAS\UI\Component\Button;
use ILIAS\UI\Component\Symbol\Avatar\Avatar;
use ILIAS\UI\Factory;
use ILIAS\UI\Renderer;

class PostingUI
{
    use PostingRender;

    public function __construct(
        \ilLfDebatePlugin $plugin,
        string $type,
        Avatar $avatar,
        string $name,
        string $create_date,
        string $last_edit,
        string $title,
        string $text,
        string $title_link = "",
        bool $showpin = false,
        int $comment_count = -1,
        ?\ilLanguage $lng = null,
        ?Factory $ui_fac = null,
        ?Renderer $ui_ren = null,
        ?\ilTemplate $main_tpl = null
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
        $this->title_link = $title_link;
        $this->showpin = $showpin;
        $this->comment_count = $comment_count;

        $this->lng = ($lng) ?: $DIC->language();
        $this->ui_fac = ($ui_fac) ?: $DIC->ui()->factory();
        $this->ui_ren = ($ui_ren) ?: $DIC->ui()->renderer();
        $this->main_tpl = ($main_tpl) ?: $DIC->ui()->mainTemplate();
    }

    public function withActions(array $actions): self
    {
        foreach ($actions as $action) {
            if (!$action instanceof Button\Shy) {
                throw new \InvalidArgumentException("Actions must be instance of ILIAS\UI\Component\Button\Shy");
            }
        }
        $clone = clone($this);
        $clone->actions = $actions;
        return $clone;
    }

    public function getActions(): array
    {
        return $this->actions;
    }

    public function render(): string
    {
        $tpl = $this->plugin->getTemplate("tpl.debate_item.html", true, true);

        $this->fillHTML($tpl);
        $this->maybeSetActions($tpl);
        $this->maybeSetAttachments($tpl);
        $this->maybeSetCommentCount($tpl);

        return $tpl->get();
    }

    protected function fillHTML(\ilTemplate $tpl): void
    {
        $tpl->setVariable("TYPE", $this->type);
        $tpl->setVariable("AVATAR", $this->ui_ren->render($this->avatar));
        $title = ($this->title_link === "")
            ? $this->glyph . $this->title
            : "<a href='" . $this->title_link . "'>" . $this->glyph . $this->title . "</a>";
        $tpl->setVariable("NAME", $this->name);
        $tpl->setVariable("DATE", $this->create_date);
        if ($this->last_edit !== "") {
            $tpl->setCurrentBlock("edit");
            $tpl->setVariable("EDIT", $this->lng->txt("last_change") . " " . $this->last_edit);
            $tpl->parseCurrentBlock();
        }
        $tpl->setVariable("TITLE", $title);
        $tpl->setVariable("TEXT", ($this->text));
        if ($this->showpin) {
            $tpl->setVariable("PIN", $this->ui_ren->render(
                $this->ui_fac->symbol()->glyph()->note()
            ));
        }
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

    protected function maybeSetCommentCount(\ilTemplate $tpl): void
    {
        if ($this->comment_count >= 0) {
            $comment_txt = $this->comment_count === 1 ? $this->plugin->txt("comment") : $this->plugin->txt("comments");
            $comment_cnt_html = $this->comment_count . " " . $comment_txt;
            $tpl->setCurrentBlock("comment_count");
            $tpl->setVariable("COMMENT_CNT", $comment_cnt_html);
            $tpl->parseCurrentBlock();
        }
    }
}
