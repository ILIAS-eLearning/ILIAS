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

namespace ILIAS\MetaData\Services\Derivation\Creation;

use ILIAS\MetaData\Elements\SetInterface;
use ILIAS\MetaData\Manipulator\ManipulatorInterface;
use ILIAS\MetaData\Paths\FactoryInterface as PathFactory;
use ILIAS\MetaData\Paths\PathInterface;
use ILIAS\MetaData\Manipulator\ScaffoldProvider\ScaffoldProviderInterface;

class Creator implements CreatorInterface
{
    protected ManipulatorInterface $manipulator;
    protected PathFactory $path_factory;
    protected ScaffoldProviderInterface $scaffold_provider;

    public function __construct(
        ManipulatorInterface $manipulator,
        PathFactory $path_factory,
        ScaffoldProviderInterface $scaffold_provider
    ) {
        $this->manipulator = $manipulator;
        $this->path_factory = $path_factory;
        $this->scaffold_provider = $scaffold_provider;
    }

    /**
     * @throws \ilMDServicesException if title is empty string
     */
    public function createSet(
        string $title,
        string $description = '',
        string $language = ''
    ): SetInterface {
        $set = $this->scaffold_provider->set();

        $set = $this->prepareTitle($set, $title, $language);
        $set = $this->prepareDescription($set, $description, $language);
        $set = $this->prepareLanguage($set, $language);

        return $set;
    }

    /**
     * @throws \ilMDServicesException if title is empty string
     */
    protected function prepareTitle(
        SetInterface $set,
        string $title,
        string $language
    ): SetInterface {
        if ($title === '') {
            throw new \ilMDServicesException('Title cannot be empty.');
        }

        $set = $this->manipulator->prepareCreateOrUpdate(
            $set,
            $this->getPathToTitleString(),
            $title
        );

        if ($language === '') {
            return $set;
        }
        return $this->manipulator->prepareCreateOrUpdate(
            $set,
            $this->getPathToTitleLanguage(),
            $language
        );
    }

    protected function prepareDescription(
        SetInterface $set,
        string $description,
        string $language
    ): SetInterface {
        if ($description === '') {
            return $set;
        }
        $set = $this->manipulator->prepareCreateOrUpdate(
            $set,
            $this->getPathToDescriptionString(),
            $description
        );

        if ($language === '') {
            return $set;
        }
        return $this->manipulator->prepareCreateOrUpdate(
            $set,
            $this->getPathToDescriptionLanguage(),
            $language
        );
    }

    protected function prepareLanguage(
        SetInterface $set,
        string $language
    ): SetInterface {
        if ($language === '') {
            return $set;
        }
        return $this->manipulator->prepareCreateOrUpdate(
            $set,
            $this->getPathToLanguage(),
            $language
        );
    }

    protected function getPathToTitleString(): PathInterface
    {
        return $this->path_factory
            ->custom()
            ->withNextStep('general')
            ->withNextStep('title')
            ->withNextStep('string')
            ->get();
    }

    protected function getPathToTitleLanguage(): PathInterface
    {
        return $this->path_factory
            ->custom()
            ->withNextStep('general')
            ->withNextStep('title')
            ->withNextStep('language')
            ->get();
    }

    protected function getPathToDescriptionString(): PathInterface
    {
        return $this->path_factory
            ->custom()
            ->withNextStep('general')
            ->withNextStep('description')
            ->withNextStep('string')
            ->get();
    }

    protected function getPathToDescriptionLanguage(): PathInterface
    {
        return $this->path_factory
            ->custom()
            ->withNextStep('general')
            ->withNextStep('description')
            ->withNextStep('language')
            ->get();
    }

    protected function getPathToLanguage(): PathInterface
    {
        return $this->path_factory
            ->custom()
            ->withNextStep('general')
            ->withNextStep('language')
            ->get();
    }
}
