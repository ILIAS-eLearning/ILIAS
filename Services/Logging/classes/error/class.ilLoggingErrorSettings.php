<?php

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
    protected ?ilIniFile $ilias_ini = null;
    protected ?ilIniFile $gClientIniFile = null;

    protected function __construct()
    {
        global $DIC;

        if ($DIC->offsetExists('ilIliasIniFile')) {
            $this->ilias_ini = $DIC->iliasIni();
        } elseif ($DIC->offsetExists('ini')) {
            $this->ilias_ini = $DIC['ini'];
        }
        if ($DIC->offsetExists('ilClientIniFile')) {
            $this->gClientIniFile = $DIC->clientIni();
        }
        $this->read();
    }

    public static function getInstance(): ilLoggingErrorSettings
    {
        return new ilLoggingErrorSettings();
    }

    protected function setFolder(string $folder): void
    {
        $this->folder = $folder;
    }

    public function setMail(string $mail): void
    {
        $this->mail = $mail;
    }

    public function folder(): string
    {
        return $this->folder;
    }

    public function mail(): string
    {
        return $this->mail;
    }

    /**
     * reads the values from ilias.ini.php
     */
    protected function read(): void
    {
        if ($this->ilias_ini instanceof ilIniFile) {
            $this->setFolder((string) $this->ilias_ini->readVariable("log", "error_path"));
        }
        if ($this->gClientIniFile instanceof \ilIniFile) {
            $this->setMail((string) $this->gClientIniFile->readVariable("log", "error_recipient"));
        }
    }

    /**
     * writes mail recipient into client.ini.php
     */
    public function update(): void
    {
        if ($this->gClientIniFile instanceof \ilIniFile) {
            $this->gClientIniFile->addGroup("log");
            $this->gClientIniFile->setVariable("log", "error_recipient", trim($this->mail()));
            $this->gClientIniFile->write();
        }
    }
}
