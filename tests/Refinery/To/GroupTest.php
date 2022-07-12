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

namespace ILIAS\Tests\Refinery\To;

use ILIAS\Data\Alphanumeric;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\Refinery\To\Group as ToGroup;
use ILIAS\Refinery\To\Transformation\BooleanTransformation;
use ILIAS\Refinery\To\Transformation\DictionaryTransformation;
use ILIAS\Refinery\To\Transformation\FloatTransformation;
use ILIAS\Refinery\To\Transformation\IntegerTransformation;
use ILIAS\Refinery\To\Transformation\ListTransformation;
use ILIAS\Refinery\To\Transformation\NewMethodTransformation;
use ILIAS\Refinery\To\Transformation\NewObjectTransformation;
use ILIAS\Refinery\To\Transformation\RecordTransformation;
use ILIAS\Refinery\To\Transformation\StringTransformation;
use ILIAS\Refinery\To\Transformation\TupleTransformation;
use ILIAS\Tests\Refinery\TestCase;
use InvalidArgumentException;

class GroupTest extends TestCase
{
    private ToGroup $basicGroup;

    protected function setUp() : void
    {
        $this->basicGroup = new ToGroup(new DataFactory());
    }

    public function testIsIntegerTransformationInstance() : void
    {
        $transformation = $this->basicGroup->int();

        $this->assertInstanceOf(IntegerTransformation::class, $transformation);
    }

    public function testIsStringTransformationInstance() : void
    {
        $transformation = $this->basicGroup->string();

        $this->assertInstanceOf(StringTransformation::class, $transformation);
    }

    public function testIsFloatTransformationInstance() : void
    {
        $transformation = $this->basicGroup->float();

        $this->assertInstanceOf(FloatTransformation::class, $transformation);
    }

    public function testIsBooleanTransformationInstance() : void
    {
        $transformation = $this->basicGroup->bool();

        $this->assertInstanceOf(BooleanTransformation::class, $transformation);
    }

    public function testListOfTransformation() : void
    {
        $transformation = $this->basicGroup->listOf(new StringTransformation());

        $this->assertInstanceOf(ListTransformation::class, $transformation);
    }

    public function testTupleOfTransformation() : void
    {
        $transformation = $this->basicGroup->tupleOf([new StringTransformation()]);

        $this->assertInstanceOf(TupleTransformation::class, $transformation);
    }

    /**
     * @throws \ilException
     */
    public function testRecordOfTransformation() : void
    {
        $transformation = $this->basicGroup->recordOf(['toString' => new StringTransformation()]);

        $this->assertInstanceOf(RecordTransformation::class, $transformation);
    }

    public function testDictionaryOfTransformation() : void
    {
        $transformation = $this->basicGroup->dictOf(new StringTransformation());

        $this->assertInstanceOf(DictionaryTransformation::class, $transformation);
    }

    /**
     * @throws \ilException
     */
    public function testNewObjectTransformation() : void
    {
        $transformation = $this->basicGroup->toNew(MyClass::class);

        $this->assertInstanceOf(NewObjectTransformation::class, $transformation);
    }

    /**
     * @throws \ilException
     */
    public function testNewMethodTransformation() : void
    {
        $transformation = $this->basicGroup->toNew([new MyClass(), 'myMethod']);

        $this->assertInstanceOf(NewMethodTransformation::class, $transformation);
    }

    public function testNewMethodTransformationThrowsExceptionBecauseToManyParametersAreGiven() : void
    {
        $this->expectNotToPerformAssertions();

        try {
            $transformation = $this->basicGroup->toNew([new MyClass(), 'myMethod', 'hello']);
        } catch (InvalidArgumentException $exception) {
            return;
        }

        $this->fail();
    }

    public function testNewMethodTransformationThrowsExceptionBecauseToFewParametersAreGiven() : void
    {
        $this->expectNotToPerformAssertions();

        try {
            $transformation = $this->basicGroup->toNew([new MyClass()]);
        } catch (InvalidArgumentException $exception) {
            return;
        }

        $this->fail();
    }

    /**
     * @throws \ilException
     */
    public function testCreateDataTransformation() : void
    {
        $transformation = $this->basicGroup->data('alphanumeric');

        $this->assertInstanceOf(NewMethodTransformation::class, $transformation);

        $result = $transformation->transform(['hello']);

        $this->assertInstanceOf(Alphanumeric::class, $result);
    }
}

class MyClass
{
    public function myMethod() : array
    {
        return [$this->string, $this->integer];
    }
}
