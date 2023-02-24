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
     *
     * ---
     * @return \ILIAS\UI\Component\Table\Column\Boolean
     */
    public function boolean(string $title, string $true, string $false): Boolean;

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
     *      The Table is usually not for large texts. However, if there really is
     *      the need for a longer/bigger entry in the cell, the Teaser Column MAY
     *      be used to only display a part of it and the remaining parts in a modal.
     *
     * ---
     * @return \ILIAS\UI\Component\Table\Column\Teaser
     */
    public function teaser(string $title): Teaser;
}
