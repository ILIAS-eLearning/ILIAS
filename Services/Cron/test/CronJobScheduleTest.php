<?php

declare(strict_types=1);

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

use PHPUnit\Framework\TestCase;

/**
 * Class CronJobScheduleTest
 * @author Michael Jansen <mjansen@databay.de>
 */
class CronJobScheduleTest extends TestCase
{
    private DateTimeImmutable $now;

    private DateTimeImmutable $this_quarter_start;

    private function getJob(
        bool $has_flexible_schedule,
        int $default_schedule_type,
        ?int $default_schedule_value,
        int $schedule_type,
        ?int $schedule_value
    ): ilCronJob {
        $job_instance = new class ($has_flexible_schedule, $default_schedule_type, $default_schedule_value, $schedule_type, $schedule_value) extends ilCronJob {
            public function __construct(
                private readonly bool $has_flexible_schedule,
                private readonly int $default_schedule_type,
                private readonly ?int $default_schedule_value,
                int $schedule_type,
                ?int $schedule_value
            ) {
                $this->schedule_type = $schedule_type;
                $this->schedule_value = $schedule_value;
            }

            public function getId(): string
            {
                return 'phpunit';
            }

            public function getTitle(): string
            {
                return 'phpunit';
            }

            public function getDescription(): string
            {
                return 'phpunit';
            }

            public function hasAutoActivation(): bool
            {
                return false;
            }

            public function hasFlexibleSchedule(): bool
            {
                return $this->has_flexible_schedule;
            }

            public function getDefaultScheduleType(): int
            {
                return $this->default_schedule_type;
            }

            public function getDefaultScheduleValue(): ?int
            {
                return $this->default_schedule_value;
            }

            public function run(): ilCronJobResult
            {
                return new ilCronJobResult();
            }
        };

        $job_instance->setDateTimeProvider(function (): DateTimeImmutable {
            return $this->now;
        });

        return $job_instance;
    }

    public function jobProvider(): array
    {
        // Can't be moved to setUp(), because the data provider is executed before the tests are executed
        $this->now = new DateTimeImmutable('@' . time());

        $offset = (((int) $this->now->format('n')) - 1) % 3;
        $this->this_quarter_start = $this->now->modify("first day of -$offset month midnight");

        return [
            'Manual Run is Always Due' => [
                $this->getJob(true, ilCronJob::SCHEDULE_TYPE_DAILY, null, ilCronJob::SCHEDULE_TYPE_DAILY, null),
                true,
                null,
                ilCronJob::SCHEDULE_TYPE_DAILY,
                null,
                true
            ],
            'Job Without Any Run is Always Due' => [
                $this->getJob(true, ilCronJob::SCHEDULE_TYPE_DAILY, null, ilCronJob::SCHEDULE_TYPE_DAILY, null),
                false,
                null,
                ilCronJob::SCHEDULE_TYPE_DAILY,
                null,
                true
            ],
            'Daily Schedule / Did not run Today' => [
                $this->getJob(true, ilCronJob::SCHEDULE_TYPE_DAILY, null, ilCronJob::SCHEDULE_TYPE_DAILY, null),
                false,
                $this->now->modify('-1 day'),
                ilCronJob::SCHEDULE_TYPE_DAILY,
                null,
                true
            ],
            'Daily Schedule / Did run Today' => [
                $this->getJob(true, ilCronJob::SCHEDULE_TYPE_DAILY, null, ilCronJob::SCHEDULE_TYPE_DAILY, null),
                false,
                $this->now,
                ilCronJob::SCHEDULE_TYPE_DAILY,
                null,
                false
            ],
            'Weekly Schedule / Did not run this Week' => [
                $this->getJob(true, ilCronJob::SCHEDULE_TYPE_WEEKLY, null, ilCronJob::SCHEDULE_TYPE_WEEKLY, null),
                false,
                $this->now->modify('-1 week'),
                ilCronJob::SCHEDULE_TYPE_WEEKLY,
                null,
                true
            ],
            'Weekly Schedule / Did run this Week' => [
                $this->getJob(true, ilCronJob::SCHEDULE_TYPE_WEEKLY, null, ilCronJob::SCHEDULE_TYPE_WEEKLY, null),
                false,
                $this->now->modify('monday this week'),
                ilCronJob::SCHEDULE_TYPE_WEEKLY,
                null,
                false
            ],
            'Monthly Schedule / Did not run this Month' => [
                $this->getJob(true, ilCronJob::SCHEDULE_TYPE_MONTHLY, null, ilCronJob::SCHEDULE_TYPE_MONTHLY, null),
                false,
                $this->now->modify('last day of last month'),
                ilCronJob::SCHEDULE_TYPE_MONTHLY,
                null,
                true
            ],
            'Monthly Schedule / Did run this Month' => [
                $this->getJob(true, ilCronJob::SCHEDULE_TYPE_MONTHLY, null, ilCronJob::SCHEDULE_TYPE_MONTHLY, null),
                false,
                $this->now->modify('first day of this month'),
                ilCronJob::SCHEDULE_TYPE_MONTHLY,
                null,
                false
            ],
            'Yearly Schedule / Did not run this Year' => [
                $this->getJob(true, ilCronJob::SCHEDULE_TYPE_YEARLY, null, ilCronJob::SCHEDULE_TYPE_YEARLY, null),
                false,
                $this->now->modify('-1 year'),
                ilCronJob::SCHEDULE_TYPE_YEARLY,
                null,
                true
            ],
            'Yearly Schedule / Did run this Year' => [
                $this->getJob(true, ilCronJob::SCHEDULE_TYPE_YEARLY, null, ilCronJob::SCHEDULE_TYPE_YEARLY, null),
                false,
                $this->now->modify('first day of January this year'),
                ilCronJob::SCHEDULE_TYPE_YEARLY,
                null,
                false
            ],
            'Quarterly Schedule / Did not run this Quarter' => [
                $this->getJob(true, ilCronJob::SCHEDULE_TYPE_QUARTERLY, null, ilCronJob::SCHEDULE_TYPE_QUARTERLY, null),
                false,
                $this->this_quarter_start->modify('-1 seconds'),
                ilCronJob::SCHEDULE_TYPE_QUARTERLY,
                null,
                true
            ],
            'Quarterly Schedule / Did run this Quarter' => [
                $this->getJob(true, ilCronJob::SCHEDULE_TYPE_QUARTERLY, null, ilCronJob::SCHEDULE_TYPE_QUARTERLY, null),
                false,
                $this->this_quarter_start->modify('+30 seconds'),
                ilCronJob::SCHEDULE_TYPE_QUARTERLY,
                null,
                false
            ],
            'Minutely Schedule / Did not run this Minute' => [
                $this->getJob(true, ilCronJob::SCHEDULE_TYPE_IN_MINUTES, 1, ilCronJob::SCHEDULE_TYPE_IN_MINUTES, 1),
                false,
                $this->now->modify('-1 minute'),
                ilCronJob::SCHEDULE_TYPE_IN_MINUTES,
                1,
                true
            ],
            'Minutely Schedule / Did run this Minute' => [
                $this->getJob(true, ilCronJob::SCHEDULE_TYPE_IN_MINUTES, 1, ilCronJob::SCHEDULE_TYPE_IN_MINUTES, 1),
                false,
                $this->now->modify('-30 seconds'),
                ilCronJob::SCHEDULE_TYPE_IN_MINUTES,
                1,
                false
            ],
            'Hourly Schedule / Did not run this Hour' => [
                $this->getJob(true, ilCronJob::SCHEDULE_TYPE_IN_HOURS, 7, ilCronJob::SCHEDULE_TYPE_IN_HOURS, 7),
                false,
                $this->now->modify('-7 hours'),
                ilCronJob::SCHEDULE_TYPE_IN_HOURS,
                7,
                true
            ],
            'Hourly Schedule / Did run this Hour' => [
                $this->getJob(true, ilCronJob::SCHEDULE_TYPE_IN_HOURS, 7, ilCronJob::SCHEDULE_TYPE_IN_HOURS, 7),
                false,
                $this->now->modify('-7 hours +30 seconds'),
                ilCronJob::SCHEDULE_TYPE_IN_HOURS,
                7,
                false
            ],
            'Every 5 Days Schedule / Did not run for 5 Days' => [
                $this->getJob(true, ilCronJob::SCHEDULE_TYPE_IN_DAYS, 5, ilCronJob::SCHEDULE_TYPE_IN_DAYS, 5),
                false,
                $this->now->modify('-5 days'),
                ilCronJob::SCHEDULE_TYPE_IN_DAYS,
                5,
                true
            ],
            'Every 5 Days Schedule / Did run withing the last 5 Days' => [
                $this->getJob(true, ilCronJob::SCHEDULE_TYPE_IN_DAYS, 5, ilCronJob::SCHEDULE_TYPE_IN_DAYS, 5),
                false,
                $this->now->modify('-4 days'),
                ilCronJob::SCHEDULE_TYPE_IN_DAYS,
                5,
                false
            ],
            'Invalid Schedule Type' => [
                $this->getJob(true, PHP_INT_MAX, 5, PHP_INT_MAX, 5),
                false,
                $this->now,
                PHP_INT_MAX,
                5,
                false
            ]
        ];
    }

    /**
     * @dataProvider jobProvider
     */
    public function testSchedule(
        ilCronJob $job_instance,
        bool $is_manual_run,
        ?DateTimeImmutable $last_run_datetime,
        int $schedule_type,
        ?int $schedule_value,
        bool $should_be_due
    ): void {
        $this->assertSame(
            $should_be_due,
            $job_instance->isDue($last_run_datetime, $schedule_type, $schedule_value, $is_manual_run),
            'Last run: ' . ($last_run_datetime ? $last_run_datetime->format(DATE_ATOM) : 'never')
        );
    }

    public function weeklyScheduleProvider(): Generator
    {
        yield 'Different Week' => [
            $this->getJob(true, ilCronJob::SCHEDULE_TYPE_WEEKLY, null, ilCronJob::SCHEDULE_TYPE_WEEKLY, null),
            function (): DateTimeImmutable {
                $this->now = new DateTimeImmutable('@1672570104'); // Sun Jan 01 2023 10:48:24 GMT+0000 (year: 2023 / week: 52)

                return $this->now->modify('-1 week'); // Sun Dec 25 2022 10:48:24 GMT+0000 (year: 2022 / week: 51)
            },
            true
        ];

        yield 'Same Week and Year, but different Month: December (now) and January (Last run)' => [
            $this->getJob(true, ilCronJob::SCHEDULE_TYPE_WEEKLY, null, ilCronJob::SCHEDULE_TYPE_WEEKLY, null),
            function (): DateTimeImmutable {
                $this->now = new DateTimeImmutable('@1703669703'); // Wed Dec 27 2023 09:35:03 GMT+0000 (year: 2023 / week: 52 / month: 12)

                return new DateTimeImmutable('@1672570104'); // Sun Jan 01 2023 10:48:24 GMT+0000 (year: 2023 / week: 52 / month: 1)
            },
            true
        ];

        yield 'Same Week and Year and same Month: January' => [
            $this->getJob(true, ilCronJob::SCHEDULE_TYPE_WEEKLY, null, ilCronJob::SCHEDULE_TYPE_WEEKLY, null),
            function (): DateTimeImmutable {
                $this->now = new DateTimeImmutable('@1704188103'); // Tue Jan 02 2024 09:35:03 GMT+0000 (year: 2024 / week: 1 / month: 1)

                return $this->now->modify('-1 day'); // Mon Jan 01 2024 09:35:03 GMT+0000 (year: 2024 / week: 1 / month: 1)
            },
            false
        ];

        yield 'Same Week (52nd), but Year Difference > 1' => [
            $this->getJob(true, ilCronJob::SCHEDULE_TYPE_WEEKLY, null, ilCronJob::SCHEDULE_TYPE_WEEKLY, null),
            function (): DateTimeImmutable {
                $this->now = new DateTimeImmutable('@1672570104'); // Sun Jan 01 2023 10:48:24 GMT+0000 (year: 2023 / week: 52)

                return $this->now->modify('tuesday this week')->modify('-1 year'); // Mon Dec 27 2021 10:48:24 GMT+0000 (year: 2021 / week: 52)
            },
            true
        ];

        yield 'Same Week (52nd) in different Years, but Turn of the Year' => [
            $this->getJob(true, ilCronJob::SCHEDULE_TYPE_WEEKLY, null, ilCronJob::SCHEDULE_TYPE_WEEKLY, null),
            function (): DateTimeImmutable {
                $this->now = new DateTimeImmutable('@1672570104'); // Sun Jan 01 2023 10:48:24 GMT+0000 (year: 2023 / week: 52 / month: 1)

                return $this->now->modify('monday this week'); // Mon Dec 26 2022 10:48:24 GMT+0000 (year: 2022 / week: 52 / month: 12)
            },
            false
        ];

        yield 'Same Week (52nd) in different Years, but not Turn of the Year' => [
            $this->getJob(true, ilCronJob::SCHEDULE_TYPE_WEEKLY, null, ilCronJob::SCHEDULE_TYPE_WEEKLY, null),
            function (): DateTimeImmutable {
                $this->now = new DateTimeImmutable('@1703669703'); // Wed Dec 27 2023 09:35:03 GMT+0000 (year: 2023 / week: 52 / month: 12)

                return new DateTimeImmutable('@1672012800'); // Mon Dec 26 2022 00:00:00 GMT+0000 (year: 2022 / week: 52 / month: 12)
            },
            true
        ];
    }

    /**
     * @dataProvider weeklyScheduleProvider
     * @param callable(): DateTimeImmutable $last_run_datetime_provider
     */
    public function testWeeklySchedules(
        ilCronJob $job_instance,
        callable $last_run_datetime_provider,
        bool $should_be_due
    ): void {
        $last_run_datetime = $last_run_datetime_provider();

        $this->assertSame(
            $should_be_due,
            $job_instance->isDue(
                $last_run_datetime,
                $job_instance->getScheduleType(),
                $job_instance->getScheduleValue(),
                false
            ),
            'Last run: ' . $last_run_datetime->format(DATE_ATOM)
        );
    }
}
