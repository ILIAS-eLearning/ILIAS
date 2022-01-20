<?php declare(strict_types=1);

/* Copyright (c) 2021 Thibeau Fuhrer <thibeau@sr.solutions> Extended GPL, see docs/LICENSE */

namespace ILIAS\Tests\UI\Component\Input;

use ILIAS\UI\Implementation\Component\Input\Field\DynamicInputDataIterator;
use PHPUnit\Framework\TestCase;
use ILIAS\UI\Implementation\Component\Input\InputData;
use ILIAS\UI\Implementation\Component\Input\Field\ArrayInputData;

/**
 * @author  Thibeau Fuhrer <thibeau@sr.solutions>
 */
class DynamicInputDataIteratorTest extends TestCase
{
    public function testBasicIterationFunctionality() : void
    {
        $iterator = new DynamicInputDataIterator(
            $this->getTestInputData([]),

        );
    }

    protected function getTestInputData(array $data) : InputData
    {
        return new ArrayInputData($data);
    }
}