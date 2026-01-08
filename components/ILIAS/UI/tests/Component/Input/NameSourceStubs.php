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
 */

declare(strict_types=1);

use ILIAS\UI\Implementation\Component\Input\HasDynamicInputsNameSource;
use ILIAS\UI\Implementation\Component\Input\NameSource;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @mixin TestCase, please only use inside this context.
 *
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
trait NameSourceStubs
{
    protected function createFixedNameSourceStub(string $name): NameSource&MockObject
    {
        $stub = $this->createMock(NameSource::class);
        $stub->method('getNextName')->willReturn($name);
        $stub->method('withParentName')->willReturnSelf();
        $stub->method('withReset')->willReturnSelf();
        return $stub;
    }

    protected function createRelayArgumentNameSourceStub(): NameSource&MockObject
    {
        $stub = $this->createMock(NameSource::class);
        $stub->method('getNextName')->willReturnCallback(function(?string $arg) {
            if (null === $arg) {
                $this->fail('Only use this NameSource stub if Input::withDedicatedName() is used.');
            }
            return $arg;
        });
        $stub->method('withParentName')->willReturnSelf();
        $stub->method('withReset')->willReturnSelf();
        return $stub;
    }

    protected function createFixedHasDynamicInputsNameSourceStub(string $name): HasDynamicInputsNameSource&MockObject
    {
        $stub = $this->createMock(HasDynamicInputsNameSource::class);
        $stub->method('getNextName')->willReturn($name);
        $stub->method('withParentName')->willReturnSelf();
        $stub->method('withResetDefaultNameSource')->willReturnSelf();
        $stub->method('withIndices')->willReturnSelf();
        $stub->method('withReset')->willReturnSelf();
        return $stub;
    }

    /**
     * Attention: please only use this method instead of createFixedNameSourceStub() if it matters
     * that multiple input's have different names. I.e. input data processing with withInput().
     * Everything else should be done with static names from createFixedNameSourceStub().
     *
     * Note: most usages of this method are probably wrong at the moment. Usages should be checked
     * and migrated towards createFixedNameSourceStub() while working on these test cases again.
     */
    protected function createCountingNameSourceStub(string $prefix, int $start_count = 0): NameSource&MockObject
    {
        $stub = $this->createMock(NameSource::class);
        $stub->method('getNextName')->willReturnCallback(static function () use ($prefix, &$start_count) {
            return $prefix . ($start_count++);
        });
        $stub->method('withParentName')->willReturnSelf();
        $stub->method('withReset')->willReturnSelf();
        return $stub;
    }
}
