<?php declare(strict_types=1);

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
 * This class represents a regular expression input property in a property form.
 *
 * @author Roland Küstermann <roland.kuestermann@kit.edu>
 */
class ilRegExpInputGUI extends ilTextInputGUI
{
    private string $pattern = "";
    protected string $nomatchmessage = "";

    public function __construct(
        string $a_title = "",
        string $a_postvar = ""
    ) {
        global $DIC;

        $this->lng = $DIC->language();
        parent::__construct($a_title, $a_postvar);
        $this->setType("feedurl");
    }

    public function setNoMatchMessage(string $a_nomatchmessage) : void
    {
        $this->nomatchmessage = $a_nomatchmessage;
    }

    public function getNoMatchMessage() : string
    {
        return $this->nomatchmessage;
    }

    public function setPattern(string $pattern) : void
    {
        $this->pattern = $pattern;
    }
    
    public function getPattern() : string
    {
        return $this->pattern;
    }

    public function checkInput() : bool
    {
        $lng = $this->lng;
        
        $value = $this->getInput();
        
        if (!$this->getRequired() && strcasecmp($value, "") == 0) {
            return true;
        }
        
        if ($this->getRequired() && trim($value) == "") {
            $this->setAlert($lng->txt("msg_input_is_required"));
            return false;
        }

        $result = preg_match($this->pattern, $value);
        if (!$result) {
            if ($this->getNoMatchMessage() == "") {
                $this->setAlert($lng->txt("msg_input_does_not_match_regexp"));
            } else {
                $this->setAlert($this->getNoMatchMessage());
            }
        }
        return $result;
    }

    public function getInput() : string
    {
        return $this->str($this->getPostVar());
    }
}
