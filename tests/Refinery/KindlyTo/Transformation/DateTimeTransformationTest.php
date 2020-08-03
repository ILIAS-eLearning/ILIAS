<?php
/* Copyright (c) 2020 Luka K. A. Stocker, Extended GPL, see docs/LICENSE */

namespace ILIAS\Tests\Refinery\KindlyTo\Transformation;

require_once('./libs/composer/vendor/autoload.php');

use ILIAS\Refinery\ConstraintViolationException;
use ILIAS\Refinery\KindlyTo\Transformation\DateTimeTransformation;
use ILIAS\Tests\Refinery\TestCase;

/**
 * Tests for DateTimeImmutable and Unix Timetable transformation
 */

class DateTimeTransformationTest extends TestCase
{
    private $transformation;

    public function setUp() : void
    {
        $this->transformation = new DateTimeTransformation();
    }

    /**
     * @dataProvider DateTimeTransformationDataProvider
     * @param $originVal
     * @param $expectedVal
     */
    public function testDateTimeISOTransformation($originVal, $expectedVal)
    {
        $transformedValue = $this->transformation->transform($originVal);
        $this->assertIsObject($transformedValue);
        $this->assertInstanceOf(\DateTimeImmutable::class, $transformedValue);
        $this->assertEquals($expectedVal, $transformedValue);
    }

    /**
     * @dataProvider TransformationFailureDataProvider
     * @param $failingValue
     */
    public function testTransformIsInvalid($failingValue)
    {
        $this->expectException(ConstraintViolationException::class);
        $this->transformation->transform($failingValue);
    }

    public function DateTimeTransformationDataProvider()
    {
        $now = new \DateTimeImmutable();
        return [
            'datetime' => [$now, $now],
            'iso8601' => ['2020-07-06T12:23:05+0000',\DateTimeImmutable::createFromFormat(\DateTimeImmutable::ISO8601, '2020-07-06T12:23:05+0000')],
            'atom' => ['2020-07-06T12:23:05+00:00',\DateTimeImmutable::createFromFormat(\DateTimeImmutable::ATOM, '2020-07-06T12:23:05+00:00')],
            'rfc3339_ext' => ['2020-07-06T12:23:05.000+00:00',\DateTimeImmutable::createFromFormat(\DateTimeImmutable::RFC3339_EXTENDED, '2020-07-06T12:23:05.000+00:00')],
            'cookie' => ['Monday, 06-Jul-2020 12:23:05 GMT+0000',\DateTimeImmutable::createFromFormat(\DateTimeImmutable::COOKIE, 'Monday, 06-Jul-2020 12:23:05 GMT+0000')],
            'rfc822' => ['Mon, 06 Jul 20 12:23:05 +0000',\DateTimeImmutable::createFromFormat(\DateTimeImmutable::RFC822, 'Mon, 06 Jul 20 12:23:05 +0000')],
            'rfc7231' => ['Mon, 06 Jul 2020 12:23:05 GMT',\DateTimeImmutable::createFromFormat(\DateTimeImmutable::RFC7231, 'Mon, 06 Jul 2020 12:23:05 GMT')],
            'unix_timestamp' => [481556262, \DateTimeImmutable::createFromFormat(\DateTimeImmutable::ISO8601, '1985-04-05T13:37:42+0000')],
            'unix_timestamp_float' => [481556262.4, \DateTimeImmutable::createFromFormat(\DateTimeImmutable::ISO8601, '1985-04-05T13:37:42+0000')]
        ];
    }

    public function TransformationFailureDataProvider()
    {
        return [
            'no_matching_string_format' => ['hello']
        ];
    }
}
