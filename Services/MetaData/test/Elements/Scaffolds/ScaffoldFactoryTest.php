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

use PHPUnit\Framework\TestCase;
use ILIAS\MetaData\Elements\Data\DataFactoryInterface;
use ILIAS\MetaData\Structure\Definitions\DefinitionInterface;
use ILIAS\MetaData\Elements\Data\Type;
use ILIAS\MetaData\Elements\Data\DataInterface;
use ILIAS\MetaData\Elements\ElementInterface;
use ILIAS\MetaData\Elements\NoID;
use ILIAS\MetaData\Structure\Definitions\NullDefinition;
use ILIAS\MetaData\Elements\Data\NullDataFactory;

class ScaffoldFactoryTest extends TestCase
{
    public function testCreateScaffold(): void
    {
        $factory = new ScaffoldFactory(new NullDataFactory());
        $scaffold = $factory->scaffold(new NullDefinition());

        $this->assertInstanceOf(ElementInterface::class, $scaffold);
        $this->assertSame(NoID::SCAFFOLD, $scaffold->getMDID());
        $this->assertSame(Type::NULL, $scaffold->getData()->type());
    }
}
