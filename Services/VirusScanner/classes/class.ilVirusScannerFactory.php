<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilVirusScannerFactory
 */
class ilVirusScannerFactory
{
    /**
     * @return ilVirusScannerAntiVir|ilVirusScannerClamAV|ilVirusScannerICapClient|ilVirusScannerICapRemoteAvClient|ilVirusScannerSophos|null
     */
    static public function _getInstance()
    {
        $vs = null;

        if (IL_SCANNER_TYPE == "1") {
            if (strlen(IL_ICAP_CLIENT) > 0) {
                $vs = new ilVirusScannerICapClient('', '');
            } else {
                $vs = new ilVirusScannerICapRemoteAvClient('', '');
            }
        } elseif (IL_SCANNER_TYPE == 0) {
            switch (IL_VIRUS_SCANNER) {
                case "Sophos":
                    $vs = new ilVirusScannerSophos(IL_VIRUS_SCAN_COMMAND, IL_VIRUS_CLEAN_COMMAND);
                    break;
                case "AntiVir":
                    global $DIC;
                    $DIC->logger()->error('AntiVir is deprecated, please install and use a different virus scanner.');
                    $vs = new ilVirusScannerAntiVir(IL_VIRUS_SCAN_COMMAND, IL_VIRUS_CLEAN_COMMAND);
                    break;
                case "ClamAV":
                    $vs = new ilVirusScannerClamAV(IL_VIRUS_SCAN_COMMAND, IL_VIRUS_CLEAN_COMMAND);
                    break;
            }
        }
        return $vs;
    }
}
