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
* QTI material class
*
* @author Helmut Schottmüller <hschottm@gmx.de>
* @version $Id$
*
* @package assessment
*/
class ilQTIMaterial
{
	var $comment;
	var $mattext;
	var $matemtext;
	var $matimage;
	var $mataudio;
	var $matvideo;
	var $matapplet;
	var $matapplication;
	var $matref;
	var $matbreak;
	var $mat_extension;
	var $altmaterial;
	
	function ilQTIMaterial()
	{
		$this->mattext = array();
		$this->matemtext = array();
		$this->matimage = array();
		$this->mataudio = array();
		$this->matvideo = array();
		$this->matapplet = array();
		$this->matapplication = array();
		$this->matref = array();
		$this->matbreak = array();
		$this->mat_extension = array();
		$this->altmaterial = array();
	}
	
	function addMattext($a_mattext)
	{
		array_push($this->mattext, $a_mattext);
	}

	function addMatimage($a_matimage)
	{
		array_push($this->matimage, $a_matimage);
	}

	function addMatemtext($a_matemtext)
	{
		array_push($this->matemtext, $a_matemtext);
	}

	function addMataudio($a_mataudio)
	{
		array_push($this->mataudio, $a_mataudio);
	}

	function addMatvideo($a_matvideo)
	{
		array_push($this->matvideo, $a_matvideo);
	}

	function addMatapplet($a_matapplet)
	{
		array_push($this->matapplet, $a_matapplet);
	}

	function addMatapplication($a_matapplication)
	{
		array_push($this->matapplication, $a_matapplication);
	}

	function addMatref($a_matref)
	{
		array_push($this->matref, $a_matref);
	}

	function addMatbreak($a_matbreak)
	{
		array_push($this->matbreak, $a_matbreak);
	}

	function addMat_extension($a_mat_extension)
	{
		array_push($this->mat_extension, $a_mat_extension);
	}

	function addAltmaterial($a_altmaterial)
	{
		array_push($this->altmaterial, $a_altmaterial);
	}
}
?>
