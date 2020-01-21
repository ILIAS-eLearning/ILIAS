<?php
/* Copyright (c) 2016 Stefan Hecken, Extended GPL, see docs/LICENSE */

/**
 * Settings for the error protcoll system
 *
 * @author Stefan Hecken <stefan.hecken@concepts-and-training.de>
 */
class ilLoggingErrorSettings
{
    protected $folder;
    protected $mail;

    protected function __construct()
    {
        global $DIC;

        $ilIliasIniFile = $DIC['ilIliasIniFile'];
        // temporary bugfix for global usage
        if ($DIC->offsetExists('ini')) {
            $ini = $DIC['ini'];
        }

        $ilClientIniFile = null;
        if (isset($DIC['ilClientIniFile'])) {
            $ilClientIniFile = $DIC['ilClientIniFile'];
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

        $this->folder = null;
        $this->mail = null;

        $this->read();
    }

    public static function getInstance()
    {
        return new ilLoggingErrorSettings();
    }

    protected function setFolder($folder)
    {
        $this->folder = $folder;
    }

    public function setMail($mail)
    {
        $this->mail = $mail;
    }

    public function folder()
    {
        return $this->folder;
    }

    public function mail()
    {
        return $this->mail;
    }

    /**
     * reads the values from ilias.ini.php
     */
    protected function read()
    {
        $this->setFolder($this->ilias_ini->readVariable("log", "error_path"));

        if ($this->gClientIniFile instanceof \ilIniFile) {
            $this->setMail($this->gClientIniFile->readVariable("log", "error_recipient"));
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
