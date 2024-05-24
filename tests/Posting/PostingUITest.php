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

class PostingUITest extends TestCase
{
    protected function buildAvatarFactory(): I\Symbol\Avatar\Factory
    {
        return new I\Symbol\Avatar\Factory();
    }

    protected function buildButtonFactory(): I\Button\Factory
    {
        return new I\Button\Factory();
    }

    protected function buildLinkFactory(): I\Link\Factory
    {
        return new I\Link\Factory();
    }

    public function getPostingDefault(): PostingUI
    {
        $af = $this->buildAvatarFactory();
        $avatar = $af->letter("name");
        $posting_ui = new PostingUI(
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

        return $posting_ui;
    }

    public function testConstruct(): void
    {
        $this->assertInstanceOf(PostingUI::class, $this->getPostingDefault());
        $this->assertNotInstanceOf(CommentUI::class, $this->getPostingDefault());
        $this->assertNotInstanceOf(PostingLightUI::class, $this->getPostingDefault());
        $this->assertNotInstanceOf(CommentLightUI::class, $this->getPostingDefault());
    }

    public function testWithActions(): void
    {
        $bf = $this->buildButtonFactory();
        $shys = [$bf->shy("label1", ""), $bf->shy("label2", "")];
        $posting = $this->getPostingDefault();
        $posting_with_actions = $posting->withActions($shys);

        $this->assertEquals($shys, $posting_with_actions->getActions());
    }

    public function testWithInvalidActions(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $lf = $this->buildButtonFactory();
        $links = [$lf->standard("label1", ""), $lf->standard("label2", "")];
        $posting = $this->getPostingDefault();
        $posting_with_actions = $posting->withActions($links);
    }

    public function testWithAttachments(): void
    {
        $lf = $this->buildLinkFactory();
        $links = [$lf->standard("label1", ""), $lf->standard("label2", "")];
        $posting = $this->getPostingDefault();
        $posting_with_attachments = $posting->withAttachments($links);

        $this->assertEquals($links, $posting_with_attachments->getAttachments());
    }

    public function testWithInvalidAttachments(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $bf = $this->buildButtonFactory();
        $shys = [$bf->shy("label1", ""), $bf->shy("label2", "")];
        $posting = $this->getPostingDefault();
        $posting_with_attachments = $posting->withAttachments($shys);
    }
}
