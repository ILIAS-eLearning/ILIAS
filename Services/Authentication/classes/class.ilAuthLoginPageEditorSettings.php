<?php declare(strict_types=1);

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/

/**
 * Storage of editor settings
 *
 * @author Stefan Meyer <meyer@leifos.com>
 */
class ilAuthLoginPageEditorSettings
{
    const MODE__UNDEFINED = 0;
    const MODE_RTE = 1;
    const MODE_IPE = 2;

    private array $languages = [];

    private static ?ilAuthLoginPageEditorSettings $instance = null;
    private ilSetting $storage;

    private int $mode = 0;

    private ilLanguage $lng;

    public function __construct()
    {
        global $DIC;
        $this->lng = $DIC->language();
        
        $this->storage = new ilSetting('login_editor');
        $this->read();
    }

    /**
     * Get singelton instance
     * @return ilAuthLoginPageEditorSettings
     */
    public static function getInstance() : ilAuthLoginPageEditorSettings
    {
        if (self::$instance) {
            return self::$instance;
        }
        return self::$instance = new ilAuthLoginPageEditorSettings();
    }

    /**
     * @return ilSetting
     */
    protected function getStorage() : ilSetting
    {
        return $this->storage;
    }

    public function setMode(int $a_mode) : void
    {
        //TODO check for proper mode
        $this->mode = $a_mode;
    }

    public function getMode() : int
    {
        return $this->mode;
    }

    /**
     * Get ilias editor language
     * @param string $a_langkey
     * @return string
     */
    public function getIliasEditorLanguage(string $a_langkey) : string
    {
        if ($this->mode != self::MODE_IPE) {
            return '';
        }
        if ($this->isIliasEditorEnabled($a_langkey)) {
            return $a_langkey;
        }
        if ($this->isIliasEditorEnabled($this->lng->getDefaultLanguage())) {
            return $this->lng->getDefaultLanguage();
        }
        return '';
    }

    /**
     * Enable editor for language
     */
    public function enableIliasEditor(string $a_langkey, bool $a_status)
    {
        $this->languages[$a_langkey] = $a_status;
    }

    /**
     * Check if ilias editor is enabled for a language
     * @param string $a_langkey
     */
    public function isIliasEditorEnabled(string $a_langkey) : bool
    {
        if (isset($this->languages[$a_langkey])) {
            return $this->languages[$a_langkey];
        }
        return false;
    }

    /**
     * Update settings
     */
    public function update()
    {
        $this->getStorage()->set('mode', (string) $this->getMode());

        foreach ($this->languages as $lngkey => $stat) {
            $this->storage->set($lngkey, (string) $stat);
        }
    }

    /**
     * Read settings
     */
    public function read()
    {
        $this->setMode((int) $this->getStorage()->get('mode', (string) self::MODE_RTE));

        // Language settings
        $this->languages = [];
        foreach (array_values($this->lng->getInstalledLanguages()) as $lngkey) {
            $this->enableIliasEditor($lngkey, (bool) $this->getStorage()->get($lngkey, (string) false));
        }
    }
}
