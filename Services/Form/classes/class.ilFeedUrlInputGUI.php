<?php
/*
    +-----------------------------------------------------------------------------+
    | ILIAS open source                                                           |
    +-----------------------------------------------------------------------------+
    | Copyright (c) 1998-2007 ILIAS open source, University of Cologne            |
    |                                                                             |
    | This program is free software; you can redistribute it and/or               |
    | modify it under the terms of the GNU General Public License                 |
    | as published by the Free Software Foundation; either version 2              |
    | of the License, or (at your option) any later version.                      |
    |                                                                             |
    | This program is distributed in the hope that it will be useful,             |
    | but WITHOUT ANY WARRANTY; without even the implied warranty of              |
    | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
    | GNU General Public License for more details.                                |
    |                                                                             |
    | You should have received a copy of the GNU General Public License           |
    | along with this program; if not, write to the Free Software                 |
    | Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
    +-----------------------------------------------------------------------------+
*/

/**
* This class represents a feed url property in a property form.
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
* @ingroup	ServicesForm
*/
class ilFeedUrlInputGUI extends ilTextInputGUI
{
    /**
     * @var ilLanguage
     */
    protected $lng;

    protected $value;
    protected $maxlength = 200;
    protected $size = 40;
    
    /**
    * Constructor
    *
    * @param	string	$a_title	Title
    * @param	string	$a_postvar	Post Variable
    */
    public function __construct($a_title = "", $a_postvar = "")
    {
        global $DIC;

        $this->lng = $DIC->language();
        parent::__construct($a_title, $a_postvar);
        $this->setType("feedurl");
    }

    /**
    * Check input, strip slashes etc. set alert, if input is not ok.
    *
    * @return	boolean		Input ok, true/false
    */
    public function checkInput()
    {
        $lng = $this->lng;
        
        $lng->loadLanguageModule("feed");
        
        $_POST[$this->getPostVar()] =
            ilUtil::stripSlashes($_POST[$this->getPostVar()]);
            
        // remove safari pseudo protocol
        if (substr($_POST[$this->getPostVar()], 0, 5) == "feed:") {
            $_POST[$this->getPostVar()] = "http:" .
                substr($_POST[$this->getPostVar()], 5);
        }
        
        // add missing http://
        if (!is_int(strpos($_POST[$this->getPostVar()], "://"))) {
            $_POST[$this->getPostVar()] = "http://" . $_POST[$this->getPostVar()];
        }
            
        // check required
        if ($this->getRequired() && trim($_POST[$this->getPostVar()]) == "") {
            $this->setAlert($lng->txt("msg_input_is_required"));

            return false;
        }
        
        // check feed url
        $url = $_POST[$this->getPostVar()];
        include_once("./Services/Feeds/classes/class.ilExternalFeed.php");
        $check = ilExternalFeed::_checkUrl($url);

        // try to determine a feed url, if we failed here
        if ($check !== true) {
            $url2 = ilExternalFeed::_determineFeedUrl($url);
            $check2 = ilExternalFeed::_checkUrl($url2);
            
            if ($check2 === true) {
                $_POST[$this->getPostVar()] = $url2;
                $check = true;
            }
        }

        // if check failed, output error message
        if ($check !== true) {
            $check = str_replace("MagpieRSS:", "", $check);
            $this->setAlert($lng->txt("feed_no_valid_url") . "<br />" . $check);
            return false;
        }
        
        return true;
    }
}
