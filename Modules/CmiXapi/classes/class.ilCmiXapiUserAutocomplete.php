<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
 * Class ilCmiXapiUserAutocomplete
 *
 * @author      Uwe Kohnle <kohnle@internetlehrer-gmbh.de>
 * @author      Björn Heyser <info@bjoernheyser.de>
 * @author      Stefan Schneider <info@eqsoft.de>
 *
 * @package     Module/CmiXapi
 */
class ilCmiXapiUserAutocomplete extends ilUserAutoComplete
{
	/**
	 * @var int
	 */
	protected $objId;
	
	/**
	 * @param int $objId
	 */
	public function __construct($objId)
	{
		parent::__construct();
		$this->objId = $objId;
	}
	
	/**
	 * {@inheritdoc}
	 */
	protected function getFromPart()
	{
		global $DIC;
		
		$fromPart = parent::getFromPart();
		
		$fromPart .= "
			INNER JOIN cmix_users
			ON cmix_users.obj_id = {$DIC->database()->quote($this->objId, 'integer')}
			AND cmix_users.usr_id = ud.usr_id
		";
		
		return $fromPart;
	}
}
