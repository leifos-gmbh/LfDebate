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

use PHPUnit\Framework\TestCase;
use ILIAS\UI\Factory;
use ILIAS\UI\Renderer;

class PostingLightUITest extends TestCase
{
    public function getPostingLightDefault(): PostingLightUI
    {
        $posting_light_ui = new PostingLightUI(
            $this->createMock(\ilLfDebatePlugin::class),
            "initial",
            "2024-01-01 00:00:00",
            "title",
            "text",
            $this->getMockBuilder(Factory::class)->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder(Renderer::class)->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder(\ilTemplate::class)->disableOriginalConstructor()->getMock(),
        );

        return $posting_light_ui;
    }

    public function testConstruct(): void
    {
        $this->assertInstanceOf(PostingLightUI::class, $this->getPostingLightDefault());
        $this->assertNotInstanceOf(CommentLightUI::class, $this->getPostingLightDefault());
        $this->assertNotInstanceOf(PostingUI::class, $this->getPostingLightDefault());
        $this->assertNotInstanceOf(CommentUI::class, $this->getPostingLightDefault());
    }
}
