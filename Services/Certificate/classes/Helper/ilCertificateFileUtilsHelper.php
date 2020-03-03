<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilCertificateFileUtilsHelper
{
    /**
     * @param $targetFilename
     * @return string
     * @throws ilFileUtilsException
     */
    public function getValidFilename($targetFilename)
    {
        return ilFileUtils::getValidFilename($targetFilename);
    }
}
