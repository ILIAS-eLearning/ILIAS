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
        $settings = new ilSetting('webdav');

        $settings->set('webdav_enabled', $this->webdavEnabled ? '1' : '0');
        $settings->set('webdav_versioning_enabled', $this->webdavVersioningEnabled ? '1' : '0');
    }

    /**
     * read object data from db into object
     */
    public function read()
    {
        parent::read();

        global $DIC;
        $settings = new ilSetting('webdav');
        $this->webdavEnabled = $settings->get('webdav_enabled', '0') == '1';
        // default_value = 1 for versionigEnabled because it was already standard before ilias5.4
        $this->webdavVersioningEnabled = $settings->get('webdav_versioning_enabled', '1') == '1';
    }
}
