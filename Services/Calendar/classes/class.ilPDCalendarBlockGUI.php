<?php declare(strict_types=1);

/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Calendar blocks, displayed on personal desktop
 * @author            Alex Killing <alex.killing@gmx.de>
 * @ilCtrl_IsCalledBy ilPDCalendarBlockGUI: ilColumnGUI
 * @ilCtrl_Calls      ilPDCalendarBlockGUI: ilCalendarDayGUI, ilCalendarAppointmentGUI
 * @ilCtrl_Calls      ilPDCalendarBlockGUI: ilCalendarMonthGUI, ilCalendarWeekGUI, ilCalendarInboxGUI
 * @ilCtrl_Calls      ilPDCalendarBlockGUI: ilConsultationHoursGUI, ilCalendarAppointmentPresentationGUI
 * @ingroup           ServicesCalendar
 */
class ilPDCalendarBlockGUI extends ilCalendarBlockGUI
{
    public static string $block_type = "pdcal";
    protected bool $initialized = false;

    /**
     * @inheritDoc
     */
    public function __construct()
    {
        parent::__construct();
        $this->setBlockId('0');
    }

    /**
     * @inheritdoc
     */
    public function getBlockType() : string
    {
        return self::$block_type;
    }

    /**
     * @inheritDoc
     */
    protected function initCategories() : void
    {
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
     * @inheritDoc
     */
    public function returnToUpperContext() : void
    {
        $this->ctrl->redirectByClass("ildashboardgui", "show");
    }
}
