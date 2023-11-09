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
 * Interface for assigned items of taxonomies
 * @author Alexander Killing <killing@leifos.de>
 */
interface ilTaxAssignedItemInfo
{
    /**
     * Get title of an assigned item
     * @param string $a_comp_id   component identifier, e.g. "glo" for glossary
     * @param string $a_item_type item type identifier, e.g. "term" for glossary terms
     * @param int    $a_item_id   item id
     * @return string
     */
    public function getTitle(string $a_comp_id, string $a_item_type, int $a_item_id): string;
}
