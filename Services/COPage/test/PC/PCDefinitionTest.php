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

namespace ILIAS\COPage\Test\PC;

use PHPUnit\Framework\TestCase;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class PCDefinitionTest extends \COPageTestBase
{
    public function testGetPCDefinitionByType(): void
    {
        $def = $this->getPCDefinition();
        $pc_def = $def->getPCDefinitionByType("par");

        $this->assertEquals(
            "Paragraph",
            $pc_def["name"]
        );
    }

    public function testGetPCDefinitionByName(): void
    {
        $def = $this->getPCDefinition();
        $pc_def = $def->getPCDefinitionByName("Paragraph");

        $this->assertEquals(
            "par",
            $pc_def["pc_type"]
        );
    }

    public function testGetPCDefinitionByGUIClassName(): void
    {
        $def = $this->getPCDefinition();
        $pc_def = $def->getPCDefinitionByGUIClassName("ilPCParagraphGUI");

        $this->assertEquals(
            "par",
            $pc_def["pc_type"]
        );
    }

    public function testIsPCGUIClassName(): void
    {
        $def = $this->getPCDefinition();

        $this->assertEquals(
            true,
            $def->isPCGUIClassName("ilPCParagraphGUI")
        );

        $this->assertEquals(
            false,
            $def->isPCGUIClassName("xyz")
        );
    }

    public function testGetPCEditorInstanceByName(): void
    {
        $def = $this->getPCDefinition();
        $pc_ed = $def->getPCEditorInstanceByName("Paragraph");

        $this->assertEquals(
            "ilPCParagraphEditorGUI",
            get_class($pc_ed)
        );
    }
}
