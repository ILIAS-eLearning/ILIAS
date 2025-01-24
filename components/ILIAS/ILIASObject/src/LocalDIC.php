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

namespace ILIAS\ILIASObject;

use ILIAS\Object\Properties\ObjectTypeSpecificProperties\Factory as ObjectTypeSpecificPropertiesFactory;
use ILIAS\Object\Properties\ObjectTypeSpecificProperties\ilObjectTypeSpecificPropertiesArtifactObjective;
use ILIAS\Object\Properties\MultiObjectPropertiesManipulator;
use ILIAS\Object\Properties\CoreProperties\TileImage\ilObjectTileImageStakeholder;
use ILIAS\Object\Properties\CoreProperties\TileImage\ilObjectTileImageFlavourDefinition;
use ILIAS\Object\Properties\ObjectReferenceProperties\ObjectReferencePropertiesCachedRepository;
use ILIAS\Object\Properties\ObjectReferenceProperties\ObjectAvailabilityPeriodPropertiesCachedRepository;
use ILIAS\ILIASObject\Translations\DatabaseRepository;
use Pimple\Container as PimpleContainer;
use ILIAS\DI\Container as ILIASContainer;

class LocalDIC extends PimpleContainer
{
    private static ?LocalDIC $dic = null;

    public static function dic(): self
    {
        if (self::$dic === null) {
            global $DIC;
            self::$dic = new LocalDIC();
            self::$dic->init($DIC);
        }

        return self::$dic;
    }

    private function init(ILIASContainer $DIC): void
    {
        $this['settings.common'] = fn($c): \ilObjectCommonSettings => new \ilObjectCommonSettings(
            $DIC['lng'],
            $DIC['upload'],
            $DIC['resource_storage'],
            $DIC['http'],
            $c['properties.additional.tile_image.stackholder'],
            $c['properties.additional.tile_image.flavour']
        );

        $this['properties.agregator'] = fn($c): \ilObjectPropertiesAgregator => new \ilObjectPropertiesAgregator(
            $c['properties.core.repository'],
            $c['properties.additional.repository'],
            $c['properties.object_type_specific.factory'],
            $DIC['learning_object_metadata']
        );

        $this['properties.core.repository'] = fn($c): \ilObjectCorePropertiesRepository
            => new \ilObjectCorePropertiesCachedRepository(
                $DIC['ilDB'],
                $DIC['objDefinition'],
                $DIC['resource_storage'],
                $c['properties.additional.tile_image.stackholder'],
                new ilObjectTileImageFlavourDefinition(),
                $c['properties.object_type_specific.factory']
            );

        $this['properties.multi_manipulator'] = fn($c): MultiObjectPropertiesManipulator
            => new MultiObjectPropertiesManipulator(
                $c['properties.object_reference.repositoy'],
                $c['properties.agregator'],
                $DIC['lng'],
                $DIC['ilCtrl'],
                $DIC['ilUser'],
                $DIC['ui.factory'],
                $DIC['tpl'],
                $DIC['refinery']
            );

        $this['properties.additional.repository'] = fn($c): \ilObjectAdditionalPropertiesRepository
            => new \ilObjectAdditionalPropertiesLegacyRepository(
                $DIC['object.customicons.factory'],
                $c['properties.object_type_specific.factory']
            );

        $this['properties.additional.tile_image.stackholder'] = static fn($c): ilObjectTileImageStakeholder
            => new ilObjectTileImageStakeholder();

        $this['properties.additional.tile_image.flavour'] = static fn($c): ilObjectTileImageFlavourDefinition
            => new ilObjectTileImageFlavourDefinition();

        $this['properties.object_type_specific.factory'] = fn($c): ObjectTypeSpecificPropertiesFactory
            => new ObjectTypeSpecificPropertiesFactory(
                is_readable(ilObjectTypeSpecificPropertiesArtifactObjective::PATH()) ?
                    include ilObjectTypeSpecificPropertiesArtifactObjective::PATH()
                    : [],
                $DIC['ilDB']
            );

        $this['properties.object_reference.repositoy'] = fn($c): ObjectReferencePropertiesCachedRepository
            => new ObjectReferencePropertiesCachedRepository(
                $c['properties.object_reference.availability_period.repository'],
                $DIC['ilDB']
            );

        $this['properties.object_reference.availability_period.repository'] = fn($c): ObjectAvailabilityPeriodPropertiesCachedRepository
            => new ObjectAvailabilityPeriodPropertiesCachedRepository(
                $DIC['ilDB'],
                $DIC['tree']
            );
        $this['translations.repository'] = fn($c): DatabaseRepository
            => new DatabaseRepository(
                $DIC['ilDB']
            );
    }
}
