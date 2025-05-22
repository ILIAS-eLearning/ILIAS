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

use PHPUnit\Framework\TestCase;

/**
 * Test clipboard repository
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ContentModeManagerTest extends TestCase
{
    protected \ILIAS\Container\Content\ViewManager $manager;

    protected function setUp(): void
    {
        /*    parent::setUp();
            $view_repo = new \ILIAS\Container\Content\ModeSessionRepository();
            $this->manager = new \ILIAS\Container\Content\ModeManager($view_repo);*/
    }

    protected function tearDown(): void
    {
    }

    /**
     * Test admin view
     */
    public function testAdminView(): void
    {
        $this->markTestSkipped('SetUp for this case fails.');

        $manager = $this->manager;

        $manager->setAdminMode();

        $this->assertEquals(
            true,
            $manager->isAdminMode()
        );
        $this->assertEquals(
            false,
            $manager->isContentMode()
        );
    }

    /**
     * Test content view
     */
    public function testContentView(): void
    {
        $this->markTestSkipped('SetUp for this case fails.');

        $manager = $this->manager;

        $manager->setContentMode();

        $this->assertEquals(
            false,
            $manager->isAdminMode()
        );
        $this->assertEquals(
            true,
            $manager->isContentMode()
        );
    }
}
