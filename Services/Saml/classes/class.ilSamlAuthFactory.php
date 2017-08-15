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
		require_once 'Services/Saml/classes/class.ilSamlAuthSimpleSAMLphpWrapper.php';
		return new ilSamlAuthSimpleSAMLphpWrapper(
			$authSourceName, $this->getConfigDirectory() 
		);
	}

	/**
	 * @return string
	 */
	public function getConfigDirectory()
	{
		global $DIC;

		$DIC->filesystem()->storage()->createDir("auth/saml/config");

		return ilUtil::getDataDir() . '/auth/saml/config';
	}

	/**
	 * @return string
	 */
	public function getMetadaDirectory()
	{
		global $DIC;

		$DIC->filesystem()->storage()->createDir("auth/saml/metadata");

		return ilUtil::getDataDir() . '/auth/saml/metadata';
	}
}