<?php

declare(strict_types=1);
/**
 *
 * @author Jesús López Reyes <lopez@leifos.de>
 *
 * @ingroup ServicesCalendar
 */
interface ilCalendarAppointmentPresentation
{
    public function getToolbar(): ?ilToolbarGUI;

    public function getInfoScreen(): ?ilInfoScreenGUI;
}
