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
 ********************************************************************
 */

/**
 * Class ilDclDateTimeRecordRepresentation
 * @author  Michael Herren <mh@studer-raimann.ch>
 * @version 1.0.0
 */
class ilDclDatetimeRecordRepresentation extends ilDclBaseRecordRepresentation
{
    /**
     * Outputs html of a certain field
     */
    public function getHTML(bool $link = true): string
    {
        global $DIC;
        $ilUser = $DIC['ilUser'];

        $value = $this->getRecordField()->getValue();
        if ($value == '0000-00-00 00:00:00' or !$value) {
            return $this->lng->txt('no_date');
        }

        return $this->formatDate($value, $ilUser->getDateFormat());
    }

    /**
     * @return bool|string
     */
    protected function formatDate(string $value, string $format)
    {
        $timestamp = strtotime($value);
        switch ($format) {
            case ilCalendarSettings::DATE_FORMAT_DMY:
                return date("d.m.Y", $timestamp);
            case ilCalendarSettings::DATE_FORMAT_YMD:
                return date("Y-m-d", $timestamp);
            case ilCalendarSettings::DATE_FORMAT_MDY:
                return date("m/d/Y", $timestamp);
        }

        return $this->lng->txt('no_date');
    }

    /**
     * function parses stored value to the variable needed to fill into the form for editing.
     * @param string|int $value
     */
    public function parseFormInput($value): ?string
    {
        if (!$value || $value == "-") {
            return null;
        }

        return substr($value, 0, -9);
    }
}
