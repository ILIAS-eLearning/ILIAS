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

namespace ILIAS\MetaData\Vocabularies\Slots;

use ILIAS\MetaData\Paths\PathInterface;
use ILIAS\MetaData\Vocabularies\Slots\Conditions\ConditionInterface;
use ILIAS\MetaData\Paths\FactoryInterface as PathFactory;
use ILIAS\MetaData\Paths\Steps\StepToken;
use ILIAS\MetaData\Vocabularies\Slots\Conditions\Condition;
use ILIAS\MetaData\Elements\Data\Type as DataType;
use ILIAS\MetaData\Paths\Navigator\NavigatorFactoryInterface;
use ILIAS\MetaData\Elements\Structure\StructureSetInterface;

class Handler implements HandlerInterface
{
    protected PathFactory $path_factory;
    protected NavigatorFactoryInterface $navigator_factory;
    protected StructureSetInterface $structure;

    public function __construct(
        PathFactory $path_factory,
        NavigatorFactoryInterface $navigator_factory,
        StructureSetInterface $structure
    ) {
        $this->path_factory = $path_factory;
        $this->navigator_factory = $navigator_factory;
        $this->structure = $structure;
    }

    public function pathForSlot(Identifier $identifier): PathInterface
    {
        return match ($identifier) {
            Identifier::GENERAL_STRUCTURE => $this->buildPath('general', 'structure', 'value'),
            Identifier::GENERAL_AGGREGATION_LEVEL => $this->buildPath('general', 'aggregationLevel', 'value'),
            Identifier::GENERAL_COVERAGE => $this->buildPath('general', 'coverage', 'string'),
            Identifier::GENERAL_IDENTIFIER_CATALOG => $this->buildPath('general', 'identifier', 'catalog'),
            Identifier::LIFECYCLE_STATUS => $this->buildPath('lifeCycle', 'status', 'value'),
            Identifier::LIFECYCLE_CONTRIBUTE_ROLE => $this->buildPath('lifeCycle', 'contribute', 'role', 'value'),
            Identifier::LIFECYCLE_CONTRIBUTE_PUBLISHER => $this->buildPath('lifeCycle', 'contribute', 'entity'),
            Identifier::METAMETADATA_IDENTIFIER_CATALOG => $this->buildPath('metaMetadata', 'identifier', 'catalog'),
            Identifier::METAMETADATA_CONTRIBUTE_ROLE => $this->buildPath('metaMetadata', 'contribute', 'role', 'value'),
            Identifier::METAMETADATA_SCHEMA => $this->buildPath('metaMetadata', 'metadataSchema'),
            Identifier::TECHNICAL_REQUIREMENT_TYPE => $this->buildPath('technical', 'requirement', 'orComposite', 'type', 'value'),
            Identifier::TECHNICAL_REQUIREMENT_BROWSER, Identifier::TECHNICAL_REQUIREMENT_OS => $this->buildPath('technical', 'requirement', 'orComposite', 'name', 'value'),
            Identifier::TECHNICAL_OTHER_PLATFORM_REQUIREMENTS => $this->buildPath('technical', 'otherPlatformRequirements', 'string'),
            Identifier::TECHNICAL_FORMAT => $this->buildPath('technical', 'format'),
            Identifier::EDUCATIONAL_INTERACTIVITY_TYPE => $this->buildPath('educational', 'interactivityType', 'value'),
            Identifier::EDUCATIONAL_LEARNING_RESOURCE_TYPE => $this->buildPath('educational', 'learningResourceType', 'value'),
            Identifier::EDUCATIONAL_INTERACTIVITY_LEVEL => $this->buildPath('educational', 'interactivityLevel', 'value'),
            Identifier::EDUCATIONAL_SEMANTIC_DENSITY => $this->buildPath('educational', 'semanticDensity', 'value'),
            Identifier::EDCUCATIONAL_INTENDED_END_USER_ROLE => $this->buildPath('educational', 'intendedEndUserRole', 'value'),
            Identifier::EDUCATIONAL_CONTEXT => $this->buildPath('educational', 'context', 'value'),
            Identifier::EDUCATIONAL_DIFFICULTY => $this->buildPath('educational', 'difficulty', 'value'),
            Identifier::EDUCATIONAL_TYPICAL_AGE_RANGE => $this->buildPath('educational', 'typicalAgeRange', 'string'),
            Identifier::RIGHTS_COST => $this->buildPath('rights', 'cost', 'value'),
            Identifier::RIGHTS_CP_AND_OTHER_RESTRICTIONS => $this->buildPath('rights', 'copyrightAndOtherRestrictions', 'value'),
            Identifier::RIGHTS_DESCRIPTION => $this->buildPath('rights', 'description', 'string'),
            Identifier::RELATION_KIND => $this->buildPath('relation', 'kind', 'value'),
            Identifier::RELATION_RESOURCE_IDENTIFIER_CATALOG => $this->buildPath('relation', 'resource', 'identifier', 'catalog'),
            Identifier::CLASSIFICATION_PURPOSE => $this->buildPath('classification', 'purpose', 'value'),
            Identifier::CLASSIFICATION_KEYWORD => $this->buildPath('classification', 'keyword', 'string'),
            Identifier::CLASSIFICATION_TAXPATH_SOURCE => $this->buildPath('classification', 'taxonPath', 'source', 'string'),
            Identifier::CLASSIFICATION_TAXON_ENTRY => $this->buildPath('classification', 'taxonPath', 'taxon', 'entry', 'string'),
            Identifier::NULL => $this->buildPath()
        };
    }

    public function isSlotConditional(Identifier $identifier): bool
    {
        return !is_null($this->conditionForSlot($identifier));
    }

    public function conditionForSlot(Identifier $identifier): ?ConditionInterface
    {
        return match ($identifier) {
            Identifier::LIFECYCLE_CONTRIBUTE_PUBLISHER => $this->buildCondition(
                'publisher',
                StepToken::SUPER,
                'role',
                'value'
            ),
            Identifier::TECHNICAL_REQUIREMENT_BROWSER => $this->buildCondition(
                'browser',
                StepToken::SUPER,
                StepToken::SUPER,
                'type',
                'value'
            ),
            Identifier::TECHNICAL_REQUIREMENT_OS => $this->buildCondition(
                'operating system',
                StepToken::SUPER,
                StepToken::SUPER,
                'type',
                'value'
            ),
            default => null
        };
    }

    public function identiferFromPathAndCondition(
        PathInterface $path_to_element,
        ?PathInterface $path_to_condition,
        ?string $condition_value
    ): Identifier {
        foreach (Identifier::cases() as $identifier) {
            if ($this->pathForSlot($identifier)->toString() !== $path_to_element->toString()) {
                continue;
            }

            $condition = $this->conditionForSlot($identifier);
            if (
                $condition?->value() !== $condition_value ||
                $condition?->path()?->toString() !== $path_to_condition?->toString()
            ) {
                continue;
            }

            return $identifier;
        }
        return Identifier::NULL;
    }

    public function allSlotsForPath(PathInterface $path_to_element): \Generator
    {
        foreach (Identifier::cases() as $identifier) {
            if ($this->pathForSlot($identifier)->toString() === $path_to_element->toString()) {
                yield $identifier;
            }
        }
    }

    public function doesSlotExist(
        PathInterface $path_to_element,
        ?PathInterface $path_to_condition,
        ?string $condition_value
    ): bool {
        $identifier = $this->identiferFromPathAndCondition(
            $path_to_element,
            $path_to_condition,
            $condition_value
        );
        return $identifier !== Identifier::NULL;
    }

    protected function buildPath(string ...$steps): PathInterface
    {
        $builder = $this->path_factory->custom();
        foreach ($steps as $step) {
            $builder = $builder->withNextStep($step);
        }
        return $builder->get();
    }

    protected function buildCondition(
        string $condition_value,
        string|StepToken ...$steps_to_condition,
    ): ConditionInterface {
        $builder = $this->path_factory->custom();
        foreach ($steps_to_condition as $step) {
            if ($step === StepToken::SUPER) {
                $builder = $builder->withNextStepToSuperElement();
                continue;
            }
            $builder = $builder->withNextStep($step);
        }
        $path = $builder->withRelative(true)->get();

        return new Condition(
            $condition_value,
            $path
        );
    }

    public function dataTypeForSlot(Identifier $identifier): DataType
    {
        return $this->navigator_factory->structureNavigator(
            $this->pathForSlot($identifier),
            $this->structure->getRoot()
        )->elementAtFinalStep()->getDefinition()->dataType();
    }
}
