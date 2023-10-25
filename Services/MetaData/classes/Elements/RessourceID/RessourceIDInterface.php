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

namespace ILIAS\MetaData\Elements\RessourceID;

interface RessourceIDInterface
{
    /**
     * (Sub-)Type of the object (e.g st,pg,crs ...)
     * NOTE: In the metadata tables, this corresponds to the field obj_type.
     */
    public function type(): string;

    /**
     * Object ID (NOT ref_id!) of the parent repository object (e.g for page objects the obj_id
     * of the content object; for media objects this is set to 0, because their
     * object id are not assigned to ref ids)
     * NOTE: In the metadata tables, this corresponds to the field rbac_id.
     */
    public function objID(): int;

    /**
     * ID of the object carrying the metadata, which might be a subobject of an
     * enclosing repository object (e.g for structure objects the obj_id of the
     * structure object). Might be the same as the objID.
     * NOTE: In the metadata tables, this corresponds to the field obj_id.
     */
    public function subID(): int;
}
