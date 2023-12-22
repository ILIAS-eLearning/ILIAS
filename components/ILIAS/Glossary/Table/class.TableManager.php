<?php

declare(strict_types=1);

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
 ********************************************************************
 */

namespace ILIAS\components\ILIAS\Glossary\Table;

/**
 * @author Thomas Famula <famula@leifos.de>
 */
class TableManager
{
    public function __construct(
    ) {
    }

    public function getGlossaryAutoLinkTable(
        \ilObjGlossary $glossary
    ): GlossaryAutoLinkTable {
        return new GlossaryAutoLinkTable($glossary);
    }

    public function getGlossaryForeignTermTable(
        \ilObjGlossary $glossary
    ): GlossaryForeignTermTable {
        return new GlossaryForeignTermTable($glossary);
    }

    public function getTermUsagesTable(
        int $term_id
    ): TermUsagesTable {
        return new TermUsagesTable($term_id);
    }

    public function getTermDefinitionBulkCreationTable(
        string $raw_data,
        \ilObjGlossary $glossary
    ): TermDefinitionBulkCreationTable {
        return new TermDefinitionBulkCreationTable($raw_data, $glossary);
    }

    public function getPresentationListTable(
        \ilObjGlossary $glossary,
        bool $offline,
        int $tax_node
    ): PresentationListTable {
        return new PresentationListTable($glossary, $offline, $tax_node);
    }

    public function getTermListTable(
        \ilObjGlossary $glossary,
        int $tax_node
    ): TermListTable {
        return new TermListTable($glossary, $tax_node);
    }
}
