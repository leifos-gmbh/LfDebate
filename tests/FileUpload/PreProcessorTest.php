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

use ILIAS\Filesystem\Stream\Streams;
use ILIAS\FileUpload\DTO\ProcessingStatus;
use ILIAS\FileUpload\DTO\Metadata;
use PHPUnit\Framework\TestCase;

class PreProcessorTest extends TestCase
{
    public function testConstruct(): void
    {
        $this->assertInstanceOf(ValidExtensionsPreProcessor::class, new ValidExtensionsPreProcessor(["dummy_extension"]));
    }

    public function testProcessAccepted(): void
    {
        $preProcessor = new ValidExtensionsPreProcessor(["png", "jpg", "gif"]);
        $stream = Streams::ofString("Dummy content");
        $metadata = new Metadata("dummy.jpg", $stream->getSize(), "image/jpg"); // Cannot be mocked because it is final.
        $file = pathinfo($metadata->getFilename());
        $file_extension = strtolower($file["extension"]);

        $result = $preProcessor->process($stream, $metadata);

        $this->assertInstanceOf(ProcessingStatus::class, $result);
        $this->assertSame(ProcessingStatus::OK, $result->getCode());
        $this->assertNotSame(ProcessingStatus::DENIED, $result->getCode());
        $this->assertSame("Entity is allowed.", $result->getMessage());
    }

    public function testProcessDenied(): void
    {
        $preProcessor = new ValidExtensionsPreProcessor(["png", "svg", "gif"]);
        $stream = Streams::ofString("Dummy content");
        $metadata = new Metadata("dummy.jpg", $stream->getSize(), "image/jpg"); // Cannot be mocked because it is final.
        $file = pathinfo($metadata->getFilename());
        $file_extension = strtolower($file["extension"]);

        $result = $preProcessor->process($stream, $metadata);

        $this->assertInstanceOf(ProcessingStatus::class, $result);
        $this->assertNotSame(ProcessingStatus::OK, $result->getCode());
        $this->assertSame(ProcessingStatus::DENIED, $result->getCode());
        $this->assertSame("The file extension " . $file_extension . " is not allowed.", $result->getMessage());
    }
}
