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

use PHPUnit\Framework\TestCase;

/**
 * Test clipboard repository
 * @author Alexander Killing <killing@leifos.de>
 */
class FeedItemTest extends TestCase
{
    protected function tearDown(): void
    {
    }

    /**
     * Test get HTML return an array
     */
    public function testFeedItemProperties(): void
    {
        $feed_item = new ilFeedItem();

        $feed_item->setAbout("about");
        $this->assertEquals(
            "about",
            $feed_item->getAbout()
        );

        $feed_item->setDescription("desc");
        $this->assertEquals(
            "desc",
            $feed_item->getDescription()
        );

        $feed_item->setEnclosureLength(6);
        $this->assertEquals(
            6,
            $feed_item->getEnclosureLength()
        );

        $feed_item->setEnclosureType("etype");
        $this->assertEquals(
            "etype",
            $feed_item->getEnclosureType()
        );

        $feed_item->setEnclosureUrl("eurl");
        $this->assertEquals(
            "eurl",
            $feed_item->getEnclosureUrl()
        );
    }
}
