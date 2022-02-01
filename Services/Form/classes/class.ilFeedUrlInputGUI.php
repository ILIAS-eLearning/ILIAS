<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 */

/**
 * This class represents a feed url property in a property form.
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilFeedUrlInputGUI extends ilTextInputGUI
{
    protected int $maxlength = 200;
    protected int $size = 40;
    
    public function __construct(
        string $a_title = "",
        string $a_postvar = ""
    ) {
        global $DIC;

        $this->lng = $DIC->language();
        parent::__construct($a_title, $a_postvar);
        $this->setType("feedurl");
    }

    public function checkInput() : bool
    {
        $lng = $this->lng;
        
        $lng->loadLanguageModule("feed");

        // check required
        if ($this->getRequired() && trim($this->str($this->getPostVar())) == "") {
            $this->setAlert($lng->txt("msg_input_is_required"));
            return false;
        }
        
        // check feed url
        $url = $this->getInput();
//        $check = ilExternalFeed::_checkUrl($url);
        $check = true;

        // if check failed, output error message
        if ($check !== true) {
            $check = str_replace("MagpieRSS:", "", $check);
            $this->setAlert($lng->txt("feed_no_valid_url") . "<br />" . $check);
            return false;
        }
        
        return true;
    }

    public function getInput() : string
    {
        $val = $this->str($this->getPostVar());
        // remove safari pseudo protocol
        if (substr($val, 0, 5) == "feed:") {
            $val = "http:" .
                substr($val, 5);
        }

        // add missing http://
        if (!is_int(strpos($val, "://"))) {
            $val = "https://" . $val;
        }

        // check feed url
        $url = $this->str($this->getPostVar());
        //$check = ilExternalFeed::_checkUrl($url);
        $check = true;

        // try to determine a feed url, if we failed here
        if ($check !== true) {
            $url2 = ilExternalFeed::_determineFeedUrl($url);
            $check2 = ilExternalFeed::_checkUrl($url2);

            if ($check2 === true) {
                $val = $url2;
            }
        }
        return $val;
    }
}
