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
use ILIAS\ILIASObject\Definition\ObjectGroup;
use ILIAS\ILIASObject\Definition\ObjectType;
use ILIAS\Administration\Node\AdministrationNode;
use ILIAS\Administration\Settings\ExternalSettings;

class Group implements Component\Component
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
        $contribute[Component\Resource\PublicAsset::class] = fn() =>
            new Component\Resource\ComponentJS($this, 'GroupUserActions.js');
        $contribute[LanguageFileDirectory::class] = fn() => new ComponentLanguageFileDirectory(
            $this,
            'grp'
        );

        $contribute[ObjectGroup::class] = static fn(): ObjectGroup => new Group\ObjectType\GroupsObjectGroup();
        $contribute[ObjectType::class] = static fn(): ObjectType => new Group\ObjectType\GroupObjectType();
        $contribute[ObjectType::class] = static fn(): ObjectType => new Group\ObjectType\GroupAdministrationObjectType();

        $contribute[AdministrationNode::class] = static fn(): AdministrationNode => new Group\AdministrationNode\GroupAdministrationAdministrationNode();
        $contribute[ExternalSettings::class] = static fn(): ExternalSettings => new Group\AdministrationNode\GroupAdministrationExternalSettings();

    }
}
