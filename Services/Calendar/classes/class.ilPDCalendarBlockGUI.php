<?php

/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("Services/Calendar/classes/class.ilCalendarBlockGUI.php");

/**
* Calendar blocks, displayed on personal desktop
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ilCtrl_IsCalledBy ilPDCalendarBlockGUI: ilColumnGUI
* @ilCtrl_Calls ilPDCalendarBlockGUI: ilCalendarDayGUI, ilCalendarAppointmentGUI
* @ilCtrl_Calls ilPDCalendarBlockGUI: ilCalendarMonthGUI, ilCalendarWeekGUI, ilCalendarInboxGUI
* @ilCtrl_Calls ilPDCalendarBlockGUI: ilConsultationHoursGUI, ilCalendarAppointmentPresentationGUI
*
* @ingroup ServicesCalendar
*/
class ilPDCalendarBlockGUI extends ilCalendarBlockGUI
{
    public static $block_type = "pdcal";

    /**
     * @var bool
     */
    protected $initialized = false;
    
    /**
    * Constructor
    */
    public function __construct()
    {
        parent::__construct(true);
        $this->setBlockId(0);
    }


    /**
     * @inheritdoc
     */
    public function getBlockType() : string
    {
        return self::$block_type;
    }

    /**
     * init categories
     *
     * @access protected
     * @param
     * @return
     */
    protected function initCategories()
    {
        include_once './Services/Calendar/classes/class.ilCalendarUserSettings.php';
        if (!$this->initialized) {
            if (ilCalendarUserSettings::_getInstance()->getCalendarSelectionType() == ilCalendarUserSettings::CAL_SELECTION_MEMBERSHIP) {
                $this->mode = ilCalendarCategories::MODE_PERSONAL_DESKTOP_MEMBERSHIP;
            } else {
                $this->mode = ilCalendarCategories::MODE_PERSONAL_DESKTOP_ITEMS;
            }

            $cats = \ilCalendarCategories::_getInstance();
            if ($this->getForceMonthView()) {
                // nothing to do here
            } elseif (!$cats->getMode()) {
                $cats->initialize($this->mode, (int) $this->requested_ref_id, true);
            }
        }
        $this->initialized = true;
    }

    /**
    * Return to upper context
    */
    public function returnToUpperContext()
    {
        global $DIC;

        $ilCtrl = $DIC['ilCtrl'];
        
        $ilCtrl->redirectByClass("ildashboardgui", "show");
    }
}
