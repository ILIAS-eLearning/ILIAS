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

namespace ILIAS\MetaData\Repository\IdentifierHandler;

use ILIAS\MetaData\Elements\SetInterface;
use ILIAS\MetaData\Elements\RessourceID\RessourceIDInterface;
use ILIAS\MetaData\Manipulator\ManipulatorInterface;
use ILIAS\MetaData\Paths\PathInterface;
use ILIAS\MetaData\Paths\FactoryInterface as PathFactory;
use ILIAS\MetaData\Paths\Filters\FilterType;

class IdentifierHandler implements IdentifierHandlerInterface
{
    protected ManipulatorInterface $manipulator;
    protected PathFactory $path_factory;

    public function __construct(
        ManipulatorInterface $manipulator,
        PathFactory $path_factory
    ) {
        $this->manipulator = $manipulator;
        $this->path_factory = $path_factory;
    }

    public function prepareUpdateOfIdentifier(
        SetInterface $set,
        RessourceIDInterface $ressource_id
    ): SetInterface {
        $set = $this->manipulator->prepareCreateOrUpdate(
            $set,
            $this->getPathToFirstIdentifierEntry(),
            $this->generateIdentifierEntry($ressource_id)
        );
        $set = $this->manipulator->prepareCreateOrUpdate(
            $set,
            $this->getPathToFirstIdentifierCatalog(),
            $this->generateIdentifierCatalog()
        );
        return $set;
    }

    protected function generateIdentifierEntry(RessourceIDInterface $ressource_id): string
    {
        $numeric_id = $ressource_id->subID() !== 0 ?
            $ressource_id->subID() :
            $ressource_id->objID();

        return 'il_' . $this->getInstallID() . '_' . $ressource_id->type() . '_' . $numeric_id;
    }

    protected function generateIdentifierCatalog(): string
    {
        return 'ILIAS';
    }

    protected function getPathToFirstIdentifierEntry(): PathInterface
    {
        return $this->path_factory
            ->custom()
            ->withNextStep('general')
            ->withNextStep('identifier')
            ->withAdditionalFilterAtCurrentStep(FilterType::INDEX, '0')
            ->withNextStep('entry')
            ->get();
    }

    protected function getPathToFirstIdentifierCatalog(): PathInterface
    {
        return $this->path_factory
            ->custom()
            ->withNextStep('general')
            ->withNextStep('identifier')
            ->withAdditionalFilterAtCurrentStep(FilterType::INDEX, '0')
            ->withNextStep('catalog')
            ->get();
    }

    protected function getInstallID(): string
    {
        return (string) IL_INST_ID;
    }
}
