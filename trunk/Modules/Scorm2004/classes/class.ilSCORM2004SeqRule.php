<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2008 ILIAS open source, University of Cologne            |
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

require_once("./Modules/Scorm2004/classes/class.ilSCORM2004Chapter.php");

/**
* Class ilSCORM2004Chapter
*
* Sequencing Template class for SCORM 2004 Editing
*
* @author Hendrik Holtmann <holtmann@me.com>
* @version $Id$
*
* @ingroup ModulesScorm2004
*/
class ilSCORM2004SeqTemplate extends ilSCORM2004Chapter
{

	const SEQ_TEMPLATE_DIR = './Modules/Scorm2004/templates/sequencing/';
	const SEQ_TEMPLATE_XSL = './Modules/Scorm2004/templates/xsl/seq_template.xsl';
	
	/**
	* Constructor
	* @access	public
	*/
	function ilSCORM2004SeqTemplate($a_slm_object, $a_id = 0)
	{
		parent::ilSCORM2004SeqTemplate($a_slm_object, $a_id);
	}
	
	

}
?>
