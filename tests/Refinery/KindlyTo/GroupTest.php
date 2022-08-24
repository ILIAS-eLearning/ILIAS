<?php

declare(strict_types=1);

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

namespace ILIAS\Tests\Refinery\KindlyTo;

use ILIAS\Refinery\KindlyTo\Group as KindlyToGroup;
use ILIAS\Refinery\KindlyTo\Transformation\FloatTransformation;
use ILIAS\Refinery\KindlyTo\Transformation\StringTransformation;
use ILIAS\Refinery\KindlyTo\Transformation\BooleanTransformation;
use ILIAS\Refinery\KindlyTo\Transformation\DateTimeTransformation;
use ILIAS\Refinery\KindlyTo\Transformation\IntegerTransformation;
use ILIAS\Refinery\KindlyTo\Transformation\RecordTransformation;
use ILIAS\Refinery\KindlyTo\Transformation\TupleTransformation;
use ILIAS\Refinery\KindlyTo\Transformation\DictionaryTransformation;
use ILIAS\Tests\Refinery\TestCase;

class GroupTest extends TestCase
{
    private KindlyToGroup $basicGroup;

    protected function setUp(): void
    {
        $this->basicGroup = new KindlyToGroup(new \ILIAS\Data\Factory());
    }

    public function testIsStringTransformationInstance(): void
    {
        $transformation = $this->basicGroup->string();
        $this->assertInstanceOf(StringTransformation::class, $transformation);
    }

    public function testIsBooleanTransformationInstance(): void
    {
        $transformation = $this->basicGroup->bool();
        $this->assertInstanceOf(BooleanTransformation::class, $transformation);
    }

    public function testIsDateTimeTransformationInterface(): void
    {
        $transformation = $this->basicGroup->dateTime();
        $this->assertInstanceOf(DateTimeTransformation::class, $transformation);
    }

    public function testIsIntegerTransformationInterface(): void
    {
        $transformation = $this->basicGroup->int();
        $this->assertInstanceOf(IntegerTransformation::class, $transformation);
    }

    public function testIsFloatTransformationInterface(): void
    {
        $transformation = $this->basicGroup->float();
        $this->assertInstanceOf(FloatTransformation::class, $transformation);
    }

    public function testIsRecordTransformationInterface(): void
    {
        $transformation = $this->basicGroup->recordOf(['tostring' => new StringTransformation()]);
        $this->assertInstanceOf(RecordTransformation::class, $transformation);
    }

    public function testIsTupleTransformationInterface(): void
    {
        $transformation = $this->basicGroup->tupleOf([new StringTransformation()]);
        $this->assertInstanceOf(TupleTransformation::class, $transformation);
    }

    public function testNewDictionaryTransformation(): void
    {
        $transformation = $this->basicGroup->dictOf(new StringTransformation());
        $this->assertInstanceOf(DictionaryTransformation::class, $transformation);
    }
}
