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

namespace ILIAS\MetaData\Editor\Digest;

use ILIAS\MetaData\Elements\SetInterface;
use ILIAS\MetaData\Editor\Http\RequestForFormInterface;
use ILIAS\MetaData\Paths\FactoryInterface as PathFactory;
use ILIAS\MetaData\Paths\Navigator\NavigatorFactoryInterface;
use ILIAS\MetaData\Editor\Manipulator\ManipulatorInterface;
use ILIAS\MetaData\Vocabularies\Factory\Factory;
use ILIAS\MetaData\Vocabularies\Factory\FactoryInterface;
use ILIAS\MetaData\Editor\Vocabulary\AdapterInterface as VocabularyAdapter;
use ILIAS\MetaData\Vocabularies\Slots\Identifier as SlotIdentifier;

class ManipulatorAdapter
{
    protected ContentAssembler $content_assembler;
    protected CopyrightHandler $copyright_handler;
    protected PathCollection $path_collection;
    protected ManipulatorInterface $manipulator;
    protected PathFactory $path_factory;
    protected NavigatorFactoryInterface $navigator_factory;
    protected VocabularyAdapter $vocabulary_adapter;

    public function __construct(
        ContentAssembler $content_assembler,
        CopyrightHandler $copyright_handler,
        PathCollection $path_collection,
        ManipulatorInterface $manipulator,
        PathFactory $path_factory,
        NavigatorFactoryInterface $navigator_factory,
        VocabularyAdapter $vocabulary_adapter
    ) {
        $this->content_assembler = $content_assembler;
        $this->copyright_handler = $copyright_handler;
        $this->path_collection = $path_collection;
        $this->manipulator = $manipulator;
        $this->path_factory = $path_factory;
        $this->navigator_factory = $navigator_factory;
        $this->vocabulary_adapter = $vocabulary_adapter;
    }

    public function update(
        SetInterface $set,
        RequestForFormInterface $request
    ): bool {
        $form = null;
        foreach ($this->content_assembler->get($set, $request) as $type => $entity) {
            if ($type === ContentType::FORM) {
                $form = $entity;
                break;
            }
        }

        if (!$form?->getData()) {
            return false;
        }
        $data = $form->getData();

        $set = $this->prepareGeneral($set, $data[ContentAssembler::GENERAL]);
        $set = $this->prepareClassification($set, $data[ContentAssembler::CLASSIFICATION]);
        $set = $this->prepareContributors($set, $data[ContentAssembler::AUTHORS]);
        if (
            $this->copyright_handler->isCPSelectionActive() &&
            isset($data[ContentAssembler::RIGHTS])
        ) {
            $set = $this->prepareRights($set, $data[ContentAssembler::RIGHTS][0]);
        }

        $this->manipulator->execute($set);
        return true;
    }

    protected function prepareGeneral(
        SetInterface $set,
        array $data
    ): SetInterface {
        foreach ($data as $post_key => $value) {
            if ($post_key === ContentAssembler::KEYWORDS) {
                $set = $this->prepareKeywords($set, $value);
                continue;
            }
            $path = $this->path_factory->fromString($post_key);
            if ($value === null || $value === '') {
                $set = $this->manipulator->prepareDelete($set, $path);
                continue;
            }
            $set = $this->manipulator->prepareCreateOrUpdate($set, $path, $value);
        }
        return $set;
    }

    /**
     * @param string[]  $values
     */
    protected function prepareKeywords(
        SetInterface $set,
        array $values
    ): SetInterface {
        $keyword_els = $this->navigator_factory->navigator(
            $path = $this->path_collection->keywords(),
            $set->getRoot()
        )->elementsAtFinalStep();
        $number_of_keywords = count(iterator_to_array($keyword_els));

        if (!empty($values)) {
            $set = $this->manipulator->prepareCreateOrUpdate($set, $path, ...$values);
        }
        if (count($values) < $number_of_keywords) {
            $delete_path = $this->path_collection->keywordsBetweenIndices(
                count($values),
                $number_of_keywords - 1
            );
            $set = $this->manipulator->prepareDelete($set, $delete_path);
        }
        return $set;
    }

    protected function prepareClassification(
        SetInterface $set,
        array $data
    ): SetInterface {
        $type_value = $data[ContentAssembler::LEARNING_RESOURCE_TYPE] ?? '';
        $type_source = $this->vocabulary_adapter->sourceMapForSlot(SlotIdentifier::EDUCATIONAL_LEARNING_RESOURCE_TYPE)($type_value);
        if ($type_value === null || $type_value === '') {
            $set = $this->manipulator->prepareDelete($set, $this->path_collection->firstLearningResourceType());
        } else {
            $set = $this->manipulator->prepareCreateOrUpdate(
                $set,
                $this->path_collection->firstLearningResourceType(),
                $type_value
            );
        }
        if ($type_source === null || $type_source === '') {
            $set = $this->manipulator->prepareDelete($set, $this->path_collection->firstLearningResourceTypeSource());
        } else {
            $set = $this->manipulator->prepareCreateOrUpdate(
                $set,
                $this->path_collection->firstLearningResourceTypeSource(),
                $type_source
            );
        }

        $discipline = $data[ContentAssembler::DISCIPLINE] ?? '';
        if ($discipline === null || $discipline === '') {
            $set = $this->manipulator->prepareDelete($set, $this->path_collection->firstDiscipline());
        } else {
            $set = $this->manipulator->prepareCreateOrUpdate(
                $set,
                $this->path_collection->firstDiscipline(),
                $discipline
            );
        }

        return $set;
    }

    protected function prepareContributors(
        SetInterface $set,
        array $data
    ): SetInterface {
        $paths = [
            ContentAssembler::FIRST_AUTHOR => $this->path_collection->firstAuthor(),
            ContentAssembler::SECOND_AUTHOR => $this->path_collection->secondAuthor(),
            ContentAssembler::THIRD_AUTHOR => $this->path_collection->thirdAuthor(),
            ContentAssembler::PUBLISHER => $this->path_collection->firstPublisher()
        ];
        foreach ($data as $post_key => $value) {
            $path = $paths[$post_key];
            if ($value === null || $value === '') {
                $set = $this->manipulator->prepareDelete($set, $path);
                continue;
            }
            $set = $this->manipulator->prepareCreateOrUpdate($set, $path, $value);
        }
        return $set;
    }

    protected function prepareRights(
        SetInterface $set,
        array $data
    ): SetInterface {
        $description = $data[0];
        if ($description === ContentAssembler::CUSTOM_CP) {
            $description = $data[1][ContentAssembler::CUSTOM_CP_DESCRIPTION];
        }

        if ($description === '' || $description === null) {
            $set = $this->manipulator->prepareCreateOrUpdate(
                $set,
                $this->path_collection->hasCopyright(),
                'no'
            );
            $set = $this->manipulator->prepareCreateOrUpdate(
                $set,
                $this->path_collection->sourceForHasCopyright(),
                FactoryInterface::STANDARD_SOURCE
            );
            return $this->manipulator->prepareDelete($set, $this->path_collection->copyright());
        }

        $set = $this->manipulator->prepareCreateOrUpdate(
            $set,
            $this->path_collection->hasCopyright(),
            'yes'
        );
        $set = $this->manipulator->prepareCreateOrUpdate(
            $set,
            $this->path_collection->sourceForHasCopyright(),
            FactoryInterface::STANDARD_SOURCE
        );
        $set = $this->manipulator->prepareCreateOrUpdate(
            $set,
            $this->path_collection->copyright(),
            $description
        );

        return $set;
    }
}
