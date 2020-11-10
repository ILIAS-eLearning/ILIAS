<?php

/**
 * Class ilObjWebDAV
 * @author Lukas Zehnder <lz@studer-raimann.ch>
 * @package WebDAV
 */
class ilObjWebDAV extends ilObject
{
    /**
     * Boolean property. Set this to true, to enable WebDAV access to files.
     */
    private $webdavEnabled;
    /**
     * Boolean property. Set this to true, to enable versioning for existing files uploaded with WebDAV.
     * Set this to false, to overwrite existing file version on file upload
     */
    private $webdavVersioningEnabled;
    /**
     * Boolean property. Set this to true, to make WebDAV item actions visible for repository items.
     */
    private $webdavActionsVisible;
    /**
     * Boolean property. Set this to true, to use customized mount instructions.
     * If the value is false, the default mount instructions are used.
     */
    private $customWebfolderInstructionsEnabled;
    /**
     * String property. Customized mount instructions for WebDAV access to files.
     */
    private $customWebfolderInstructions;


    /**
     * ilObjWebDAV constructor.
     * @param int  $id
     * @param bool $call_by_reference
     */
    public function __construct($id = 0, bool $call_by_reference = true)
    {
        $this->type = "wbdv";
        parent::__construct($id, $call_by_reference);
    }

    /**
     * @inheritDoc
     */
    public function getPresentationTitle()
    {
        return $this->lng->txt("webdav");
    }

    /**
     * @inheritDoc
     */
    public function getLongDescription()
    {
        return $this->lng->txt("webdav_description");
    }


    /**
     * Sets the webdavEnabled property.
     *
     * @param boolean    new value
     *
     * @return    void
     */
    public function setWebdavEnabled($newValue)
    {
        $this->webdavEnabled = $newValue;
    }

    /**
     * Gets the webdavEnabled property.
     *
     * @return    boolean    value
     */
    public function isWebdavEnabled()
    {
        return $this->webdavEnabled;
    }

    public function setWebdavVersioningEnabled($newValue)
    {
        $this->webdavVersioningEnabled = $newValue;
    }

    public function isWebdavVersioningEnabled()
    {
        return $this->webdavVersioningEnabled;
    }

    /**
     * Sets the webdavActionsVisible property.
     *
     * @param boolean    new value
     *
     * @return    void
     */
    public function setWebdavActionsVisible($newValue)
    {
        $this->webdavActionsVisible = $newValue;
    }

    /**
     * Gets the webdavActionsVisible property.
     *
     * @return    boolean    value
     */
    public function isWebdavActionsVisible()
    {
        return $this->webdavActionsVisible;
    }

    /**
     * Gets the defaultWebfolderInstructions property.
     * This is a read only property. The text is retrieved from $lng.
     *
     * @return    String    value
     */
    public function getDefaultWebfolderInstructions()
    {
        return self::_getDefaultWebfolderInstructions();
    }

    /**
     * Gets the customWebfolderInstructionsEnabled property.
     *
     * @return    boolean    value
     */
    public function isCustomWebfolderInstructionsEnabled()
    {
        return $this->customWebfolderInstructionsEnabled;
    }

    /**
     * Sets the customWebfolderInstructionsEnabled property.
     *
     * @param boolean new value.
     *
     * @return    void
     */
    public function setCustomWebfolderInstructionsEnabled($newValue)
    {
        $this->customWebfolderInstructionsEnabled = $newValue;
    }


    /**
     * create
     *
     * note: title, description and type should be set when this function is called
     *
     * @return    integer        object id
     */
    public function create()
    {
        parent::create();
        $this->write();
    }


    /**
     * update object in db
     *
     * @return    boolean    true on success
     */
    public function update()
    {
        parent::update();
        $this->write();
    }

    /**
     * write object data into db
     *
     * @param boolean
     */
    private function write()
    {
        global $DIC;
        $ilClientIniFile = $DIC['ilClientIniFile'];
        $settings = new ilSetting('webdav');

        // Clear any old error messages
        $ilClientIniFile->error(null);

        if (!$ilClientIniFile->groupExists('file_access')) {
            $ilClientIniFile->addGroup('file_access');
        }
        $ilClientIniFile->setVariable('file_access', 'webdav_enabled', $this->webdavEnabled ? '1' : '0');
        $settings->set('webdav_versioning_enabled', $this->webdavVersioningEnabled ? '1' : '0');
        $ilClientIniFile->setVariable('file_access', 'webdav_actions_visible', $this->webdavActionsVisible ? '1' : '0');
        $ilClientIniFile->write();

        if ($ilClientIniFile->getError()) {
            global $DIC;
            $ilErr = $DIC['ilErr'];
            $ilErr->raiseError($ilClientIniFile->getError(), $ilErr->WARNING);
        }
    }

    /**
     * read object data from db into object
     */
    public function read()
    {
        parent::read();

        global $DIC;
        $settings = new ilSetting('webdav');
        $ilClientIniFile = $DIC['ilClientIniFile'];
        $this->webdavEnabled = $ilClientIniFile->readVariable('file_access', 'webdav_enabled') == '1';
        // default_value = 1 for versionigEnabled because it was already standard before ilias5.4
        $this->webdavVersioningEnabled = $settings->get('webdav_versioning_enabled', '1') == '1';
        $this->webdavActionsVisible = $ilClientIniFile->readVariable('file_access', 'webdav_actions_visible') == '1';
        $ilClientIniFile->ERROR = false;
    }

    /**
     * Gets instructions for the usage of webfolders.
     *
     * The instructions consist of HTML text with placeholders.
     * See _getWebfolderInstructionsFor for a description of the supported
     * placeholders.
     *
     * @return String HTML text with placeholders.
     */
    public static function _getDefaultWebfolderInstructions()
    {
        global $lng;

        return $lng->txt('webfolder_instructions_text');
    }



}
