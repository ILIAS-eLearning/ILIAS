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

namespace ILIAS\Object;

use ILIAS\Object\Properties\ObjectTypeSpecificProperties\Factory as ObjectTypeSpecificPropertiesFactory;
use ILIAS\Object\Properties\ObjectTypeSpecificProperties\ilObjectTypeSpecificPropertiesArtifactObjective;
use ILIAS\Object\Properties\MultiObjectPropertiesManipulator;
use ILIAS\Object\Properties\CoreProperties\TileImage\ilObjectTileImageStakeholder;
use ILIAS\Object\Properties\CoreProperties\TileImage\ilObjectTileImageFlavourDefinition;
use ILIAS\Object\Properties\ObjectReferenceProperties\ObjectReferencePropertiesCachedRepository;
use ILIAS\Object\Properties\ObjectReferenceProperties\ObjectAvailabilityPeriodPropertiesCachedRepository;
use Pimple\Container as PimpleContainer;
use ILIAS\DI\Container as ILIASContainer;

class ilObjectDIC extends PimpleContainer
{
    public static ?ilObjectDIC $dic = null;

    public static function dic(): self
    {
        if (self::$dic === null) {
            global $DIC;
            self::$dic = new ilObjectDIC();
            self::$dic->init($DIC);
        }

        return self::$dic;
    }

    private function init(ILIASContainer $DIC): void
    {
        $this['common_settings'] = fn($c): \ilObjectCommonSettings => new \ilObjectCommonSettings(
            $DIC->language(),
            $DIC->upload(),
            $DIC->resourceStorage(),
            $DIC->http(),
            $c['tile_image_stackholder'],
            $c['tile_image_flavour'],
            $c['core_properties_repository'],
            $c['additional_properties_repository']
        );

        $this['object_properties_agregator'] = fn($c): \ilObjectPropertiesAgregator => new \ilObjectPropertiesAgregator(
            $c['core_properties_repository'],
            $c['additional_properties_repository'],
            $c['object_type_specific_properties_factory']
        );

        $this['core_properties_repository'] = fn($c): \ilObjectCorePropertiesRepository
            => new \ilObjectCorePropertiesCachedRepository(
                $DIC['ilDB'],
                $DIC->ui(),
                $DIC['resource_storage'],
                $c['tile_image_stackholder'],
                new ilObjectTileImageFlavourDefinition(),
                $c['object_type_specific_properties_factory']
            );

        $this['multi_object_properties_manipulator'] = fn($c): MultiObjectPropertiesManipulator
            => new MultiObjectPropertiesManipulator(
                $c['object_reference_repository'],
                $c['object_properties_agregator'],
                $DIC['lng'],
                $DIC['ilCtrl'],
                $DIC['ilUser'],
                $DIC['ui.factory'],
                $DIC['tpl'],
                $DIC['refinery']
            );

        $this['additional_properties_repository'] = fn($c): \ilObjectAdditionalPropertiesRepository
            => new \ilObjectAdditionalPropertiesLegacyRepository(
                $DIC['object.customicons.factory'],
                $c['object_type_specific_properties_factory']
            );

        $this['tile_image_stackholder'] = static fn($c): ilObjectTileImageStakeholder
            => new ilObjectTileImageStakeholder();

        $this['tile_image_flavour'] = static fn($c): ilObjectTileImageFlavourDefinition
            => new ilObjectTileImageFlavourDefinition();

        $this['object_type_specific_properties_factory'] = fn($c): ObjectTypeSpecificPropertiesFactory
            => new ObjectTypeSpecificPropertiesFactory(
                is_readable(ilObjectTypeSpecificPropertiesArtifactObjective::PATH) ?
                    include ilObjectTypeSpecificPropertiesArtifactObjective::PATH
                    : [],
                $DIC['ilDB']
            );

        $this['object_reference_repository'] = fn($c): ObjectReferencePropertiesCachedRepository
            => new ObjectReferencePropertiesCachedRepository(
                $c['availability_period_repository'],
                $DIC['ilDB']
            );

        $this['availability_period_repository'] = fn($c): ObjectAvailabilityPeriodPropertiesCachedRepository
            => new ObjectAvailabilityPeriodPropertiesCachedRepository(
                $DIC['ilDB'],
                $DIC['tree']
            );
    }
}
