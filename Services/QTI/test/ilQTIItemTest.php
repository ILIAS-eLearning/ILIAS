<?php declare(strict_types=1);

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
 ********************************************************************
 */

use PHPUnit\Framework\TestCase;

class ilQTIItemTest extends TestCase
{
    public function testConstruct() : void
    {
        $this->assertInstanceOf(ilQTIItem::class, new ilQTIItem());
    }

    public function testSetGetIdent() : void
    {
        $instance = new ilQTIItem();
        $instance->setIdent('Some input.');
        $this->assertEquals('Some input.', $instance->getIdent());
    }

    public function testSetGetTitle() : void
    {
        $instance = new ilQTIItem();
        $instance->setTitle('Some input.');
        $this->assertEquals('Some input.', $instance->getTitle());
    }

    public function testSetGetLabel() : void
    {
        $instance = new ilQTIItem();
        $instance->setLabel('Some input.');
        $this->assertEquals('Some input.', $instance->getLabel());
    }

    public function testSetGetXmllang() : void
    {
        $instance = new ilQTIItem();
        $instance->setXmllang('Some input.');
        $this->assertEquals('Some input.', $instance->getXmllang());
    }

    public function testSetGetComment() : void
    {
        $instance = new ilQTIItem();
        $instance->setComment('Some input.');
        $this->assertEquals('Some input.', $instance->getComment());
    }

    public function testSetGetIlias_version() : void
    {
        $instance = new ilQTIItem();
        $instance->setComment('ILIAS Version=8.0');
        $this->assertEquals('8.0', $instance->ilias_version);
    }

    public function testSetGetAuthor() : void
    {
        $instance = new ilQTIItem();
        $instance->setAuthor('Some input.');
        $this->assertEquals('Some input.', $instance->getAuthor());

        $instance->setComment('Author=Lukas Scharmer');
        $this->assertEquals('Lukas Scharmer', $instance->getAuthor());
    }

    public function testSetGetQuestiontype() : void
    {
        $instance = new ilQTIItem();
        $instance->setQuestiontype('Some input.');
        $this->assertEquals('Some input.', $instance->getQuestiontype());

        $instance->setComment('Questiontype=Abc');
        $this->assertEquals('Abc', $instance->getQuestionType());
    }

    public function testSetGetIliasSourceVersion() : void
    {
        $instance = new ilQTIItem();
        $instance->setIliasSourceVersion('Some input.');
        $this->assertEquals('Some input.', $instance->getIliasSourceVersion());
    }
}
