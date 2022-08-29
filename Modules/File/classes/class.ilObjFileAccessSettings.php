<?php
/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

class ilObjFileAccessSettings extends ilObject
{
    /**
     * String property. Contains a list of file extensions separated by space.
     * Files with a matching extension are displayed inline in the browser.
     * Non-matching files are offered for download to the user.
     */
    private string $inline_file_extensions = '';
    /** Boolean property.
     *
     * If this variable is true, the filename of downloaded
     * files is the same as the filename of the uploaded file.
     *
     * If this variable is false, the filename of downloaded
     * files is the title of the file object.
     */
    private bool $download_with_uploaded_filename = false;
    private ilIniFile $ini_file;
    private ilSetting $settings;

    /**
     * Constructor
     */
    public function __construct(int $a_id = 0, bool $a_call_by_reference = true)
    {
        global $DIC;

        $this->type = "facs";
        parent::__construct($a_id, $a_call_by_reference);
        $this->ini_file = $DIC['ilClientIniFile'];
        $this->settings = new ilSetting('file_access');
    }


    /**
     * Sets the inlineFileExtensions property.
     *
     * @param string $value a space separated list of filename extensions.
     */
    public function setInlineFileExtensions(string $value): void
    {
        $this->inline_file_extensions = $value;
    }


    /**
     * Gets the inlineFileExtensions property.
     */
    public function getInlineFileExtensions(): string
    {
        return $this->inline_file_extensions;
    }


    /**
     * Sets the downloadWithUploadedFilename property.
     */
    public function setDownloadWithUploadedFilename(bool $value): void
    {
        $this->download_with_uploaded_filename = $value;
    }

    /**
     * Gets the downloadWithUploadedFilename property.
     */
    public function isDownloadWithUploadedFilename(): bool
    {
        return $this->download_with_uploaded_filename;
    }


    /**
     * create
     *
     * note: title, description and type should be set when this function is called
     *
     * @return    integer        object id
     */
    public function create(): int
    {
        $id = parent::create();
        $this->write();

        return $id;
    }


    /**
     * update object in db
     */
    public function update(): bool
    {
        parent::update();
        $this->write();

        return true;
    }


    /**
     * write object data into db
     */
    private function write(): void
    {
        if (!$this->ini_file->groupExists('file_access')) {
            $this->ini_file->addGroup('file_access');
        }
        $this->ini_file->setVariable(
            'file_access',
            'download_with_uploaded_filename',
            $this->download_with_uploaded_filename ? '1' : '0'
        );
        $this->ini_file->write();
        if ($this->ini_file->getError()) {
        }
        $this->settings->set('inline_file_extensions', $this->inline_file_extensions);
    }


    /**
     * read object data from db into object
     */
    public function read(): void
    {
        parent::read();

        $this->download_with_uploaded_filename = $this->ini_file->readVariable(
            'file_access',
            'download_with_uploaded_filename'
        ) === '1';

        $this->inline_file_extensions = $this->settings->get('inline_file_extensions', '');
    }
}
