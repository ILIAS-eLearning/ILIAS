<?php

/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Interface for assigned items of taxonomies
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 *
 * @ingroup ServicesTaxonomy
 */
interface ilTaxAssignedItemInfo
{
    /**
     * Get title of an assigned item
     *
     * @param string $a_comp_id component identifier, e.g. "glo" for glossary
     * @param string $a_item_type item type identifier, e.g. "term" for glossary terms
     * @param integer $a_item_id item id
     */
    public function getTitle($a_comp_id, $a_item_type, $a_item_id);
}
