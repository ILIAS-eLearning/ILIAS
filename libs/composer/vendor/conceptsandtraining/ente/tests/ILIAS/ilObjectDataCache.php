<?php
/******************************************************************************
 * An entity component framework for PHP.
 *
 * Copyright (c) 2017 Richard Klees <richard.klees@concepts-and-training.de>
 *
 * This software is licensed under GPLv3. You should have received a copy of
 * the license along with the code.
 */

/**
 * Essentials of ILIAS object data cache for this framework.
 */
abstract class ilObjectDataCache {
    public function preloadReferenceCache($a_ref_ids, $a_incl_obj = true) {
        assert(false);
    }

    public function lookupObjId($a_ref_id) {
        assert(false);
    }
}
