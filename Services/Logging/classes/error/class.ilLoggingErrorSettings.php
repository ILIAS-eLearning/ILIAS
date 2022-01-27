<?php declare(strict_types=1);
/* Copyright (c) 2016 Stefan Hecken, Extended GPL, see docs/LICENSE */

/**
 * Settings for the error protcoll system
 *
 * @author Stefan Hecken <stefan.hecken@concepts-and-training.de>
 */
class ilLoggingErrorSettings
{
    protected string $folder = '';
    protected string $mail = '';
    protected ilIniFile $ilias_ini;
    protected ilIniFile $gClientIniFile;

    protected function __construct()
    {
        global $DIC;

        $ilIliasIniFile = $DIC->iliasIni();
        // temporary bugfix for global usage
        $ini = null;
        if ($DIC->offsetExists('ini')) {
            $ini = $DIC['ini'];
        }

        $ilClientIniFile = null;
        if ($DIC->clientIni()) {
            $ilClientIniFile = $DIC->clientIni();
        }

        //realy not nice but necessary to initalize logger at setup
        //ilias_ini is named only as $ini in inc.setup_header.php
        if (!$ilIliasIniFile) {
            if (!$ini) {
                throw new Exception("No ILIAS ini");
            } else {
                $this->ilias_ini = $ini;
            }
        } else {
            $this->ilias_ini = $ilIliasIniFile;
        }

        if ($ilClientIniFile !== null) {
            $this->gClientIniFile = $ilClientIniFile;
        }
        $this->read();
    }

    public static function getInstance() : ilLoggingErrorSettings
    {
        return new ilLoggingErrorSettings();
    }

    protected function setFolder(string $folder) : void
    {
        $this->folder = $folder;
    }

    public function setMail(string $mail) : void
    {
        $this->mail = $mail;
    }

    public function folder() : string
    {
        return $this->folder;
    }

    public function mail() : string
    {
        return $this->mail;
    }

    /**
     * reads the values from ilias.ini.php
     */
    protected function read()
    {
        $this->setFolder((string) $this->ilias_ini->readVariable("log", "error_path"));

        if ($this->gClientIniFile instanceof \ilIniFile) {
            $this->setMail((string) $this->gClientIniFile->readVariable("log", "error_recipient"));
        }
    }

    /**
     * writes mail recipient into client.ini.php
     */
    public function update()
    {
        if ($this->gClientIniFile instanceof \ilIniFile) {
            $this->gClientIniFile->addGroup("log");
            $this->gClientIniFile->setVariable("log", "error_recipient", trim($this->mail()));
            $this->gClientIniFile->write();
        }
    }
}
