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

class ScaffoldFactory
{
    protected DataFactoryInterface $data_factory;

    public function __construct(DataFactoryInterface $data_factory)
    {
        $this->data_factory = $data_factory;
    }

    public function scaffold(DefinitionInterface $definition): ElementInterface
    {
        return new Element(
            NoID::SCAFFOLD,
            $definition,
            $this->data_factory->null()
        );
    }
}
