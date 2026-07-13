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

declare(strict_types=1);

/**
 * @author  Lukas Zehnder <lz@studer-raimann.ch>
 * @package WebDAV
 */
class ilObjWebDAV extends ilObject
{
    private bool $webdavEnabled;

    public function __construct(int $id = 0, bool $call_by_reference = true)
    {
        $this->type = "wbdv";
        parent::__construct($id, $call_by_reference);
    }

    #[\Override]
    public function getPresentationTitle(): string
    {
        return $this->lng->txt("webdav");
    }

    #[\Override]
    public function getLongDescription(): string
    {
        return $this->lng->txt("webdav_description");
    }

    public function setWebdavEnabled(bool $newValue): void
    {
        $this->webdavEnabled = $newValue;
    }

    public function isWebdavEnabled(): bool
    {
        return $this->webdavEnabled;
    }

    #[\Override]
    public function create(): int
    {
        $id = parent::create();
        $this->write();
        return $id;
    }

    #[\Override]
    public function update(): bool
    {
        parent::update();
        $this->write();
        return true;
    }

    private function write(): void
    {
        $settings = new ilSetting('webdav');

        $settings->set('webdav_enabled', $this->webdavEnabled ? '1' : '0');
    }

    #[\Override]
    public function read(): void
    {
        parent::read();

        $settings = new ilSetting('webdav');
        $this->webdavEnabled = $settings->get('webdav_enabled', '0') == '1';
    }

    /**
     *
     * @return string[]
     */
    public function retrieveWebDAVCommandArrayForActionMenu(): array
    {
        global $DIC;
        $ilUser = $DIC->user();

        $status = ilAuthUtils::supportsLocalPasswordValidation($ilUser->getAuthMode(true));
        $cmd = 'mount_webfolder';
        if ($status === ilAuthUtils::LOCAL_PWV_USER && (string) $ilUser->getPasswd() === '') {
            $cmd = 'showPasswordInstruction';
        }

        // Check if user has local password
        return ["permission" => "read", "cmd" => $cmd, "lang_var" => "mount_webfolder", "enable_anonymous" => "false"];
    }
}
