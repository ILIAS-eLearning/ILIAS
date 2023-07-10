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
 * This file contains constants for PHPStan analyis, see: https://phpstan.org/config-reference#constants
 */

// User an role specific constants
const SYSTEM_USER_ID = 6;
const ANONYMOUS_USER_ID = 13;
const ANONYMOUS_ROLE_ID = 2;
const SYSTEM_ROLE_ID = 14;

// Folder specific constants
const ROOT_FOLDER_ID = 1;
const USER_FOLDER_ID = 7;
const ROLE_FOLDER_ID = 8;
const SYSTEM_FOLDER_ID = 9;
const MAIL_SETTINGS_ID = 12;
const RECOVERY_FOLDER_ID = 15;

// Installation and environment specific constants
const IL_INST_ID = '0';
const CLIENT_ID = 'phpstan';
const CLIENT_NAME = 'PHPStan';
const ABSOLUTE_PATH = '/';
const ILIAS_DATA_DIR = './external_data';
const ILIAS_WEB_DIR = './data';
const CLIENT_DATA_DIR = './external_data';
const CLIENT_WEB_DIR = './data';

// Mail system specific constants
const MAILPATH = 'mail';
