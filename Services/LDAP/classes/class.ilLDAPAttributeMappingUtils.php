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
* A collection of static utility functions for LDAP attribute mapping
*  
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
* 
* @ingroup ServicesLDAP
*/
class ilLDAPAttributeMappingUtils
{
	/**
	 * Get mapping rule by objectClass
	 *
	 * @access public
	 * @param string 
	 * 
	 */
	public static function _getMappingRulesByClass($a_class)
	{
		$mapping_rule = array();
		
		switch($a_class)
		{
			case 'inetOrgPerson':
				$mapping_rule['firstname'] = 'givenName';
				$mapping_rule['institution'] = 'o';
				$mapping_rule['department'] = 'departmentNumber';
				$mapping_rule['phone_home'] = 'homePhone';
				$mapping_rule['phone_mobile'] = 'mobile';
				$mapping_rule['email'] = 'mail';
				$mapping_rule['photo'] = 'jpegPhoto';
				// No break since it inherits from organizationalPerson and person
				
			case 'organizationalPerson':
				$mapping_rule['fax'] = 'facsimileTelephoneNumber';
				$mapping_rule['title'] = 'title';
				$mapping_rule['street'] = 'street';
				$mapping_rule['zipcode'] = 'postalCode';
				$mapping_rule['city'] = 'l';
				$mapping_rule['country'] = 'st';
				// No break since it inherits from person
				
			case 'person':
				$mapping_rule['lastname'] = 'sn';
				$mapping_rule['phone_office'] = 'telephoneNumber';
				break;
				
			case 'ad_2003':
				$mapping_rule['firstname'] = 'givenName';
				$mapping_rule['lastname'] = 'sn';
				$mapping_rule['title'] = 'title';
				$mapping_rule['institution'] = 'company';
				$mapping_rule['department'] = 'department';
				$mapping_rule['phone_home'] = 'telephoneNumber';
				$mapping_rule['phone_mobile'] = 'mobile';
				$mapping_rule['email'] = 'mail';
				$mapping_rule['street'] = 'streetAddress';
				$mapping_rule['city'] = 'l,st';
				$mapping_rule['country'] = 'co';
				$mapping_rule['zipcode'] = 'postalCode';
				$mapping_rule['fax'] = 'facsimileTelephoneNumber';
				break;
		}
		return $mapping_rule ? $mapping_rule : array();
	}
	
} 


?>