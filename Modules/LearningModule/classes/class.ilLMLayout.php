<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2001 ILIAS open source, University of Cologne            |
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
* Class ilLMLayout
*
* Handles Layout Section of Page, Structure and Media Objects (see ILIAS DTD)
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup ModulesIliasLearningModule
*/
class ilLMLayout
{
	var $ilias;
	var $keep_with_previous;
	var $keep_with_next;
	var $css_url;
	var $ver_align;
	var $hor_align;
	var $target_frame;
	var $width;
	var $height;

	/**
	* Constructor
	* @access	public
	*/
	function ilLMLayout()
	{
		global $ilias;

		$this->ilias =& $ilias;
	}

	/**
	* set keep with previous
	*
	* @param	boolean		$a_keep
	*/
	function setKeepWithPrevious ($a_keep)
	{
		$this->keep_with_previous = $a_keep;
	}


	/**
	* get keep with previous
	*/
	function getKeepWithPrevious ()
	{
		return $this->keep_with_previous;
	}


	/**
	* set keep with next
	*
	* @param	boolean		$a_keep
	*/
	function setKeepWithNext ($a_keep)
	{
		$this->keep_with_next = $a_keep;
	}


	/**
	* get keep with next
	*/
	function getKeepWithNext ()
	{
		return $this->keep_with_next;
	}


	/**
	* set css url
	*
	* @param	string		$a_url		CSS URL
	*/
	function setCssUrl ($a_url)
	{
		$this->css_url = $a_url;
	}


	/**
	* get css url
	*/
	function getCssUrl ()
	{
		return $this->css_url;
	}

	/**
	* set horizontal align
	*
	* @param	string		$a_align		left | center | right
	*/
	function setHorizontalAlign ($a_align)
	{
		$this->hor_align = $a_align;
	}

	/**
	* get horizontal align
	*/
	function getHorizontalAlign ()
	{
		return $this->hor_align;
	}

	/**
	* set vertical align
	*
	* @param	string		$a_align		top | middle | bottom
	*/
	function setVerticalAlign ($a_align)
	{
		$this->ver_align = $a_align;
	}

	/**
	* get vertical align
	*/
	function getVerticalAlign ()
	{
		return $this->ver_align;
	}


	/**
	* set target frame ?????
	*
	* @param	string		$a_align		Media | FAQ | Glossary
	*/
	function setTargetFrame ($a_frame)
	{
		$this->target_frame = $a_frame;
	}

	/**
	* get target frame ?????
	*/
	function getTargetFrame ()
	{
		return $this->target_frame;
	}

	/**
	* set width
	*
	* @param	string		$a_width		width
	*/
	function setWidth ($a_width)
	{
		$this->width = $a_width;
	}

	/**
	* get width
	*/
	function getWidth ()
	{
		return $this->width;
	}

	/**
	* set height
	*
	* @param	string		$a_height		height
	*/
	function setHeight ($a_height)
	{
		$this->height = $a_height;
	}

	/**
	* get height
	*/
	function getHeight ()
	{
		return $this->height;
	}

}
?>
