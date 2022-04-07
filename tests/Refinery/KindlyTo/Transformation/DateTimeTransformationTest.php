<?php declare(strict_types=1);

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

namespace ILIAS\Tests\Refinery\KindlyTo\Transformation;

use DateTimeImmutable;
use DateTimeInterface;
use ILIAS\Refinery\ConstraintViolationException;
use ILIAS\Refinery\KindlyTo\Transformation\DateTimeTransformation;
use ILIAS\Tests\Refinery\TestCase;

class DateTimeTransformationTest extends TestCase
{
    private DateTimeTransformation $transformation;

    protected function setUp() : void
    {
        $this->transformation = new DateTimeTransformation();
    }

    /**
     * @dataProvider DateTimeTransformationDataProvider
     * @param mixed $originVal
     * @param DateTimeImmutable $expectedVal
     */
    public function testDateTimeISOTransformation($originVal, DateTimeImmutable $expectedVal) : void
    {
        $transformedValue = $this->transformation->transform($originVal);
        $this->assertIsObject($transformedValue);
        $this->assertInstanceOf(DateTimeImmutable::class, $transformedValue);
        $this->assertEquals($expectedVal, $transformedValue);
    }

    /**
     * @dataProvider TransformationFailureDataProvider
     * @param string$failingValue
     */
    public function testTransformIsInvalid(string $failingValue) : void
    {
        $this->expectException(ConstraintViolationException::class);
        $this->transformation->transform($failingValue);
    }

    public function DateTimeTransformationDataProvider() : array
    {
        $now = new DateTimeImmutable();
        return [
            'datetime' => [$now, $now],
            'iso8601' => ['2020-07-06T12:23:05+0000',
                DateTimeImmutable::createFromFormat(DateTimeInterface::ISO8601, '2020-07-06T12:23:05+0000')],
            'atom' => ['2020-07-06T12:23:05+00:00',
                DateTimeImmutable::createFromFormat(DateTimeInterface::ATOM, '2020-07-06T12:23:05+00:00')],
            'rfc3339_ext' => ['2020-07-06T12:23:05.000+00:00',
                DateTimeImmutable::createFromFormat(DateTimeInterface::RFC3339_EXTENDED, '2020-07-06T12:23:05.000+00:00')],
            'cookie' => ['Monday, 06-Jul-2020 12:23:05 GMT+0000',
                DateTimeImmutable::createFromFormat(DateTimeInterface::COOKIE, 'Monday, 06-Jul-2020 12:23:05 GMT+0000')],
            'rfc822' => ['Mon, 06 Jul 20 12:23:05 +0000',
                DateTimeImmutable::createFromFormat(DateTimeInterface::RFC822, 'Mon, 06 Jul 20 12:23:05 +0000')],
            'rfc7231' => ['Mon, 06 Jul 2020 12:23:05 GMT',
                DateTimeImmutable::createFromFormat(DateTimeInterface::RFC7231, 'Mon, 06 Jul 2020 12:23:05 GMT')],
            'unix_timestamp' => [481556262, DateTimeImmutable::createFromFormat(DateTimeInterface::ISO8601, '1985-04-05T13:37:42+0000')],
            'unix_timestamp_float' => [481556262.4, DateTimeImmutable::createFromFormat(DateTimeInterface::ISO8601, '1985-04-05T13:37:42+0000')]
        ];
    }

    public function TransformationFailureDataProvider() : array
    {
        return [
            'no_matching_string_format' => ['hello']
        ];
    }
}
