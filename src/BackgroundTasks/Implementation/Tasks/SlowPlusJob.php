<?php

namespace ILIAS\BackgroundTasks\Implementation\Tasks;

use ILIAS\BackgroundTasks\Implementation\Bucket\BucketMock;
use ILIAS\BackgroundTasks\Implementation\Values\ScalarValues\IntegerValue;
use ILIAS\BackgroundTasks\Observer;
use ILIAS\BackgroundTasks\Types\SingleType;
use ILIAS\BackgroundTasks\Types\Type;
use ILIAS\BackgroundTasks\Value;

class SlowPlusJob extends AbstractJob
{

    const SLEEP_SECONDS = 10;


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
     * @return Type[] Class-Name of the IO
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

        sleep(self::SLEEP_SECONDS);
        $observer->notifyPercentage($this, 20);
        sleep(self::SLEEP_SECONDS);
        $observer->notifyPercentage($this, 40);
        sleep(self::SLEEP_SECONDS);
        $observer->notifyPercentage($this, 60);
        sleep(self::SLEEP_SECONDS);
        $observer->notifyPercentage($this, 80);

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
     * @inheritdoc
     */
    public function getExpectedTimeOfTaskInSeconds()
    {
        return 2;
    }
}