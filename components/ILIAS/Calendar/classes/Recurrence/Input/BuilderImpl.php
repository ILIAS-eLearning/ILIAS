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

namespace ILIAS\Calendar\Recurrence\Input;

use ilLanguage;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Component\Input\Field\Group;
use ILIAS\UI\Component\Input\Input;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\Refinery\Transformation;
use ilCalendarUserSettings;
use ilCalendarUtil;
use ilCalendarRecurrence;
use ilDate;
use DateTimeZone;
use ILIAS\Calendar\Recurrence\Weekday;
use ILIAS\Calendar\Recurrence\Ordinal;
use ILIAS\Calendar\Recurrence\Month;
use DateTimeImmutable;

class BuilderImpl implements Builder
{
    // radios
    protected const string RULE = 'rule';
    protected const string END = 'end';

    //rule freq
    protected const string NO_RECURRENCE = 'none';
    protected const string DAILY = 'daily';
    protected const string WEEKLY = 'weekly';
    protected const string MONTHLY_BY_DAY = 'monthly_by_day';
    protected const string MONTHLY_BY_DATE = 'monthly_by_date';
    protected const string YEARLY_BY_DAY = 'yearly_by_day';
    protected const string YEARLY_BY_DATE = 'yearly_by_date';

    // common rule inputs
    protected const string INTERVAL = 'interval';
    protected const string MONTH = 'month';
    protected const string WEEK = 'week';
    protected const string DAY = 'day';
    protected const string DAY_OF_MONTH = 'day_of_month';


    // end inputs
    protected const string NO_UNTIL = 'no_until';
    protected const string COUNT = 'count';
    protected const string UNTIL_COUNT = 'until_count';
    protected const string END_DATE = 'end_date';
    protected const string UNTIL_END_DATE = 'until_end_date';

    protected bool $unlimited_recurrences = true;
    protected bool $daily = true;
    protected bool $weekly = true;
    protected bool $monthly = true;
    protected bool $yearly = true;

    public function __construct(
        protected ilCalendarRecurrence $recurrence,
        protected UIFactory $ui_factory,
        protected Refinery $refinery,
        protected ilLanguage $lng,
        protected ilCalendarUserSettings $user_settings
    ) {
    }

    public function withoutUnlimitedRecurrences(bool $without = true): Builder
    {
        $clone = clone $this;
        $clone->unlimited_recurrences = !$without;
        return $clone;
    }

    public function withoutDaily(bool $without = true): Builder
    {
        $clone = clone $this;
        $clone->daily = !$without;
        return $clone;
    }

    public function withoutWeekly(bool $without = true): Builder
    {
        $clone = clone $this;
        $clone->weekly = !$without;
        return $clone;
    }

    public function withoutMonthly(bool $without = true): Builder
    {
        $clone = clone $this;
        $clone->monthly = !$without;
        return $clone;
    }

    public function withoutYearly(bool $without = true): Builder
    {
        $clone = clone $this;
        $clone->yearly = !$without;
        return $clone;
    }

    public function hasUnlimitedRecurrences(): bool
    {
        return $this->unlimited_recurrences;
    }

    public function hasDaily(): bool
    {
        return $this->daily;
    }

    public function hasWeekly(): bool
    {
        return $this->weekly;
    }

    public function hasMonthly(): bool
    {
        return $this->monthly;
    }

    public function hasYearly(): bool
    {
        return $this->yearly;
    }

    public function get(): Group
    {
        return $this->ui_factory->input()->field()->group([
            self::RULE => $this->getRuleInput()
        ])->withAdditionalTransformation($this->getOutputTransformation());
    }

    protected function getRuleInput(): Input
    {
        $groups = [];
        $groups[self::NO_RECURRENCE] = $this->ui_factory->input()->field()->group(
            [],
            $this->lng->txt('cal_no_recurrence')
        );
        if ($this->hasDaily()) {
            $groups[self::DAILY] = $this->getDailyGroup();
        }
        if ($this->hasWeekly()) {
            $groups[self::WEEKLY] = $this->getWeeklyGroup();
        }
        if ($this->hasMonthly()) {
            $groups[self::MONTHLY_BY_DAY] = $this->getMonthlyByDayGroup();
            $groups[self::MONTHLY_BY_DATE] = $this->getMonthlyByDateGroup();
        }
        if ($this->hasYearly()) {
            $groups[self::YEARLY_BY_DAY] = $this->getYearlyByDayGroup();
            $groups[self::YEARLY_BY_DATE] = $this->getYearlyByDateGroup();
        }

        $value = match ($this->recurrence->getFrequenceType()) {
            ilCalendarRecurrence::FREQ_DAILY => self::DAILY,
            ilCalendarRecurrence::FREQ_WEEKLY => self::WEEKLY,
            ilCalendarRecurrence::FREQ_MONTHLY => $this->recurrence->getBYDAY() ? self::MONTHLY_BY_DAY : self::MONTHLY_BY_DATE,
            ilCalendarRecurrence::FREQ_YEARLY => $this->recurrence->getBYDAY() ? self::YEARLY_BY_DAY : self::YEARLY_BY_DATE,
            default => self::NO_RECURRENCE
        };
        return $this->ui_factory->input()->field()->switchableGroup(
            $groups,
            $this->lng->txt('cal_recurrences')
        )->withValue($value);
    }

    protected function getDailyGroup(): Group
    {
        return $this->ui_factory->input()->field()->group(
            [
                self::INTERVAL => $this->getIntervalInput($this->lng->txt('cal_recurrence_day_interval')),
                self::END => $this->getEndInput()
            ],
            $this->lng->txt('cal_daily')
        );
    }

    protected function getWeeklyGroup(): Group
    {
        return $this->ui_factory->input()->field()->group(
            [
                self::INTERVAL => $this->getIntervalInput($this->lng->txt('cal_recurrence_week_interval')),
                self::DAY => $this->getDayInput(),
                self::END => $this->getEndInput()
            ],
            $this->lng->txt('cal_weekly')
        );
    }

    protected function getMonthlyByDayGroup(): Group
    {
        return $this->ui_factory->input()->field()->group(
            [
                self::INTERVAL => $this->getIntervalInput($this->lng->txt('cal_recurrence_month_interval')),
                self::WEEK => $this->getWeekInput(),
                self::DAY => $this->getDayInput(),
                self::END => $this->getEndInput()
            ],
            $this->lng->txt('cal_monthly_by_day')
        );
    }

    protected function getMonthlyByDateGroup(): Group
    {
        return $this->ui_factory->input()->field()->group(
            [
                self::INTERVAL => $this->getIntervalInput($this->lng->txt('cal_recurrence_month_interval')),
                self::DAY_OF_MONTH => $this->getDayOfMonthInput(),
                self::END => $this->getEndInput()
            ],
            $this->lng->txt('cal_monthly_by_date')
        );
    }

    protected function getYearlyByDayGroup(): Group
    {
        return $this->ui_factory->input()->field()->group(
            [
                self::INTERVAL => $this->getIntervalInput($this->lng->txt('cal_recurrence_year_interval')),
                self::MONTH => $this->getMonthInput(),
                self::WEEK => $this->getWeekInput(),
                self::DAY => $this->getDayInput(),
                self::END => $this->getEndInput()
            ],
            $this->lng->txt('cal_yearly_by_day')
        );
    }

    protected function getYearlyByDateGroup(): Group
    {
        return $this->ui_factory->input()->field()->group(
            [
                self::INTERVAL => $this->getIntervalInput($this->lng->txt('cal_recurrence_year_interval')),
                self::MONTH => $this->getMonthInput(),
                self::DAY_OF_MONTH => $this->getDayOfMonthInput(),
                self::END => $this->getEndInput()
            ],
            $this->lng->txt('cal_yearly_by_date')
        );
    }

    protected function getEndInput(): Input
    {
        $groups = [];

        if ($this->unlimited_recurrences) {
            $groups[self::NO_UNTIL] = $this->ui_factory->input()->field()->group(
                [],
                $this->lng->txt('cal_no_ending')
            );
        }

        $count_value = $this->recurrence->getFrequenceUntilCount();
        if ($count_value < 1 || $count_value > 100) {
            $count_value = 1;
        }
        $count = $this->ui_factory->input()->field()->numeric($this->lng->txt('cal_recurrence_count'))
                         ->withValue($count_value)
                         ->withRequired(true)
                         ->withAdditionalTransformation(
                             $this->refinery->in()->series([
                                 $this->refinery->int()->isGreaterThanOrEqual(1),
                                 $this->refinery->int()->isLessThanOrEqual(100),
                             ])
                         );
        $groups[self::UNTIL_COUNT] = $this->ui_factory->input()->field()->group(
            [self::COUNT => $count],
            $this->lng->txt('cal_recurrence_until_count')
        );

        $end_date = $this->ui_factory->input()->field()->dateTime(
            $this->lng->txt('cal_recurrence_end_date'),
            $this->lng->txt('cal_recurrence_end_date_info')
        )->withTimezone('UTC')
         ->withUseTime(false)
         ->withRequired(true);
        if ($this->recurrence->getFrequenceUntilDate()) {
            $end_date = $end_date->withValue(
                new DateTimeImmutable('@' . $this->recurrence->getFrequenceUntilDate()->getUnixTime())
            );
        }
        $groups[self::UNTIL_END_DATE] = $this->ui_factory->input()->field()->group(
            [self::END_DATE => $end_date],
            $this->lng->txt('cal_recurrence_until_end_date')
        );

        $value = self::NO_UNTIL;
        if ($this->recurrence->getFrequenceUntilDate()) {
            $value = self::UNTIL_END_DATE;
        }
        if ($this->recurrence->getFrequenceUntilCount()) {
            $value = self::UNTIL_COUNT;
        }
        return $this->ui_factory->input()->field()->switchableGroup(
            $groups,
            $this->lng->txt('cal_recurrence_until')
        )->withValue($value);
    }

    protected function getIntervalInput(string $label): Input
    {
        return $this->ui_factory->input()->field()->numeric($label)
                                ->withValue($this->recurrence->getInterval())
                                ->withRequired(true)
                                ->withAdditionalTransformation(
                                    $this->refinery->int()->isGreaterThanOrEqual(1)
                                );
    }

    protected function getDayInput(): Input
    {
        $days = [
            0 => Weekday::SUNDAY->value,
            1 => Weekday::MONDAY->value,
            2 => Weekday::TUESDAY->value,
            3 => Weekday::WEDNESDAY->value,
            4 => Weekday::THURSDAY->value,
            5 => Weekday::FRIDAY->value,
            6 => Weekday::SATURDAY->value,
            7 => Weekday::SUNDAY->value
        ];
        $options = [];
        for ($i = $this->user_settings->getWeekStart(); $i < 7 + $this->user_settings->getWeekStart(); $i++) {
            $options[$days[$i]] = ilCalendarUtil::_numericDayToString($i);
        }

        $values = [];
        foreach ($this->recurrence->getBYDAYList() as $byday) {
            // BYDAY can also contain ordinance numbers in front of the days
            $v = substr($byday, -2);
            if (in_array($v, $days)) {
                $values[] = $v;
            }
        }

        return $this->ui_factory->input()->field()->multiSelect(
            $this->lng->txt('cal_day_s'),
            $options
        )->withValue($values)->withRequired(true);
    }

    protected function getWeekInput(): Input
    {
        $options = [
            Ordinal::FIRST->value => $this->lng->txt('cal_first'),
            Ordinal::SECOND->value => $this->lng->txt('cal_second'),
            Ordinal::THIRD->value => $this->lng->txt('cal_third'),
            Ordinal::FOURTH->value => $this->lng->txt('cal_fourth'),
            Ordinal::FIFTH->value => $this->lng->txt('cal_fifth'),
            Ordinal::LAST->value => $this->lng->txt('cal_last')
        ];

        // The last two characters of any BYDAY entry are the day, the remainder is the ordinal.
        $value = substr($this->recurrence->getBYDAYList()[0] ?? '', 0, -2);
        if ($value === '') {
            $value = Ordinal::FIRST->value;
        }

        return $this->ui_factory->input()->field()->select(
            $this->lng->txt('week'),
            $options
        )->withValue($value)->withRequired(true);
    }

    protected function getDayOfMonthInput(): Input
    {
        $value = (int) $this->recurrence->getBYMONTHDAY();
        if ($value < 1 || $value > 31) {
            $value = 1;
        }
        return $this->ui_factory->input()->field()->numeric($this->lng->txt('cal_day_of_month'))
                                ->withValue($value)
                                ->withRequired(true)
                                ->withAdditionalTransformation(
                                    $this->refinery->in()->series([
                                        $this->refinery->int()->isGreaterThanOrEqual(1),
                                        $this->refinery->int()->isLessThanOrEqual(31),
                                    ])
                                );
    }

    protected function getMonthInput(): Input
    {
        $months = [
            1 => Month::JANUARY->value,
            2 => Month::FEBRUARY->value,
            3 => Month::MARCH->value,
            4 => Month::APRIL->value,
            5 => Month::MAY->value,
            6 => Month::JUNE->value,
            7 => Month::JULY->value,
            8 => Month::AUGUST->value,
            9 => Month::SEPTEMBER->value,
            10 => Month::OCTOBER->value,
            11 => Month::NOVEMBER->value,
            12 => Month::DECEMBER->value
        ];
        $options = [];
        foreach ($months as $month => $key) {
            $options[$key] = ilCalendarUtil::_numericMonthToString($month);
        }

        $value = $this->recurrence->getBYMONTH();
        if (!in_array($value, $options)) {
            $value = Month::JANUARY->value;
        }
        return $this->ui_factory->input()->field()->select(
            $this->lng->txt('month'),
            $options
        )->withValue($value)->withRequired(true);
    }

    protected function getOutputTransformation(): Transformation
    {
        $recurrence = clone $this->recurrence;
        $with_daily = $this->hasDaily();
        $with_weekly = $this->hasWeekly();
        $with_monthly = $this->hasMonthly();
        $with_yearly = $this->hasYearly();
        $with_unlimited = $this->hasUnlimitedRecurrences();

        return $this->refinery->custom()->transformation(function ($values) use (
            $recurrence,
            $with_daily,
            $with_weekly,
            $with_monthly,
            $with_yearly,
            $with_unlimited
        ) {
            $recurrence->reset();

            $rule_data = $values[self::RULE];
            switch ($rule_data[0]) {
                case self::DAILY:
                    $recurrence->setFrequenceType(ilCalendarRecurrence::FREQ_DAILY);
                    $recurrence->setInterval((int) $rule_data[1][self::INTERVAL]);
                    break;

                case self::WEEKLY:
                    $recurrence->setFrequenceType(ilCalendarRecurrence::FREQ_WEEKLY);
                    $recurrence->setInterval((int) $rule_data[1][self::INTERVAL]);
                    if (is_array($rule_data[1][self::DAY]) && $rule_data[1][self::DAY] !== []) {
                        $recurrence->setBYDAY(implode(',', $rule_data[1][self::DAY]));
                    }
                    break;

                case self::MONTHLY_BY_DAY:
                    $recurrence->setFrequenceType(ilCalendarRecurrence::FREQ_MONTHLY);
                    $recurrence->setInterval((int) $rule_data[1][self::INTERVAL]);
                    if (is_array($rule_data[1][self::DAY]) && $rule_data[1][self::DAY] !== []) {
                        $index = $rule_data[1][self::WEEK];
                        $recurrence->setBYDAY($index . implode(',' . $index, $rule_data[1][self::DAY]));
                    }
                    break;

                case self::MONTHLY_BY_DATE:
                    $recurrence->setFrequenceType(ilCalendarRecurrence::FREQ_MONTHLY);
                    $recurrence->setInterval((int) $rule_data[1][self::INTERVAL]);
                    $recurrence->setBYMONTHDAY((string) $rule_data[1][self::DAY_OF_MONTH]);
                    break;

                case self::YEARLY_BY_DAY:
                    $recurrence->setFrequenceType(ilCalendarRecurrence::FREQ_YEARLY);
                    $recurrence->setInterval((int) $rule_data[1][self::INTERVAL]);
                    $recurrence->setBYMONTH((string) $rule_data[1][self::MONTH]);
                    if (is_array($rule_data[1][self::DAY]) && $rule_data[1][self::DAY] !== []) {
                        $index = $rule_data[1][self::WEEK];
                        $recurrence->setBYDAY($index . implode(',' . $index, $rule_data[1][self::DAY]));
                    }
                    break;

                case self::YEARLY_BY_DATE:
                    $recurrence->setFrequenceType(ilCalendarRecurrence::FREQ_YEARLY);
                    $recurrence->setInterval((int) $rule_data[1][self::INTERVAL]);
                    $recurrence->setBYMONTH((string) $rule_data[1][self::MONTH]);
                    $recurrence->setBYMONTHDAY((string) $rule_data[1][self::DAY_OF_MONTH]);
                    break;

                default:
                case self::NO_RECURRENCE:
                    break;
            }

            $end_data = $rule_data[1][self::END];
            if ($end_data[0] === self::UNTIL_COUNT) {
                $recurrence->setFrequenceUntilCount($end_data[1][self::COUNT]);
            }
            if ($end_data[0] === self::UNTIL_END_DATE) {
                $recurrence->setFrequenceUntilDate(new ilDate(
                    $end_data[1][self::END_DATE]->getTimestamp(),
                    IL_CAL_UNIX
                ));
            }

            return $recurrence;
        });
    }
}
