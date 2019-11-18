<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
 * Class ilLPStatusCmiXapiCompletedOrPassed
 *
 * @author      Uwe Kohnle <kohnle@internetlehrer-gmbh.de>
 * @author      Björn Heyser <info@bjoernheyser.de>
 * @author      Stefan Schneider <info@eqsoft.de>
 */
class ilLPStatusCmiXapiCompletedOrPassed extends ilLPStatusCmiXapiAbstract
{
	protected function resultSatisfyCompleted(ilCmiXapiResult $result)
	{
		if( $result->getStatus() == 'completed' )
		{
			return true;
		}
		
		if( $result->getStatus() == 'passed' )
		{
			return true;
		}
		
		return false;
	}
	
	protected function resultSatisfyFailed(ilCmiXapiResult $result)
	{
		return false;
	}
}
