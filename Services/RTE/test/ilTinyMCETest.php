<?php

declare(strict_types=1);

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

require_once __DIR__ . '/bootstrap.php';

/**
 * Class iTinyMCETest
 * @author Jephte Abijuru <jephte.abijuru@minervis.com>
 */
class ilTinyMCETest extends ilRTEBaseTest
{
    public function testAddPlugin(): void
    {
        $tinymce = new ilTinyMCE();
        $tinymce->addPlugin('a_new_test_plugin');
        $plugins = $tinymce->getPlugins();
        $this->assertContains('a_new_test_plugin', $plugins);
    }

    public function testTiny3xCodeHasbeenRemoved(): void
    {
        $this->assertDirectoryDoesNotExist('./Services/RTE/tiny_mce_3_4_7');
        $this->assertDirectoryDoesNotExist('./Services/RTE/tiny_mce_3_5_11');
    }

    public function testRemovePlugin(): void
    {
        $tinymce = new ilTinyMCE();
        $plugins_before_empty_removal = $tinymce->getPlugins();

        $tinymce->removePlugin('');//empty name for the plugin
        $this->assertEquals($plugins_before_empty_removal, $tinymce->getPlugins());
        $tinymce->removePlugin('link');
        $this->assertArrayNotHasKey('link', $tinymce->getPlugins());
    }
}
