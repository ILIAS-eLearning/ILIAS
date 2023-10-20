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

require_once(__DIR__ . "/../../../../../libs/composer/vendor/autoload.php");
require_once(__DIR__ . "/../../../Base.php");

use ILIAS\UI\Implementation\Component\Input\Field\FormInput;
use ILIAS\UI\Implementation\Component\Input\NameSource;
use ILIAS\UI\Implementation\Component\Input\InputData;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\Data\Result;
use ILIAS\Refinery\Constraint;
use ILIAS\Refinery\Factory as Refinery;

class DefInput extends FormInput
{
    public bool $value_ok = true;
    public ?Constraint $requirement_constraint = null;

    public function isClientSideValueOk($value): bool
    {
        return $this->value_ok;
    }

    protected function getConstraintForRequirement(): ?Constraint
    {
        return $this->requirement_constraint;
    }

    public function getUpdateOnLoadCode(): Closure
    {
        return function (): void {
        };
    }
}

class DefNamesource implements NameSource
{
    public int $count = 0;

    public function getNewName(): string
    {
        $name = "name_{$this->count}";
        $this->count++;

        return $name;
    }

    public function getNewDedicatedName($dedicated_name = 'dedicated_name'): string
    {
        $name = $dedicated_name . "_{$this->count}";
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
 * Test on field implementation.
 */
class InputTest extends ILIAS_UI_TestBase
{
    protected DataFactory $data_factory;
    protected Refinery $refinery;
    protected DefInput $input;
    protected DefInput $dedicated_input;
    protected DefNamesource $name_source;
    protected FormInput $named_input;

    public function setUp(): void
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
        $this->named_input = $this->input->withDedicatedName('dedicated_name');
        $this->name_source = new DefNamesource();
    }

    public function testConstructor(): void
    {
        $this->assertEquals("label", $this->input->getLabel());
        $this->assertEquals("byline", $this->input->getByline());
    }

    public function testWithLabel(): void
    {
        $label = "new label";
        $input = $this->input->withLabel($label);
        $this->assertEquals($label, $input->getLabel());
        $this->assertNotSame($this->input, $input);
    }

    public function testWithByline(): void
    {
        $byline = "new byline";
        $input = $this->input->withByline($byline);
        $this->assertEquals($byline, $input->getByline());
        $this->assertNotSame($this->input, $input);
    }

    public function testWithRequired(): void
    {
        $this->assertFalse($this->input->isRequired());
        $input = $this->input->withRequired(true);
        $this->assertTrue($input->isRequired());
        $input = $input->withRequired(false);
        $this->assertFalse($input->isRequired());
    }

    public function testWithRequiredAndCustomConstraint(): void
    {
        $custom_constraint = $this->refinery->custom()->constraint(
            function ($value) {
                return (substr($value, 0, 1) === 'H') ? true : false;
            },
            "Your name does not start with an H"
        );
        $input = $this->input->withRequired(true, $custom_constraint);
        $this->assertTrue($input->isRequired());
        $this->assertEquals($input->requirement_constraint, $custom_constraint);
    }

    public function testWithDisabled(): void
    {
        $this->assertFalse($this->input->isDisabled());
        $input = $this->input->withDisabled(true);
        $this->assertTrue($input->isDisabled());
        $input = $input->withDisabled(false);
        $this->assertFalse($input->isDisabled());
    }

    public function testWithValue(): void
    {
        $value = "some value";
        $input = $this->input->withValue($value);
        $this->assertEquals(null, $this->input->getValue());
        $this->assertEquals($value, $input->getValue());
        $this->assertNotSame($this->input, $input);
    }

    public function testWithValueThrows(): void
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

    public function testWithName(): void
    {
        $name = "name_0";
        $input = $this->input->withNameFrom($this->name_source);
        $this->assertEquals(null, $this->input->getName());
        $this->assertEquals($name, $input->getName());
        $this->assertNotSame($this->input, $input);
        $this->assertEquals(1, $this->name_source->count);
    }

    public function testWithNameForNamedInput(): void
    {
        $name = "dedicated_name_0";
        $input = $this->named_input->withNameFrom($this->name_source);
        $this->assertEquals(null, $this->named_input->getName());
        $this->assertEquals($name, $input->getName());
        $this->assertNotSame($this->named_input, $input);
        $this->assertEquals(1, $this->name_source->count);
    }

    public function testWithError(): void
    {
        $error = "error";
        $input = $this->input->withError($error);
        $this->assertEquals(null, $this->input->getError());
        $this->assertEquals($error, $input->getError());
        $this->assertNotSame($this->input, $input);
    }

    public function testGetContent(): void
    {
        $this->expectException(LogicException::class);

        $this->input->getContent();
    }

    public function testWithInput(): void
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

    public function testOnlyRunWithInputWithName(): void
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

    public function testWithInputAndTransformation(): void
    {
        $name = "name_0";
        $value = "value";
        $transform_to = "other value";
        $input = $this->input->withNameFrom($this->name_source);
        $values = new DefInputData([$name => $value]);

        $input2 = $input->withAdditionalTransformation(
            $this->refinery->custom()->transformation(function ($v) use ($value, $transform_to): string {
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

    public function testWithInputAndTransformationDifferentOrder(): void
    {
        $name = "name_0";
        $value = "value";
        $transform_to = "other value";
        $input = $this->input->withNameFrom($this->name_source);
        $values = new DefInputData([$name => $value]);

        $input2 = $input->withInput($values)->withAdditionalTransformation(
            $this->refinery->custom()->transformation(function ($v) use ($value, $transform_to): string {
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

    public function testWithInputAndConstraintSuccessfull(): void
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

    public function testWithInputAndConstraintFails(): void
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

    public function testWithInputAndConstraintFailsDifferentOrder(): void
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

    public function testWithInputTransformationAndConstraint(): void
    {
        $name = "name_0";
        $value = "value";
        $transform_to = "other value";
        $error = "an error";
        $input = $this->input->withNameFrom($this->name_source);
        $values = new DefInputData([$name => $value]);

        $input2 = $input->withAdditionalTransformation(
            $this->refinery->custom()->transformation(function ($v) use ($value, $transform_to): string {
                $this->assertEquals($value, $v);
                return $transform_to;
            })
        )->withAdditionalTransformation(
            $this->refinery->custom()->constraint(function ($v) use ($transform_to): bool {
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

    public function testWithInputTransformationAndConstraintDifferentOrder(): void
    {
        $name = "name_0";
        $value = "value";
        $transform_to = "other value";
        $error = "an error";
        $input = $this->input->withNameFrom($this->name_source);
        $values = new DefInputData([$name => $value]);

        $input2 = $input->withInput($values)->withAdditionalTransformation(
            $this->refinery->custom()->transformation(function ($v) use ($value, $transform_to): string {
                $this->assertEquals($value, $v);
                return $transform_to;
            })
        )->withAdditionalTransformation(
            $this->refinery->custom()->constraint(function ($v) use ($transform_to): bool {
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

    public function testWithInputConstraintAndTransformation(): void
    {
        $name = "name_0";
        $value = "value";
        $transform_to = "other value";
        $error = "an error";
        $input = $this->input->withNameFrom($this->name_source);
        $values = new DefInputData([$name => $value]);

        $input2 = $input->withAdditionalTransformation(
            $this->refinery->custom()->constraint(function ($v) use ($value): bool {
                $this->assertEquals($value, $v);
                return true;
            }, $error)
        )->withAdditionalTransformation(
            $this->refinery->custom()->transformation(function ($v) use ($value, $transform_to): string {
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

    public function testWithInputConstraintFailsAndTransformation(): void
    {
        $rc = $this->refinery->custom();

        $name = "name_0";
        $value = "value";
        $transform_to = "other value";
        $error = "an error";
        $input = $this->input->withNameFrom($this->name_source);
        $values = new DefInputData([$name => $value]);

        $input2 = $input
            ->withAdditionalTransformation($rc->constraint(function ($v) use ($value): bool {
                $this->assertEquals($value, $v);

                return false;
            }, $error))
            ->withAdditionalTransformation($rc->transformation(function () use ($value, $transform_to): string {
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

    public function testWithInputConstraintFailsAndTransformationDifferentOrder(): void
    {
        $name = "name_0";
        $value = "value";
        $transform_to = "other value";
        $error = "an error";
        $input = $this->input->withNameFrom($this->name_source);
        $values = new DefInputData([$name => $value]);

        $input2 = $input->withInput($values)->withAdditionalTransformation(
            $this->refinery->custom()->constraint(function ($v) use ($value): bool {
                $this->assertEquals($value, $v);
                return false;
            }, $error)
        )->withAdditionalTransformation(
            $this->refinery->custom()->transformation(function () use ($value, $transform_to): string {
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

    public function testWithInputRequirementConstraint(): void
    {
        $name = "name_0";
        $value = "Adam";
        $error = "Your name does not start with an H";
        $input = $this->input->withNameFrom($this->name_source);
        $values = new DefInputData([$name => $value]);
        $custom_constraint = $this->refinery->custom()->constraint(
            function ($value) {
                return (substr($value, 0, 1) === 'H') ? true : false;
            },
            $error
        );
        $input2 = $input->withRequired(true, $custom_constraint)->withInput($values);
        $res = $input2->getContent();
        $this->assertInstanceOf(Result::class, $res);
        $this->assertFalse($res->isOk());
        $this->assertEquals($error, $input2->getError());
    }

    public function testWithInputToggleRequirement(): void
    {
        $name = "name_0";
        $value = "value";
        $error = "an error";
        $input = $this->input->withNameFrom($this->name_source);
        $values = new DefInputData([$name => $value]);

        $custom_constraint = $this->refinery->custom()->constraint(function () {
            return false;
        }, $error);

        $input2 = $input->withRequired(true, $custom_constraint)->withRequired(false)->withInput($values);
        $res = $input2->getContent();

        $this->assertInstanceOf(Result::class, $res);
        $this->assertFalse($res->isError());
        $this->assertEquals($value, $res->value());
    }
}
