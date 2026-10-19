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

namespace ILIAS\Language\Tests\Activities;

use ILIAS\Language\Activities\AmbiguousLanguageTitleException;
use ILIAS\Language\Activities\Failure;
use ILIAS\Language\Activities\InvalidInputException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class FailureTest extends TestCase
{
    /** @return array<string, array<int, mixed>>|list<array<int, mixed>> */
    public static function clientErrorProvider(): array
    {
        return [
            'invalid input (400)' => [new InvalidInputException('bad')],
            'forbidden (403)' => [new \DomainException('nope', 403)],
            'ambiguous title (409)' => [new AmbiguousLanguageTitleException('twice')],
            'lowest 4xx' => [new \RuntimeException('x', 400)],
            'highest 4xx' => [new \RuntimeException('x', 499)],
        ];
    }

    #[DataProvider('clientErrorProvider')]
    public function testOf4xxExceptionPassesThroughUnchanged(\Exception $e): void
    {
        $this->assertSame($e, Failure::of($e));
    }

    /** @return array<string, array<int, mixed>>|list<array<int, mixed>> */
    public static function defectProvider(): array
    {
        return [
            'code 0' => [new \RuntimeException('SQL: secret', 0)],
            'below 4xx (399)' => [new \RuntimeException('secret', 399)],
            'above 4xx (500)' => [new \RuntimeException('secret', 500)],
            'above 4xx (600)' => [new \RuntimeException('secret', 600)],
            'Error with 4xx code' => [new \TypeError('secret', 400)],
            'plain Error' => [new \Error('secret')],
        ];
    }

    #[DataProvider('defectProvider')]
    public function testOfMasksEverythingElseAsGeneric500KeepingTheOriginalAsPrevious(\Throwable $defect): void
    {
        $failure = Failure::of($defect);

        $this->assertNotSame($defect, $failure);
        $this->assertInstanceOf(\RuntimeException::class, $failure);
        $this->assertSame(500, $failure->getCode());
        $this->assertSame('The activity could not be performed.', $failure->getMessage());
        $this->assertSame($defect, $failure->getPrevious());
    }
}
