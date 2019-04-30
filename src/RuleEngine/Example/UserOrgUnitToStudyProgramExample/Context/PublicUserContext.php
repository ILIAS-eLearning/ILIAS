<?php

namespace ILIAS\RuleEngine\Example\Context;

use ILIAS\RuleEngine\Context\AbstractContext;
use \ilObjUser;

 class PublicUserContext extends AbstractContext {

	public function __construct(ilObjUser $user)
	{
		$this->user = $user;
	}

	/**
	 * @return string
	 */
	/*public function returnDbTableName() {
		return il_obj
	}*/

}