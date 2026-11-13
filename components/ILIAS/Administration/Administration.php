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

namespace ILIAS;

use ILIAS\Language\ComponentTranslation\LanguageFileDirectory;
use ILIAS\Language\ComponentTranslation\ComponentLanguageFileDirectory;
use ILIAS\ILIASObject\Definition\ObjectType;
use ILIAS\Administration\Node\AdministrationNodes;
use ILIAS\Administration\Node\AdministrationNode;
use ILIAS\Administration\Settings\ExternalSettings;
use ILIAS\Administration\Settings\ExternalSettingsCollection;

class Administration implements Component\Component
{
    public function init(
        array | \ArrayAccess &$define,
        array | \ArrayAccess &$implement,
        array | \ArrayAccess &$use,
        array | \ArrayAccess &$contribute,
        array | \ArrayAccess &$seek,
        array | \ArrayAccess &$provide,
        array | \ArrayAccess &$pull,
        array | \ArrayAccess &$internal,
    ): void {
        $provide[AdministrationNodes::class] = static fn(): AdministrationNodes =>
            new AdministrationNodes($seek[AdministrationNode::class]);
        $provide[ExternalSettingsCollection::class] = static fn(): ExternalSettingsCollection =>
            new ExternalSettingsCollection($seek[ExternalSettings::class]);

        $contribute[\ILIAS\Setup\Agent::class] = static fn() =>
            new \ILIAS\Administration\Setup\ilAdministrationSetupAgent(
                $pull[\ILIAS\Refinery\Factory::class],
                $seek[AdministrationNode::class],
                $seek[ObjectType::class]
            );
        $contribute[\ILIAS\Setup\Agent::class] = static fn() =>
            new \ilSystemFolderSetupAgent(
                $pull[\ILIAS\Refinery\Factory::class]
            );

        $contribute[LanguageFileDirectory::class] = fn() => new ComponentLanguageFileDirectory(
            $this,
            'administration'
        );
        $contribute[LanguageFileDirectory::class] = fn() => new ComponentLanguageFileDirectory(
            $this,
            'adm'
        );

        $contribute[ObjectType::class] = static fn(): ObjectType => new Administration\ObjectType\SystemFolderObjectType();
        $contribute[ObjectType::class] = static fn(): ObjectType => new Administration\ObjectType\GeneralSettingsObjectType();
        $contribute[ObjectType::class] = static fn(): ObjectType => new Administration\ObjectType\ServerInfoObjectType();
        $contribute[ObjectType::class] = static fn(): ObjectType => new Administration\ObjectType\RecoveryFolderObjectType();

        $contribute[AdministrationNode::class] = static fn(): AdministrationNode => new Administration\AdministrationNode\GeneralSettingsAdministrationNode();
        $contribute[AdministrationNode::class] = static fn(): AdministrationNode => new Administration\AdministrationNode\ServerInfoAdministrationNode();
        $contribute[AdministrationNode::class] = static fn(): AdministrationNode => new Administration\AdministrationNode\RecoveryFolderAdministrationNode();
    }
}
