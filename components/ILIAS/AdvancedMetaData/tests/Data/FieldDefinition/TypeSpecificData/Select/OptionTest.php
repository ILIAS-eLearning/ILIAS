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

namespace ILIAS\AdvancedMetaData\Data\FieldDefinition\TypeSpecificData\Select;

use PHPUnit\Framework\TestCase;
use ILIAS\AdvancedMetaData\Data\Exception;

class OptionTest extends TestCase
{
    public function testIsPersistedFalse(): void
    {
        $option = new OptionImplementation(5);
        $this->assertFalse($option->isPersisted());
    }

    public function testIsPersistedTrue(): void
    {
        $option = new OptionImplementation(5, 13);
        $this->assertTrue($option->isPersisted());
    }

    public function testContainsChangesInPosition(): void
    {
        $option = new OptionImplementation(1, 13);
        $option->setPosition(103);
        $this->assertTrue($option->containsChanges());
    }

    public function testHasTranslationInLanguageTrue(): void
    {
        $translation = new OptionTranslationImplementation('lang', '');
        $option = new OptionImplementation(5, 13, $translation);
        $this->assertTrue($option->hasTranslationInLanguage('lang'));
    }

    public function testHasTranslationInLanguageFalse(): void
    {
        $translation = new OptionTranslationImplementation('lang', '');
        $option = new OptionImplementation(5, 13, $translation);
        $this->assertFalse($option->hasTranslationInLanguage('other lang'));
    }

    public function testGetTranslationInLanguage(): void
    {
        $translation = new OptionTranslationImplementation('lang', '');
        $option = new OptionImplementation(5, 13, $translation);
        $this->assertSame(
            $translation,
            $option->getTranslationInLanguage('lang')
        );
    }

    public function testAddTranslation(): void
    {
        $option = new OptionImplementation(13);
        $translation = $option->addTranslation('lang');
        $this->assertSame(
            $translation,
            $option->getTranslations()->current()
        );
    }

    public function testAddTranslationDuplicateLanguageException(): void
    {
        $translation = new OptionTranslationImplementation('lang', '');
        $option = new OptionImplementation(5, 13, $translation);

        $this->expectException(Exception::class);
        $option->addTranslation('lang');
    }

    public function testRemoveTranslation(): void
    {
        $translation = new OptionTranslationImplementation('lang', '');
        $option = new OptionImplementation(5, 13, $translation);
        $option->removeTranslation('lang');
        $this->assertFalse($option->hasTranslationInLanguage('lang'));
    }

    public function testContainsChangesTranslationRemoved(): void
    {
        $translation = new OptionTranslationImplementation('lang', '', true);
        $option = new OptionImplementation(5, 13, $translation);
        $option->removeTranslation('lang');
        $this->assertTrue($option->containsChanges());
    }
}
