<?php

namespace ILIAS\BackgroundTasks\Implementation\Tasks;

use ILIAS\BackgroundTasks\Implementation\Bucket\BucketMock;
use ILIAS\BackgroundTasks\Implementation\Values\ScalarValues\IntegerValue;
use ILIAS\BackgroundTasks\Observer;
use ILIAS\BackgroundTasks\Types\SingleType;
use ILIAS\BackgroundTasks\Types\Type;
use ILIAS\BackgroundTasks\Value;

class PlusJob extends AbstractJob
{

    /**
     * PlusJob constructor.
     *
     * Jobs dependencies will be injected. Type hinting is necessary for that!
     *
     */
    public function __construct()
    {
    }


    /**
     * @return Type[] Classof the Values
     */
    public function getInputTypes()
    {
        return [
            new SingleType(IntegerValue::class),
            new SingleType(IntegerValue::class),
        ];
    }


    /**
     * @return Type
     */
    public function getOutputType()
    {
        return new SingleType(IntegerValue::class);
    }


    /**
     * @param Value[]  $input
     * @param Observer $observer Notify the bucket about your progress!
     *
     * @return Value
     */
    public function run(Array $input, Observer $observer)
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
    public function isStateless()
    {
        return true;
    }


    /**
     * @inheritDoc
     */
    public function getExpectedTimeOfTaskInSeconds()
    {
        return 1;
    }
}