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
* A FeedItem represents an item in a News Feed.
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*/
class ilFeedItem 
{

	private $about;
	private $title;
	private $link;
	private $description;

	/**
	* Set About.
	*
	* @param	string	$a_About	
	*/
	public function setAbout($a_About)
	{
		$this->about = $a_About;
	}

	/**
	* Get About.
	*
	* @return	string	
	*/
	public function getAbout()
	{
		return $this->about;
	}

	/**
	* Set Title.
	*
	* @param	string	$a_Title	
	*/
	public function setTitle($a_Title)
	{
		$this->title = $a_Title;
	}

	/**
	* Get Title.
	*
	* @return	string	
	*/
	public function getTitle()
	{
		return $this->title;
	}

	/**
	* Set Link.
	*
	* @param	string	$a_Link	
	*/
	public function setLink($a_Link)
	{
		$this->link = $a_Link;
	}

	/**
	* Get Link.
	*
	* @return	string	
	*/
	public function getLink()
	{
		return $this->link;
	}

	/**
	* Set Description.
	*
	* @param	string	$a_Description	
	*/
	public function setDescription($a_Description)
	{
		$this->description = $a_Description;
	}

	/**
	* Get Description.
	*
	* @return	string	
	*/
	public function getDescription()
	{
		return $this->description;
	}

	/**
	* Set Enclosure URL.
	*
	* @param	string	$a_enclosureurl	Enclosure URL
	*/
	function setEnclosureUrl($a_enclosureurl)
	{
		$this->enclosureurl = $a_enclosureurl;
	}

	/**
	* Get Enclosure URL.
	*
	* @return	string	Enclosure URL
	*/
	function getEnclosureUrl()
	{
		return $this->enclosureurl;
	}

	/**
	* Set Enclosure Type.
	*
	* @param	string	$a_enclosuretype	Enclosure Type
	*/
	function setEnclosureType($a_enclosuretype)
	{
		$this->enclosuretype = $a_enclosuretype;
	}

	/**
	* Get Enclosure Type.
	*
	* @return	string	Enclosure Type
	*/
	function getEnclosureType()
	{
		return $this->enclosuretype;
	}

	/**
	* Set Enclosure Length.
	*
	* @param	int	$a_enclosurelength	Enclosure Length
	*/
	function setEnclosureLength($a_enclosurelength)
	{
		$this->enclosurelength = $a_enclosurelength;
	}

	/**
	* Get Enclosure Length.
	*
	* @return	int	Enclosure Length
	*/
	function getEnclosureLength()
	{
		return $this->enclosurelength;
	}

	/**
	* Set Date.
	*
	* @param	string	$a_date	Date (yyyy-mm-dd hh:mm:ss)
	*/
	function setDate($a_date)
	{
		$this->date = $a_date;
	}

	/**
	* Get Date.
	*
	* @return	string	Date
	*/
	function getDate()
	{
		return $this->date;
	}

}
