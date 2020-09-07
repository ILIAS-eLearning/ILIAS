<?php

/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Page collector interface
 *
 * @author killing@leifos.de
 * @ingroup ServicesCOPage
 */
interface ilCOPageCollectorInterface
{
    /**
     * Get all page IDs of an repository object
     *
     * @param int $obj_id object id of repository object
     * @return array[] inner array keys: "parent_type", "id", "lang"
     */
    public function getAllPageIds($obj_id);
}
