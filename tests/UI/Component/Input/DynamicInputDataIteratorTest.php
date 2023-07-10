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

namespace ILIAS\Tests\UI\Component\Input;

use ILIAS\UI\Implementation\Component\Input\DynamicInputDataIterator;
use ILIAS\UI\Implementation\Component\Input\InputData;
use PHPUnit\Framework\TestCase;
use LogicException;
use ILIAS\UI\Implementation\Component\Input\DynamicInputsNameSource;

/**
 * @author  Thibeau Fuhrer <thibeau@sr.solutions>
 */
class DynamicInputDataIteratorTest extends TestCase
{
    public function testValidityWithEmptyData(): void
    {
        $iterator = new DynamicInputDataIterator(
            $this->getTestInputData([]),
            'test_name_1'
        );

        $this->assertFalse($iterator->valid());
        $this->assertNull($iterator->key());
        $this->assertNull($iterator->current());
    }

    public function testValidityWithData(): void
    {
        $iterator = new DynamicInputDataIterator(
            $this->getTestInputData([
                'test_input_1' => [
                    [
                        'test_value_1'
                    ]
                ]
            ]),
            'test_input_1'
        );

        $this->assertTrue($iterator->valid());
        $this->assertNotNull($iterator->key());
        $this->assertNotNull($iterator->current());

        $iterator->next();

        $this->assertFalse($iterator->valid());
        $this->assertNull($iterator->key());
        $this->assertNull($iterator->current());
    }

    public function testCurrentValue(): void
    {
        $test_value = 'val1';
        $parent_input_name = 'parent_input';
        $dynamic_input_name = 'dynamic_input';
        $fake_post_array = [
            $parent_input_name => [
                $dynamic_input_name => [
                    $test_value,
                ]
            ]
        ];

        $iterator = new DynamicInputDataIterator(
            $this->getTestInputData($fake_post_array),
            $parent_input_name
        );

        $current = $iterator->current();
        $this->assertInstanceOf(
            InputData::class,
            $current
        );

        $rendered_dynamic_input_name = "{$parent_input_name}[$dynamic_input_name][]";
        $this->assertEquals(
            $test_value,
            $current->getOr($rendered_dynamic_input_name, null)
        );
    }

    protected function getTestInputData(array $data): InputData
    {
        return new class ($data) implements InputData {
            protected array $data;

            public function __construct(array $data)
            {
                $this->data = $data;
            }

            public function get($name)
            {
                if (!isset($this->data[$name])) {
                    throw new LogicException();
                }

                return $this->data[$name];
            }

            public function getOr($name, $default)
            {
                return $this->data[$name] ?? $default;
            }
        };
    }
}
