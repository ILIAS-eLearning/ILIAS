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
 *********************************************************************/
 
require_once(__DIR__ . "/../../../../../libs/composer/vendor/autoload.php");
require_once(__DIR__ . "/../../../Base.php");

use ILIAS\UI\Implementation\Component\Input\Field\Input;
use ILIAS\UI\Implementation\Component\Input\NameSource;
use ILIAS\UI\Implementation\Component\Input\InputData;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\Data\Result;
use ILIAS\Refinery\Constraint;
use ILIAS\Refinery\Factory as Refinery;

class DefInput extends Input
{
    public bool $value_ok = true;
    public ?Constraint $requirement_constraint = null;

    protected function isClientSideValueOk($value) : bool
    {
        return $this->value_ok;
    }

    protected function getConstraintForRequirement() : ?Constraint
    {
        return $this->requirement_constraint;
    }

    public function getUpdateOnLoadCode() : Closure
    {
        return function () : void {
        };
    }
}

class DefNamesource implements NameSource
{
    public int $count = 0;

    public function getNewName() : string
    {
        $name = "name_{$this->count}";
        $this->count++;

        return $name;
    }
}

class DefInputData implements InputData
{
    public array $values = array();

    public function __construct(array $values)
    {
        $this->values = $values;
    }

    /**
     * @ineritdoc
     */
    public function get(string $name)
    {
        if (!is_string($name)) {
            throw new InvalidArgumentException('$name is no string.');
        }
        if (!isset($this->values[$name])) {
            throw new LogicException("'$name' does not exist.");
        }

        return $this->values[$name];
    }

    /**
     * @ineritdoc
     */
    public function getOr(string $name, $default)
    {
        if (!is_string($name)) {
            throw new InvalidArgumentException('$name is no string.');
        }
        if (!isset($this->values[$name])) {
            return $default;
        }

        return $this->values[$name];
    }
}

/**
 * Test on input implementation.
 */
class InputTest extends ILIAS_UI_TestBase
{
    protected DataFactory $data_factory;
    protected Refinery $refinery;
    protected DefInput $input;
    protected DefNamesource $name_source;

    public function setUp() : void
    {
        $this->data_factory = new DataFactory();
        $language = $this->createMock(ilLanguage::class);
        $this->refinery = new Refinery($this->data_factory, $language);
        $this->input = new DefInput(
            $this->data_factory,
            $this->refinery,
            "label",
            "byline"
        );
        $this->name_source = new DefNamesource();
    }

    public function test_constructor() : void
    {
        $this->assertEquals("label", $this->input->getLabel());
        $this->assertEquals("byline", $this->input->getByline());
    }

    public function test_withLabel() : void
    {
        $label = "new label";
        $input = $this->input->withLabel($label);
        $this->assertEquals($label, $input->getLabel());
        $this->assertNotSame($this->input, $input);
    }

    public function test_withByline() : void
    {
        $byline = "new byline";
        $input = $this->input->withByline($byline);
        $this->assertEquals($byline, $input->getByline());
        $this->assertNotSame($this->input, $input);
    }

    public function test_withRequired() : void
    {
        $this->assertFalse($this->input->isRequired());
        $input = $this->input->withRequired(true);
        $this->assertTrue($input->isRequired());
        $input = $input->withRequired(false);
        $this->assertFalse($input->isRequired());
    }

    public function test_withDisabled() : void
    {
        $this->assertFalse($this->input->isDisabled());
        $input = $this->input->withDisabled(true);
        $this->assertTrue($input->isDisabled());
        $input = $input->withDisabled(false);
        $this->assertFalse($input->isDisabled());
    }

    public function test_withValue() : void
    {
        $value = "some value";
        $input = $this->input->withValue($value);
        $this->assertEquals(null, $this->input->getValue());
        $this->assertEquals($value, $input->getValue());
        $this->assertNotSame($this->input, $input);
    }

    public function test_withValue_throws() : void
    {
        $this->input->value_ok = false;
        $raised = false;
        try {
            $this->input->withValue("foo");
            $this->assertFalse("This should not happen.");
        } catch (InvalidArgumentException $e) {
            $raised = true;
        }
        $this->assertTrue($raised);
        $this->assertEquals(null, $this->input->getValue());
    }

    public function test_withName() : void
    {
        $name = "name_0";
        $input = $this->input->withNameFrom($this->name_source);
        $this->assertEquals(null, $this->input->getName());
        $this->assertEquals($name, $input->getName());
        $this->assertNotSame($this->input, $input);
        $this->assertEquals(1, $this->name_source->count);
    }

    public function test_withError() : void
    {
        $error = "error";
        $input = $this->input->withError($error);
        $this->assertEquals(null, $this->input->getError());
        $this->assertEquals($error, $input->getError());
        $this->assertNotSame($this->input, $input);
    }

    public function test_getContent() : void
    {
        $this->expectException(LogicException::class);

        $this->input->getContent();
    }

    public function test_withInput() : void
    {
        $name = "name_0";
        $value = "valu";
        $input = $this->input->withNameFrom($this->name_source);
        $values = new DefInputData([$name => $value]);

        $input2 = $input->withInput($values);
        $res = $input2->getContent();

        $this->assertInstanceOf(Result::class, $res);
        $this->assertTrue($res->isOk());
        $this->assertEquals($value, $res->value());

        $this->assertNotSame($input, $input2);
        $this->assertEquals($value, $input2->getValue());
    }

    public function test_only_run_withInput_with_name() : void
    {
        $raised = false;
        try {
            $this->input->withInput(new DefInputData([]));
            $this->assertFalse("This should not happen.");
        } catch (LogicException $e) {
            $raised = true;
        }
        $this->assertTrue($raised);
    }

    public function test_withInput_and_transformation() : void
    {
        $name = "name_0";
        $value = "value";
        $transform_to = "other value";
        $input = $this->input->withNameFrom($this->name_source);
        $values = new DefInputData([$name => $value]);

        $input2 = $input->withAdditionalTransformation(
            $this->refinery->custom()->transformation(function ($v) use ($value, $transform_to) : string {
                $this->assertEquals($value, $v);
                return $transform_to;
            })
        )->withInput($values);

        $res = $input2->getContent();

        $this->assertInstanceOf(Result::class, $res);
        $this->assertTrue($res->isOk());
        $this->assertEquals($transform_to, $res->value());

        $this->assertNotSame($input, $input2);
        $this->assertEquals($value, $input2->getValue());
    }

    public function test_withInput_and_transformation_different_order() : void
    {
        $name = "name_0";
        $value = "value";
        $transform_to = "other value";
        $input = $this->input->withNameFrom($this->name_source);
        $values = new DefInputData([$name => $value]);

        $input2 = $input->withInput($values)->withAdditionalTransformation(
            $this->refinery->custom()->transformation(function ($v) use ($value, $transform_to) : string {
                $this->assertEquals($value, $v);
                return $transform_to;
            })
        );

        $res = $input2->getContent();

        $this->assertInstanceOf(Result::class, $res);
        $this->assertTrue($res->isOk());
        $this->assertEquals($transform_to, $res->value());

        $this->assertNotSame($input, $input2);
        $this->assertEquals($value, $input2->getValue());
    }

    public function test_withInput_and_constraint_successfull() : void
    {
        $name = "name_0";
        $value = "value";
        $error = "an error";
        $input = $this->input->withNameFrom($this->name_source);
        $values = new DefInputData([$name => $value]);

        $input2 = $input->withAdditionalTransformation($this->refinery->custom()->constraint(function () {
            return true;
        }, $error))->withInput($values);
        $res = $input2->getContent();

        $this->assertInstanceOf(Result::class, $res);
        $this->assertTrue($res->isOk());
        $this->assertEquals($value, $res->value());

        $this->assertNotSame($input, $input2);
        $this->assertEquals($value, $input2->getValue());
        $this->assertEquals(null, $input2->getError());
    }

    public function test_withInput_and_constraint_fails() : void
    {
        $name = "name_0";
        $value = "value";
        $error = "an error";
        $input = $this->input->withNameFrom($this->name_source);
        $values = new DefInputData([$name => $value]);

        $input2 = $input->withAdditionalTransformation($this->refinery->custom()->constraint(function () {
            return false;
        }, $error))->withInput($values);
        $res = $input2->getContent();

        $this->assertInstanceOf(Result::class, $res);
        $this->assertTrue($res->isError());
        $this->assertEquals($error, $res->error());

        $this->assertNotSame($input, $input2);
        $this->assertEquals($value, $input2->getValue());
        $this->assertEquals($error, $input2->getError());
    }

    public function test_withInput_and_constraint_fails_different_order() : void
    {
        $rc = $this->refinery->custom();

        $name = "name_0";
        $value = "value";
        $error = "an error";
        $input = $this->input->withNameFrom($this->name_source);
        $values = new DefInputData([$name => $value]);

        $input2 = $input
            ->withInput($values)
            ->withAdditionalTransformation($rc->constraint(function () {
                return false;
            }, $error));

        $res = $input2->getContent();

        $this->assertInstanceOf(Result::class, $res);
        $this->assertTrue($res->isError());
        $this->assertEquals($error, $res->error());

        $this->assertNotSame($input, $input2);
        $this->assertEquals($value, $input2->getValue());
        $this->assertEquals($error, $input2->getError());
    }

    public function test_withInput_transformation_and_constraint() : void
    {
        $name = "name_0";
        $value = "value";
        $transform_to = "other value";
        $error = "an error";
        $input = $this->input->withNameFrom($this->name_source);
        $values = new DefInputData([$name => $value]);

        $input2 = $input->withAdditionalTransformation(
            $this->refinery->custom()->transformation(function ($v) use ($value, $transform_to) : string {
                $this->assertEquals($value, $v);
                return $transform_to;
            })
        )->withAdditionalTransformation(
            $this->refinery->custom()->constraint(function ($v) use ($transform_to) : bool {
                $this->assertEquals($transform_to, $v);
                return true;
            }, $error)
        )->withInput($values);

        $res = $input2->getContent();

        $this->assertInstanceOf(Result::class, $res);
        $this->assertTrue($res->isOk());
        $this->assertEquals($transform_to, $res->value());

        $this->assertNotSame($input, $input2);
        $this->assertEquals($value, $input2->getValue());
        $this->assertEquals(null, $input2->getError());
    }

    public function test_withInput_transformation_and_constraint_different_order() : void
    {
        $name = "name_0";
        $value = "value";
        $transform_to = "other value";
        $error = "an error";
        $input = $this->input->withNameFrom($this->name_source);
        $values = new DefInputData([$name => $value]);

        $input2 = $input->withInput($values)->withAdditionalTransformation(
            $this->refinery->custom()->transformation(function ($v) use ($value, $transform_to) : string {
                $this->assertEquals($value, $v);
                return $transform_to;
            })
        )->withAdditionalTransformation(
            $this->refinery->custom()->constraint(function ($v) use ($transform_to) : bool {
                $this->assertEquals($transform_to, $v);
                return true;
            }, $error)
        );

        $res = $input2->getContent();

        $this->assertInstanceOf(Result::class, $res);
        $this->assertTrue($res->isOk());
        $this->assertEquals($transform_to, $res->value());

        $this->assertNotSame($input, $input2);
        $this->assertEquals($value, $input2->getValue());
        $this->assertEquals(null, $input2->getError());
    }

    public function test_withInput_constraint_and_transformation() : void
    {
        $name = "name_0";
        $value = "value";
        $transform_to = "other value";
        $error = "an error";
        $input = $this->input->withNameFrom($this->name_source);
        $values = new DefInputData([$name => $value]);

        $input2 = $input->withAdditionalTransformation(
            $this->refinery->custom()->constraint(function ($v) use ($value) : bool {
                $this->assertEquals($value, $v);
                return true;
            }, $error)
        )->withAdditionalTransformation(
            $this->refinery->custom()->transformation(function ($v) use ($value, $transform_to) : string {
                $this->assertEquals($value, $v);
                return $transform_to;
            })
        )->withInput($values);

        $res = $input2->getContent();

        $this->assertInstanceOf(Result::class, $res);
        $this->assertTrue($res->isOk());
        $this->assertEquals($transform_to, $res->value());

        $this->assertNotSame($input, $input2);
        $this->assertEquals($value, $input2->getValue());
        $this->assertEquals(null, $input2->getError());
    }

    public function test_withInput_constraint_fails_and_transformation() : void
    {
        $rc = $this->refinery->custom();

        $name = "name_0";
        $value = "value";
        $transform_to = "other value";
        $error = "an error";
        $input = $this->input->withNameFrom($this->name_source);
        $values = new DefInputData([$name => $value]);

        $input2 = $input
            ->withAdditionalTransformation($rc->constraint(function ($v) use ($value) : bool {
                $this->assertEquals($value, $v);

                return false;
            }, $error))
            ->withAdditionalTransformation($rc->transformation(function () use ($value, $transform_to) : string {
                $this->assertFalse("This should not happen");

                return $transform_to;
            }))->withInput($values);
        $res = $input2->getContent();

        $this->assertInstanceOf(Result::class, $res);
        $this->assertTrue($res->isError());
        $this->assertEquals($error, $res->error());

        $this->assertNotSame($input, $input2);
        $this->assertEquals($value, $input2->getValue());
        $this->assertEquals($error, $input2->getError());
    }

    public function test_withInput_constraint_fails_and_transformation_different_order() : void
    {
        $name = "name_0";
        $value = "value";
        $transform_to = "other value";
        $error = "an error";
        $input = $this->input->withNameFrom($this->name_source);
        $values = new DefInputData([$name => $value]);

        $input2 = $input->withInput($values)->withAdditionalTransformation(
            $this->refinery->custom()->constraint(function ($v) use ($value) : bool {
                $this->assertEquals($value, $v);
                return false;
            }, $error)
        )->withAdditionalTransformation(
            $this->refinery->custom()->transformation(function () use ($value, $transform_to) : string {
                $this->assertFalse("This should not happen");
                return $transform_to;
            })
        );

        $res = $input2->getContent();

        $this->assertInstanceOf(Result::class, $res);
        $this->assertTrue($res->isError());
        $this->assertEquals($error, $res->error());

        $this->assertNotSame($input, $input2);
        $this->assertEquals($value, $input2->getValue());
        $this->assertEquals($error, $input2->getError());
    }

    public function test_withInput_requirement_constraint() : void
    {
        $name = "name_0";
        $value = "value";
        $error = "an error";
        $input = $this->input->withNameFrom($this->name_source);
        $values = new DefInputData([$name => $value]);

        $input->requirement_constraint = $this->refinery->custom()->constraint(function () {
            return false;
        }, $error);

        $input2 = $input->withRequired(true)->withInput($values);
        $res = $input2->getContent();

        $this->assertInstanceOf(Result::class, $res);
        $this->assertTrue($res->isError());
        $this->assertEquals($error, $res->error());

        $this->assertNotSame($input, $input2);
        $this->assertEquals($value, $input2->getValue());
        $this->assertEquals($error, $input2->getError());
    }

    public function test_withInput_toggle_requirement() : void
    {
        $name = "name_0";
        $value = "value";
        $error = "an error";
        $input = $this->input->withNameFrom($this->name_source);
        $values = new DefInputData([$name => $value]);

        $input->requirement_constraint = $this->refinery->custom()->constraint(function () {
            return false;
        }, $error);

        $input2 = $input->withRequired(true)->withRequired(false)->withInput($values);
        $res = $input2->getContent();

        $this->assertInstanceOf(Result::class, $res);
        $this->assertFalse($res->isError());
        $this->assertEquals($value, $res->value());
    }
}
