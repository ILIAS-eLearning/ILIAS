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

use ILIAS\MetaData\Paths\FactoryInterface as PathFactory;
use ILIAS\MetaData\Paths\PathInterface;
use ILIAS\MetaData\Elements\Structure\StructureSetInterface;
use ILIAS\MetaData\Paths\Filters\FilterType;
use ILIAS\MetaData\Paths\Path;

class PathCollection
{
    protected PathFactory $path_factory;
    protected StructureSetInterface $structure;

    protected PathInterface $title;
    protected PathInterface $descriptions;
    protected PathInterface $languages;
    protected PathInterface $keywords;
    protected PathInterface $first_author;
    protected PathInterface $second_author;
    protected PathInterface $third_author;
    protected PathInterface $first_typical_learning_time;
    protected PathInterface $copyright;
    protected PathInterface $has_copyright;
    protected PathInterface $has_copyright_source;

    public function __construct(
        PathFactory $path_factory,
        StructureSetInterface $structure
    ) {
        $this->path_factory = $path_factory;
        $this->structure = $structure;
        $this->init();
    }

    protected function init(): void
    {
        $general = $this->structure->getRoot()->getSubElement('general');
        $this->title = $this->path_factory->toElement(
            $general->getSubElement('title')->getSubElement('string')
        );
        $this->descriptions = $this->path_factory->toElement(
            $general->getSubElement('description')->getSubElement('string')
        );
        $this->languages = $this->path_factory->toElement(
            $general->getSubElement('language')
        );
        $this->keywords = $this->path_factory->toElement(
            $general->getSubElement('keyword')->getSubElement('string')
        );

        $this->first_author = $this->authorWithIndex(0);
        $this->second_author = $this->authorWithIndex(1);
        $this->third_author = $this->authorWithIndex(2);

        $educational = $this->structure->getRoot()->getSubElement('educational');
        $tlt = $educational->getSubElement('typicalLearningTime');
        $duration = $tlt->getSubElement('duration');
        $this->first_typical_learning_time = $this->path_factory
            ->custom()
            ->withNextStep($educational->getDefinition())
            ->withAdditionalFilterAtCurrentStep(FilterType::INDEX, '0')
            ->withNextStep($tlt->getDefinition())
            ->withNextStep($duration->getDefinition())
            ->get();

        $rights = $this->structure->getRoot()->getSubElement('rights');
        $this->copyright = $this->path_factory->toElement(
            $rights->getSubElement('description')->getSubElement('string')
        );
        $cor = $rights->getSubElement('copyrightAndOtherRestrictions');
        $this->has_copyright = $this->path_factory->toElement(
            $cor->getSubElement('value')
        );
        $this->has_copyright_source = $this->path_factory->toElement(
            $cor->getSubElement('source')
        );
    }

    public function title(): PathInterface
    {
        return $this->title;
    }

    public function descriptions(): PathInterface
    {
        return $this->descriptions;
    }

    public function languages(): PathInterface
    {
        return $this->languages;
    }

    public function keywords(): PathInterface
    {
        return $this->keywords;
    }

    public function keywordsBetweenIndices(
        int $first_index,
        int $last_index
    ): PathInterface {
        $indices = [];
        for ($i = $first_index; $i <= $last_index; $i++) {
            $indices[] = (string) $i;
        }

        $general = $this->structure->getRoot()->getSubElement('general');
        $keyword = $general->getSubElement('keyword');
        $string = $keyword->getSubElement('string');
        return $this->path_factory
            ->custom()
            ->withNextStep($general->getDefinition())
            ->withNextStep($keyword->getDefinition())
            ->withNextStep($string->getDefinition())
            ->withNextStepToSuperElement()
            ->withAdditionalFilterAtCurrentStep(FilterType::INDEX, ...$indices)
            ->get();
    }

    public function firstAuthor(): PathInterface
    {
        return $this->first_author;
    }

    public function secondAuthor(): PathInterface
    {
        return $this->second_author;
    }

    public function thirdAuthor(): PathInterface
    {
        return $this->third_author;
    }

    protected function authorWithIndex(int $index): PathInterface
    {
        $lifecycle = $this->structure->getRoot()->getSubElement('lifeCycle');
        $contribute = $lifecycle->getSubElement('contribute');
        $role = $contribute->getSubElement('role');
        $value = $role->getSubElement('value');
        $source = $role->getSubElement('source');
        $entity = $contribute->getSubElement('entity');
        return $this->path_factory
            ->custom()
            ->withNextStep($lifecycle->getDefinition())
            ->withNextStep($contribute->getDefinition())
            ->withNextStep($role->getDefinition())
            ->withNextStep($value->getDefinition())
            ->withAdditionalFilterAtCurrentStep(FilterType::DATA, 'author')
            ->withNextStepToSuperElement()
            ->withNextStep($source->getDefinition())
            ->withAdditionalFilterAtCurrentStep(FilterType::DATA, 'LOMv1.0')
            ->withNextStepToSuperElement()
            ->withNextStepToSuperElement()
            ->withNextStep($entity->getDefinition())
            ->withAdditionalFilterAtCurrentStep(FilterType::INDEX, (string) $index)
            ->get();
    }

    public function firstTypicalLearningTime(): PathInterface
    {
        return $this->first_typical_learning_time;
    }

    public function copyright(): PathInterface
    {
        return $this->copyright;
    }

    public function hasCopyright(): PathInterface
    {
        return $this->has_copyright;
    }

    public function sourceForHasCopyright(): PathInterface
    {
        return $this->has_copyright_source;
    }
}
