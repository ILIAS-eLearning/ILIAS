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
 ********************************************************************
 */

use ILIAS\UI\Component\Item\Item;

/**
 * Class ilAppointmentPresentationEmployeeTalkGUI
 * @ilCtrl_IsCalledBy ilAppointmentPresentationEmployeeTalkGUI: ilCalendarAppointmentPresentationGUI
 * @ingroup           ServicesCalendar
 */
class ilAppointmentPresentationEmployeeTalkGUI extends ilAppointmentPresentationGUI implements ilCalendarAppointmentPresentation
{
    /**
     * ilAppointmentPresentationEmployeeTalkGUI constructor.
     */
    public function __construct(
        array $a_appointment,
        ?ilInfoScreenGUI $a_info_screen,
        ?ilToolbarGUI $a_toolbar,
        Item $a_list_item
    ) {
        parent::__construct($a_appointment, $a_info_screen, $a_toolbar, $a_list_item);

        $this->lng->loadLanguageModule(ilObjEmployeeTalk::TYPE);
    }

    public function collectPropertiesAndActions(): void
    {
        $talk = new ilObjEmployeeTalk($this->getObjIdForAppointment(), false);

        $superior = $this->getUserName($talk->getOwner(), true);
        $employee = $this->getUserName($talk->getData()->getEmployee(), true);

        $this->addObjectLinks($talk->getId(), $this->appointment);

        // get talk ref id (this is possible, since talks only have one ref id)
        $refs = ilObject::_getAllReferences($talk->getId());
        $etalRef = current($refs);
        $this->addAction($this->lng->txt("etal_open"), ilLink::_getStaticLink($etalRef, ilObjEmployeeTalk::TYPE));

        $this->addInfoSection($this->lng->txt('obj_etal'));
        $this->addInfoProperty($this->lng->txt('title'), $talk->getTitle());
        $this->addEventDescription($this->appointment);

        $this->addEventLocation($this->appointment);
        $this->addLastUpdate($this->appointment);
        $this->addListItemProperty($this->lng->txt("il_orgu_superior"), $superior);
        $this->addListItemProperty($this->lng->txt("il_orgu_employee"), $employee);

        $this->addInfoProperty($this->lng->txt("il_orgu_superior"), $superior);
        $this->addInfoProperty($this->lng->txt("il_orgu_employee"), $employee);

        parent::collectPropertiesAndActions();
    }
}
