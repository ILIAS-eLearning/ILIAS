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

namespace ILIAS\ILIASObject\Definition;

/**
 * Boolean properties of an object type. The values correspond to the keys
 * used by ilObjectDefinition.
 */
enum ObjectTypeFlag: string
{
    case CHECKBOX = 'checkbox';
    case INHERIT = 'inherit';
    case DEVMODE = 'devmode';
    case ALLOW_LINK = 'allow_link';
    case ALLOW_COPY = 'allow_copy';
    case RBAC = 'rbac';
    case SYSTEM = 'system';
    case SIDEBLOCK = 'sideblock';
    case EXPORT = 'export';
    case REPOSITORY = 'repository';
    case WORKSPACE = 'workspace';
    case ADMINISTRATION = 'administration';
    case AMET = 'amet';
    case ORGUNIT_PERMISSIONS = 'orgunit_permissions';
    case LTI_PROVIDER = 'lti_provider';
    case OFFLINE_HANDLING = 'offline_handling';
}
