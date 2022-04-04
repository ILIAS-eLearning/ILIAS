<?php

use PHPUnit\Framework\TestCase;

/**
 * Test peer reviews
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class RatingCategoryTest extends TestCase
{
    //protected $backupGlobals = false;

    // PHP8-Review: Redundant method override
    protected function setUp() : void
    {
        parent::setUp();
    }

    protected function tearDown() : void
    {
    }

    /**
     * Test if each rater has $num_assignments peers
     */
    public function testRatingCategoryProperties()
    {
        $database = $this->getMockBuilder(ilDBInterface::class)->getMock();

        $rating_category = new ilRatingCategory(
            null,
            $database
        );

        $rating_category->setTitle("title");
        $rating_category->setDescription("description");
        $rating_category->setId(1);
        $rating_category->setParentId(2);
        $rating_category->setPosition(10);

        $this->assertEquals(
            "title",
            $rating_category->getTitle()
        );

        $this->assertEquals(
            "description",
            $rating_category->getDescription()
        );

        $this->assertEquals(
            1,
            $rating_category->getId()
        );

        $this->assertEquals(
            2,
            $rating_category->getParentId()
        );

        $this->assertEquals(
            10,
            $rating_category->getPosition()
        );
    }
}
