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

class CommentLightUITest extends TestCase
{
    protected function buildAvatarFactory(): I\Symbol\Avatar\Factory
    {
        return new I\Symbol\Avatar\Factory();
    }

    public function getCommentLightDefault(): CommentLightUI
    {
        $af = $this->buildAvatarFactory();
        $avatar = $af->letter("name");
        $comment_light_ui = new CommentLightUI(
            $this->createMock(\ilLfDebatePlugin::class),
            "initial",
            "2024-01-01 00:00:00",
            "title",
            "text",
            $this->getMockBuilder(Factory::class)->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder(Renderer::class)->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder(\ilTemplate::class)->disableOriginalConstructor()->getMock(),
        );

        return $comment_light_ui;
    }

    public function testConstruct(): void
    {
        $this->assertInstanceOf(CommentLightUI::class, $this->getCommentLightDefault());
        $this->assertInstanceOf(PostingLightUI::class, $this->getCommentLightDefault());
        $this->assertNotInstanceOf(CommentUI::class, $this->getCommentLightDefault());
        $this->assertNotInstanceOf(PostingUI::class, $this->getCommentLightDefault());
    }
}
