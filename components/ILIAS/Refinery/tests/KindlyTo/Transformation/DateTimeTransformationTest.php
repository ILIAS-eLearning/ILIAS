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

namespace ILIAS\Tests\Refinery\KindlyTo\Transformation;

use DateTimeImmutable;
use DateTimeInterface;
use ILIAS\Refinery\ConstraintViolationException;
use ILIAS\Refinery\KindlyTo\Transformation\DateTimeTransformation;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use DateTimeZone;

class DateTimeTransformationTest extends TestCase
{
    private DateTimeTransformation $transformation;

    protected function setUp(): void
    {
        $this->transformation = new DateTimeTransformation();
    }

    #[DataProvider('DateTimeTransformationDataProvider')]
    public function testDateTimeISOTransformation(mixed $originVal, DateTimeImmutable $expectedVal): void
    {
        $transformedValue = $this->transformation->transform($originVal);
        $this->assertIsObject($transformedValue);
        $this->assertInstanceOf(DateTimeImmutable::class, $transformedValue);
        $this->assertEquals($expectedVal, $transformedValue);
    }

    /**
     * @see https://github.com/php/php-src/pull/2450
     * @see https://github.com/php/php-src/pull/12989
     */
    public function testRFC7231ResultsInMisleadingFormattedDateString(): void
    {
        $gmt_format = 'D, d M Y H:i:s \G\M\T'; // former DateTimeInterface::RFC7231
        $test_gmt_date_time = 'Mon, 06 Jul 2020 12:23:05 GMT';

        $exptected = DateTimeImmutable::createFromFormat(
            $gmt_format,
            $test_gmt_date_time
        );

        $actual = $this->transformation->transform($test_gmt_date_time);
        $this->assertEquals($exptected, $actual);

        $actual = $actual->setTimezone(new DateTimeZone('Europe/Berlin'));
        // GMT in the provided format is just a string, it does not effect the presented timezone
        $this->assertSame('Mon, 06 Jul 2020 14:23:05 GMT', $actual->format($gmt_format));
        $this->assertEquals($exptected, $actual);
    }

    #[DataProvider('TransformationFailureDataProvider')]
    public function testTransformIsInvalid(string $failingValue): void
    {
        $this->expectException(ConstraintViolationException::class);
        $this->transformation->transform($failingValue);
    }

    public static function DateTimeTransformationDataProvider(): array
    {
        $now = new DateTimeImmutable();
        return [
            'datetime' => [$now, $now],
            'iso8601' => [
                '2020-07-06T12:23:05+0000',
                DateTimeImmutable::createFromFormat(DateTimeInterface::ISO8601, '2020-07-06T12:23:05+0000')
            ],
            'atom' => [
                '2020-07-06T12:23:05+00:00',
                DateTimeImmutable::createFromFormat(DateTimeInterface::ATOM, '2020-07-06T12:23:05+00:00')
            ],
            'rfc3339_ext' => [
                '2020-07-06T12:23:05.000+00:00',
                DateTimeImmutable::createFromFormat(
                    DateTimeInterface::RFC3339_EXTENDED,
                    '2020-07-06T12:23:05.000+00:00'
                )
            ],
            'cookie' => [
                'Monday, 06-Jul-2020 12:23:05 GMT+0000',
                DateTimeImmutable::createFromFormat(DateTimeInterface::COOKIE, 'Monday, 06-Jul-2020 12:23:05 GMT+0000')
            ],
            'rfc822' => [
                'Mon, 06 Jul 20 12:23:05 +0000',
                DateTimeImmutable::createFromFormat(DateTimeInterface::RFC822, 'Mon, 06 Jul 20 12:23:05 +0000')
            ],
            'rfc7231' => [
                'Mon, 06 Jul 2020 12:23:05 GMT',
                DateTimeImmutable::createFromFormat('D, d M Y H:i:s \G\M\T', 'Mon, 06 Jul 2020 12:23:05 GMT')
            ],
            'unix_timestamp' => [
                481556262,
                DateTimeImmutable::createFromFormat(
                    DateTimeInterface::ISO8601,
                    '1985-04-05T13:37:42+0000'
                )
            ],
            'unix_timestamp_float' => [
                481556262.4,
                DateTimeImmutable::createFromFormat(
                    DateTimeInterface::ISO8601,
                    '1985-04-05T13:37:42+0000'
                )
            ]
        ];
    }

    public static function TransformationFailureDataProvider(): array
    {
        return [
            'no_matching_string_format' => ['hello']
        ];
    }
}
