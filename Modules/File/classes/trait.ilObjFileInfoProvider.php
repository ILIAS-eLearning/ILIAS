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

use ILIAS\Modules\File\Settings\General;
use ILIAS\Data\DataSize;

/**
 * @author Lukas Zehnder <lukas@sr.solutions>
 */
trait ilObjFileInfoProvider
{
    /**
     * @throws ilFileException
     */
    public function getFileInfoForUsers(): array
    {
        // page count
        $page_count = $this->getFileObj()->getPageCount();
        $page_count = ($page_count !== 0) ? $page_count : null;
        // preview
        $preview_renderer = new ilObjFilePreviewRendererGUI($this->getFileObj()->getId());
        $preview = null;
        if ($preview_renderer->has() && !$this->getCtrl()->isAsynch()) {
            $preview = $preview_renderer->getRenderedTriggerComponents(true);
        }

        $data_size = new DataSize(
            $this->getFileObj()->getFileSize(),
            DataSize::KB
        );

        return [
            $this->getLanguage()->txt("filename") => $this->getFileObj()->getFileName(),
            $this->getLanguage()->txt("type") => $this->getFileObj()->getFileExtension(),
            $this->getLanguage()->txt("size") => (string) $data_size,
            $this->getLanguage()->txt("page_count") => $page_count,
            $this->getLanguage()->txt("preview") => $preview
        ];
    }

    /**
     * @throws ilWACException
     * @throws ilDateTimeException
     * @throws ilFileException
     * @throws ilTemplateException
     */
    public function getFileInfoForAll(): array
    {
        // version number
        $version_nr = $this->getFileObj()->getVersion();
        // version date
        $version = $this->getFileObj()->getVersions([$version_nr]);
        $version = end($version);
        $version_date = null;
        if ($version instanceof ilObjFileVersion) {
            $version_date = (new ilDateTime($version->getDate(), IL_CAL_DATETIME))->get(IL_CAL_DATETIME);
        }
        // version uploader
        $versions = $this->getFileObj()->getVersions();
        $versions = array_shift($versions);
        $version_uploader = $versions["user_id"] ?? -1; // unknown uploader
        // download link
        $download_link_tpl = null;
        if ($this->getAccessHandler()->checkAccessOfUser(
            $this->getUser()->getId(),
            "read",
            "sendfile",
            $this->getFileObj()->getRefId()
        )) {
            $download_link_tpl = new ilTemplate("tpl.download_link.html", true, true, "Modules/File");
            $download_link_tpl->setVariable("LINK", ilObjFileAccess::_getPermanentDownloadLink($this->getNodeID()));
        }

        return [
            $this->getLanguage()->txt("version") => $version_nr,
            $this->getLanguage()->txt("version_uploaded") => $version_date,
            $this->getLanguage()->txt("file_uploaded_by") => ilUserUtil::getNamePresentation($version_uploader),
            $this->getLanguage()->txt("download_link") => $download_link_tpl->get()
        ];
    }

    /**
     * @throws ilFileException
     */
    public function getFileInfoForAuthorsAndAdmins(): array
    {
        $amount_of_downloads = null;
        if ($this->getGeneralSettings()->isShowAmountOfDownloads()) {
            sprintf(
                $this->getLanguage()->txt("amount_of_downloads_since"),
                $this->getFileObj()->getAmountOfDownloads(),
                $this->getFileObj()->getCreateDate(),
            );
        }

        return [
            $this->getLanguage()->txt("mime_type") => $this->getFileObj()->getFileType(),
            $this->getLanguage()->txt("resource_id") => $this->getFileObj()->getResourceId(),
            $this->getLanguage()->txt("storage_id") => $this->getFileObj()->getStorageID(),
            $this->getLanguage()->txt("amount_of_downloads") => $amount_of_downloads
        ];
    }

    /**
     * @throws ilWACException
     * @throws ilDateTimeException
     * @throws ilFileException
     * @throws ilTemplateException
     */
    public function getAllFileInfoForCurrentUser(): array
    {
        $file_info = [];
        if ($this->getAccessHandler()->checkAccessOfUser(
            $this->getUser()->getId(),
            "read",
            "",
            $this->getFileObj()->getRefId()
        )) {
            $file_info['for_users'] = $this->getFileInfoForUsers();
            $file_info['for_all'] = $this->getFileInfoForAll();
        }
        if ($this->getAccessHandler()->checkAccessOfUser(
            $this->getUser()->getId(),
            "write",
            "",
            $this->getFileObj()->getRefId()
        )) {
            $file_info['for_authors_and_admins'] = $this->getFileInfoForAuthorsAndAdmins();
        }

        return $file_info;
    }

    abstract protected function getAccessHandler(); // must be compatible with ilObject2GUI::getAccessHandler()

    abstract protected function getCtrl(): ilCtrl;

    abstract protected function getFileObj(): ?ilObjFile;

    abstract protected function getFileStakeholder(): ilObjFileStakeholder;

    abstract protected function getGeneralSettings(): General;

    abstract protected function getLanguage(): ilLanguage;

    abstract protected function getNodeID(): int;

    abstract protected function getUser(): ilObjUser;
}
