<?php declare(strict_types=1);

/* Copyright (c) 1998-2021 ILIAS open source, Extended GPL, see docs/LICENSE */

use PHPUnit\Framework\TestCase;

/**
 * Class RandomizationStrategiesTest
 * @author Michael Jansen <mjansen@databay.de>
 */
class RandomizationStrategiesTest extends TestCase
{
    private const NUM_RUNS = 1000;
    private const NUM_RANDOM_NUMBERS = 1000;
    private const NUM_FINAL_ELEMENTS = 10;

    private function randomArrayElementsProvider() : Generator
    {
        $randomNumbers = range(0, self::NUM_RANDOM_NUMBERS);

        for ($i = 1; $i <= self::NUM_RUNS; $i++) {
            $numbersOfRun = $randomNumbers;
            shuffle($numbersOfRun);
            $numbersOfRun = array_slice($numbersOfRun, 0, self::NUM_FINAL_ELEMENTS);
            yield 'Random numbers run ' . $i => $numbersOfRun;
        }
    }

    public function testDeterministicElementStrategy() : void
    {
        $provider = new ilDeterministicArrayElementProvider();

        foreach ($this->randomArrayElementsProvider() as $runInfo => $elements) {
            $provider->setSeed(1);
            $actualWithInitialSeed = $provider->shuffle($elements);
            if ($elements !== $actualWithInitialSeed) {
                $this->fail($runInfo . ' unexpectedly resulted in a random order of elements.');
            }

            $provider->setSeed(2);
            $actualWithOtherSeed = $provider->shuffle($elements);
            if ($elements !== $actualWithOtherSeed) {
                $this->fail($runInfo . ' unexpectedly resulted in a random order of elements.');
            }
        }

        $this->assertTrue(true, 'Shuffling elements with ' . get_class($provider) . ' resulted in output=input');
    }

    /**
     * Attention: Testing random is generally a bad idea, but we assume that from the defined N runs at least 1
     * one will randomly return a random ordering of elements
     */
    public function testRandomElementStrategy() : void
    {
        foreach ($this->randomArrayElementsProvider() as $runInfo => $elements) {
            $provider = new ilArrayElementShuffler();
            $actual = $provider->shuffle($elements);

            if ($elements !== $actual) {
                $this->assertTrue(true, $runInfo . ' resulted in a random ordering of elements');
                return;
            }
        }

        $this->fail(sprintf(
            'None of %s runs resulted in a random ordering of elements',
            self::NUM_RUNS
        ));
    }
}
