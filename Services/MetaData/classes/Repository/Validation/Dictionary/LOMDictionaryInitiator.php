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

namespace ILIAS\MetaData\Repository\Validation\Dictionary;

use ILIAS\MetaData\Structure\Dictionaries\DictionaryInitiator as BaseDictionaryInitiator;
use ILIAS\MetaData\Paths\FactoryInterface as PathFactoryInterface;
use ILIAS\MetaData\Elements\Structure\StructureSetInterface;
use ILIAS\MetaData\Elements\Structure\StructureElementInterface;

class LOMDictionaryInitiator extends BaseDictionaryInitiator
{
    protected const MD_SCHEMA = 'LOMv1.0';

    protected TagFactory $tag_factory;

    public function __construct(
        TagFactory $tag_factory,
        PathFactoryInterface $path_factory,
        StructureSetInterface $structure
    ) {
        $this->tag_factory = $tag_factory;
        parent::__construct($path_factory, $structure);
    }

    public function get(): DictionaryInterface
    {
        $this->initDictionary();
        return new LOMDictionary($this->path_factory, ...$this->getTagAssignments());
    }

    protected function initDictionary(): void
    {
        $structure = $this->getStructure();

        $this->addTag($structure->getRoot(), Restriction::NOT_DELETABLE, 0);

        $this->setTagsForGeneral($structure);
        $this->setTagsForMetaMetadata($structure);
    }

    protected function setTagsForGeneral(
        StructureSetInterface $structure
    ): void {
        $this->addTag(
            $general = $structure->getRoot()->getSubElement('general'),
            Restriction::NOT_DELETABLE,
            0
        );
        $this->addTag(
            $title = $general->getSubElement('title'),
            Restriction::NOT_DELETABLE,
            0
        );
        $this->addTag(
            $title->getSubElement('string'),
            Restriction::NOT_DELETABLE,
            0
        );
        $this->addTag(
            $identifier = $general->getSubElement('identifier'),
            Restriction::NOT_DELETABLE,
            0
        );
        $this->addTag(
            $identifier->getSubElement('catalog'),
            Restriction::NOT_DELETABLE,
            0
        );
        $this->addTag(
            $identifier->getSubElement('catalog'),
            Restriction::NOT_EDITABLE,
            0
        );
        $this->addTag(
            $identifier->getSubElement('entry'),
            Restriction::NOT_DELETABLE,
            0
        );
        $this->addTag(
            $identifier->getSubElement('entry'),
            Restriction::NOT_EDITABLE,
            0
        );
    }

    protected function setTagsForMetaMetadata(
        StructureSetInterface $structure
    ): void {
        $meta_schema = $structure->getRoot()
                                 ->getSubElement('metaMetadata')
                                 ->getSubElement('metadataSchema');
        $this->addTag(
            $meta_schema,
            Restriction::NOT_EDITABLE,
            0
        );
        $this->addTag(
            $meta_schema,
            Restriction::NOT_DELETABLE,
            0
        );
        $this->addPresetValueTag(
            $meta_schema,
            self::MD_SCHEMA,
            0
        );
    }

    protected function addTag(
        StructureElementInterface $element,
        Restriction $restriction,
        int $index
    ): void {
        $tag = $this->tag_factory->tag(
            $restriction,
            '',
            $index
        );
        $this->addTagToElement($tag, $element);
    }

    protected function addPresetValueTag(
        StructureElementInterface $element,
        string $value,
        int $index
    ): void {
        $tag = $this->tag_factory->tag(
            Restriction::PRESET_VALUE,
            $value,
            $index
        );
        $this->addTagToElement($tag, $element);
    }
}
