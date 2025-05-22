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

namespace ILIAS\MetaData\Elements\Scaffolds;

use ILIAS\MetaData\Structure\Definitions\DefinitionInterface;
use ILIAS\MetaData\Elements\Element;
use ILIAS\MetaData\Elements\Data\DataFactoryInterface;
use ILIAS\MetaData\Elements\NoID;
use ILIAS\MetaData\Elements\ElementInterface;
use ILIAS\MetaData\Elements\SetInterface;
use ILIAS\MetaData\Elements\Set;
use ILIAS\MetaData\Elements\RessourceID\NullRessourceID;
use ILIAS\MetaData\Elements\RessourceID\RessourceIDFactoryInterface;

class ScaffoldFactory implements ScaffoldFactoryInterface
{
    protected DataFactoryInterface $data_factory;
    protected RessourceIDFactoryInterface $ressource_id_factory;

    public function __construct(
        DataFactoryInterface $data_factory,
        RessourceIDFactoryInterface $ressource_id_factory
    ) {
        $this->data_factory = $data_factory;
        $this->ressource_id_factory = $ressource_id_factory;
    }

    public function scaffold(DefinitionInterface $definition): ElementInterface
    {
        return new Element(
            NoID::SCAFFOLD,
            $definition,
            $this->data_factory->null()
        );
    }

    public function set(DefinitionInterface $root_definition): SetInterface
    {
        return new Set(
            $this->ressource_id_factory->null(),
            new Element(
                NoID::ROOT,
                $root_definition,
                $this->data_factory->null()
            )
        );
    }
}
