<?php declare(strict_types=1);
/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilSamlAuthFactory
 */
class ilSamlAuthFactory
{
    const METADATA_PATH = 'auth/saml/config';

    /**
     * @param string $authSourceName
     * @return ilSamlAuth
     * @throws Exception
     */
    public function auth(string $authSourceName = 'default-sp') : ilSamlAuth
    {
        return new ilSimpleSAMLphpWrapper(
            $authSourceName,
            $this->getConfigDirectory()
        );
    }

    /**
     * @return string
     * @throws \ILIAS\Filesystem\Exception\IOException
     */
    public function getConfigDirectory() : string
    {
        global $DIC;

        $fs = $DIC->filesystem()->storage();

        $fs->createDir(self::METADATA_PATH);

        return rtrim(ilUtil::getDataDir(), '/') . '/' . self::METADATA_PATH;
    }
}
