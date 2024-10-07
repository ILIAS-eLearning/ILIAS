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

namespace ILIAS\MetaData\Services;

use ILIAS\MetaData\Services\Paths\PathsInterface;
use ILIAS\MetaData\Services\DataHelper\DataHelperInterface;
use ILIAS\MetaData\Services\Manipulator\ManipulatorInterface;
use ILIAS\MetaData\Services\Reader\ReaderInterface;
use ILIAS\MetaData\Paths\PathInterface;
use ILIAS\MetaData\Services\Derivation\SourceSelectorInterface;
use ILIAS\MetaData\Services\Search\SearcherInterface;
use ILIAS\MetaData\Services\CopyrightHelper\CopyrightHelperInterface;

interface ServicesInterface
{
    /**
     * Get a reader, which can read out LOM of an ILIAS object. The object is specified
     * with three parameters:
     * 1. **obj_id:** The `obj_id` of the object if it is a repository object, else the
     * `obj_id` of its parent repository object. If the object does not have
     * a fixed parent  (e.g. MediaObject), then this parameter is 0.
     * 2. **sub_id:** The `obj_id` of the object. If the object is a repository object by
     * itself and not a sub-object, then you can set this parameter to 0, but
     * we recommend passing the `obj_id` again.
     * 3. **type:** The type of the object (and not its parent's), e.g. `'crs'` or `'lm'`.
     *
     * Optionally, a path can be specified to which the reading is restricted: the reader
     * will then only have access to elements on the path, along with recursively all
     * sub-elements of the last element of the path.
     * Note that path filters are ignored, and if the path contains steps to super elements,
     * it is only followed down to the first element that the path returns to.
     */
    public function read(
        int $obj_id,
        int $sub_id,
        string $type,
        PathInterface $limited_to = null
    ): ReaderInterface;

    /**
     * Get a searcher, in which you can assemble a search clause and filters,
     * and use these to find objects whose LOM matches the search.
     */
    public function search(): SearcherInterface;

    /**
     * Get a manipulator, which can manipulate the LOM of an ILIAS object.
     * See {@see \ILIAS\MetaData\Services\ServicesInterface::read()} for a description of the parameters.
     */
    public function manipulate(int $obj_id, int $sub_id, string $type): ManipulatorInterface;

    /**
     * Derives LOM from a target, for a source. Encompasses both copying LOM between
     * ILIAS objects and creating LOM for an object from some basic properties.
     */
    public function derive(): SourceSelectorInterface;

    /**
     * Delete all LOM of an ILIAS object. See {@see \ILIAS\MetaData\Services\ServicesInterface::read()}
     * for a description of the parameters.
     */
    public function deleteAll(int $obj_id, int $sub_id, string $type): void;

    /**
     * Elements in LOM are identified by paths to them from the root. Get a collection of
     * frequently used paths, as well as a builder to construct custom ones.
     */
    public function paths(): PathsInterface;

    /**
     * The data carried by many LOM elements is in LOM-specific formats. Get a collection
     * of helpful translations from or to these formats.
     */
    public function dataHelper(): DataHelperInterface;

    /**
     * The LOM of an object also contains its copyright information, which might consist of
     * a reference to a preset copyright. This helper offers some infrastructure
     * to parse and manipulate copyright information of objects, to access the copyright
     * presets, and to search and filter objects by copyright.
     */
    public function copyrightHelper(): CopyrightHelperInterface;
}
