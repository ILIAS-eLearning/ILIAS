<?php
/*
        +-----------------------------------------------------------------------------+
        | ILIAS open source                                                           |
        +-----------------------------------------------------------------------------+
        | Copyright (c) 1998-2006 ILIAS open source, University of Cologne            |
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
* GUI class for calendar recurrences.
* Used for calendar appointments and course events
*
* @author Stefan Meyer <smeyer.ilias@gmx.de>
* @version $Id$
*
* @ingroup ServicesCalendar
*/

class ilCalendarRecurrenceGUI
{
    protected $form;
    protected $appointment;
    protected $recurrence;
    
    /**
     * Constructor
     *
     * @access public
     * @param
     */
    public function __construct($a_form, $a_recurrence)
    {
        global $DIC;

        $tpl = $DIC['tpl'];
        $lng = $DIC['lng'];
        
        $this->form = $a_form;
        $this->recurrence = $a_recurrence;

        $this->lng = $lng;
        $this->lng->loadLanguageModule('dateplaner');
    }
    
    /**
     * set recurrence object
     *
     * @access public
     * @param recurrence
     * @return
     */
    public function setRecurrence($a_rec)
    {
        $this->recurrence = $a_rec;
    }
    
    /**
     * get html
     *
     * @access public
     * @param
     * @return
     */
    public function initForm()
    {
        $rec = new ilSelectInputGUI($this->lng->txt('cal_recurrences'), 'frequence');
        $rec->setRequired(true);
        $rec->setOptions(
            array(0 => $this->lng->txt('cal_no_recurrence'),
                ilCalendarRecurrence::FREQ_DAILY => $this->lng->txt('cal_rec_daily'),
                ilCalendarRecurrence::FREQ_WEEKLY => $this->lng->txt('cal_rec_weekly'),
                ilCalendarRecurrence::FREQ_MONTHLY => $this->lng->txt('cal_rec_monthly'),
                ilCalendarRecurrence::FREQ_YEARLY => $this->lng->txt('cal_rec_yearly'))
        );
        $rec->setValue($this->recurrence->getFrequenceType());
        $this->form->addItem($rec);
        
        // DAILY part
        $interval = new ilTextInputGUI($this->lng->txt('interval'), 'interval');
        $interval->setSize(2);
        $interval->setMaxLength(3);
        $interval->setValue($this->recurrence->getInterval() ? $this->recurrence->getInterval() : 1);
        $interval->setInfo($this->lng->txt('interval_info'));
        $rec->addSubItem($interval);
        
        // Weekly
        $check = new ilCheckboxInputGUI($this->lng->txt('Su_short'), 'w_day[0]');
        $check->setChecked(true);
        $rec->addSubItem($check);
        $check = new ilCheckboxInputGUI($this->lng->txt('Mo_short'), 'w_day[1]');
        $check->setChecked(true);
        $rec->addSubItem($check);
        $check = new ilCheckboxInputGUI($this->lng->txt('Tu_short'), 'w_day[2]');
        $check->setChecked(true);
        $rec->addSubItem($check);
        $check = new ilCheckboxInputGUI($this->lng->txt('We_short'), 'w_day[3]');
        $check->setChecked(true);
        $rec->addSubItem($check);
        $check = new ilCheckboxInputGUI($this->lng->txt('Th_short'), 'w_day[4]');
        $check->setChecked(true);
        $rec->addSubItem($check);
        $check = new ilCheckboxInputGUI($this->lng->txt('Fr_short'), 'w_day[5]');
        $check->setChecked(true);
        $rec->addSubItem($check);
        $check = new ilCheckboxInputGUI($this->lng->txt('Sa_short'), 'w_day[6]');
        $check->setChecked(true);
        $rec->addSubItem($check);
    }
}
