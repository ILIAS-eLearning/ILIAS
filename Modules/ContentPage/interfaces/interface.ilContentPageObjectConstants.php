<?php declare(strict_types=1);

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
    public const UI_TAB_ID_MD = 'meta_data';
}
