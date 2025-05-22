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

declare(strict_types=1);

namespace ILIAS\MetaData\OERExposer\OAIPMH\Requests;

use PHPUnit\Framework\TestCase;
use ILIAS\MetaData\OERExposer\OAIPMH\Requests\Verb;
use ILIAS\MetaData\OERExposer\OAIPMH\Requests\Argument;
use ILIAS\Data\URI;

class RequestTest extends TestCase
{
    protected function getURI(): URI
    {
        return $this->createMock(URI::class);
    }

    protected function getEmptyRequest(): Request
    {
        return new Request($this->getURI(), Verb::NULL);
    }

    public function testVerbAndBaseURL(): void
    {
        $url = $this->getURI();
        $request = new Request($url, Verb::LIST_IDENTIFIERS);

        $this->assertSame($url, $request->baseURL());
        $this->assertSame(Verb::LIST_IDENTIFIERS, $request->verb());
    }

    public function testNoArgument(): void
    {
        $request = $this->getEmptyRequest();

        foreach (Argument::cases() as $argument) {
            $this->assertFalse($request->hasArgument($argument));
            $this->assertSame('', $request->argumentValue($argument));
        }
    }

    public function testSingleArgument(): void
    {
        $request = $this->getEmptyRequest()
                        ->withArgument(Argument::FROM_DATE, 'today');

        foreach (Argument::cases() as $argument) {
            if ($argument === Argument::FROM_DATE) {
                continue;
            }
            $this->assertFalse($request->hasArgument($argument));
            $this->assertSame('', $request->argumentValue($argument));
        }

        $this->assertTrue($request->hasArgument(Argument::FROM_DATE));
        $this->assertSame('today', $request->argumentValue(Argument::FROM_DATE));
    }

    public function testMultipleDifferentArguments(): void
    {
        $request = $this->getEmptyRequest()
                        ->withArgument(Argument::FROM_DATE, 'today')
                        ->withArgument(Argument::RESUMPTION_TOKEN, 'resume!');

        foreach (Argument::cases() as $argument) {
            if (
                $argument === Argument::FROM_DATE ||
                $argument === Argument::RESUMPTION_TOKEN
            ) {
                continue;
            }
            $this->assertFalse($request->hasArgument($argument));
            $this->assertSame('', $request->argumentValue($argument));
        }

        $this->assertTrue($request->hasArgument(Argument::FROM_DATE));
        $this->assertSame('today', $request->argumentValue(Argument::FROM_DATE));
        $this->assertTrue($request->hasArgument(Argument::RESUMPTION_TOKEN));
        $this->assertSame('resume!', $request->argumentValue(Argument::RESUMPTION_TOKEN));
    }

    public function testArgumentKeysNoArgument(): void
    {
        $request = $this->getEmptyRequest();

        $this->assertNull($request->argumentKeys()->current());
    }

    public function testArgumentKeys(): void
    {
        $request = $this->getEmptyRequest();

        $request = $request->withArgument(Argument::IDENTIFIER, 'some identifier');
        $request = $request->withArgument(Argument::UNTIL_DATE, 'some date');

        $argument_keys = iterator_to_array($request->argumentKeys());
        $this->assertCount(2, $argument_keys);
        $this->assertContains(Argument::IDENTIFIER, $argument_keys);
        $this->assertContains(Argument::UNTIL_DATE, $argument_keys);
    }

    public function testHasCorrectArguments(): void
    {
        $expect_true = [];
        $expect_false = [];

        $expect_true[] = $this->getEmptyRequest()
                              ->withArgument(Argument::IDENTIFIER, 'some identifier');

        $expect_true[] = $this->getEmptyRequest()
                              ->withArgument(Argument::IDENTIFIER, 'some identifier')
                              ->withArgument(Argument::RESUMPTION_TOKEN, 'token');

        $expect_true[] = $this->getEmptyRequest()
                              ->withArgument(Argument::IDENTIFIER, 'some identifier')
                              ->withArgument(Argument::RESUMPTION_TOKEN, 'token')
                              ->withArgument(Argument::FROM_DATE, 'date');

        $expect_true[] = $this->getEmptyRequest()
                              ->withArgument(Argument::MD_PREFIX, 'prefix');

        $expect_false[] = $this->getEmptyRequest();

        $expect_false[] = $this->getEmptyRequest()
                               ->withArgument(Argument::UNTIL_DATE, 'date');

        $expect_false[] = $this->getEmptyRequest()
                               ->withArgument(Argument::FROM_DATE, 'date');

        $expect_false[] = $this->getEmptyRequest()
                               ->withArgument(Argument::IDENTIFIER, 'some identifier')
                               ->withArgument(Argument::UNTIL_DATE, 'date');

        $expect_false[] = $this->getEmptyRequest()
                               ->withArgument(Argument::IDENTIFIER, 'some identifier')
                               ->withArgument(Argument::MD_PREFIX, 'prefix');

        $expect_false[] = $this->getEmptyRequest()
                               ->withArgument(Argument::FROM_DATE, 'date')
                               ->withArgument(Argument::MD_PREFIX, 'prefix');

        foreach ($expect_true as $request) {
            $this->assertTrue($request->hasCorrectArguments(
                [Argument::IDENTIFIER],
                [Argument::RESUMPTION_TOKEN, Argument::FROM_DATE],
                [Argument::MD_PREFIX]
            ));
        }
        foreach ($expect_false as $request) {
            $this->assertFalse($request->hasCorrectArguments(
                [Argument::IDENTIFIER],
                [Argument::RESUMPTION_TOKEN, Argument::FROM_DATE],
                [Argument::MD_PREFIX]
            ));
        }
    }

    public function testHasCorrectArgumentsNoArguments(): void
    {
        $request_no_arguments = $this->getEmptyRequest();

        $request_one_argument = $this->getEmptyRequest()
                                     ->withArgument(Argument::IDENTIFIER, 'some identifier');

        $request_two_arguments = $this->getEmptyRequest()
                                      ->withArgument(Argument::IDENTIFIER, 'some identifier')
                                      ->withArgument(Argument::UNTIL_DATE, 'some date');

        $this->assertTrue($request_no_arguments->hasCorrectArguments([], [], []));
        $this->assertFalse($request_one_argument->hasCorrectArguments([], [], []));
        $this->assertFalse($request_two_arguments->hasCorrectArguments([], [], []));
    }

    public function testHasCorrectArgumentsRequired(): void
    {
        $request_no_arguments = $this->getEmptyRequest();

        $request_one_argument = $this->getEmptyRequest()
                                     ->withArgument(Argument::IDENTIFIER, 'some identifier');

        $request_two_arguments = $this->getEmptyRequest()
                                      ->withArgument(Argument::IDENTIFIER, 'some identifier')
                                      ->withArgument(Argument::UNTIL_DATE, 'some date');

        $this->assertFalse($request_no_arguments->hasCorrectArguments([Argument::IDENTIFIER], [], []));
        $this->assertTrue($request_one_argument->hasCorrectArguments([Argument::IDENTIFIER], [], []));
        $this->assertFalse($request_two_arguments->hasCorrectArguments([Argument::IDENTIFIER], [], []));
    }

    public function testHasCorrectArgumentsOptional(): void
    {
        $request_no_arguments = $this->getEmptyRequest();

        $request_one_argument = $this->getEmptyRequest()
                                     ->withArgument(Argument::IDENTIFIER, 'some identifier');

        $request_two_arguments = $this->getEmptyRequest()
                                      ->withArgument(Argument::IDENTIFIER, 'some identifier')
                                      ->withArgument(Argument::UNTIL_DATE, 'some date');

        $this->assertTrue($request_no_arguments->hasCorrectArguments([], [Argument::IDENTIFIER], []));
        $this->assertTrue($request_one_argument->hasCorrectArguments([], [Argument::IDENTIFIER], []));
        $this->assertFalse($request_two_arguments->hasCorrectArguments([], [Argument::IDENTIFIER], []));
    }

    public function testHasCorrectArgumentsExclusive(): void
    {
        $expect_true = [];
        $expect_false = [];

        $expect_false[] = $this->getEmptyRequest();

        $expect_true[] = $this->getEmptyRequest()
                              ->withArgument(Argument::IDENTIFIER, 'some identifier');

        $expect_true[] = $this->getEmptyRequest()
                              ->withArgument(Argument::UNTIL_DATE, 'some date');

        $expect_false[] = $this->getEmptyRequest()
                               ->withArgument(Argument::FROM_DATE, 'some date');

        $expect_false[] = $this->getEmptyRequest()
                               ->withArgument(Argument::IDENTIFIER, 'some identifier')
                               ->withArgument(Argument::UNTIL_DATE, 'some date');

        $expect_false[] = $this->getEmptyRequest()
                               ->withArgument(Argument::FROM_DATE, 'some date')
                               ->withArgument(Argument::UNTIL_DATE, 'some date');

        foreach ($expect_true as $request) {
            $this->assertTrue($request->hasCorrectArguments(
                [Argument::UNTIL_DATE],
                [],
                [Argument::IDENTIFIER]
            ));
        }
        foreach ($expect_false as $request) {
            $this->assertFalse($request->hasCorrectArguments(
                [Argument::UNTIL_DATE],
                [],
                [Argument::IDENTIFIER]
            ));
        }
    }
}
