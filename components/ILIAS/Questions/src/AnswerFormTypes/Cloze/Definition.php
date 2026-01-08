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

namespace ILIAS\Questions\AnswerFormTypes\Cloze;

use ILIAS\Questions\AnswerForm\Definition as DefinitionInterface;
use ILIAS\Questions\AnswerForm\Capabilities\Capability;
use ILIAS\Questions\AnswerForm\Persistence;
use ILIAS\Questions\AnswerForm\TypeGenericProperties;
use ILIAS\Questions\AnswerFormTypes\Cloze\Properties\Factory as PropertiesFactory;
use ILIAS\Questions\AnswerFormTypes\Cloze\Properties\Properties;
use ILIAS\Questions\AnswerFormTypes\Cloze\Views\Edit;
use ILIAS\Questions\AnswerFormTypes\Cloze\Views\Participant;
use ILIAS\Questions\Persistence\Query;
use ILIAS\Language\Language;

class Definition implements DefinitionInterface
{
    /**
     * @param array<string, \ILIAS\Questions\AnswerForm\Capabilities\Capability> $available_capabilities
     */
    public function __construct(
        private readonly PropertiesFactory $properties_factory,
        private readonly Persistence $persistence,
        private readonly array $available_capabilities,
        private readonly Edit $edit_view,
        private readonly Participant $participant_view
    ) {
    }

    #[\Override]
    public function getLabel(
        Language $lng
    ): string {
        return $lng->txt('assClozeTest');
    }

    #[\Override]
    public function buildProperties(
        TypeGenericProperties $type_generic_data,
        ?Query $query
    ): Properties {
        return $this->properties_factory->fromData(
            $type_generic_data,
            $query
        );
    }

    #[\Override]
    public function getPersistence(): Persistence
    {
        return $this->persistence;
    }

    #[\Override]
    public function hasCapability(
        string $capability_class_name
    ): bool {
        return array_key_exists($capability_class_name, $this->available_capabilities);
    }

    #[\Override]
    public function getCapability(
        string $capability_class_name
    ): ?Capability {
        return $this->available_capabilities[$capability_class_name];
    }

    #[\Override]
    public function getEditView(): Edit
    {
        return $this->edit_view;
    }

    #[\Override]
    public function getParticipantView(): Participant
    {
        return $this->participant_view;
    }
}
