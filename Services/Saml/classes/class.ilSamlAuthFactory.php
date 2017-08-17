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

		$fs = $DIC->filesystem()->storage();

		$fs->createDir("auth/saml/config");

		if(!$fs->has('auth/saml/config/config.php'))
		{
			$fs->put('auth/saml/config/config.php', file_get_contents('./Services/Saml/lib/config.php.dist'));
		}

		return ilUtil::getDataDir() . '/auth/saml/config';
	}

	/**
	 * @return string
	 */
	public function getMetadataDirectory()
	{
		global $DIC;

		$DIC->filesystem()->storage()->createDir("auth/saml/metadata");

		return ilUtil::getDataDir() . '/auth/saml/metadata';
	}
}