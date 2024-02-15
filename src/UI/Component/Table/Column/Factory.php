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

namespace ILIAS\UI\Component\Table\Column;

use ILIAS\UI\Component\Symbol\Icon\Icon;
use ILIAS\UI\Component\Symbol\Glyph\Glyph;

interface Factory
{
    /**
     * ---
     * description:
     *   purpose: >
     *      The Text Column is used for (short) text.
     * ---
     * @return \ILIAS\UI\Component\Table\Column\Text
     */
    public function text(string $title): Text;

    /**
     * ---
     * description:
     *   purpose: >
     *      The Number Column is used for numeric values.
     * ---
     * @return \ILIAS\UI\Component\Table\Column\Number
     */
    public function number(string $title): Number;

    /**
     * ---
     * description:
     *   purpose: >
     *      The Date Column is used for single dates.
     *
     * ---
     * @return \ILIAS\UI\Component\Table\Column\Date
     */
    public function date(string $title, \ILIAS\Data\DateFormat\DateFormat $format): Date;

    /**
     * ---
     * description:
     *   purpose: >
     *      The Status Column is used for _very_ small texts expressing a status
     *
     * ---
     * @return \ILIAS\UI\Component\Table\Column\Status
     */
    public function status(string $title): Status;

    /**
     * ---
     * description:
     *   purpose: >
     *      Sometimes, a status or progress is better expressed by an Icon.
     *      Use the StatusIcon Column for it.
     *
     * ---
     * @return \ILIAS\UI\Component\Table\Column\StatusIcon
     */
    public function statusIcon(string $title): StatusIcon;

    /**
     * ---
     * description:
     *   purpose: >
     *      The Boolean Column is used to indicate a binary state, e.g. on/off.
     *   composition: >
     *      The two states could either be represented by two strings, two Icons
     *      or two Glyphs.
     *
     * rules:
     *   composition:
     *     1: >
     *       If Glyps are used to indicate the state, they MUST NOT have an attached
     *       action.
     * ---
     * @var string|Icon|Glyph $true
     * @var string|Icon|Glyph $false
     * @return \ILIAS\UI\Component\Table\Column\Boolean
     */
    public function boolean(string $title, $true, $false): Boolean;

    /**
     * ---
     * description:
     *   purpose: >
     *      Special formating for Mails in the EMail Column.
     *
     * ---
     * @return \ILIAS\UI\Component\Table\Column\EMail
     */
    public function eMail(string $title): EMail;

    /**
     * ---
     * description:
     *   purpose: >
     *      To express a timespan, a duration: use the TimeSpan Column to
     *      visualize a start- and an enddate.
     *
     * ---
     * @return \ILIAS\UI\Component\Table\Column\TimeSpan
     */
    public function timeSpan(string $title, \ILIAS\Data\DateFormat\DateFormat $format): TimeSpan;

    /**
     * ---
     * description:
     *   purpose: >
     *      The Link Column features a Standard Link.
     *
     * ---
     * @return \ILIAS\UI\Component\Table\Column\Link
     */
    public function link(string $title): Link;

    /**
     * ---
     * description:
     *   purpose: >
     *      The LinkListing Column features an Ordered or Unordered Listing of Standard Links.
     *
     * ---
     * @return \ILIAS\UI\Component\Table\Column\LinkListing
     */
    public function linkListing(string $title): LinkListing;
}
