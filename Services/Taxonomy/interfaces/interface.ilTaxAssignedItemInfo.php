<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

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
    public function getTitle(string $a_comp_id, string $a_item_type, int $a_item_id) : string;
}
