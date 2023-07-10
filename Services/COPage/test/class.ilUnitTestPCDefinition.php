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

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class ilUnitTestPCDefinition extends \ILIAS\COPage\PC\PCDefinition
{
    public function getRecords(): array
    {
        return [
            [
                "pc_type" => "par",
                "name" => "Paragraph",
                "directory" => "classes",
                "int_links" => 1,
                "style_classes" => 1,
                "xsl" => 0,
                "component" => "Services/COPage",
                "def_enabled" => 1,
                "top_item" => 1,
                "order_nr" => 10
            ]
        ];
    }
}
