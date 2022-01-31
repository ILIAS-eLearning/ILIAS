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
 * @ingroup ServicesAuthentication
 */
class ilAuthLoginPageEditorSettings
{
    const MODE__UNDEFINED = 0;
    const MODE_RTE = 1;
    const MODE_IPE = 2;

    private $languages = array();


    private static $instance = null;
    private $storage = null;

    private $mode = 0;


    public function __construct()
    {
        $this->storage = new ilSetting('login_editor');
        $this->read();
    }

    /**
     * Get singelton instance
     * @return ilAuthLoginPageEditorSettings
     */
    public static function getInstance()
    {
        if (self::$instance) {
            return self::$instance;
        }
        return self::$instance = new ilAuthLoginPageEditorSettings();
    }

    /**
     * @return ilSetting
     */
    protected function getStorage()
    {
        return $this->storage;
    }

    public function setMode($a_mode)
    {
        $this->mode = $a_mode;
    }

    public function getMode()
    {
        return $this->mode;
    }

    /**
     * Get ilias editor language
     * @global ilLanguage $lng
     * @param string $a_langkey
     * @return string
     */
    public function getIliasEditorLanguage($a_langkey)
    {
        global $DIC;

        $lng = $DIC['lng'];

        if ($this->getMode() != self::MODE_IPE) {
            return '';
        }
        if ($this->isIliasEditorEnabled($a_langkey)) {
            return $a_langkey;
        }
        if ($this->isIliasEditorEnabled($lng->getDefaultLanguage())) {
            return $lng->getDefaultLanguage();
        }
        return '';
    }

    /**
     * Enable editor for language
     */
    public function enableIliasEditor($a_langkey, $a_status)
    {
        $this->languages[$a_langkey] = (bool) $a_status;
    }

    /**
     * Check if ilias editor is enabled for a language
     * @param string $a_langkey
     */
    public function isIliasEditorEnabled($a_langkey)
    {
        if (isset($this->languages[$a_langkey])) {
            return (bool) $this->languages[$a_langkey];
        }
        return false;
    }

    /**
     * Update settings
     */
    public function update()
    {
        $this->getStorage()->set('mode', (string) $this->getMode());

        foreach ((array) $this->languages as $lngkey => $stat) {
            $this->storage->set($lngkey, (string) $stat);
        }
    }

    /**
     * Read settings
     */
    public function read()
    {
        global $DIC;

        $lng = $DIC['lng'];
        
        $this->setMode((int) $this->getStorage()->get('mode', (string) self::MODE_RTE));

        // Language settings
        $this->languages = array();
        foreach ($lng->getInstalledLanguages() as $num => $lngkey) {
            $this->enableIliasEditor($lngkey, (int) $this->getStorage()->get($lngkey, (string) 0));
        }
    }
}
