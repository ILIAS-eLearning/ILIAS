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
 * ilObjLanguage's absolute_path must be four "../" up from components/ILIAS/Language/classes/ (a fifth once made
 * check() report every language as invalid); the constructor's expression is inspected since a real object needs a DB.
 */
class ilObjLanguageTest extends TestCase
{
    public function testAbsolutePathExpressionResolvesToTheIliasRoot(): void
    {
        $file = (new ReflectionClass(ilObjLanguage::class))->getFileName();
        $source = file_get_contents($file);

        self::assertStringContainsString(
            '$this->absolute_path = (string) realpath(__DIR__ . "/../../../../");',
            $source,
            'ilObjLanguage::__construct() no longer builds absolute_path with the ' .
            'expected four "../" segments - update this test if the file moved, ' .
            'but otherwise this is the off-by-one regression this test guards against.'
        );

        $computed_root = realpath(dirname($file) . '/../../../../');
        self::assertNotFalse($computed_root, 'The computed absolute_path must resolve to a real directory.');
        self::assertFileExists(
            $computed_root . '/lang/ilias_en.lang',
            'absolute_path must resolve to the ILIAS root, where lang/ilias_xx.lang files ' .
            'live - otherwise check()/refresh()/removeLocalChanges() will report every ' .
            'language as invalid.'
        );
    }

    public function testCheckOfTheShippedFileOnlyAsksTheRepositoryForTheShippedLanguage(): void
    {
        $repository = $this->createMock(\ILIAS\Language\Setup\InstalledLanguageDatabaseRepository::class);
        $repository->expects($this->once())->method('checkShippedLanguage')->with('de')->willReturn(true);
        $repository->expects($this->never())->method('checkLanguage');
        $language = (new ReflectionClass(ilObjLanguage::class))->newInstanceWithoutConstructor();
        $reflected = new ReflectionObject($language);
        $reflected->getProperty('repository')->setValue($language, $repository);
        $reflected->getProperty('key')->setValue($language, 'de');

        $this->assertTrue($language->check(true));
    }
}
