<?php declare(strict_types=1);
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

    public function setContextInfo(string $a_info) : void
    {
        $this->context_info = $a_info;
    }

    public function getContextInfo() : string
    {
        return $this->context_info;
    }

    public function setTitle(string $a_title) : void
    {
        $this->title = $a_title;
    }

    public function getTitle() : string
    {
        return $this->title;
    }

    /**
     * set subtitle
     * Used for automatic generated appointments.
     * Will be translated automatically and be appended to the title.
     */
    public function setSubtitle(string $a_subtitle) : void
    {
        $this->subtitle = $a_subtitle;
    }

    /**
     * get subtitle
     */
    public function getSubtitle() : string
    {
        return $this->subtitle;
    }

    public function setDescription(string $a_description) : void
    {
        $this->description = $a_description;
    }

    public function getDescription() : string
    {
        return $this->description;
    }

    public function setInformation(string $a_information) : void
    {
        $this->information = $a_information;
    }

    public function getInformation() : string
    {
        return $this->information;
    }

    public function setLocation(string $a_location) : void
    {
        $this->location = $a_location;
    }

    public function getLocation() : string
    {
        return $this->location;
    }

    public function setStart(ilDateTime $start) : void
    {
        $this->start = $start;
    }

    public function getStart() : ?ilDateTime
    {
        return $this->start;
    }

    public function setEnd(ilDateTime $end) : void
    {
        $this->end = $end;
    }

    /**
     * @todo check if this is required
     */
    public function getEnd() : ?ilDateTime
    {
        return $this->end ?: $this->getStart();
    }

    public function setFullday(bool $a_fullday) : void
    {
        $this->fullday = $a_fullday;
    }

    public function isFullday() : bool
    {
        return $this->fullday;
    }

    public function setTranslationType(int $a_type) : void
    {
        $this->translation_type = $a_type;
    }

    public function getTranslationType() : int
    {
        return $this->translation_type;
    }

    public function getContextId() : int
    {
        return $this->context_id;
    }
}
