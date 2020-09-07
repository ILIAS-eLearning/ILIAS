<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Factory for virus scanner class(es)
 * @author    Alex Killing <alex.killing@gmx.de>
 * @version   $Id$
 */
class ilVirusScannerFactory
{
    /**
     * @return ilVirusScannerAntiVir|ilVirusScannerClamAV|ilVirusScannerSophos|null
     */
    public static function _getInstance()
    {
        // create global virus scanner class instance
        switch (IL_VIRUS_SCANNER) {
            case "Sophos":
                require_once("./Services/VirusScanner/classes/class.ilVirusScannerSophos.php");
                $vs = new ilVirusScannerSophos(IL_VIRUS_SCAN_COMMAND, IL_VIRUS_CLEAN_COMMAND);
                return $vs;
                break;

            case "AntiVir":
                require_once("./Services/VirusScanner/classes/class.ilVirusScannerAntiVir.php");
                $vs = new ilVirusScannerAntiVir(IL_VIRUS_SCAN_COMMAND, IL_VIRUS_CLEAN_COMMAND);
                return $vs;
                break;

            case "ClamAV":
                require_once("./Services/VirusScanner/classes/class.ilVirusScannerClamAV.php");
                $vs = new ilVirusScannerClamAV(IL_VIRUS_SCAN_COMMAND, IL_VIRUS_CLEAN_COMMAND);
                return $vs;
                break;

            default:
                return null;
                break;
        }
    }
}
