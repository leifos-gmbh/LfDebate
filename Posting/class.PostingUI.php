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
    public const TYPE_PRO = "pro";
    public const TYPE_CONTRA = "contra";
    public const TYPE_QUESTION = "question";
    public const TYPE_INITIAL = "initial";
    /**
     * @var \ilPlugin
     */
    protected $plugin;
    /**
     * @var string
     */
    protected $text;
    /**
     * @var string
     */
    protected $date;
    /**
     * @var string
     */
    protected $name;
    /**
     * @var Avatar
     */
    protected $avatar;

    /**
     * @var string
     */
    protected $type = "";
    /**
     * @var string
     */
    protected $title = "";

    /**
     * @var array
     */
    protected $actions = [];

    public function __construct(
        \ilPlugin $plugin,
        string $type,
        Avatar $avatar,
        string $name,
        string $date,
        string $title,
        string $text
    )
    {
        $this->avatar = $avatar;
        $this->name = $name;
        $this->date = $date;
        $this->title = $title;
        $this->text = $text;
        $this->type = $type;
        $this->plugin = $plugin;
    }

    public function withActions(array $actions):self
    {
        $clone = clone($this);
        $clone->actions = $actions;
        return $clone;
    }

    public function render() : string
    {
        global $DIC;

        $r = $DIC->ui()->renderer();
        $f = $DIC->ui()->factory();

        $DIC->ui()->mainTemplate()->addCss(
            "./Customizing/global/plugins/Services/Repository/RepositoryObject/LfDebate/css/debate.css");

        $tpl = $this->plugin->getTemplate(
            "tpl.debate_item.html"
            , true, true);


        $glyph = "";
        switch (trim($this->type)) {
            case "pro":
                $glyph = "glyphicon-circle-arrow-up debate-glyph-pro";
                break;
            case "contra":
                $glyph = "glyphicon-circle-arrow-down debate-glyph-contra";
                break;
            case "question":
                $glyph = "glyphicon-question-sign debate-glyph-question";
                break;
        }
        if ($glyph !== "") {
            $glyph = '<span class="glyphicon '. $glyph .'" aria-hidden="true"></span> ';
        }

        if (count($this->actions) > 0) {
            $action_html = "";
            foreach ($this->actions as $c) {
                if ($action_html !== "") {
                    $action_html .= trim($r->render($f->divider()->vertical()));
                }
                $action_html .= trim($r->render($c));
            }
            $tpl->setCurrentBlock("actions");
            $tpl->setVariable("ACTIONS", $action_html);
            $tpl->parseCurrentBlock();
        }

        $tpl->setVariable("NAME", $this->name);
        $tpl->setVariable("DATE", $this->date);
        $tpl->setVariable("TITLE", $glyph . $this->title);
        $tpl->setVariable("TEXT", $this->text);
        $tpl->setVariable("TYPE", $this->type);
        $tpl->setVariable("AVATAR",$r->render($this->avatar));

        return $tpl->get();
    }
}
