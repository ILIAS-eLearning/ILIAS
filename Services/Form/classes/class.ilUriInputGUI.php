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

use ILIAS\Data\URI;

/**
 * Legacy Uri input
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilUriInputGUI extends ilTextInputGUI
{
    protected int $maxlength = 500;
    protected int $size = 40;

    public function __construct(
        string $a_title = "",
        string $a_postvar = ""
    ) {
        global $DIC;

        $this->lng = $DIC->language();
        parent::__construct($a_title, $a_postvar);
        $this->setType("uri");
    }

    public function checkInput(): bool
    {
        $lng = $this->lng;

        // check required
        if ($this->getRequired() && trim($this->str($this->getPostVar())) == "") {
            $this->setAlert($lng->txt("msg_input_is_required"));
            return false;
        }

        $url = $this->getInput();

        try {
            new URI($url);
        } catch (Throwable $e) {
            $this->setAlert($lng->txt("form_invalid_uri"));
            return false;
        }

        return true;
    }
}
