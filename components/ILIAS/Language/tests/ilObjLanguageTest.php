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
 * Regression coverage for ilObjLanguage's absolute_path computation.
 *
 * Constructing a real ilObjLanguage needs a full ilObject/DB bootstrap, so
 * this inspects the constructor's "../" expression directly instead - it is
 * a plain string of directory-traversal segments with no other moving
 * parts, so pinning it (and independently confirming it lands on the real
 * ILIAS root) is a cheap, reliable way to guard the exact regression that
 * slipped in here: components/ILIAS/Language/classes/ is four levels below
 * the ILIAS root, but the constructor used a fifth "../", pointing one
 * directory too high. With that bug, the "Main" language directory (lang/)
 * could never be found, so ilObjLanguage::check() (and therefore refresh()
 * and removeLocalChanges()) reported every installed language - including a
 * perfectly valid one - as invalid.
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
}
