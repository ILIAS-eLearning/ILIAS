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
    protected \ILIAS\Container\Content\ModeManager $manager;
    protected \ILIAS\Container\Content\ModeSessionRepository $mode_repo;

    protected function setUp(): void
    {
        parent::setUp();

        if (!defined('ANONYMOUS_USER_ID')) {
            define('ANONYMOUS_USER_ID', 13);
        }

        $this->mode_repo = new \ILIAS\Container\Content\ModeSessionRepository();
        $this->mode_repo->setContentMode();
        $this->manager = new \ILIAS\Container\Content\ModeManager(
            $this->createStub(\ilContainer::class),
            $this->mode_repo,
            $this->createStub(\ILIAS\Repository\Clipboard\ClipboardManager::class),
            1
        );
    }

    protected function tearDown(): void
    {
        $this->mode_repo->setContentMode();

        parent::tearDown();
    }

    /**
     * Test admin view
     */
    public function testAdminView(): void
    {
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
