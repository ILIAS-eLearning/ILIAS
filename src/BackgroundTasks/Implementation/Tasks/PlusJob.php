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

namespace ILIAS\BackgroundTasks\Implementation\Tasks;

use ILIAS\BackgroundTasks\Implementation\Values\ScalarValues\IntegerValue;
use ILIAS\BackgroundTasks\Observer;
use ILIAS\BackgroundTasks\Types\SingleType;
use ILIAS\BackgroundTasks\Types\Type;
use ILIAS\BackgroundTasks\Value;

class PlusJob extends AbstractJob
{
    /**
     * @return Type[] Classof the Values
     */
    public function getInputTypes(): array
    {
        return [
            new SingleType(IntegerValue::class),
            new SingleType(IntegerValue::class),
        ];
    }

    public function getOutputType(): Type
    {
        return new SingleType(IntegerValue::class);
    }

    /**
     * @param Value[]  $input
     * @param Observer $observer Notify the bucket about your progress!
     */
    public function run(array $input, Observer $observer): Value
    {
        /** @var IntegerValue $a */
        $a = $input[0];
        /** @var IntegerValue $b */
        $b = $input[1];

        $output = new IntegerValue();
        $output->setValue($a->getValue() + $b->getValue());

        return $output;
    }

    /**
     * @return bool returns true iff the job's output ONLY depends on the input. Stateless task
     *              results may be cached!
     */
    public function isStateless(): bool
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function getExpectedTimeOfTaskInSeconds(): int
    {
        return 1;
    }
}
