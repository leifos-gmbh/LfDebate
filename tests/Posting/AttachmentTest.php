<?php

use PHPUnit\Framework\TestCase;
use Leifos\Debate\Attachment;

/**
 * @author Thomas Famula <famula@leifos.de>
 */
class AttachmentTest extends TestCase
{
    /**
     * @var Attachment
     */
    protected $attachment;

    protected function setUp(): void
    {
        parent::setUp();
        $this->attachment = new Attachment(
            123,
            321,
            "abc999",
            0
        );
    }

    public function testGetId(): void
    {
        $attachment = $this->attachment;
        $this->assertEquals(
            123,
            $attachment->getId()
        );
    }

    public function testGetPostingId(): void
    {
        $attachment = $this->attachment;
        $this->assertEquals(
            321,
            $attachment->getPostingId()
        );
    }

    public function testGetRid(): void
    {
        $attachment = $this->attachment;
        $this->assertEquals(
            "abc999",
            $attachment->getRid()
        );
    }

    public function testGetCreateVersion(): void
    {
        $attachment = $this->attachment;
        $this->assertEquals(
            0,
            $attachment->getCreateVersion()
        );
    }
}
