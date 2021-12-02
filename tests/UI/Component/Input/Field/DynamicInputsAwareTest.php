<?php declare(strict_types=1);

/* Copyright (c) 2021 Thibeau Fuhrer <thf@studer-raimann.ch> Extended GPL, see docs/LICENSE */

namespace ILIAS\Tests\UI\Component\Input\Field;

use ILIAS\UI\Implementation\Component\Input\Field\DynamicInputsAwareInput;
use ILIAS\UI\Component\Input\Field\DynamicInputsAware;
use PHPUnit\Framework\TestCase;
use ILIAS\Refinery\Constraint;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\Data\Factory as DataFactory;
use ilLanguage;
use Closure;

/**
 * @author  Thibeau Fuhrer <thf@studer-raimann.ch>
 */
class DynamicInputsAwareTest extends TestCase
{
    protected DynamicInputsAware $input;

    public function setUp() : void
    {
        $this->input = new class(
            $this->createMock(ilLanguage::class),
            $this->createMock(DataFactory::class),
            $this->createMock(Refinery::class),
            'test_input',
            null
        ) extends DynamicInputsAwareInput {
            public function getUpdateOnLoadCode() : Closure
            {
                return static function () {
                };
            }

            protected function getConstraintForRequirement() : ?Constraint
            {
                return null;
            }

            protected function isClientSideValueOk($value) : bool
            {
                return true;
            }
        };
    }

    public function test() : void
    {
        $this->assertTrue(true);
    }
}