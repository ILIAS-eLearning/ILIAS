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

use ILIAS\MetaData\Elements\SetInterface;
use ILIAS\MetaData\Paths\PathInterface;
use ILIAS\MetaData\Elements\RessourceID\RessourceIDInterface;
use ILIAS\MetaData\Search\Clauses\ClauseInterface;
use ILIAS\MetaData\Search\Filters\FilterInterface;

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
     * The path must start from the root element. Note that path filters are ignored,
     * and if the path contains steps to super elements, it is only followed down to
     * the first element that the path returns to.
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

    /**
     * Results are always ordered first by obj_id, then sub_id, then type.
     * Multiple filters are joined with a logical OR, values within the
     * same filter with AND.
     * @return RessourceIDInterface[]
     */
    public function searchMD(
        ClauseInterface $clause,
        ?int $limit,
        ?int $offset,
        FilterInterface ...$filters
    ): \Generator;

    /**
     * Follows a trail of markers from the root element,
     * and creates, updates or deletes marked MD elements along the trail.
     * Non-scaffold elements with 'create or update' markers are
     * updated, and scaffold elements with 'create or update' markers
     * are created with the data value on the marker. Stops when encountering
     * a neutral marker on a scaffold.
     */
    public function manipulateMD(SetInterface $set): void;

    /**
     * Transfers a metadata set to an object, regardless of its source. Takes
     * The data from 'create or update' markers takes priority over the data
     * carried by marked elements, but 'delete' markers and unmarked or neutrally
     * marked scaffolds are ignored.
     * Always deletes whatever metadata already exist at the target.
     *
     * If $throw_error_if_invalid is set true, an error is thrown if the
     * markers on the $from_set are invalid, otherwise the invalid markers
     * are replaced by neutral markers.
     */
    public function transferMD(
        SetInterface $from_set,
        int $to_obj_id,
        int $to_sub_id,
        string $to_type,
        bool $throw_error_if_invalid
    ): void;

    public function deleteAllMD(
        int $obj_id,
        int $sub_id,
        string $type
    ): void;
}
