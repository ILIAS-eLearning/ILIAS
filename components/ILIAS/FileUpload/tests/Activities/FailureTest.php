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

namespace ILIAS\FileUpload\Activities;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class FailureTest extends TestCase
{
    /**
     * @return array<string, array{\Throwable}>
     */
    public static function answers(): array
    {
        return [
            'bad request' => [new \InvalidArgumentException('A file name is required.', 400)],
            'forbidden' => [new \DomainException('You are not allowed to read this file.', 403)],
            'not found' => [new \OutOfBoundsException('There is no file object 23.', 404)],
            'unprocessable' => [new \DomainException('Too many files.', 422)],
        ];
    }

    #[DataProvider('answers')]
    public function testAnAnswerToTheRequestLeavesAsItIs(\Throwable $e): void
    {
        $this->assertSame($e, Failure::of($e));
    }

    /**
     * @return array<string, array{\Throwable}>
     */
    public static function defects(): array
    {
        return [
            'an exception without a code' => [new \RuntimeException('SELECT * FROM il_resource WHERE rid = 1')],
            'a server error' => [new \RuntimeException('File "storage/fsv2/some/path" not found.', 500)],
            'an error' => [new \TypeError('Argument #1 must be of type int')],
            'a database error with its SQLSTATE' => [
                self::withCode(new \PDOException("SQLSTATE[42S22]: Unknown column 'x' in SELECT x FROM usr_data"), '42S22')
            ],
        ];
    }

    /**
     * PDO sets its SQLSTATE as the code, the constructor only takes an int.
     */
    private static function withCode(\Exception $e, string $code): \Exception
    {
        (new \ReflectionProperty(\Exception::class, 'code'))->setValue($e, $code);

        return $e;
    }

    #[DataProvider('defects')]
    public function testADefectKeepsItsMessageInside(\Throwable $e): void
    {
        $failure = Failure::of($e);

        $this->assertNotSame($e, $failure);
        $this->assertSame(500, $failure->getCode());
        $this->assertSame('The activity could not be performed.', $failure->getMessage());
        $this->assertSame($e, $failure->getPrevious());
    }
}
