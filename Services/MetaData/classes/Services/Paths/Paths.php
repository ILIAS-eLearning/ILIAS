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

namespace ILIAS\MetaData\Services\Paths;

use ILIAS\MetaData\Paths\PathInterface;
use ILIAS\MetaData\Paths\Filters\FilterType;
use ILIAS\MetaData\Paths\FactoryInterface as PathFactory;

class Paths implements PathsInterface
{
    protected PathFactory $path_factory;

    public function __construct(PathFactory $path_factory)
    {
        $this->path_factory = $path_factory;
    }

    public function title(): PathInterface
    {
        return $this->custom()
                    ->withNextStep('general')
                    ->withNextStep('title')
                    ->withNextStep('string')
                    ->get();
    }

    public function descriptions(): PathInterface
    {
        return $this->custom()
                    ->withNextStep('general')
                    ->withNextStep('description')
                    ->withNextStep('string')
                    ->get();
    }

    public function keywords(): PathInterface
    {
        return $this->custom()
                    ->withNextStep('general')
                    ->withNextStep('keyword')
                    ->withNextStep('string')
                    ->get();
    }

    public function languages(): PathInterface
    {
        return $this->custom()
                    ->withNextStep('general')
                    ->withNextStep('language')
                    ->get();
    }

    public function authors(): PathInterface
    {
        return $this->custom()
                    ->withNextStep('lifeCycle')
                    ->withNextStep('contribute')
                    ->withNextStep('role')
                    ->withNextStep('value')
                    ->withAdditionalFilterAtCurrentStep(FilterType::DATA, 'author')
                    ->withNextStepToSuperElement()
                    ->withNextStep('source')
                    ->withAdditionalFilterAtCurrentStep(FilterType::DATA, 'LOMv1.0')
                    ->withNextStepToSuperElement()
                    ->withNextStepToSuperElement()
                    ->withNextStep('entity')
                    ->get();
    }

    public function firstTypicalLearningTime(): PathInterface
    {
        return $this->custom()
                    ->withNextStep('educational')
                    ->withAdditionalFilterAtCurrentStep(FilterType::INDEX, '0')
                    ->withNextStep('typicalLearningTime')
                    ->withNextStep('duration')
                    ->get();
    }

    public function copyright(): PathInterface
    {
        return $this->custom()
                    ->withNextStep('rights')
                    ->withNextStep('description')
                    ->withNextStep('string')
                    ->get();
    }

    public function custom(): BuilderInterface
    {
        return new Builder($this->path_factory->custom());
    }
}
