<?php declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

class ilVirusScannerFactory
{
    public static function _getInstance() : ?ilVirusScanner
    {
        $vs = null;

        if (IL_VIRUS_SCANNER === "icap") {
            if (strlen(IL_ICAP_CLIENT) > 0) {
                $vs = new ilVirusScannerICapClient('', '');
            } else {
                $vs = new ilVirusScannerICapRemoteAvClient('', '');
            }
        } else {
            switch (IL_VIRUS_SCANNER) {
                case "Sophos":
                    $vs = new ilVirusScannerSophos(IL_VIRUS_SCAN_COMMAND, IL_VIRUS_CLEAN_COMMAND);
                    break;
                case "AntiVir":
                    global $DIC;
                    $DIC->logger()->root()->error('AntiVir is deprecated, please install and use a different virus scanner.');
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
