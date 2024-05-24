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

use ILIAS\UI\Implementation\Component as I;
use PHPUnit\Framework\TestCase;
use ILIAS\UI\Factory;
use ILIAS\UI\Renderer;

class CommentUITest extends TestCase
{
    protected function buildAvatarFactory(): I\Symbol\Avatar\Factory
    {
        return new I\Symbol\Avatar\Factory();
    }

    public function getCommentDefault(): CommentUI
    {
        $af = $this->buildAvatarFactory();
        $avatar = $af->letter("name");
        $comment_ui = new CommentUI(
            $this->createMock(\ilLfDebatePlugin::class),
            "initial",
            $avatar,
            "name",
            "2024-01-01 00:00:00",
            "2024-01-01 01:00:00",
            "title",
            "text",
            "",
            false,
            -1,
            $this->getMockBuilder(\ilLanguage::class)->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder(Factory::class)->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder(Renderer::class)->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder(\ilTemplate::class)->disableOriginalConstructor()->getMock(),
        );

        return $comment_ui;
    }

    public function testConstruct(): void
    {
        $this->assertInstanceOf(CommentUI::class, $this->getCommentDefault());
        $this->assertInstanceOf(PostingUI::class, $this->getCommentDefault());
        $this->assertNotInstanceOf(CommentLightUI::class, $this->getCommentDefault());
        $this->assertNotInstanceOf(PostingLightUI::class, $this->getCommentDefault());
    }
}
