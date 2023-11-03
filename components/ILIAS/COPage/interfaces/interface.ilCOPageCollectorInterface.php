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
 * Page collector interface
 *
 * @author Alexander Killing <killing@leifos.de>
 */
interface ilCOPageCollectorInterface
{
    /**
     * Get all page IDs of an repository object
     * @param int $obj_id object id of repository object
     * @return array[] inner array keys: "parent_type", "id", "lang"
     */
    public function getAllPageIds(int $obj_id): array;
}
