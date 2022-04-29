<?php declare(strict_types=1);

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

use PHPUnit\Framework\TestCase;

/**
 * Test peer reviews
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class RatingCategoryTest extends TestCase
{
    protected function tearDown() : void
    {
    }

    /**
     * Test if each rater has $num_assignments peers
     */
    public function testRatingCategoryProperties() : void
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
