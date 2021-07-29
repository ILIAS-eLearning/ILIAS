<?php
class ilObjFileAccessSettings extends ilObject
{

    /**
     * String property. Contains a list of file extensions separated by space.
     * Files with a matching extension are displayed inline in the browser.
     * Non-matching files are offered for download to the user.
     */
    private $inlineFileExtensions;
    /** Boolean property.
     *
     * If this variable is true, the filename of downloaded
     * files is the same as the filename of the uploaded file.
     *
     * If this variable is false, the filename of downloaded
     * files is the title of the file object.
     */
    private $downloadWithUploadedFilename;


    /**
     * Constructor
     *
     * @param integer    reference_id or object_id
     * @param boolean    treat the id as reference_id (true) or object_id (false)
     */
    public function __construct($a_id = 0, $a_call_by_reference = true)
    {
        $this->type = "facs";
        parent::__construct($a_id, $a_call_by_reference);
    }


    /**
     * Sets the inlineFileExtensions property.
     *
     * @param string    new value, a space separated list of filename extensions.
     *
     * @return    void
     */
    public function setInlineFileExtensions($newValue)
    {
        $this->inlineFileExtensions = $newValue;
    }


    /**
     * Gets the inlineFileExtensions property.
     *
     * @return    boolean    value
     */
    public function getInlineFileExtensions()
    {
        return $this->inlineFileExtensions;
    }


    /**
     * Sets the downloadWithUploadedFilename property.
     *
     * @param boolean
     *
     * @return    void
     */
    public function setDownloadWithUploadedFilename($newValue)
    {
        $this->downloadWithUploadedFilename = $newValue;
    }


    /**
     * Gets the downloadWithUploadedFilename property.
     *
     * @return    boolean    value
     */
    public function isDownloadWithUploadedFilename()
    {
        return $this->downloadWithUploadedFilename;
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
        $settings = new ilSetting('file_access');

        // Clear any old error messages
        $ilClientIniFile->error(null);

        if (!$ilClientIniFile->groupExists('file_access')) {
            $ilClientIniFile->addGroup('file_access');
        }
        $ilClientIniFile->setVariable('file_access', 'download_with_uploaded_filename', $this->downloadWithUploadedFilename ? '1' : '0');
        $ilClientIniFile->write();

        if ($ilClientIniFile->getError()) {
            global $DIC;
            $ilErr = $DIC['ilErr'];
            $ilErr->raiseError($ilClientIniFile->getError(), $ilErr->WARNING);
        }
        $settings->set('inline_file_extensions', $this->inlineFileExtensions);
    }


    /**
     * read object data from db into object
     */
    public function read()
    {
        parent::read();

        global $DIC;
        $settings = new ilSetting('file_access');
        $ilClientIniFile = $DIC['ilClientIniFile'];
        $this->downloadWithUploadedFilename = $ilClientIniFile->readVariable('file_access', 'download_with_uploaded_filename') == '1';
        $ilClientIniFile->ERROR = false;
        $this->inlineFileExtensions = $settings->get('inline_file_extensions', '');
    }


    /**
     * TODO: Check if needed and refactor
     *
     * Gets the maximum permitted upload filesize from php.ini in bytes.
     *
     * @return int Upload Max Filesize in bytes.
     */
    private function getUploadMaxFilesize()
    {
        $val = ini_get('upload_max_filesize');

        $val = trim($val);
        $last = strtolower($val[strlen($val) - 1]);
        switch ($last) {
            // The 'G' modifier is available since PHP 5.1.0
            case 'g':
                $val *= 1024;
                // no break
            case 'm':
                $val *= 1024;
                // no break
            case 'k':
                $val *= 1024;
        }

        return $val;
    }
} // END class.ilObjFileAccessSettings
