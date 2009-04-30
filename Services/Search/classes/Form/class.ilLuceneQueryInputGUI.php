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
* Lucene query input form gui
* 
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
* 
*
* @ingroup ServicesSearch
*/
class ilLuceneQueryInputGUI extends ilTextInputGUI
{
	
	/**
	 * Constructor
	 */
	public function __construct($a_title,$a_postvar)
	{
		parent::__construct($a_title,$a_postvar);
	}	
	
	public function checkInput()
	{
		global $lng,$ilUser;
		
		$ok = parent::checkInput();
		
		$query = ilUtil::stripSlashes($_POST[$this->getPostVar()]);
		
		if(!$ok or !strlen($query))
		{
			return false;
		}
		
		include_once './Services/Search/classes/Lucene/class.ilLuceneQueryParser.php';
		try {
			ilLuceneQueryParser::validateQuery($query);
			return true;
		}
		catch(ilLuceneQueryParserException $e)
		{
			$this->setAlert($lng->txt($e->getMessage()));
			return false;
		}
	}
	
}
?>
