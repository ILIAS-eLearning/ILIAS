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

trait ilDclDatetimeRecordDateFormatter
{
    protected function formatDateFromString(string $value): string
    {
        return $this->formatDateFromInt(strtotime($value));
    }

    protected function formatDateFromInt(int $timestamp): string
    {
        $format = $this->getUserDateFormat();
        switch ($format) {
            case ilCalendarSettings::DATE_FORMAT_DMY:
                return date("d.m.Y", $timestamp);
            case ilCalendarSettings::DATE_FORMAT_YMD:
                return date("Y-m-d", $timestamp);
            case ilCalendarSettings::DATE_FORMAT_MDY:
                return date("m/d/Y", $timestamp);
            default:
                return date($format, $timestamp);
        }

        return $this->lng->txt('no_date');
    }

    abstract protected function getUserDateFormat(): string;
}
