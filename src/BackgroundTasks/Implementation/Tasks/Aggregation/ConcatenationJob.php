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

namespace ILIAS\BackgroundTasks\Implementation\Tasks\Aggregation;

use ILIAS\BackgroundTasks\Implementation\Tasks\AbstractJob;
use ILIAS\BackgroundTasks\Implementation\Values\ScalarValues\ScalarValue;
use ILIAS\BackgroundTasks\Implementation\Values\ScalarValues\StringValue;
use ILIAS\BackgroundTasks\Observer;
use ILIAS\BackgroundTasks\Types\ListType;
use ILIAS\BackgroundTasks\Types\SingleType;
use ILIAS\BackgroundTasks\Types\Type;
use ILIAS\BackgroundTasks\Value;

class ConcatenationJob extends AbstractJob
{
    /**
     * @param \ILIAS\BackgroundTasks\Value[] $input
     * @param Observer                       $observer Notify the bucket about your progress!
     */
    public function run(array $input, Observer $observer): Value
    {
        /** @var ScalarValue[] $list */
        $list = $input[0]->getList();
        /** @var ScalarValue[] $values */
        $values = array_map(
            fn ($a) => $a->getValue(),
            $list
        );

        $string_value = new StringValue();
        $string_value->setValue(implode(', ', $values));

        return $string_value;
    }

    /**
     * @return bool returns true iff the job's output ONLY depends on the input. Stateless task
     *              results may be cached!
     */
    public function isStateless(): bool
    {
        return true;
    }

    public function getId(): string
    {
        return static::class;
    }

    /**
     * @return Type[] Class-Name of the IO
     */
    public function getInputTypes(): array
    {
        return [new ListType(ScalarValue::class)];
    }

    public function getOutputType(): Type
    {
        return new SingleType(StringValue::class);
    }

    /**
     * @inheritdoc
     */
    public function getExpectedTimeOfTaskInSeconds(): int
    {
        return 1;
    }
}
