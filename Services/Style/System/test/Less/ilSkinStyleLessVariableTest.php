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

require_once('libs/composer/vendor/autoload.php');

use PHPUnit\Framework\TestCase;

class ilSkinStyleLessVariableTest extends TestCase
{
    public function testConstruct(): void
    {
        $variable = new ilSystemStyleLessVariable('name', 'value', 'comment', 'category_name', ['references_id']);
        $this->assertEquals('name', $variable->getName());
        $this->assertEquals('value', $variable->getValue());
        $this->assertEquals('comment', $variable->getComment());
        $this->assertEquals('category_name', $variable->getCategoryName());
        $this->assertEquals(['references_id'], $variable->getReferences());
    }

    public function testSetters(): void
    {
        $variable = new ilSystemStyleLessVariable('name', 'value', 'comment', 'category_name', ['references_id']);

        $variable->setName('newName');
        $variable->setValue('newValue');
        $variable->setComment('newComment');
        $variable->setCategoryName('new_category_name');
        $variable->setReferences(['new_references_id']);

        $this->assertEquals('newName', $variable->getName());
        $this->assertEquals('newValue', $variable->getValue());
        $this->assertEquals('newComment', $variable->getComment());
        $this->assertEquals('new_category_name', $variable->getCategoryName());
        $this->assertEquals(['new_references_id'], $variable->getReferences());
    }

    public function testIconFontPathUpdate(): void
    {
        $variable = new ilSystemStyleLessVariable('il-icon-font-path', 'value', 'comment', 'category_name', ['references_id']);

        $variable->setValue("\"../../node_modules/bootstrap/fonts/\"");
        $this->assertEquals("\"../../../../node_modules/bootstrap/fonts/\"", $variable->getValue());
    }

    public function testIconFontPathQuotation(): void
    {
        $variable = new ilSystemStyleLessVariable('il-icon-font-path', 'value', 'comment', 'category_name', ['references_id']);

        $variable->setValue("\"somePath\"");
        $this->assertEquals("\"somePath\"", $variable->getValue());

        $variable->setValue('somePath');
        $this->assertEquals("\"somePath\"", $variable->getValue());


        $variable->setValue("\"somePath");
        $this->assertEquals("\"somePath\"", $variable->getValue());


        $variable->setValue("somePath\"");
        $this->assertEquals("\"somePath\"", $variable->getValue());
    }

    public function testToString(): void
    {
        $variable = new ilSystemStyleLessVariable('name', 'value', 'comment', 'category_name', ['references_id']);
        $this->assertEquals("//** comment\n@name:\t\tvalue;\n", (string) $variable);
    }
}
