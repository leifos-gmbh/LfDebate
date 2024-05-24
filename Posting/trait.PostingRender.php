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
    protected \ilLfDebatePlugin $plugin;
    protected string $type = "";
    protected Avatar $avatar;
    protected string $name = "";
    protected string $create_date = "";
    protected string $last_edit = "";
    protected string $title = "";
    protected string $text = "";
    protected string $title_link;
    protected bool $showpin;
    protected int $comment_count = -1;
    protected string $glyph = "";
    /**
     * @var Button\Shy[]
     */
    protected array $actions = [];
    /**
     * @var Link\Standard[]
     */
    protected array $attachments = [];
    protected \ilLanguage $lng;
    protected \ILIAS\UI\Factory $ui_fac;
    protected \ILIAS\UI\Renderer $ui_ren;
    protected \ilGlobalTemplateInterface $main_tpl;

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
