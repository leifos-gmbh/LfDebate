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
use ILIAS\UI\Component\Link;
use ILIAS\UI\Component\Symbol\Avatar\Avatar;

trait PostingRender
{
    /**
     * @var bool
     */
    protected $showpin;
    /**
     * @var string
     */
    protected $title_link;
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
     * @var string
     */
    protected $glyph = "";
    /**
     * @var Button\Shy
     */
    protected $actions = [];
    /**
     * @var Link\Standard[]
     */
    protected $attachments = [];
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

    public function withAttachments(array $attachments): self
    {
        foreach ($attachments as $attachment) {
            if (!$attachment instanceof Link\Standard) {
                throw new \InvalidArgumentException("Attachments must be instance of ILIAS\UI\Component\Link\Standard");
            }
        }
        $clone = clone($this);
        $clone->attachments = $attachments;
        return $clone;
    }

    public function getAttachments(): array
    {
        return $this->attachments;
    }

    protected function maybeSetAttachments(\ilTemplate $tpl): void
    {
        if (count($this->attachments) > 0) {
            $att_html = $this->ui_ren->render($this->ui_fac->listing()->unordered($this->attachments));
            $tpl->setCurrentBlock("attachments");
            $tpl->setVariable("ATTACHMENTS", $att_html);
            $tpl->parseCurrentBlock();
        }
    }
}
