<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author        Björn Heyser <bheyser@databay.de>
 * @version        $Id$
 *
 * @package        Modules/Test(QuestionPool)
 */
class ilAssQuestionType
{
	/**
	 * @param array $questionTypeData
	 * @return array
	 */
	public static function conmpleteMissingPluginName($questionTypeData)
	{
		if( $questionTypeData['plugin'] && !strlen($questionTypeData['plugin_name']) )
		{
			$questionTypeData['plugin_name'] = $questionTypeData['type_tag'];
		}
		
		return $questionTypeData;
	}
}