<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/
class ilWebDAVCheckValidTitleTraitTest extends TestCase
{
    use ilWebDAVCheckValidTitleTrait;
    
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
    
    protected array $randomUnicodeStrings = [
        '斛翔簫輷㠋캍쵮읞㉡⚫ﴏ',
                'ੳ卵Ὃ죿퐥㿼㘩輔푬㳟宵錠◷⻨돁',
        '㿃㺝ᅴ㙂楳⦍텥鹰⍛合븺쑂瀎屴',
        '42342afafasfERf',
        'ADFsdf234df',
        'afas 234ADFASFD',
        '_23daf32DE簫'
    ];
    
    public function testDAVableTitleWithStringsOfValidCharactersReturnsTrue() : void
    {
        foreach ($this->randomUnicodeStrings as $filename) {
            $this->assertTrue(
                $this->isDAVableObjTitle($filename)
            );
        }
    }
    
    public function testDAVableTitleWithForbiddenCharactersReturnsFalse() : void
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
    
    public function testDAVableTitleWithHiddenFileReturnsFalse() : void
    {
        foreach ($this->randomUnicodeStrings as $filename) {
            $this->assertFalse(
                $this->isDAVableObjTitle('.' . $filename)
            );
        }
    }
}
