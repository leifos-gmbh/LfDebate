<?php

namespace Leifos\Debate;

use ILIAS\Filesystem\Stream\FileStream;
use ILIAS\FileUpload\DTO\Metadata;
use ILIAS\FileUpload\DTO\ProcessingStatus;
use ILIAS\FileUpload\Processor\PreProcessor;

/**
 * @author Thomas Famula <famula@leifos.de>
 */
final class ValidExtensionsPreProcessor implements PreProcessor
{
    /**
     * @var string[]
     */
    private array $valid;

    /**
     * Valid extensions example:
     * ['jpg', 'pdf']
     *
     * @param string[] $valid
     *
     * @throws \InvalidArgumentException    Thrown if the supplied valid extensions are empty.
     */
    public function __construct(array $valid)
    {
        if (count($valid) === 0) {
            throw new \InvalidArgumentException("Valid extensions must not be empty.");
        }

        $this->valid = $valid;
    }

    /**
     * @inheritDoc
     */
    public function process(FileStream $stream, Metadata $metadata): ProcessingStatus
    {
        $file = pathinfo($metadata->getFilename());
        $file_extension = strtolower($file["extension"]);
        if (!in_array($file_extension, $this->valid)) {
            return new ProcessingStatus(ProcessingStatus::DENIED, "The file extension " . $file_extension . " is not allowed.");
        }

        return new ProcessingStatus(ProcessingStatus::OK, "Entity is allowed.");
    }
}
