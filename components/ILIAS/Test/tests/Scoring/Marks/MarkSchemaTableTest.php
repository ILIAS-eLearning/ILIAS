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

use ILIAS\Test\Scoring\Marks\MarkSchemaTable;
use ILIAS\Test\Scoring\Marks\MarkSchema;

/**
 * @author Marvin Beym <mbeym@databay.de>
 */
class MarkSchemaTableTest extends ilTestBaseTestCase
{
    private MarkSchemaTable $table;

    protected function setUp(): void
    {
        parent::setUp();
        $this->table = new MarkSchemaTable(
            $this->createMock(MarkSchema::class),
            true,
            $this->createMock(ilLanguage::class),
            $this->createMock(\ILIAS\UI\URLBuilder::class),
            $this->createMock(\ILIAS\UI\URLBuilderToken::class),
            $this->createMock(\ILIAS\UI\URLBuilderToken::class),
            $this->createMock(ILIAS\UI\Factory::class),
        );
    }

    public function test_instantiateObject_shouldReturnInstance(): void
    {
        $this->assertInstanceOf(MarkSchemaTable::class, $this->table);
    }
}
