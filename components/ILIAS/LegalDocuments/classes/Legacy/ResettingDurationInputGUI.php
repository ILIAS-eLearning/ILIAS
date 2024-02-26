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

namespace ILIAS\LegalDocuments\Legacy;

use ilDateDurationInputGUI;

/**
 * The ilDateDurationInputGUI class doesn't set it's value back to an empty value when called with an empty array, in contrast to e.g. ilTextInputGUI.
 * This prevents that the value is cleared when a ilTable2GUI is reset.
 * To prevent that the whole table needs to be recreated just to clear this input GUI, this class directly clears the value instead.
 */
class ResettingDurationInputGUI extends ilDateDurationInputGUI
{
    public function setValueByArray(array $a_values): void
    {
        if (array_filter($a_values) === []) {
            $this->setStart(null);
            $this->setEnd(null);
            return;
        }
        parent::setValueByArray($a_values);
    }
}
