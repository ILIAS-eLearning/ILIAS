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

namespace ILIAS\MetaData\Editor\Http;

enum Command: string
{
    case INDEX = 'listSection';
    case SHOW_DIGEST = 'listQuickEdit';
    case UPDATE_DIGEST = 'updateQuickEdit';
    case SHOW_FULL = 'fullEditor';
    case CREATE_FULL = 'fullEditorCreate';
    case UPDATE_FULL = 'fullEditorUpdate';
    case DELETE_FULL = 'fullEditorDelete';
    case DEBUG = 'debug';
}
