<?php declare(strict_types = 1);

/**
 * @author Lukas Zehnder <lz@studer-raimann.ch>
 * @package WebDAV
 */
class ilObjWebDAV extends ilObject
{
    private bool $webdavEnabled;
    private bool $webdavVersioningEnabled;
    private bool $webdavActionsVisible;
    private bool $customWebfolderInstructionsEnabled;
    private string $customWebfolderInstructions;
    private bool $pwd_instruction;
    
    public function __construct(int $id = 0, bool $call_by_reference = true)
    {
        $this->type = "wbdv";
        parent::__construct($id, $call_by_reference);
    }
    
    public function getPresentationTitle() : string
    {
        return $this->lng->txt("webdav");
    }
    
    public function getLongDescription() : string
    {
        return $this->lng->txt("webdav_description");
    }
    
    public function setWebdavEnabled(bool $newValue) : void
    {
        $this->webdavEnabled = $newValue;
    }
    
    public function isWebdavEnabled() : bool
    {
        return $this->webdavEnabled;
    }

    public function setWebdavVersioningEnabled(bool $newValue) : void
    {
        $this->webdavVersioningEnabled = $newValue;
    }

    public function isWebdavVersioningEnabled() : bool
    {
        return $this->webdavVersioningEnabled;
    }
    
    public function create() : void
    {
        parent::create();
        $this->write();
    }
    
    public function update() : void
    {
        parent::update();
        $this->write();
    }

    private function write() : void
    {
        $settings = new ilSetting('webdav');

        $settings->set('webdav_enabled', $this->webdavEnabled ? '1' : '0');
        $settings->set('webdav_versioning_enabled', $this->webdavVersioningEnabled ? '1' : '0');
    }
    
    public function read() : void
    {
        parent::read();
        
        $settings = new ilSetting('webdav');
        $this->webdavEnabled = $settings->get('webdav_enabled', '0') == '1';
        // default_value = 1 for versionigEnabled because it was already standard before ilias5.4
        $this->webdavVersioningEnabled = $settings->get('webdav_versioning_enabled', '1') == '1';
    }
    
    public function retrieveWebDAVCommandArrayForActionMenu() : array
    {
        global $DIC;
        $ilUser = $DIC->user();

        $status = ilAuthUtils::supportsLocalPasswordValidation($ilUser->getAuthMode(true));
        $cmd = 'mount_webfolder';
        if ($status === ilAuthUtils::LOCAL_PWV_USER && strlen($ilUser->getPasswd() === 0)) {
            $cmd = 'showPasswordInstruction';
        }
        
        // Check if user has local password
        return ["permission" => "read", "cmd" => $cmd, "lang_var" => "mount_webfolder", "enable_anonymous" => "false"];
    }
}
