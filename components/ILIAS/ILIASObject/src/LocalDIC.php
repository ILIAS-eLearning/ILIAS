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

use ILIAS\ILIASObject\Properties\ObjectTypeSpecificProperties\Factory as ObjectTypeSpecificPropertiesFactory;
use ILIAS\ILIASObject\Properties\ObjectTypeSpecificProperties\ArtifactObjective;
use ILIAS\ILIASObject\Properties\AdditionalProperties\Repository as AdditionalPropertiesRepository;
use ILIAS\ILIASObject\Properties\AdditionalProperties\LegacyRepository as AdditionalPropertiesLegacyRepository;
use ILIAS\ILIASObject\Properties\CoreProperties\Repository as CorePropertiesRepository;
use ILIAS\ILIASObject\Properties\CoreProperties\CachedRepository as CorePropertiesCachedRepository;
use ILIAS\ILIASObject\Properties\CoreProperties\TileImage\Stakeholder;
use ILIAS\ILIASObject\Properties\CoreProperties\TileImage\FlavourDefinition;
use ILIAS\ILIASObject\Properties\ObjectReferenceProperties\CachedRepository as ObjectReferencePropertiesRepository;
use ILIAS\ILIASObject\Properties\ObjectReferenceProperties\AvailabilityPeriod\CachedRepository as AvailabilityPeriodRepository;
use ILIAS\ILIASObject\Properties\Agregator;
use ILIAS\ILIASObject\Properties\MultiPropertiesManipulator;
use ILIAS\ILIASObject\Properties\Translations\CachedRepository as TranslationsRepository;
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

        $this['properties.agregator'] = fn($c): Agregator => new Agregator(
            $c['properties.core.repository'],
            $c['properties.additional.repository'],
            $c['properties.translations.repository'],
            $c['properties.object_type_specific.factory'],
            $DIC['learning_object_metadata']
        );

        $this['properties.core.repository'] = fn($c): CorePropertiesRepository
            => new CorePropertiesCachedRepository(
                $DIC['ilDB'],
                $DIC['objDefinition'],
                $DIC['resource_storage'],
                $c['properties.additional.tile_image.stackholder'],
                new FlavourDefinition(),
                $c['properties.object_type_specific.factory']
            );

        $this['properties.multi_manipulator'] = fn($c): MultiPropertiesManipulator
            => new MultiPropertiesManipulator(
                $c['properties.object_reference.repositoy'],
                $c['properties.agregator'],
                $DIC['lng'],
                $DIC['ilCtrl'],
                $DIC['ilUser'],
                $DIC['ui.factory'],
                $DIC['tpl'],
                $DIC['refinery']
            );

        $this['properties.additional.repository'] = fn($c): AdditionalPropertiesRepository
            => new AdditionalPropertiesLegacyRepository(
                $DIC['object.customicons.factory'],
                $c['properties.object_type_specific.factory']
            );

        $this['properties.additional.tile_image.stackholder'] = static fn($c): Stakeholder
            => new Stakeholder();

        $this['properties.additional.tile_image.flavour'] = static fn($c): FlavourDefinition
            => new FlavourDefinition();

        $this['properties.object_type_specific.factory'] = fn($c): ObjectTypeSpecificPropertiesFactory
            => new ObjectTypeSpecificPropertiesFactory(
                is_readable(ArtifactObjective::PATH()) ?
                    include ArtifactObjective::PATH()
                    : [],
                $DIC['ilDB']
            );

        $this['properties.object_reference.repositoy'] = fn($c): ObjectReferencePropertiesRepository
            => new ObjectReferencePropertiesRepository(
                $c['properties.object_reference.availability_period.repository'],
                $DIC['ilDB']
            );

        $this['properties.object_reference.availability_period.repository'] = fn($c): AvailabilityPeriodRepository
            => new AvailabilityPeriodRepository(
                $DIC['ilDB'],
                $DIC['tree']
            );
        $this['properties.translations.repository'] = fn($c): TranslationsRepository
            => new TranslationsRepository(
                $DIC['ilDB']
            );
    }
}
