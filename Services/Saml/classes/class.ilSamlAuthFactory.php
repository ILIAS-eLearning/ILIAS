<?php declare(strict_types=1);
/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\Filesystem\Exception\IOException;

/**
 * Class ilSamlAuthFactory
 */
class ilSamlAuthFactory
{
    private const METADATA_PATH = 'auth/saml/config';

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
     * @throws IOException
     */
    public function getConfigDirectory() : string
    {
        global $DIC;

        $fs = $DIC->filesystem()->storage();

        $fs->createDir(self::METADATA_PATH);

        return rtrim(ilFileUtils::getDataDir(), '/') . '/' . self::METADATA_PATH;
    }
}
