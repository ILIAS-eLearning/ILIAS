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

class ilWebDAVCheckValidTitleTraitTest extends TestCase
{
    use ilWebDAVCheckValidTitleTrait;

    /**
     * @var string[]
     */
    protected array $notDavableCharacters = [
        '\\',
        '<',
        '>',
        '/',
        ':',
        '*',
        '?',
        '"',
        '|',
        '#'
    ];

    /**
     * @var string[]
     */
    protected array $randomUnicodeStrings = [
        '斛翔簫輷㠋캍쵮읞㉡⚫ﴏ',
        'ੳ卵Ὃ죿퐥㿼㘩輔푬㳟宵錠◷⻨돁',
        '㿃㺝ᅴ㙂楳⦍텥鹰⍛合븺쑂瀎屴',
        '42342afafasfERf',
        'ADFsdf234df',
        'afas 234ADFASFD',
        '_23daf32DE簫'
    ];

    public function testDAVableTitleWithStringsOfValidCharactersReturnsTrue(): void
    {
        foreach ($this->randomUnicodeStrings as $filename) {
            $this->assertTrue(
                $this->isDAVableObjTitle($filename)
            );
        }
    }

    public function testDAVableTitleWithForbiddenCharactersReturnsFalse(): void
    {
        foreach (str_split('\\<>/:*?"|#') as $forbidden_character) {
            $this->assertFalse(
                $this->isDAVableObjTitle(
                    $this->randomUnicodeStrings[array_rand($this->randomUnicodeStrings)]
                    . $forbidden_character
                    . $this->randomUnicodeStrings[array_rand($this->randomUnicodeStrings)]
                )
            );
        }
    }

    public function testDAVableTitleWithHiddenFileReturnsFalse(): void
    {
        foreach ($this->randomUnicodeStrings as $filename) {
            $this->assertFalse(
                $this->isDAVableObjTitle('.' . $filename)
            );
        }
    }
}
