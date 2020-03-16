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
* This class represents a duration (typical hh:mm:ss) property in a property form.
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
* @ingroup	ServicesForm
*/
class ilDurationInputGUI extends ilFormPropertyGUI
{
    /**
     * @var ilLanguage
     */
    protected $lng;

    protected $months = 0;
    protected $days = 0;
    protected $hours = 0;
    protected $minutes = 0;
    protected $seconds = 0;
    protected $showmonths = false;
    protected $showdays = false;
    protected $showhours = true;
    protected $showminutes = true;
    protected $showseconds = false;
    
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
        $this->setType("duration");
    }

    /**
    * Set Days.
    *
    * @param	int	$a_days	Days
    */
    public function setDays($a_days)
    {
        $this->days = $a_days;
    }

    /**
    * Get Days.
    *
    * @return	int	Days
    */
    public function getDays()
    {
        return (int) $this->days;
    }

    /**
    * Set Hours.
    *
    * @param	int	$a_hours	Hours
    */
    public function setHours($a_hours)
    {
        $this->hours = $a_hours;
    }

    /**
    * Get Hours.
    *
    * @return	int	Hours
    */
    public function getHours()
    {
        return (int) $this->hours;
    }

    /**
    * Set Minutes.
    *
    * @param	int	$a_minutes	Minutes
    */
    public function setMinutes($a_minutes)
    {
        $this->minutes = $a_minutes;
    }

    /**
    * Get Minutes.
    *
    * @return	int	Minutes
    */
    public function getMinutes()
    {
        return (int) $this->minutes;
    }

    /**
    * Set Seconds.
    *
    * @param	int	$a_seconds	Seconds
    */
    public function setSeconds($a_seconds)
    {
        $this->seconds = $a_seconds;
    }
    
    /**
     * set months
     *
     * @access public
     * @param int months
     *
     */
    public function setMonths($a_months)
    {
        $this->months = $a_months;
    }
    
    /**
     * get months
     *
     * @access public
     *
     */
    public function getMonths()
    {
        return (int) $this->months;
    }

    /**
    * Get Seconds.
    *
    * @return	int	Seconds
    */
    public function getSeconds()
    {
        return (int) $this->seconds;
    }
    
    /**
     * Set show months
     *
     * @access public
     * @param boolean $a_show_month
     */
    public function setShowMonths($a_show_months)
    {
        $this->showmonths = $a_show_months;
    }
    
    /**
     * Get show months
     *
     * @access public
     */
    public function getShowMonths()
    {
        return $this->showmonths;
    }

    /**
    * Set Show Days.
    *
    * @param	boolean	$a_showdays	Show Days
    */
    public function setShowDays($a_showdays)
    {
        $this->showdays = $a_showdays;
    }

    /**
    * Get Show Days.
    *
    * @return	boolean	Show Days
    */
    public function getShowDays()
    {
        return $this->showdays;
    }

    /**
    * Set Show Hours.
    *
    * @param	boolean	$a_showhours	Show Hours
    */
    public function setShowHours($a_showhours)
    {
        $this->showhours = $a_showhours;
    }

    /**
    * Get Show Hours.
    *
    * @return	boolean	Show Hours
    */
    public function getShowHours()
    {
        return $this->showhours;
    }

    /**
    * Set Show Minutes.
    *
    * @param	boolean	$a_showminutes	Show Minutes
    */
    public function setShowMinutes($a_showminutes)
    {
        $this->showminutes = $a_showminutes;
    }

    /**
    * Get Show Minutes.
    *
    * @return	boolean	Show Minutes
    */
    public function getShowMinutes()
    {
        return $this->showminutes;
    }

    /**
    * Set Show Seconds.
    *
    * @param	boolean	$a_showseconds	Show Seconds
    */
    public function setShowSeconds($a_showseconds)
    {
        $this->showseconds = $a_showseconds;
    }

    /**
    * Get Show Seconds.
    *
    * @return	boolean	Show Seconds
    */
    public function getShowSeconds()
    {
        return $this->showseconds;
    }

    /**
    * Set value by array
    *
    * @param	array	$a_values	value array
    */
    public function setValueByArray($a_values)
    {
        $this->setMonths($a_values[$this->getPostVar()]["MM"]);
        $this->setDays($a_values[$this->getPostVar()]["dd"]);
        $this->setHours($a_values[$this->getPostVar()]["hh"]);
        $this->setMinutes($a_values[$this->getPostVar()]["mm"]);
        $this->setSeconds($a_values[$this->getPostVar()]["ss"]);
    }

    /**
    * Check input, strip slashes etc. set alert, if input is not ok.
    *
    * @return	boolean		Input ok, true/false
    */
    public function checkInput()
    {
        $lng = $this->lng;
        
        $_POST[$this->getPostVar()]["MM"] =
            ilUtil::stripSlashes($_POST[$this->getPostVar()]["MM"]);
        $_POST[$this->getPostVar()]["dd"] =
            ilUtil::stripSlashes($_POST[$this->getPostVar()]["dd"]);
        $_POST[$this->getPostVar()]["hh"] =
            ilUtil::stripSlashes($_POST[$this->getPostVar()]["hh"]);
        $_POST[$this->getPostVar()]["mm"] =
            ilUtil::stripSlashes($_POST[$this->getPostVar()]["mm"]);
        $_POST[$this->getPostVar()]["ss"] =
            ilUtil::stripSlashes($_POST[$this->getPostVar()]["ss"]);

        return true;
    }

    /**
    * Insert property html
    *
    * @return	int	Size
    */
    public function insert($a_tpl)
    {
        $html = $this->render();

        $a_tpl->setCurrentBlock("prop_generic");
        $a_tpl->setVariable("PROP_GENERIC", $html);
        $a_tpl->parseCurrentBlock();
    }

    /**
    * Insert property html
    *
    */
    public function render()
    {
        $lng = $this->lng;

        $tpl = new ilTemplate("tpl.prop_duration.html", true, true, "Services/Form");
        
        if ($this->getShowMonths()) {
            $tpl->setCurrentBlock("dur_months");
            $tpl->setVariable("TXT_MONTHS", $lng->txt("form_months"));
            $val = array();
            for ($i = 0; $i <= 36; $i++) {
                $val[$i] = $i;
            }
            $tpl->setVariable(
                "SELECT_MONTHS",
                ilUtil::formSelect(
                    $this->getMonths(),
                    $this->getPostVar() . "[MM]",
                    $val,
                    false,
                    true,
                    0,
                    '',
                    '',
                    $this->getDisabled()
                )
            );
            $tpl->parseCurrentBlock();
        }
        if ($this->getShowDays()) {
            $tpl->setCurrentBlock("dur_days");
            $tpl->setVariable("TXT_DAYS", $lng->txt("form_days"));
            $val = array();
            for ($i = 0; $i <= 366; $i++) {
                $val[$i] = $i;
            }
            $tpl->setVariable(
                "SELECT_DAYS",
                ilUtil::formSelect(
                    $this->getDays(),
                    $this->getPostVar() . "[dd]",
                    $val,
                    false,
                    true,
                    0,
                    '',
                    '',
                    $this->getDisabled()
                )
            );
            $tpl->parseCurrentBlock();
        }
        if ($this->getShowHours()) {
            $tpl->setCurrentBlock("dur_hours");
            $tpl->setVariable("TXT_HOURS", $lng->txt("form_hours"));
            $val = array();
            for ($i = 0; $i <= 23; $i++) {
                $val[$i] = $i;
            }
            $tpl->setVariable(
                "SELECT_HOURS",
                ilUtil::formSelect(
                    $this->getHours(),
                    $this->getPostVar() . "[hh]",
                    $val,
                    false,
                    true,
                    0,
                    '',
                    '',
                    $this->getDisabled()
                )
            );
            $tpl->parseCurrentBlock();
        }
        if ($this->getShowMinutes()) {
            $tpl->setCurrentBlock("dur_minutes");
            $tpl->setVariable("TXT_MINUTES", $lng->txt("form_minutes"));
            $val = array();
            for ($i = 0; $i <= 59; $i++) {
                $val[$i] = $i;
            }
            $tpl->setVariable(
                "SELECT_MINUTES",
                ilUtil::formSelect(
                    $this->getMinutes(),
                    $this->getPostVar() . "[mm]",
                    $val,
                    false,
                    true,
                    0,
                    '',
                    '',
                    $this->getDisabled()
                )
            );
            $tpl->parseCurrentBlock();
        }
        if ($this->getShowSeconds()) {
            $tpl->setCurrentBlock("dur_seconds");
            $tpl->setVariable("TXT_SECONDS", $lng->txt("form_seconds"));
            $val = array();
            for ($i = 0; $i <= 59; $i++) {
                $val[$i] = $i;
            }
            $tpl->setVariable(
                "SELECT_SECONDS",
                ilUtil::formSelect(
                    $this->getSeconds(),
                    $this->getPostVar() . "[ss]",
                    $val,
                    false,
                    true,
                    0,
                    '',
                    '',
                    $this->getDisabled()
                )
            );
            $tpl->parseCurrentBlock();
        }
        
        return $tpl->get();
    }

    /**
    * Get HTML for table filter
    */
    public function getTableFilterHTML()
    {
        $html = $this->render();
        return $html;
    }

    /**
     * serialize data
     */
    public function serializeData()
    {
        $data = array("months" => $this->getMonths(),
            "days" => $this->getDays(),
            "hours" => $this->getHours(),
            "minutes" => $this->getMinutes(),
            "seconds" => $this->getSeconds());

        return serialize($data);
    }

    /**
     * unserialize data
     */
    public function unserializeData($a_data)
    {
        $data = unserialize($a_data);
        
        $this->setMonths($data["months"]);
        $this->setDays($data["days"]);
        $this->setHours($data["hours"]);
        $this->setMinutes($data["minutes"]);
        $this->setSeconds($data["seconds"]);
    }

    /**
     * Get combined value in seconds
     *
     * @return int
     */
    public function getValueInSeconds()
    {
        $value = 0;
        if ($this->getShowMonths()) {
            $value += $this->getMonths() * 30 * 24 * 60 * 60;
        }
        if ($this->getShowDays()) {
            $value += $this->getDays() * 24 * 60 * 60;
        }
        if ($this->getShowHours()) {
            $value += $this->getHours() * 60 * 60;
        }
        if ($this->getShowMinutes()) {
            $value += $this->getMinutes() * 60;
        }
        if ($this->getShowSeconds()) {
            $value += $this->getSeconds();
        }
        return $value;
    }
}
