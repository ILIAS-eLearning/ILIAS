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

interface ilSamlCommands
{
    public const CMD_LIST_IDPS = 'listIdps';
    public const CMD_TABLE_ACTIONS = 'handleTableActions';
    public const CMD_SHOW_NEW_IDP_FORM = 'showNewIdpForm';
    public const CMD_SAVE_NEW_IDP = 'saveNewIdp';
    public const CMD_DELETE_IDP = 'deleteIdp';
    public const CMD_SAVE_SETTINGS = 'saveSettings';
    public const CMD_SHOW_SETTINGS = 'showSettings';
    public const CMD_SHOW_IDP_SETTINGS = 'showIdpSettings';
    public const CMD_SAVE_IDP_SETTINGS = 'saveIdpSettings';
    public const CMD_SAVE_USER_ATTRIBUTE_MAPPING = 'saveUserAttributeMapping';
    public const CMD_SHOW_USER_ATTRIBUTE_MAPPING_FORM = 'showUserAttributeMappingForm';

    public const TABLE_ACTION_CONFIRM_DELETE_IDP = 'confirmDeleteIdp';
    public const TABLE_ACTION_DEACTIVATE_IDP = 'deactivateIdp';
    public const TABLE_ACTION_ACTIVATE_IDP = 'activateIdp';
    public const TABLE_ACTION_SHOW_IDP_SETTINGS = 'showIdpSettings';

    /** @var list<string> */
    public const GLOBAL_COMMANDS = [
        self::CMD_LIST_IDPS,
        self::CMD_SHOW_SETTINGS,
        self::CMD_SAVE_SETTINGS,
        self::CMD_SHOW_NEW_IDP_FORM, self::CMD_SAVE_NEW_IDP,
    ];

    /** @var list<string> */
    public const GLOBAL_ENTITY_COMMANDS = [
        self::CMD_DELETE_IDP,
    ];

    /** @var list<string> */
    public const GLOBAL_ENTITY_TABLE_ACTIONS = [
        self::TABLE_ACTION_CONFIRM_DELETE_IDP,
        self::TABLE_ACTION_ACTIVATE_IDP,
        self::TABLE_ACTION_DEACTIVATE_IDP,
    ];
}
