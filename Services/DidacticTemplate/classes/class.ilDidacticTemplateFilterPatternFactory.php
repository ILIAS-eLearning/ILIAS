<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
 * Factory for didactic template filter patterns
 *
 * @author Stefan Meyer <meyer@leifos.com>
 * @ingroup ServicesDidacticTemplate
 */
class ilDidacticTemplateFilterPatternFactory
{
	/**
	 * Get patterns by template id
	 * @param int $a_tpl_id
	 * @param array Array of ilDidacticTemplateFilterPattern
	 */
	public static function lookupPatternsByParentId($a_parent_id,$a_parent_type)
	{
		global $ilDB;
		
		$query = 'SELECT pattern_id,pattern_type FROM didactic_tpl_fp '.
			'WHERE parent_id = '.$ilDB->quote($a_parent_id).' '.
			'AND parent_type = '.$ilDB->quote($a_parent_type,'text');
		$res = $ilDB->query($query);

		$patterns = array();
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{

			include_once './Services/DidacticTemplate/classes/class.ilDidacticTemplateFilterPattern.php';
			switch($row->pattern_type)
			{
				case ilDidacticTemplateFilterPattern::PATTERN_INCLUDE:
					include_once './Services/DidacticTemplate/classes/class.ilDidacticTemplateIncludeFilterPattern.php';
					$patterns[] = new ilDidacticTemplateIncludeFilterPattern($row->pattern_id);
					break;

				case ilDidacticTemplateFilterPattern::PATTERN_EXCLUDE:
					include_once './Services/DidacticTemplate/classes/class.ilDidacticTemplateExcludeFilterPattern.php';
					$patterns[] = new ilDidacticTemplateExcludeFilterPattern($row->pattern_id);
					break;
			}

		}
		return $patterns;
	}
}
