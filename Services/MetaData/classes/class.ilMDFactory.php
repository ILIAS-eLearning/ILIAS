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
* Meta Data factory class 
*
* @package ilias-core
* @version $Id$
*/

class ilMDFactory
{


	/*
	 * get md element by index and type
	 *
	 * @param string type (name e.g meta_general,meta_language)
	 *
	 * @return MD object
	 */
	function &_getInstance($a_type,$a_index)
	{
		switch($a_type)
		{
			case 'meta_identifier':
				include_once 'Services/MetaData/classes/class.ilMDIdentifier.php';

				$ide =& new ilMDIdentifier();
				$ide->setMetaId($a_index);
				
				return $ide;
			
			case 'meta_description':
				include_once 'Services/MetaData/classes/class.ilMDDescription.php';

				$des =& new ilMDDescription();
				$des->setMetaId($a_index);
				
				return $des;

			case 'meta_keyword':
				include_once 'Services/MetaData/classes/class.ilMDKeyword.php';

				$key =& new ilMDKeyword();
				$key->setMetaId($a_index);
				
				return $key;

			case 'meta_language':
				include_once 'Services/MetaData/classes/class.ilMDLanguage.php';

				$lan =& new ilMDLanguage();
				$lan->setMetaId($a_index);

				return $lan;
				
			case 'meta_rights':
				include_once 'Services/MetaData/classes/class.ilMDRights.php';

				$rights =& new ilMDRights();
				$rights->setMetaId($a_index);
				return $rights;

			case 'meta_educational':
				include_once 'Services/MetaData/classes/class.ilMDEducational.php';

				$rights =& new ilMDEducational();
				$rights->setMetaId($a_index);
				return $rights;

			default:
				echo $a_type . " not known";
				
		}
	}
}
?>