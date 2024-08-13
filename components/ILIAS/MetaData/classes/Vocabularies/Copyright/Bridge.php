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

namespace ILIAS\MetaData\Vocabularies\Copyright;

use ILIAS\MetaData\Vocabularies\VocabularyInterface;
use ILIAS\MetaData\Copyright\RepositoryInterface as CopyrightRepository;
use ILIAS\MetaData\Settings\SettingsInterface;
use ILIAS\MetaData\Copyright\Identifiers\HandlerInterface as IdentifierHandler;
use ILIAS\MetaData\Vocabularies\Dispatch\Presentation\LabelledValueInterface;
use ILIAS\MetaData\Vocabularies\Dispatch\Presentation\LabelledValue;
use ILIAS\MetaData\Vocabularies\Factory\FactoryInterface;
use ILIAS\MetaData\Vocabularies\Slots\Identifier as SlotIdentifier;

class Bridge implements BridgeInterface
{
    protected FactoryInterface $factory;
    protected SettingsInterface $settings;
    protected CopyrightRepository $copyright_repository;
    protected IdentifierHandler $identifier_handler;

    public function __construct(
        FactoryInterface $factory,
        SettingsInterface $settings,
        CopyrightRepository $copyright_repository,
        IdentifierHandler $identifier_handler
    ) {
        $this->factory = $factory;
        $this->settings = $settings;
        $this->copyright_repository = $copyright_repository;
        $this->identifier_handler = $identifier_handler;
    }

    public function vocabulary(SlotIdentifier $slot): ?VocabularyInterface
    {
        if (
            !$this->settings->isCopyrightSelectionActive() ||
            $slot !== SlotIdentifier::RIGHTS_DESCRIPTION
        ) {
            return null;
        }

        $values = [];
        foreach ($this->copyright_repository->getAllEntries() as $copyright) {
            $values[] = $this->identifier_handler->buildIdentifierFromEntryID($copyright->id());
        }
        if (empty($values)) {
            return null;
        }
        return $this->factory->copyright(...$values)->get();
    }

    /**
     * @return LabelledValueInterface[]
     */
    public function labelsForValues(
        SlotIdentifier $slot,
        string ...$values
    ): \Generator {
        if (
            !$this->settings->isCopyrightSelectionActive() ||
            $slot !== SlotIdentifier::RIGHTS_DESCRIPTION
        ) {
            return;
        }

        foreach ($this->copyright_repository->getAllEntries() as $copyright) {
            $identifier = $this->identifier_handler->buildIdentifierFromEntryID($copyright->id());
            if (!in_array($identifier, $values)) {
                continue;
            }
            yield new LabelledValue(
                $identifier,
                $copyright->title()
            );
        }
    }
}
