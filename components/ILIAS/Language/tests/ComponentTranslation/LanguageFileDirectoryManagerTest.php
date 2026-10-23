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

namespace ILIAS\Language\ComponentTranslation;

use ILIAS\Component\Component;
use PHPUnit\Framework\TestCase;

class LanguageFileDirectoryManagerTest extends TestCase
{
    public function testModulesOfOneComponentShareItsFolder(): void
    {
        $component = $this->anyComponent();

        $manager = new LanguageFileDirectoryManager(
            new CustomizingLanguageFileDirectory(),
            new MainLanguageFileDirectory(),
            new ComponentLanguageFileDirectory($component, 'adve'),
            new ComponentLanguageFileDirectory($component, 'copg')
        );

        $this->assertCount(3, iterator_to_array($manager->getDirectories(), false));
    }

    public function testTwoModulesMustNotShipTheSameFiles(): void
    {
        $component = $this->anyComponent();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('shipping the same files');
        new LanguageFileDirectoryManager(
            new CustomizingLanguageFileDirectory(),
            new ComponentLanguageFileDirectory($component, 'adve', 'lang/', 'ilias_%s'),
            new ComponentLanguageFileDirectory($component, 'copg', 'lang/', 'ilias_%s')
        );
    }

    public function testTheSamePrefixIsReportedAsSuch(): void
    {
        $component = $this->anyComponent();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('with the same prefix');
        new LanguageFileDirectoryManager(
            new CustomizingLanguageFileDirectory(),
            new ComponentLanguageFileDirectory($component, 'copg'),
            new ComponentLanguageFileDirectory($component, 'copg')
        );
    }

    /** The build warns about an invalid pattern, so it must not stop the manager from being built. */
    public function testADirectoryWithAnInvalidPatternIsLeftToTheBuild(): void
    {
        $invalid = new class () implements LanguageFileDirectory, NamesShippedLanguageFiles {
            public function getPrefix(): string
            {
                return 'broken';
            }

            public function getPath(): string
            {
                return 'components/ILIAS/Broken/lang/';
            }

            public function getSuffix(): string
            {
                return '';
            }

            public function isLocal(): bool
            {
                return false;
            }

            public function getShippedFileNamePattern(): string
            {
                return 'no-placeholder';
            }
        };

        $manager = new LanguageFileDirectoryManager(new CustomizingLanguageFileDirectory(), $invalid);

        $this->assertSame([$invalid], iterator_to_array($manager->getDirectories(), false));
    }

    private function anyComponent(): Component
    {
        return new class () implements Component {
            public function init(
                array|\ArrayAccess &$define,
                array|\ArrayAccess &$implement,
                array|\ArrayAccess &$use,
                array|\ArrayAccess &$contribute,
                array|\ArrayAccess &$seek,
                array|\ArrayAccess &$provide,
                array|\ArrayAccess &$pull,
                array|\ArrayAccess &$internal,
            ): void {
            }
        };
    }
}
