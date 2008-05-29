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

define('IL_CALENDAR_ACTION_CREATE',1);
define('IL_CALENDAR_ACTION_UPDATE',2);
define('IL_CALENDAR_ACTION_DELETE',3);

include_once('./Services/Calendar/classes/class.ilDate.php');
include_once('./Services/Calendar/classes/class.ilCalendarEntry.php');

/**
* Apointment templates are used for automatic generated apointments.
* 
* @author Stefan Meyer <smeyer.ilias@gmx.de>
* @version $Id$
*
* @ingroup ServicesCalendar
*/

class ilCalendarAppointmentTemplate
{
	protected $context_id;
	protected $title;
	protected $subtitle;
	protected $description;
	protected $information;
	protected $location;
	protected $start;
	protected $end;
	protected $fullday = false;
	protected $translation_type = IL_CAL_TRANSLATION_SYSTEM;

	protected $type;
	
	/**
	 * Constructor
	 *
	 * @access public
	 * @param int unique id
	 */
	public function __construct($a_id)
	{
		$this->context_id = $a_id;
	}
	
	/**
	 * set title
	 *
	 * @access public
	 * @param string appointment title
	 */
	public function setTitle($a_title)
	{
		$this->title = $a_title;
	}
	
	/**
	 * get title
	 *
	 * @access public
	 * @return string title
	 */
	public function getTitle()
	{
		return $this->title;
	}
	
	/**
	 * set subtitle 
	 * Used for automatic generated appointments.
	 * Will be translated automatically and be appended to the title.
	 *
	 * @access public
	 * @param string subtitle
	 * @return void
	 */
	public function setSubtitle($a_subtitle)
	{
		$this->subtitle = $a_subtitle;
	}
	
	/**
	 * get subtitle
	 *
	 * @access public
	 * @return string subtitle
	 */
	public function getSubtitle()
	{
		return $this->subtitle;
	}
	
	/**
	 * get description
	 *
	 * @access public
	 * @param string description
	 */
	public function setDescription($a_description)
	{
		$this->description = $a_description;
	}
	
	/**
	 * get description
	 *
	 * @access public
	 * @return strin description
	 */
	public function getDescription()
	{
		return $this->description;
	}
	
	/**
	 * set information
	 *
	 * @access public
	 * @param string information
	 */
	public function setInformation($a_information)
	{
		$this->information = $a_information;
	}
	
	/**
	 * get information
	 *
	 * @access public
	 * @return string information
	 */
	public function getInformation()
	{
		return $this->information;
	}
	
	/**
	 * set location
	 *
	 * @access public
	 * @param strin $a_location location
	 * @return
	 */
	public function setLocation($a_location)
	{
		$this->location = $a_location;
	}
	
	/**
	 * get location 
	 *
	 * @access public
	 * @return string location
	 */
	public function getLocation()
	{
		return $this->location;
	}
	
	/**
	 * set start
	 *
	 * @access public
	 * @param ilDateTime start
	 * @return
	 */
	public function setStart(ilDateTime $start)
	{
		$this->start = $start;
	}
	
	/**
	 * get start
	 *
	 * @access public
	 * @return ilDateTime start
	 */
	public function getStart()
	{
		return $this->start;
	}
	
	/**
	 * set end 
	 *
	 * @access public
	 * @param ilDateTime end
	 */
	public function setEnd(ilDateTime $end)
	{
		$this->end = $end;
	}
	
	/**
	 * get end
	 *
	 * @access public
	 * @return ilDateTime end
	 */
	public function getEnd()
	{
		return $this->end ? $this->end : $this->getStart();
	}
	
	/**
	 * set fullday 
	 *
	 * @access public
	 * @param bool fullday appointment
	 * @return
	 */
	public function setFullday($a_fullday)
	{
		$this->fullday = $a_fullday;
	}
	
	/**
	 * is fullday
	 *
	 * @access public
	 * @return bool true if fullday event
	 */
	public function isFullday()
	{
		return $this->fullday;
	}
	
	/**
	 * set translation type
	 *
	 * @access public
	 * @param
	 * @return
	 */
	public function setTranslationType($a_type)
	{
		$this->translation_type = $a_type;
	}
	
	/**
	 * get translation type
	 *
	 * @access public
	 * @param
	 * @return
	 */
	public function getTranslationType()
	{
		return $this->translation_type;
	}
	
	/**
	 * get context id
	 *
	 * @access public
	 * @return
	 */
	public function getContextId()
	{
		return $this->context_id;
	}
	
}
?>