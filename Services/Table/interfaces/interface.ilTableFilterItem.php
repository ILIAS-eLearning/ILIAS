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

/**
 * Interface for property form input GUI classes that can be used
 * in table filters
 *
 * @author Alexander Killing <killing@leifos.de>
 */
interface ilTableFilterItem
{
    /**
     * Get input item HTML to be inserted into table filters
     */
    public function getTableFilterHTML(): string;

    /**
     * Get label "for" attribute value
     */
    public function getTableFilterLabelFor(): string;
}
