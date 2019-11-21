<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
 * Class ilLTIConsumerLP
 *
 * @author      Uwe Kohnle <kohnle@internetlehrer-gmbh.de>
 * @author      Björn Heyser <info@bjoernheyser.de>
 *
 * @package     Modules/LTIConsumer
 */
class ilLTIConsumerLP extends ilObjectLP
{
	public static function getDefaultModes($a_lp_active)
	{
		return array(
			ilLPObjSettings::LP_MODE_DEACTIVATED,
			ilLPObjSettings::LP_MODE_LTI_OUTCOME
		);
	}
	
	public function getDefaultMode()
	{
		return ilLPObjSettings::LP_MODE_DEACTIVATED;
	}
	
	public function getValidModes()
	{
		return array(
			ilLPObjSettings::LP_MODE_DEACTIVATED,
			ilLPObjSettings::LP_MODE_LTI_OUTCOME
		);
	}
}
