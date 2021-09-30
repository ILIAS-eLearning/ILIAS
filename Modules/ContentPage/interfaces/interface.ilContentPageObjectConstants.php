<?php declare(strict_types=1);
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Interface ilContentPageObjectConstants
 */
interface ilContentPageObjectConstants
{
    public const OBJ_TYPE = 'copa';

    public const UI_CMD_VIEW = 'view';
    public const UI_CMD_EDIT = 'edit';
    public const UI_CMD_UPDATE = 'update';

    public const UI_CMD_COPAGE_DOWNLOAD_FILE = 'downloadFile';
    public const UI_CMD_COPAGE_DISPLAY_FULLSCREEN = 'displayMediaFullscreen';
    public const UI_CMD_COPAGE_DOWNLOAD_PARAGRAPH = 'download_paragraph';

    public const UI_TAB_ID_CONTENT = 'content';
    public const UI_TAB_ID_INFO = 'info_short';
    public const UI_TAB_ID_SETTINGS = 'settings';
    public const UI_TAB_ID_ICON = 'icon';
    public const UI_TAB_ID_STYLE = 'style';
    public const UI_TAB_ID_I18N = 'i18n';
    public const UI_TAB_ID_LP = 'learning_progress';
    public const UI_TAB_ID_EXPORT = 'export';
    public const UI_TAB_ID_PERMISSIONS = 'perm_settings';
}
