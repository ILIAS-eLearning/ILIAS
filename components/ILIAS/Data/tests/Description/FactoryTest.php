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

/**
 * @author  Richard Klees <richard.klees@concepts-and-training.de>
 */

namespace ILIAS\Data\Description;

use ILIAS\Data\Description\Factory;
use ILIAS\Data\Description\Description;
use ILIAS\Data\Description\DValue;
use ILIAS\Data\Description\ValueType;
use ILIAS\Data\Description\DList;
use ILIAS\Data\Description\DMap;
use ILIAS\Data\Description\DObject;
use PHPUnit\Framework\TestCase;

class FactoryTest extends TestCase
{
    protected Factory $f;
    protected \ILIAS\Data\Text\SimpleDocumentMarkdown $md;

    public function setUp(): void
    {
        $this->f = new Factory();
        $this->md = $this->createMock(\ILIAS\Data\Text\SimpleDocumentMarkdown::class);
    }

    public function testInt(): void
    {
        $value = $this->f->int($this->md);

        $this->assertInstanceOf(Description::class, $value);
        $this->assertInstanceOf(DValue::class, $value);
        $this->assertEquals(ValueType::INT, $value->getType());
    }

    public function testFloat(): void
    {
        $value = $this->f->float($this->md);

        $this->assertInstanceOf(Description::class, $value);
        $this->assertInstanceOf(DValue::class, $value);
        $this->assertEquals(ValueType::FLOAT, $value->getType());
    }

    public function testString(): void
    {
        $value = $this->f->string($this->md);

        $this->assertInstanceOf(Description::class, $value);
        $this->assertInstanceOf(DValue::class, $value);
        $this->assertEquals(ValueType::STRING, $value->getType());
    }

    public function testDateTime(): void
    {
        $value = $this->f->datetime($this->md);

        $this->assertInstanceOf(Description::class, $value);
        $this->assertInstanceOf(DValue::class, $value);
        $this->assertEquals(ValueType::DATETIME, $value->getType());
    }

    public function testBool(): void
    {
        $value = $this->f->bool($this->md);

        $this->assertInstanceOf(Description::class, $value);
        $this->assertInstanceOf(DValue::class, $value);
        $this->assertEquals(ValueType::BOOL, $value->getType());
    }

    public function testNull(): void
    {
        $value = $this->f->null($this->md);

        $this->assertInstanceOf(Description::class, $value);
        $this->assertInstanceOf(DValue::class, $value);
        $this->assertEquals(ValueType::NULL, $value->getType());
    }

    public function testList(): void
    {
        $value = $this->f->string($this->md);
        $list = $this->f->list($this->md, $value);

        $this->assertInstanceOf(Description::class, $list);
        $this->assertInstanceOf(DList::class, $list);
        $this->assertEquals($value, $list->getValueType());
    }

    public function testMap(): void
    {
        $key = $this->f->string($this->md);
        $value = $this->f->string($this->md);
        $map = $this->f->map($this->md, $key, $value);

        $this->assertInstanceOf(Description::class, $map);
        $this->assertInstanceOf(DMap::class, $map);
        $this->assertEquals($key, $map->getKeyType());
        $this->assertEquals($value, $map->getValueType());
    }

    public function testObject(): void
    {
        $f1_type = $this->f->int($this->md);
        $f1_name = "field1";
        $f2_type = $this->f->float($this->md);
        $f2_name = "field2";
        $object = $this->f->object($this->md, [$f1_name => $f1_type, $f2_name => $f2_type]);

        $this->assertInstanceOf(Description::class, $object);
        $this->assertInstanceOf(DObject::class, $object);
        [$f1, $f2] = iterator_to_array($object->getFields());
        $this->assertEquals($f1_name, $f1->getName());
        $this->assertEquals($f1_type, $f1->getType());
        $this->assertEquals($f2_name, $f2->getName());
        $this->assertEquals($f2_type, $f2->getType());
    }
}
