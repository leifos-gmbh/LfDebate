<?php

use PHPUnit\Framework\TestCase;
use Leifos\Debate\CommentUI;
use Leifos\Debate\Posting;

/**
 * @author Thomas Famula <famula@leifos.de>
 */
class PostingTest extends TestCase
{
    /**
     * @var Posting
     */
    protected $posting;

    protected function setUp(): void
    {
        parent::setUp();
        $this->posting = new Posting(
            111,
            222,
            333,
            "Dummy Title",
            "Dummy Description",
            CommentUI::TYPE_INITIAL,
            "2024-01-01 00:00:00",
            0
        );
    }

    public function testGetObjId(): void
    {
        $attachment = $this->posting;
        $this->assertEquals(
            111,
            $attachment->getObjId()
        );
    }

    public function testGetId(): void
    {
        $attachment = $this->posting;
        $this->assertEquals(
            222,
            $attachment->getId()
        );
    }

    public function testGetUserId(): void
    {
        $attachment = $this->posting;
        $this->assertEquals(
            333,
            $attachment->getUserId()
        );
    }

    public function testGetTitle(): void
    {
        $attachment = $this->posting;
        $this->assertEquals(
            "Dummy Title",
            $attachment->getTitle()
        );
    }

    public function testGetDescription(): void
    {
        $attachment = $this->posting;
        $this->assertEquals(
            "Dummy Description",
            $attachment->getDescription()
        );
    }

    public function testGetType(): void
    {
        $attachment = $this->posting;
        $this->assertEquals(
            CommentUI::TYPE_INITIAL,
            $attachment->getType()
        );
    }

    public function testGetCreateDate(): void
    {
        $attachment = $this->posting;
        $this->assertEquals(
            "2024-01-01 00:00:00",
            $attachment->getCreateDate()
        );
    }

    public function testGetVersion(): void
    {
        $attachment = $this->posting;
        $this->assertEquals(
            0,
            $attachment->getVersion()
        );
    }
}
