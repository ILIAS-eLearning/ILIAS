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
use ILIAS\UI\Implementation\Component\ComponentHelper;
use ILIAS\UI\Component\Component;
use ILIAS\UI\Component\Test\TestComponent;

require_once("vendor/composer/vendor/autoload.php");

require_once(__DIR__ . "/../Renderer/TestComponent.php");

class ComponentMock implements Component
{
    use ComponentHelper;

    public function _checkArg(string $which, bool $check, string $message): void
    {
        $this->checkArg($which, $check, $message);
    }

    public function _checkStringArg(string $which, $value): void
    {
        $this->checkStringArg($which, $value);
    }

    public function _checkBoolArg(string $which, $value): void
    {
        $this->checkBoolArg($which, $value);
    }

    public function _checkArgInstanceOf(string $which, $value, string $class): void
    {
        $this->checkArgInstanceOf($which, $value, $class);
    }

    public function _checkArgIsElement(string $which, $value, array $array, string $name): void
    {
        $this->checkArgIsElement($which, $value, $array, $name);
    }

    public function _toArray($value): array
    {
        return $this->toArray($value);
    }

    public function _checkArgListElements(string $which, array &$value, $classes): void
    {
        $this->checkArgListElements($which, $value, $classes);
    }

    public function _checkArgList(string $which, array &$value, Closure $check, Closure $message): void
    {
        $this->checkArgList($which, $value, $check, $message);
    }

    public $sub_components = null;
    public $random_data;

    public function getSubComponents(): ?array
    {
        return $this->sub_components;
    }
}

class Class1
{
}

class Class2
{
}

class Class3
{
}

/**
 * @author	Richard Klees <richard.klees@concepts-and-training.de>
 */
class ComponentHelperTest extends TestCase
{
    protected ComponentMock $mock;

    public function setUp(): void
    {
        $this->mock = new ComponentMock();
    }

    public function testGetCanonicalName(): void
    {
        $c = new TestComponent("foo");
        $this->assertEquals("Test Component Test", $c->getCanonicalName());
    }

    #[\PHPUnit\Framework\Attributes\DoesNotPerformAssertions]
    public function testCheckArgOk(): void
    {
        $this->mock->_checkArg("some_arg", true, "some message");
    }

    public function testCheckArgNotOk(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Argument 'some_arg': some message");
        $this->mock->_checkArg("some_arg", false, "some message");
    }

    #[\PHPUnit\Framework\Attributes\DoesNotPerformAssertions]
    public function testCheckStringArgOk(): void
    {
        $this->mock->_checkStringArg("some_arg", "bar");
    }

    public function testCheckStringArgNotOk(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Argument 'some_arg': expected string, got integer '1'");
        $this->mock->_checkStringArg("some_arg", 1);
    }

    #[\PHPUnit\Framework\Attributes\DoesNotPerformAssertions]
    public function testCheckBoolArgOk(): void
    {
        $this->mock->_checkBoolArg("some_arg", true);
    }

    public function testCheckBoolArgNotOk(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Argument 'some_arg': expected bool, got integer '1'");
        $this->mock->_checkBoolArg("some_arg", 1);
    }

    #[\PHPUnit\Framework\Attributes\DoesNotPerformAssertions]
    public function testCheckArgInstanceofOk(): void
    {
        $this->mock->_checkArgInstanceOf("some_arg", $this->mock, ComponentMock::class);
    }

    public function testCheckArgInstanceofNotOk(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Argument 'some_arg': expected ComponentMock, got ComponentHelperTest");
        $this->mock->_checkArgInstanceOf("some_arg", $this, ComponentMock::class);
    }


    #[\PHPUnit\Framework\Attributes\DoesNotPerformAssertions]
    public function testCheckArgIsElementOk(): void
    {
        $this->mock->_checkArgIsElement("some_arg", "bar", array("foo", "bar"), "foobar");
    }

    public function testCheckStringArgIsElementNotOk(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Argument 'some_arg': expected foobar, got 'baz'");
        $this->mock->_checkArgIsElement("some_arg", "baz", array("foo", "bar"), "foobar");
    }

    public function testToArrayWithArray(): void
    {
        $foo = array("foo", "bar");
        $res = $this->mock->_toArray($foo);

        $this->assertEquals($foo, $res);
    }

    public function testToArrayWithInt(): void
    {
        $foo = 1;
        $res = $this->mock->_toArray($foo);
        $this->assertEquals(array($foo), $res);
    }

    #[\PHPUnit\Framework\Attributes\DoesNotPerformAssertions]
    public function testCheckArgListElementsOk(): void
    {
        $l = array(new Class1(), new Class1(), new Class1());
        $this->mock->_checkArgListElements("some_arg", $l, array("Class1"));
    }

    public function testCheckArgListElementsNoOk(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Argument 'some_arg': expected Class1, got Class2");
        $l = array(new Class1(), new Class1(), new Class2());
        $this->mock->_checkArgListElements("some_arg", $l, array("Class1"));
    }

    #[\PHPUnit\Framework\Attributes\DoesNotPerformAssertions]
    public function testCheckArgListElementsMultiClassOk(): void
    {
        $l = array(new Class1(), new Class2(), new Class1());
        $this->mock->_checkArgListElements("some_arg", $l, array("Class1", "Class2"));
    }

    public function testCheckArgListElementsMultiClassNotOk(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Argument 'some_arg': expected Class1, Class2, got Class3");
        $l = array(new Class1(), new Class2(), new Class3(), new Class2());
        $this->mock->_checkArgListElements("some_arg", $l, array("Class1", "Class2"));
    }

    #[\PHPUnit\Framework\Attributes\DoesNotPerformAssertions]
    public function testCheckArgListElementsStringOrIntOk(): void
    {
        $l = array(1, "foo");
        $this->mock->_checkArgListElements("some_arg", $l, array("string", "int"));
    }

    public function testCheckArgListElementsStringOrIntNotOk(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Argument 'some_arg': expected string, int, got Class1");
        $l = array(1, new Class1());
        $this->mock->_checkArgListElements("some_arg", $l, array("string", "int"));
    }

    #[\PHPUnit\Framework\Attributes\DoesNotPerformAssertions]
    public function testCheckArgListOk(): void
    {
        $l = array("a" => 1, "b" => 2, "c" => 3);
        $this->mock->_checkArgList("some_arg", $l, function ($k, $v) {
            return is_string($k) && is_int($v);
        }, function ($k, $v) {
            return "expected keys of type string and integer values, got ($k => $v)";
        });
    }

    public function testCheckArgListNotOk1(): void
    {
        $m = "expected keys of type string and integer values, got (4 => 3)";
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Argument 'some_arg': $m");
        $l = array("a" => 1, "b" => 2, 4 => 3);
        $this->mock->_checkArgList("some_arg", $l, function ($k, $v) {
            return is_string($k) && is_int($v);
        }, function ($k, $v) {
            return "expected keys of type string and integer values, got ($k => $v)";
        });
    }

    public function testCheckArgListNotOk2(): void
    {
        $m = "expected keys of type string and integer values, got (c => d)";
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Argument 'some_arg': $m");
        $l = array("a" => 1, "b" => 2, "c" => "d");
        $this->mock->_checkArgList("some_arg", $l, function ($k, $v) {
            return is_string($k) && is_int($v);
        }, function ($k, $v) {
            return "expected keys of type string and integer values, got ($k => $v)";
        });
    }

    public function testReduceWith()
    {
        $a = new ComponentMock();
        $a->random_data = "A";
        $b = new ComponentMock();
        $b->random_data = "B";
        $c = new ComponentMock();
        $c->random_data = "C";
        $c->sub_components = [$a, $b];

        $f = fn($c, $res) => [$c->random_data => $res];
        $res = $c->reduceWith($f);

        $this->assertEquals(
            ["C" =>
                [
                    ["A" => []],
                    ["B" => []],
                ]
            ],
            $res
        );
    }

    public function testReduceWithDoesNotModify()
    {
        $a = new ComponentMock();
        $a->random_data = "A";
        $b = new ComponentMock();
        $b->random_data = "B";
        $c = new ComponentMock();
        $c->random_data = "C";
        $c->sub_components = [$a, $b];

        $f = function ($c, $res) {
            $clone = clone $c;
            $clone->random_data = strtolower($c->random_data);
            $clone->sub_components = $res;
            return $clone;
        };
        $c2 = $c->reduceWith($f);

        [$a2, $b2] = $c2->sub_components;

        $this->assertNotEquals(spl_object_id($a), spl_object_id($a2));
        $this->assertNotEquals(spl_object_id($b), spl_object_id($b2));
        $this->assertNotEquals(spl_object_id($c), spl_object_id($c2));

        $this->assertEquals("A", $a->random_data);
        $this->assertEquals("B", $b->random_data);
        $this->assertEquals("C", $c->random_data);
        $this->assertEquals([$a, $b], $c->sub_components);

        $this->assertEquals("a", $a2->random_data);
        $this->assertEquals("b", $b2->random_data);
        $this->assertEquals("c", $c2->random_data);
    }

    public function testReduceWithSubStructureIsTransient()
    {
        $a = new ComponentMock();
        $a->random_data = "A";
        $b = new ComponentMock();
        $b->random_data = "B";
        $c = new ComponentMock();
        $c->random_data = "C";
        $c->sub_components = [$a, $b];

        $f = function ($c, $res) {
            return [$c, $c->random_data];
        };

        [$res, $_] = $c->reduceWith($f);
        $this->assertEquals([$a, $b], $res->sub_components);
    }
}
