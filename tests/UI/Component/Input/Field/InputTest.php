<?php

/* Copyright (c) 2017 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

require_once(__DIR__ . "/../../../../../libs/composer/vendor/autoload.php");
require_once(__DIR__ . "/../../../Base.php");

use \ILIAS\UI\Implementation\Component\Input\Field\Input;
use \ILIAS\UI\Implementation\Component\Input\NameSource;
use \ILIAS\UI\Implementation\Component\Input\InputData;
use \ILIAS\Data\Factory as DataFactory;
use \ILIAS\Data\Result;

class DefInput extends Input
{
    public $value_ok = true;


    protected function isClientSideValueOk($value) : bool
    {
        return $this->value_ok;
    }


    public $requirement_constraint = null;


    protected function getConstraintForRequirement()
    {
        return $this->requirement_constraint;
    }


    public function getUpdateOnLoadCode() : \Closure
    {
        return function () {
        };
    }
}

class DefNamesource implements NameSource
{
    public $count = 0;


    public function getNewName()
    {
        $name = "name_{$this->count}";
        $this->count++;

        return $name;
    }
}

class DefInputData implements InputData
{
    public $values = array();


    public function __construct(array $values)
    {
        $this->values = $values;
    }


    public function get($name)
    {
        if (!is_string($name)) {
            throw new \InvalidArgumentException('$name is no string.');
        }
        if (!isset($this->values[$name])) {
            throw new \LogicException("'$name' does not exist.");
        }

        return $this->values[$name];
    }


    public function getOr($name, $value)
    {
        if (!is_string($name)) {
            throw new \InvalidArgumentException('$name is no string.');
        }
        if (!isset($this->values[$name])) {
            return $value;
        }

        return $this->values[$name];
    }
}

/**
 * Test on input implementation.
 */
class InputTest extends ILIAS_UI_TestBase
{

    /**
     * @var \ILIAS\Refinery\Factory
     */
    private $refinery;

    public function setUp() : void
    {
        $this->data_factory = new DataFactory();
        $language = $this->createMock(\ilLanguage::class);
        $this->refinery = new \ILIAS\Refinery\Factory($this->data_factory, $language);
        $this->input = new DefInput(
            $this->data_factory,
            $this->refinery,
            "label",
            "byline"
        );
        $this->name_source = new DefNamesource();
    }


    public function test_constructor()
    {
        $this->assertEquals("label", $this->input->getLabel());
        $this->assertEquals("byline", $this->input->getByline());
    }


    public function test_withLabel()
    {
        $label = "new label";
        $input = $this->input->withLabel($label);
        $this->assertEquals($label, $input->getLabel());
        $this->assertNotSame($this->input, $input);
    }


    public function test_withByline()
    {
        $byline = "new byline";
        $input = $this->input->withByline($byline);
        $this->assertEquals($byline, $input->getByline());
        $this->assertNotSame($this->input, $input);
    }


    public function test_withRequired()
    {
        $this->assertFalse($this->input->isRequired());
        $input = $this->input->withRequired(true);
        $this->assertTrue($input->isRequired());
        $input = $input->withRequired(false);
        $this->assertFalse($input->isRequired());
    }


    public function test_withDisabled()
    {
        $this->assertFalse($this->input->isDisabled());
        $input = $this->input->withDisabled(true);
        $this->assertTrue($input->isDisabled());
        $input = $input->withDisabled(false);
        $this->assertFalse($input->isDisabled());
    }


    public function test_withValue()
    {
        $value = "some value";
        $input = $this->input->withValue($value);
        $this->assertEquals(null, $this->input->getValue());
        $this->assertEquals($value, $input->getValue());
        $this->assertNotSame($this->input, $input);
    }


    public function test_withValue_throws()
    {
        $this->input->value_ok = false;
        $raised = false;
        try {
            $this->input->withValue("foo");
            $this->assertFalse("This should not happen.");
        } catch (\InvalidArgumentException $e) {
            $raised = true;
        }
        $this->assertTrue($raised);
        $this->assertEquals(null, $this->input->getValue());
    }


    public function test_withName()
    {
        $name = "name_0";
        $input = $this->input->withNameFrom($this->name_source);
        $this->assertEquals(null, $this->input->getName());
        $this->assertEquals($name, $input->getName());
        $this->assertNotSame($this->input, $input);
        $this->assertEquals(1, $this->name_source->count);
    }


    public function test_withError()
    {
        $error = "error";
        $input = $this->input->withError($error);
        $this->assertEquals(null, $this->input->getError());
        $this->assertEquals($error, $input->getError());
        $this->assertNotSame($this->input, $input);
    }


    public function test_getContent()
    {
        $this->expectException(\LogicException::class);

        $this->input->getContent();
    }


    public function test_withInput()
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


    public function test_only_run_withInput_with_name()
    {
        $raised = false;
        try {
            $this->input->withInput(new DefInputData([]));
            $this->assertFalse("This should not happen.");
        } catch (\LogicException $e) {
            $raised = true;
        }
        $this->assertTrue($raised);
    }


    public function test_withInput_and_transformation()
    {
        $name = "name_0";
        $value = "value";
        $transform_to = "other value";
        $input = $this->input->withNameFrom($this->name_source);
        $values = new DefInputData([$name => $value]);

        $input2 = $input->withAdditionalTransformation($this->refinery->custom()->transformation(function ($v) use ($value, $transform_to) {
            $this->assertEquals($value, $v);

            return $transform_to;
        }))->withInput($values);
        $res = $input2->getContent();

        $this->assertInstanceOf(Result::class, $res);
        $this->assertTrue($res->isOk());
        $this->assertEquals($transform_to, $res->value());

        $this->assertNotSame($input, $input2);
        $this->assertEquals($value, $input2->getValue());
    }


    public function test_withInput_and_transformation_different_order()
    {
        $name = "name_0";
        $value = "value";
        $transform_to = "other value";
        $input = $this->input->withNameFrom($this->name_source);
        $values = new DefInputData([$name => $value]);

        $input2 = $input->withInput($values)->withAdditionalTransformation($this->refinery->custom()->transformation(function ($v) use (
            $value,
            $transform_to
        ) {
            $this->assertEquals($value, $v);

            return $transform_to;
        }));
        $res = $input2->getContent();

        $this->assertInstanceOf(Result::class, $res);
        $this->assertTrue($res->isOk());
        $this->assertEquals($transform_to, $res->value());

        $this->assertNotSame($input, $input2);
        $this->assertEquals($value, $input2->getValue());
    }


    public function test_withInput_and_constraint_successfull()
    {
        $name = "name_0";
        $value = "value";
        $error = "an error";
        $input = $this->input->withNameFrom($this->name_source);
        $values = new DefInputData([$name => $value]);

        $input2 = $input->withAdditionalTransformation($this->refinery->custom()->constraint(function ($_) {
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


    public function test_withInput_and_constraint_fails()
    {
        $name = "name_0";
        $value = "value";
        $error = "an error";
        $input = $this->input->withNameFrom($this->name_source);
        $values = new DefInputData([$name => $value]);

        $input2 = $input->withAdditionalTransformation($this->refinery->custom()->constraint(function ($_) {
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


    public function test_withInput_and_constraint_fails_different_order()
    {
        $rc = $this->refinery->custom();

        $name = "name_0";
        $value = "value";
        $error = "an error";
        $input = $this->input->withNameFrom($this->name_source);
        $values = new DefInputData([$name => $value]);

        $input2 = $input
            ->withInput($values)
            ->withAdditionalTransformation($rc->constraint(function ($_) {
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


    public function test_withInput_transformation_and_constraint()
    {
        $name = "name_0";
        $value = "value";
        $transform_to = "other value";
        $error = "an error";
        $input = $this->input->withNameFrom($this->name_source);
        $values = new DefInputData([$name => $value]);

        $input2 = $input->withAdditionalTransformation($this->refinery->custom()->transformation(function ($v) use ($value, $transform_to) {
            $this->assertEquals($value, $v);

            return $transform_to;
        }))->withAdditionalTransformation($this->refinery->custom()->constraint(function ($v) use ($transform_to) {
            $this->assertEquals($transform_to, $v);

            return true;
        }, $error))->withInput($values);
        $res = $input2->getContent();

        $this->assertInstanceOf(Result::class, $res);
        $this->assertTrue($res->isOk());
        $this->assertEquals($transform_to, $res->value());

        $this->assertNotSame($input, $input2);
        $this->assertEquals($value, $input2->getValue());
        $this->assertEquals(null, $input2->getError());
    }


    public function test_withInput_transformation_and_constraint_different_order()
    {
        $name = "name_0";
        $value = "value";
        $transform_to = "other value";
        $error = "an error";
        $input = $this->input->withNameFrom($this->name_source);
        $values = new DefInputData([$name => $value]);

        $input2 = $input->withInput($values)->withAdditionalTransformation($this->refinery->custom()->transformation(function ($v) use (
            $value,
            $transform_to
        ) {
            $this->assertEquals($value, $v);

            return $transform_to;
        }))->withAdditionalTransformation($this->refinery->custom()->constraint(function ($v) use ($transform_to) {
            $this->assertEquals($transform_to, $v);

            return true;
        }, $error));
        $res = $input2->getContent();

        $this->assertInstanceOf(Result::class, $res);
        $this->assertTrue($res->isOk());
        $this->assertEquals($transform_to, $res->value());

        $this->assertNotSame($input, $input2);
        $this->assertEquals($value, $input2->getValue());
        $this->assertEquals(null, $input2->getError());
    }


    public function test_withInput_constraint_and_transformation()
    {
        $name = "name_0";
        $value = "value";
        $transform_to = "other value";
        $error = "an error";
        $input = $this->input->withNameFrom($this->name_source);
        $values = new DefInputData([$name => $value]);

        $input2 = $input->withAdditionalTransformation($this->refinery->custom()->constraint(function ($v) use ($value) {
            $this->assertEquals($value, $v);

            return true;
        }, $error))->withAdditionalTransformation($this->refinery->custom()->transformation(function ($v) use ($value, $transform_to) {
            $this->assertEquals($value, $v);

            return $transform_to;
        }))->withInput($values);
        $res = $input2->getContent();

        $this->assertInstanceOf(Result::class, $res);
        $this->assertTrue($res->isOk());
        $this->assertEquals($transform_to, $res->value());

        $this->assertNotSame($input, $input2);
        $this->assertEquals($value, $input2->getValue());
        $this->assertEquals(null, $input2->getError());
    }


    public function test_withInput_constraint_fails_and_transformation()
    {
        $rc = $this->refinery->custom();

        $name = "name_0";
        $value = "value";
        $transform_to = "other value";
        $error = "an error";
        $input = $this->input->withNameFrom($this->name_source);
        $values = new DefInputData([$name => $value]);

        $input2 = $input
            ->withAdditionalTransformation($rc->constraint(function ($v) use ($value) {
                $this->assertEquals($value, $v);

                return false;
            }, $error))
            ->withAdditionalTransformation($rc->transformation(function ($v) use ($value, $transform_to) {
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


    public function test_withInput_constraint_fails_and_transformation_different_order()
    {
        $name = "name_0";
        $value = "value";
        $transform_to = "other value";
        $error = "an error";
        $input = $this->input->withNameFrom($this->name_source);
        $values = new DefInputData([$name => $value]);

        $input2 = $input->withInput($values)->withAdditionalTransformation($this->refinery->custom()->constraint(function ($v) use ($value) {
            $this->assertEquals($value, $v);

            return false;
        }, $error))->withAdditionalTransformation($this->refinery->custom()->transformation(function ($v) use ($value, $transform_to) {
            $this->assertFalse("This should not happen");

            return $transform_to;
        }));
        $res = $input2->getContent();

        $this->assertInstanceOf(Result::class, $res);
        $this->assertTrue($res->isError());
        $this->assertEquals($error, $res->error());

        $this->assertNotSame($input, $input2);
        $this->assertEquals($value, $input2->getValue());
        $this->assertEquals($error, $input2->getError());
    }


    public function test_withInput_requirement_constraint()
    {
        $name = "name_0";
        $value = "value";
        $error = "an error";
        $input = $this->input->withNameFrom($this->name_source);
        $values = new DefInputData([$name => $value]);

        $input->requirement_constraint = $this->refinery->custom()->constraint(function ($_) {
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


    public function test_withInput_toggle_requirement()
    {
        $name = "name_0";
        $value = "value";
        $error = "an error";
        $input = $this->input->withNameFrom($this->name_source);
        $values = new DefInputData([$name => $value]);

        $input->requirement_constraint = $this->refinery->custom()->constraint(function ($_) {
            return false;
        }, $error);

        $input2 = $input->withRequired(true)->withRequired(false)->withInput($values);
        $res = $input2->getContent();

        $this->assertInstanceOf(Result::class, $res);
        $this->assertFalse($res->isError());
        $this->assertEquals($value, $res->value());
    }
}
