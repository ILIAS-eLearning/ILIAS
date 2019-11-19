<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
 * Class ilLPStatusCmiXapiCompletedWithFailed
 *
 * @author      Uwe Kohnle <kohnle@internetlehrer-gmbh.de>
 * @author      Björn Heyser <info@bjoernheyser.de>
 * @author      Stefan Schneider <info@eqsoft.de>
 */
class ilLPStatusCmiXapiCompletedWithFailed extends ilLPStatusCmiXapiCompleted
{
	protected function resultSatisfyFailed(ilCmiXapiResult $result)
	{
		if( $result->getStatus() == 'failed' )
		{
			return true;
		}
		
		return false;
	}
}
