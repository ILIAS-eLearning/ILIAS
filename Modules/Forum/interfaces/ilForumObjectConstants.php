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
 ********************************************************************
 */

interface ilForumObjectConstants
{
    public const OBJ_TYPE = 'frm';

    public const UI_TAB_ID_INFO = 'info_short';
    public const UI_TAB_ID_SETTINGS = 'settings';
    public const UI_TAB_ID_MODERATORS = 'frm_moderators';
    public const UI_TAB_ID_THREADS = 'forums_threads';
    public const UI_TAB_ID_STATS = 'frm_statistics';
    public const UI_TAB_ID_EXPORT = 'export';
    public const UI_TAB_ID_PERMISSIONS = 'perm_settings';
    public const UI_SUB_TAB_ID_BASIC_SETTINGS = 'basic_settings';
    public const UI_SUB_TAB_ID_NOTIFICATIONS = 'notifications';
    public const UI_SUB_TAB_ID_NEWS = 'cont_news_settings';
    public const UI_SUB_TAB_ID_STYLE = 'cont_style';

    public const UI_CMD_COPAGE_DOWNLOAD_FILE = 'downloadFile';
    public const UI_CMD_COPAGE_DISPLAY_FULLSCREEN = 'displayMediaFullscreen';
    public const UI_CMD_COPAGE_DOWNLOAD_PARAGRAPH = 'download_paragraph';
}
