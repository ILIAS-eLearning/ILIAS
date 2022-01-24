<?php declare(strict_types=1);

/* Copyright (c) 2022 Thibeau Fuhrer <thibeau@sr.solutions> Extended GPL, see docs/LICENSE */

/**
 * @ilCtrl_isCalledBy ilUIAsyncDemoFileUploadHandlerGUI: ilUIPluginRouterGUI
 * @author            Thibeau Fuhrer <thibeau@sr.solutions>
 */
class ilUIAsyncDemoFileUploadHandlerGUI extends ilUIDemoFileUploadHandlerGUI
{
    public function getUploadURL() : string
    {
        return $this->ctrl->getLinkTargetByClass(
            [ilUIPluginRouterGUI::class, self::class],
            self::CMD_UPLOAD,
            null,
            true
        );
    }

    public function getExistingFileInfoURL() : string
    {
        return $this->ctrl->getLinkTargetByClass(
            [ilUIPluginRouterGUI::class, self::class],
            self::CMD_INFO,
            null,
            true
        );
    }

    public function getFileRemovalURL() : string
    {
        return $this->ctrl->getLinkTargetByClass(
            [ilUIPluginRouterGUI::class, self::class],
            self::CMD_REMOVE,
            null,
            true
        );
    }
}