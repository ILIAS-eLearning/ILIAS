<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * ILIAS base class
 * perform basic setup: init database handler, load configuration file,
 * init user authentification & error handler, load object type definitions
 * @author  Sascha Hofmann <shofmann@databay.de>
 * @version $Id$
 * @todo    review the concept how the object type definition is loaded. We need a concept to
 * edit the definitions via webfrontend in the admin console.
 */
class ILIAS
{
    public string $INI_FILE;

    /**
     * @var string
     * @deprecated
     */
    public string $client_id = '';

    /**
     * @var ilObjUser
     * @deprecated
     */
    public $account;

    /**
     * @var ilIniFile
     * @deprecated
     */
    public $ini = array();

    /**
     * @var ilIniFile
     * @deprecated
     */
    public $ini_ilias;

    /**
     * @var ilDBInterface
     * @deprecated
     */
    public $db;

    /**
     * @var ilErrorHandling
     * @deprecated
     */
    public $error_obj;

    protected ?ilSetting $setting = null;
    protected ilErrorHandling $error;

    /**
     * Constructor
     * setup ILIAS global object
     * @access    public
     */
    public function __construct()
    {
        global $DIC, $ilClientIniFile, $ilIliasIniFile, $ilDB;

        $this->ini = &$ilClientIniFile;
        $this->ini_ilias = &$ilIliasIniFile;
        $this->db = &$ilDB;

        // already available in DIC
        $this->error_obj = $DIC['ilErr'];
    }

    protected function getSettingObject(): ?ilSetting
    {
        global $DIC;

        if (!$this->setting instanceof ilSetting) {
            return $this->setting = $DIC->settings();
        }
        return $this->setting;
    }

    /**
     * read one value from settingstable
     * @deprecated
     */
    public function getSetting(string $a_keyword, ?string $a_default_value = null): ?string
    {
        return $this->getSettingObject()->get($a_keyword, $a_default_value);
    }

    /**
     * delete one value from settingstable
     * @deprecated
     * @see $DIC->settings()->delete()
     */
    public function deleteSetting(string $a_keyword): void
    {
        $this->getSettingObject()->delete($a_keyword);
    }

    /**
     * @deprecated
     */
    public function getAllSettings(): array
    {
        return $this->getSettingObject()->getAll();
    }

    /**
     * @deprecated
     */
    public function setSetting(string $a_key, string $a_val): void
    {
        $this->getSettingObject()->set($a_key, $a_val);
    }

    public function getClientId(): string
    {
        if (defined('CLIENT_ID')) {
            return (string) CLIENT_ID;
        }
        return '';
    }

    /**
     * wrapper for downward compability
     * @deprecated
     */
    public function raiseError(string $a_msg, int $a_err_obj): void
    {
        $this->error_obj->raiseError($a_msg, $a_err_obj);
    }
} // END class.ilias
