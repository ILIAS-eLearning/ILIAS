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

namespace ILIAS\MetaData\Repository;

use ILIAS\MetaData\Elements\ElementInterface;
use ILIAS\MetaData\Elements\SetInterface;
use ILIAS\MetaData\Paths\PathInterface;
use ILIAS\MetaData\Repository\Utilities\ScaffoldProviderInterface;

interface RepositoryInterface
{
    /**
     * * obj_id: Object ID (NOT ref_id!) of the parent repository object (e.g for page objects the obj_id
     *  of the content object; for media objects this is set to 0, because their
     *  object id are not assigned to ref ids).
     *  NOTE: In the metadata tables, this corresponds to the field rbac_id.
     * * sub_id: ID of the object carrying the metadata, which might be a subobject of an
     *  enclosing repository object (e.g for structure objects the obj_id of the
     *  structure object). Might be the same as the objID.
     *  NOTE: In the metadata tables, this corresponds to the field obj_id.
     * * type: (Sub-)Type of the object (e.g st,pg,crs ...).
     *  NOTE: In the metadata tables, this corresponds to the field obj_type.
     */
    public function getMD(
        int $obj_id,
        int $sub_id,
        string $type
    ): SetInterface;

    /**
     * Returns an MD set with only the elements specified on a path, and all nested
     * subelements of the last elements on the path.
     * The path must start from the root element.
     * Note that resulting partial MD sets might not be completely valid, due to
     * conditions between elements. Be careful when dealing with vocabularies, or
     * Technical > Requirement > OrComposite.
     */
    public function getMDOnPath(
        PathInterface $path,
        int $obj_id,
        int $sub_id,
        string $type
    ): SetInterface;

    public function scaffolds(): ScaffoldProviderInterface;

    /**
     * Follows a trail of markers from the root element,
     * and creates, updates or deletes marked MD elements along the trail.
     * Non-scaffold elements with 'create or update' markers are
     * updated, and scaffold elements with 'create or update' markers
     * are created with the data value on the marker. Stops when encountering
     * a neutral marker on a scaffold.
     */
    public function manipulateMD(SetInterface $set): void;

    public function deleteAllMD(
        int $obj_id,
        int $sub_id,
        string $type
    ): void;
}
