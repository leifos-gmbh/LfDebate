<?php

use PHPUnit\Framework\TestCase;
use Leifos\Debate\DomainFactory;
use Leifos\Debate\Sorting;

/**
 * @author Thomas Famula <famula@leifos.de>
 */
class SortingTest extends TestCase
{
    /**
     * @var Sorting
     */
    protected $sorting;

    protected function setUp(): void
    {
        parent::setUp();
        $this->sorting = new Sorting(
            $this->getMockBuilder(DomainFactory::class)->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder(ilObjLfDebate::class)->disableOriginalConstructor()->getMock()
        );
    }

    public function testCurrentSorting(): void
    {
        $sorting = $this->sorting;
        $sorting->setCurrentSorting(Sorting::CREATION_ASC);
        $this->assertEquals(
            Sorting::CREATION_ASC,
            $sorting->getCurrentSorting()
        );
    }
}
