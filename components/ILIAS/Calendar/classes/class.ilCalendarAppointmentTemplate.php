<?php

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

declare(strict_types=1);
/**
 * Apointment templates are used for automatic generated apointments.
 * @author  Stefan Meyer <smeyer.ilias@gmx.de>
 * @version $Id$
 * @ingroup ServicesCalendar
 */
class ilCalendarAppointmentTemplate
{
    protected int $context_id = 0;
    protected string $context_info = '';
    protected string $title = '';
    protected string $subtitle = '';
    protected string $description = '';
    protected string $information = '';
    protected string $location = '';
    protected ?ilDateTime $start = null;
    protected ?ilDateTime $end = null;
    protected bool $fullday = false;
    protected int $translation_type = ilCalendarEntry::TRANSLATION_SYSTEM;

    public function __construct(int $a_id)
    {
        $this->context_id = $a_id;
    }

    public function setContextInfo(string $a_info): void
    {
        $this->context_info = $a_info;
    }

    public function getContextInfo(): string
    {
        return $this->context_info;
    }

    public function setTitle(string $a_title): void
    {
        $this->title = $a_title;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * set subtitle
     * Used for automatic generated appointments.
     * Will be translated automatically and be appended to the title.
     */
    public function setSubtitle(string $a_subtitle): void
    {
        $this->subtitle = $a_subtitle;
    }

    /**
     * get subtitle
     */
    public function getSubtitle(): string
    {
        return $this->subtitle;
    }

    public function setDescription(string $a_description): void
    {
        $this->description = $a_description;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setInformation(string $a_information): void
    {
        $this->information = $a_information;
    }

    public function getInformation(): string
    {
        return $this->information;
    }

    public function setLocation(string $a_location): void
    {
        $this->location = $a_location;
    }

    public function getLocation(): string
    {
        return $this->location;
    }

    public function setStart(ilDateTime $start): void
    {
        $this->start = $start;
    }

    public function getStart(): ?ilDateTime
    {
        return $this->start;
    }

    public function setEnd(ilDateTime $end): void
    {
        $this->end = $end;
    }

    /**
     * @todo check if this is required
     */
    public function getEnd(): ?ilDateTime
    {
        return $this->end ?: $this->getStart();
    }

    public function setFullday(bool $a_fullday): void
    {
        $this->fullday = $a_fullday;
    }

    public function isFullday(): bool
    {
        return $this->fullday;
    }

    public function setTranslationType(int $a_type): void
    {
        $this->translation_type = $a_type;
    }

    public function getTranslationType(): int
    {
        return $this->translation_type;
    }

    public function getContextId(): int
    {
        return $this->context_id;
    }
}
