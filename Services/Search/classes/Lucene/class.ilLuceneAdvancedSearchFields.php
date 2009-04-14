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
* Field definitions of advanced meta data search
* 
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
* 
*
* @ingroup ServicesSearch
*/
class ilLuceneAdvancedSearchFields
{
	public static function getFields()
	{
		global $lng;
		
		$lng->loadLanguageModule('meta');
		
		$fields = array(
			'lom_content'				=> $lng->txt('content'),
			'lom_type'					=> $lng->txt('type'),
			'lom_language'				=> $lng->txt('language'),
			'lom_keyword'				=> $lng->txt('meta_keyword'),	
			'lom_coverage'				=> $lng->txt('meta_coverage'),	
			'lom_structure'				=> $lng->txt('meta_structure'),	
			'lom_status'				=> $lng->txt('meta_status'),	
			'lom_version'				=> $lng->txt('meta_version'),	
			'lom_contribute'			=> $lng->txt('meta_contribute'),	
			'lom_format'				=> $lng->txt('meta_format'),	
			'lom_operating_system'		=> $lng->txt('meta_operating_system'),	
			'lom_browser'				=> $lng->txt('meta_browser'),	
			'lom_interactivity'			=> $lng->txt('meta_interactivity_type'),	
			'lom_resource'				=> $lng->txt('meta_learning_resource_type'),	
			'lom_level'					=> $lng->txt('meta_interactivity_level'),	
			'lom_density'				=> $lng->txt('meta_semantic_density'),	
			'lom_user_role'				=> $lng->txt('meta_intended_end_user_role'),	
			'lom_context'				=> $lng->txt('meta_context'),	
			'lom_difficulty'			=> $lng->txt('meta_difficulty'),	
			'lom_costs'					=> $lng->txt('meta_cost'),	
			'lom_copyright'				=> $lng->txt('meta_copyright_and_other_restrictions'),	
			'lom_purpose'				=> $lng->txt('meta_purpose'),	
			'lom_taxon'					=> $lng->txt('meta_taxon')
			);
		
		return $fields;
	}
}
?>
