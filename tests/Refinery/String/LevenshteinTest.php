<?php

namespace ILIAS\src\Refinery\String;

use ILIAS\Data\Factory;
use ILIAS\Refinery\String\Group;
use ILIAS\Tests\Refinery\TestCase;
use function PHPUnit\Framework\assertEquals;

class LevenshteinTest extends TestCase
{
    /**
     * @var  Group
     */
    private $group;

    public function setUp() : void
    {
        $dataFactory = new Factory();
        $language = $this->getMockBuilder('\ilLanguage')
            ->disableOriginalConstructor()
            ->getMock();

        $this->group = new Group($dataFactory, $language);
    }

    // Code paths
    public function testSizeReturn() {
        // arrange
        $transformation = $this->group->levenshteinDefault("book", 3);

        // assert
        assertEquals(-1.0, $transformation->transform("bookshelf"));
    }

    public function testExceedMaximumDistance() {
        // arrange
        $transformation = $this->group->levenshteinDefault("book", 1);

        // assert
        assertEquals(-1.0, $transformation->transform("back"));
    }
    
    public function testSuccessfulReturn() {
        // arrange
        $transformation = $this->group->levenshteinDefault("book", 1);

        // assert
        assertEquals(0.0, $transformation->transform("book"));
    }

    public function testNoMaximumDistance() {
        // arrange
        $transformation = $this->group->levenshteinDefault("book", 0);

        // assert
        assertEquals(2.0, $transformation->transform("back"));
    }

    // Numerical
    public function testCustomCostsMixed() {
        // arrange
        $transformation = $this->group->levenshteinCustom("back", 20, 2.0, 1.0, 1.5);

        // assert
        assertEquals(12, $transformation->transform("bookshelf"));
    }

    public function testCustomCostsInsert() {
        // arrange
        $transformation = $this->group->levenshteinCustom("book", 10, 2.0, 1.0, 1.5);

        // assert expected 2(s)
        assertEquals(2, $transformation->transform("books"));
    }

    public function testCustomCostsDeletion() {
        // arrange
        $transformation = $this->group->levenshteinCustom("bookshelf", 10, 2.0, 1.0, 1.5);

        // assert expected  shelf(7.5)
        assertEquals(7.5, $transformation->transform("book"));
    }

    public function testCustomCostsReplacement() {
        // arrange
        $transformation = $this->group->levenshteinCustom("bookshelf", 10, 2.0, 1.0, 1.5);

        // assert expected  (4.0)
        assertEquals(4.0, $transformation->transform("bookstore"));
    }
}