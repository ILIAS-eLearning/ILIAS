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
use ILIAS\MetaData\Paths\Filters\FilterType;
use ILIAS\MetaData\Paths\Path;

class PathCollection
{
    protected PathFactory $path_factory;

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
        PathFactory $path_factory
    ) {
        $this->path_factory = $path_factory;
        $this->init();
    }

    protected function init(): void
    {
        $this->title = $this->path_factory
            ->custom()
            ->withNextStep('general')
            ->withNextStep('title')
            ->withNextStep('string')
            ->get();
        $this->descriptions = $this->path_factory
            ->custom()
            ->withNextStep('general')
            ->withNextStep('description')
            ->withNextStep('string')
            ->get();
        $this->languages = $this->path_factory
            ->custom()
            ->withNextStep('general')
            ->withNextStep('language')
            ->get();
        $this->keywords = $this->path_factory
            ->custom()
            ->withNextStep('general')
            ->withNextStep('keyword')
            ->withNextStep('string')
            ->get();

        $this->first_author = $this->authorWithIndex(0);
        $this->second_author = $this->authorWithIndex(1);
        $this->third_author = $this->authorWithIndex(2);

        $this->first_typical_learning_time = $this->path_factory
            ->custom()
            ->withNextStep('educational')
            ->withAdditionalFilterAtCurrentStep(FilterType::INDEX, '0')
            ->withNextStep('typicalLearningTime')
            ->withNextStep('duration')
            ->get();

        $this->copyright = $this->path_factory
            ->custom()
            ->withNextStep('rights')
            ->withNextStep('description')
            ->withNextStep('string')
            ->get();
        $this->has_copyright = $this->path_factory
            ->custom()
            ->withNextStep('rights')
            ->withNextStep('copyrightAndOtherRestrictions')
            ->withNextStep('value')
            ->get();
        $this->has_copyright_source = $this->path_factory
            ->custom()
            ->withNextStep('rights')
            ->withNextStep('copyrightAndOtherRestrictions')
            ->withNextStep('source')
            ->get();
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

        return $this->path_factory
            ->custom()
            ->withNextStep('general')
            ->withNextStep('keyword')
            ->withNextStep('string')
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
        return $this->path_factory
            ->custom()
            ->withNextStep('lifeCycle')
            ->withNextStep('contribute')
            ->withNextStep('role')
            ->withNextStep('value')
            // also capitalized to ensure backwards compatibility (38865)
            ->withAdditionalFilterAtCurrentStep(FilterType::DATA, 'author', 'Author')
            ->withNextStepToSuperElement()
            ->withNextStep('source')
            ->withAdditionalFilterAtCurrentStep(FilterType::DATA, 'LOMv1.0')
            ->withNextStepToSuperElement()
            ->withNextStepToSuperElement()
            ->withNextStep('entity')
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
