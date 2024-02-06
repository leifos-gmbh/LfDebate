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

class PostingLightUI
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
     * @var string
     */
    protected $create_date = "";
    /**
     * @var string
     */
    protected $title = "";
    /**
     * @var string
     */
    protected $text = "";
    /**
     * @var \ilTemplate
     */
    protected $main_tpl;

    public function __construct(
        \ilLfDebatePlugin $plugin,
        string $type,
        string $create_date,
        string $title,
        string $text
    ) {
        global $DIC;

        $this->plugin = $plugin;
        $this->type = $type;
        $this->create_date = $create_date;
        $this->title = $title;
        $this->text = $text;

        $this->main_tpl = $DIC->ui()->mainTemplate();
    }

    public function render(): string
    {
        $this->main_tpl->addCss(
            "./Customizing/global/plugins/Services/Repository/RepositoryObject/LfDebate/css/debate.css"
        );
        $tpl = $this->plugin->getTemplate("tpl.debate_item_light.html", true, true);

        $this->fillHTML($tpl);

        return $tpl->get();
    }

    protected function fillHTML(\ilTemplate $tpl): void
    {
        $tpl->setVariable("TYPE", $this->type);
        $tpl->setVariable("DATE", $this->create_date);
        $tpl->setVariable("TITLE", $this->title);
        $tpl->setVariable("TEXT", $this->text);
    }
}
