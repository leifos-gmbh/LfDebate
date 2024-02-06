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

class CommentUI extends PostingUI
{
    public const TYPE_INITIAL = "initial";
    public const TYPE_PRO = "pro";
    public const TYPE_CONTRA = "contra";
    public const TYPE_QUESTION = "question";
    public const TYPE_EXCLAMATION = "exclamation";
    /**
     * @var string
     */
    protected $sub_comments = "";

    public function withSubComments(string $sub_comments): self
    {
        $clone = clone($this);
        $clone->sub_comments = $sub_comments;
        return $clone;
    }

    public function render() : string
    {
        $this->main_tpl->addCss(
            "./Customizing/global/plugins/Services/Repository/RepositoryObject/LfDebate/css/debate.css"
        );
        $tpl = $this->plugin->getTemplate("tpl.debate_comment.html", true, true);

        $this->handleGlyph();
        $this->fillHTML($tpl);
        $this->maybeSetActions($tpl);
        $this->maybeSetSubComments($tpl);

        return $tpl->get();
    }

    protected function handleGlyph(): void
    {
        $glyph_type = "";
        switch (trim($this->type)) {
            case self::TYPE_PRO:
                $glyph_type = "glyphicon-circle-arrow-up debate-glyph-pro";
                break;
            case self::TYPE_CONTRA:
                $glyph_type = "glyphicon-circle-arrow-down debate-glyph-contra";
                break;
            case self::TYPE_QUESTION:
                $glyph_type = "glyphicon-question-sign debate-glyph-question";
                break;
            case self::TYPE_EXCLAMATION:
                $glyph_type = "glyphicon-exclamation-sign debate-glyph-exclamation";
                break;
        }
        if ($glyph_type !== "") {
            $this->glyph = '<span class="glyphicon '. $glyph_type .'" aria-hidden="true"></span> ';
        }
    }

    protected function maybeSetSubComments(\ilTemplate $tpl): void
    {
        if ($this->sub_comments !== "") {
            $tpl->setCurrentBlock("comments");
            $tpl->setVariable("COMMENTS", $this->sub_comments);
            $tpl->parseCurrentBlock();
        }
    }
}
