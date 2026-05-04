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

use PHPUnit\Framework\TestCase;
use ILIAS\Cron\Job\Schedule\JobScheduleType;
use ILIAS\Cron\Job\JobResult;
use ILIAS\Cron\AbstractCronJob;
use ILIAS\Cron\CronJob;
use PHPUnit\Framework\Attributes\DataProvider;

class CronJobScheduleTest extends TestCase
{
    private static DateTimeImmutable $now;
    private static DateTimeImmutable $this_quarter_start;

    /**
     * Builds the anonymous {@see CronJob} used by schedule tests (requires test-case mocks).
     */
    private function getJob(
        bool $has_flexible_schedule,
        JobScheduleType $default_schedule_type,
        ?int $default_schedule_value,
        JobScheduleType $schedule_type,
        ?int $schedule_value
    ): CronJob {
        $language = $this->createMock(\ILIAS\Language\Language::class);
        $logger_factory = $this->getMockBuilder(\ILIAS\Logging\LoggerFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $job_instance = new class (
            $has_flexible_schedule,
            $default_schedule_type,
            $default_schedule_value,
            $schedule_type,
            $schedule_value,
            $language,
            $logger_factory
        ) extends AbstractCronJob {
            public function __construct(
                private readonly bool $has_flexible_schedule,
                private readonly JobScheduleType $default_schedule_type,
                private readonly ?int $default_schedule_value,
                JobScheduleType $schedule_type,
                ?int $schedule_value,
                \ILIAS\Language\Language $language,
                \ILIAS\Logging\LoggerFactory $logger_factory,
            ) {
                parent::__construct(\ILIAS\Cron\Cron::class, $language, $logger_factory);
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

            public function getDefaultScheduleType(): JobScheduleType
            {
                return $this->default_schedule_type;
            }

            public function getDefaultScheduleValue(): ?int
            {
                return $this->default_schedule_value;
            }

            public function run(): JobResult
            {
                return new JobResult();
            }
        };

        $job_instance->setDateTimeProvider(fn(): DateTimeImmutable => self::$now);

        return $job_instance;
    }

    /**
     * Each case defers {@see self::getJob()} via a closure so static providers can run without
     * {@see TestCase} mock helpers
     *
     * @return array<string, array{
     *     0: Closure(CronJobScheduleTest): CronJob,
     *     1: bool,
     *     2: ?callable(): DateTimeImmutable,
     *     3: JobScheduleType,
     *     4: ?int,
     *     5: bool
     * }>
     */
    public static function jobProvider(): array
    {
        return [
            'Manual Run is Always Due' => [
                fn(CronJobScheduleTest $case): CronJob => $case->getJob(
                    true,
                    JobScheduleType::DAILY,
                    null,
                    JobScheduleType::DAILY,
                    null
                ),
                true,
                null,
                JobScheduleType::DAILY,
                null,
                true
            ],
            'Job Without Any Run is Always Due' => [
                fn(CronJobScheduleTest $case): CronJob => $case->getJob(
                    true,
                    JobScheduleType::DAILY,
                    null,
                    JobScheduleType::DAILY,
                    null
                ),
                false,
                null,
                JobScheduleType::DAILY,
                null,
                true
            ],
            'Daily Schedule / Did not run Today' => [
                fn(CronJobScheduleTest $case): CronJob => $case->getJob(
                    true,
                    JobScheduleType::DAILY,
                    null,
                    JobScheduleType::DAILY,
                    null
                ),
                false,
                function (): DateTimeImmutable {
                    self::$now = new DateTimeImmutable('@' . time());

                    return self::$now->modify('-1 day');
                },
                JobScheduleType::DAILY,
                null,
                true
            ],
            'Daily Schedule / Did run Today' => [
                fn(CronJobScheduleTest $case): CronJob => $case->getJob(
                    true,
                    JobScheduleType::DAILY,
                    null,
                    JobScheduleType::DAILY,
                    null
                ),
                false,
                function (): DateTimeImmutable {
                    self::$now = new DateTimeImmutable('@' . time());

                    return self::$now;
                },
                JobScheduleType::DAILY,
                null,
                false
            ],
            'Weekly Schedule / Did not run this Week' => [
                fn(CronJobScheduleTest $case): CronJob => $case->getJob(
                    true,
                    JobScheduleType::WEEKLY,
                    null,
                    JobScheduleType::WEEKLY,
                    null
                ),
                false,
                function (): DateTimeImmutable {
                    self::$now = new DateTimeImmutable('@' . time());

                    return self::$now->modify('-1 week');
                },
                JobScheduleType::WEEKLY,
                null,
                true
            ],
            'Weekly Schedule / Did run this Week' => [
                fn(CronJobScheduleTest $case): CronJob => $case->getJob(
                    true,
                    JobScheduleType::WEEKLY,
                    null,
                    JobScheduleType::WEEKLY,
                    null
                ),
                false,
                function (): DateTimeImmutable {
                    self::$now = new DateTimeImmutable('@' . time());

                    return self::$now->modify('monday this week');
                },
                JobScheduleType::WEEKLY,
                null,
                false
            ],
            'Monthly Schedule / Did not run this Month' => [
                fn(CronJobScheduleTest $case): CronJob => $case->getJob(
                    true,
                    JobScheduleType::MONTHLY,
                    null,
                    JobScheduleType::MONTHLY,
                    null
                ),
                false,
                function (): DateTimeImmutable {
                    self::$now = new DateTimeImmutable('@' . time());

                    return self::$now->modify('last day of last month');
                },
                JobScheduleType::MONTHLY,
                null,
                true
            ],
            'Monthly Schedule / Did run this Month' => [
                fn(CronJobScheduleTest $case): CronJob => $case->getJob(
                    true,
                    JobScheduleType::MONTHLY,
                    null,
                    JobScheduleType::MONTHLY,
                    null
                ),
                false,
                function (): DateTimeImmutable {
                    self::$now = new DateTimeImmutable('@' . time());

                    return self::$now->modify('first day of this month');
                },
                JobScheduleType::MONTHLY,
                null,
                false
            ],
            'Yearly Schedule / Did not run this Year' => [
                fn(CronJobScheduleTest $case): CronJob => $case->getJob(
                    true,
                    JobScheduleType::YEARLY,
                    null,
                    JobScheduleType::YEARLY,
                    null
                ),
                false,
                function (): DateTimeImmutable {
                    self::$now = new DateTimeImmutable('@' . time());

                    return self::$now->modify('-1 year');
                },
                JobScheduleType::YEARLY,
                null,
                true
            ],
            'Yearly Schedule / Did run this Year' => [
                fn(CronJobScheduleTest $case): CronJob => $case->getJob(
                    true,
                    JobScheduleType::YEARLY,
                    null,
                    JobScheduleType::YEARLY,
                    null
                ),
                false,
                function (): DateTimeImmutable {
                    self::$now = new DateTimeImmutable('@' . time());

                    return self::$now->modify('first day of January this year');
                },
                JobScheduleType::YEARLY,
                null,
                false
            ],
            'Quarterly Schedule / Did not run this Quarter' => [
                fn(CronJobScheduleTest $case): CronJob => $case->getJob(
                    true,
                    JobScheduleType::QUARTERLY,
                    null,
                    JobScheduleType::QUARTERLY,
                    null
                ),
                false,
                function (): DateTimeImmutable {
                    self::$now = new DateTimeImmutable('@' . time());

                    $offset = (((int) self::$now->format('n')) - 1) % 3;
                    self::$this_quarter_start = self::$now->modify("first day of -$offset month midnight");

                    return self::$this_quarter_start->modify('-1 seconds');
                },
                JobScheduleType::QUARTERLY,
                null,
                true
            ],
            'Quarterly Schedule / Did run this Quarter' => [
                fn(CronJobScheduleTest $case): CronJob => $case->getJob(
                    true,
                    JobScheduleType::QUARTERLY,
                    null,
                    JobScheduleType::QUARTERLY,
                    null
                ),
                false,
                function (): DateTimeImmutable {
                    self::$now = new DateTimeImmutable('@' . time());

                    $offset = (((int) self::$now->format('n')) - 1) % 3;
                    self::$this_quarter_start = self::$now->modify("first day of -$offset month midnight");

                    return self::$this_quarter_start->modify('+30 seconds');
                },
                JobScheduleType::QUARTERLY,
                null,
                false
            ],
            'Minutely Schedule / Did not run this Minute' => [
                fn(CronJobScheduleTest $case): CronJob => $case->getJob(
                    true,
                    JobScheduleType::IN_MINUTES,
                    1,
                    JobScheduleType::IN_MINUTES,
                    1
                ),
                false,
                function (): DateTimeImmutable {
                    self::$now = new DateTimeImmutable('@' . time());

                    return self::$now->modify('-1 minute');
                },
                JobScheduleType::IN_MINUTES,
                1,
                true
            ],
            'Minutely Schedule / Did run this Minute' => [
                fn(CronJobScheduleTest $case): CronJob => $case->getJob(
                    true,
                    JobScheduleType::IN_MINUTES,
                    1,
                    JobScheduleType::IN_MINUTES,
                    1
                ),
                false,
                function (): DateTimeImmutable {
                    self::$now = new DateTimeImmutable('@' . time());

                    return self::$now->modify('-30 seconds');
                },
                JobScheduleType::IN_MINUTES,
                1,
                false
            ],
            'Hourly Schedule / Did not run this Hour' => [
                fn(CronJobScheduleTest $case): CronJob => $case->getJob(
                    true,
                    JobScheduleType::IN_HOURS,
                    7,
                    JobScheduleType::IN_HOURS,
                    7
                ),
                false,
                function (): DateTimeImmutable {
                    self::$now = new DateTimeImmutable('@' . time());

                    return self::$now->modify('-7 hours');
                },
                JobScheduleType::IN_HOURS,
                7,
                true
            ],
            'Hourly Schedule / Did run this Hour' => [
                fn(CronJobScheduleTest $case): CronJob => $case->getJob(
                    true,
                    JobScheduleType::IN_HOURS,
                    7,
                    JobScheduleType::IN_HOURS,
                    7
                ),
                false,
                function (): DateTimeImmutable {
                    self::$now = new DateTimeImmutable('@' . time());

                    return self::$now->modify('-7 hours +30 seconds');
                },
                JobScheduleType::IN_HOURS,
                7,
                false
            ],
            'Every 5 Days Schedule / Did not run for 5 Days' => [
                fn(CronJobScheduleTest $case): CronJob => $case->getJob(
                    true,
                    JobScheduleType::IN_DAYS,
                    5,
                    JobScheduleType::IN_DAYS,
                    5
                ),
                false,
                function (): DateTimeImmutable {
                    self::$now = new DateTimeImmutable('@' . time());

                    return self::$now->modify('-5 days');
                },
                JobScheduleType::IN_DAYS,
                5,
                true
            ],
            'Every 5 Days Schedule / Did run withing the last 5 Days' => [
                fn(CronJobScheduleTest $case): CronJob => $case->getJob(
                    true,
                    JobScheduleType::IN_DAYS,
                    5,
                    JobScheduleType::IN_DAYS,
                    5
                ),
                false,
                function (): DateTimeImmutable {
                    self::$now = new DateTimeImmutable('@' . time());

                    return self::$now->modify('-4 days');
                },
                JobScheduleType::IN_DAYS,
                5,
                false
            ]
        ];
    }

    /**
     * @param Closure(CronJobScheduleTest): CronJob $job_factory
     * @param null|callable(): DateTimeImmutable    $last_run_datetime_callable
     */
    #[DataProvider('jobProvider')]
    public function testSchedule(
        Closure $job_factory,
        bool $is_manual_run,
        ?callable $last_run_datetime_callable,
        JobScheduleType $schedule_type,
        ?int $schedule_value,
        bool $should_be_due
    ): void {
        $job_instance = $job_factory($this);
        $last_run_datetime = $last_run_datetime_callable ? $last_run_datetime_callable() : null;
        self::assertEquals(
            $should_be_due,
            $job_instance->isDue($last_run_datetime, $schedule_type, $schedule_value, $is_manual_run),
            'Last run: ' . ($last_run_datetime ? $last_run_datetime->format(DATE_ATOM) : 'never')
        );
    }

    /**
     * @return Generator<string, array{
     *     0: Closure(CronJobScheduleTest): CronJob,
     *     1: callable(): DateTimeImmutable,
     *     2: bool
     * }>
     */
    public static function weeklyScheduleProvider(): Generator
    {
        yield 'Different Week' => [
            fn(CronJobScheduleTest $case): CronJob => $case->getJob(
                true,
                JobScheduleType::WEEKLY,
                null,
                JobScheduleType::WEEKLY,
                null
            ),
            function (): DateTimeImmutable {
                self::$now = new DateTimeImmutable(
                    '@1672570104'
                ); // Sun Jan 01 2023 10:48:24 GMT+0000 (year: 2023 / week: 52)

                return self::$now->modify('-1 week'); // Sun Dec 25 2022 10:48:24 GMT+0000 (year: 2022 / week: 51)
            },
            true
        ];

        yield 'Same Week and Year, but different Month: December (now) and January (Last run)' => [
            fn(CronJobScheduleTest $case): CronJob => $case->getJob(
                true,
                JobScheduleType::WEEKLY,
                null,
                JobScheduleType::WEEKLY,
                null
            ),
            function (): DateTimeImmutable {
                self::$now = new DateTimeImmutable(
                    '@1703669703'
                ); // Wed Dec 27 2023 09:35:03 GMT+0000 (year: 2023 / week: 52 / month: 12)

                return new DateTimeImmutable(
                    '@1672570104'
                ); // Sun Jan 01 2023 10:48:24 GMT+0000 (year: 2023 / week: 52 / month: 1)
            },
            true
        ];

        yield 'Same Week and Year and same Month: January' => [
            fn(CronJobScheduleTest $case): CronJob => $case->getJob(
                true,
                JobScheduleType::WEEKLY,
                null,
                JobScheduleType::WEEKLY,
                null
            ),
            function (): DateTimeImmutable {
                self::$now = new DateTimeImmutable(
                    '@1704188103'
                ); // Tue Jan 02 2024 09:35:03 GMT+0000 (year: 2024 / week: 1 / month: 1)

                return self::$now->modify(
                    '-1 day'
                ); // Mon Jan 01 2024 09:35:03 GMT+0000 (year: 2024 / week: 1 / month: 1)
            },
            false
        ];

        yield 'Same Week (52nd), but Year Difference > 1' => [
            fn(CronJobScheduleTest $case): CronJob => $case->getJob(
                true,
                JobScheduleType::WEEKLY,
                null,
                JobScheduleType::WEEKLY,
                null
            ),
            function (): DateTimeImmutable {
                self::$now = new DateTimeImmutable(
                    '@1672570104'
                ); // Sun Jan 01 2023 10:48:24 GMT+0000 (year: 2023 / week: 52)

                return self::$now->modify('tuesday this week')->modify(
                    '-1 year'
                ); // Mon Dec 27 2021 10:48:24 GMT+0000 (year: 2021 / week: 52)
            },
            true
        ];

        yield 'Same Week (52nd) in different Years, but Turn of the Year' => [
            fn(CronJobScheduleTest $case): CronJob => $case->getJob(
                true,
                JobScheduleType::WEEKLY,
                null,
                JobScheduleType::WEEKLY,
                null
            ),
            function (): DateTimeImmutable {
                self::$now = new DateTimeImmutable(
                    '@1672570104'
                ); // Sun Jan 01 2023 10:48:24 GMT+0000 (year: 2023 / week: 52 / month: 1)

                return self::$now->modify(
                    'monday this week'
                ); // Mon Dec 26 2022 10:48:24 GMT+0000 (year: 2022 / week: 52 / month: 12)
            },
            false
        ];

        yield 'Same Week (52nd) in different Years, but not Turn of the Year' => [
            fn(CronJobScheduleTest $case): CronJob => $case->getJob(
                true,
                JobScheduleType::WEEKLY,
                null,
                JobScheduleType::WEEKLY,
                null
            ),
            function (): DateTimeImmutable {
                self::$now = new DateTimeImmutable(
                    '@1703669703'
                ); // Wed Dec 27 2023 09:35:03 GMT+0000 (year: 2023 / week: 52 / month: 12)

                return new DateTimeImmutable(
                    '@1672012800'
                ); // Mon Dec 26 2022 00:00:00 GMT+0000 (year: 2022 / week: 52 / month: 12)
            },
            true
        ];
    }

    /**
     * @param Closure(CronJobScheduleTest): CronJob $job_factory
     * @param callable(): DateTimeImmutable         $last_run_datetime_provider
     */
    #[DataProvider('weeklyScheduleProvider')]
    public function testWeeklySchedules(
        Closure $job_factory,
        callable $last_run_datetime_provider,
        bool $should_be_due
    ): void {
        $job_instance = $job_factory($this);
        $last_run_datetime = $last_run_datetime_provider();

        self::assertSame(
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
