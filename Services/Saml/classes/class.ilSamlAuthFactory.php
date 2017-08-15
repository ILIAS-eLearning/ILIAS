<?php
/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilSamlAuthFactory
 */
class ilSamlAuthFactory
{
	/**
	 * @param string $authSourceName
	 * @return ilSamlAuth
	 */
	public function auth($authSourceName)
	{
		global $DIC;

		$DIC->filesystem()->storage()->createDir("auth/saml/config");

		require_once 'Services/Saml/classes/class.ilSamlAuthSimpleSAMLphpWrapper.php';
		return new ilSamlAuthSimpleSAMLphpWrapper(
			$authSourceName, ilUtil::getDataDir() . '/auth/saml/config'
		);
	}
}