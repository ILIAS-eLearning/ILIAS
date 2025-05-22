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

namespace ILIAS\MetaData\Services\CopyrightHelper;

use ILIAS\MetaData\Services\Reader\ReaderInterface;
use ILIAS\MetaData\Services\Manipulator\ManipulatorInterface;
use ILIAS\MetaData\Search\Clauses\ClauseInterface as SearchClause;

interface CopyrightHelperInterface
{
    /**
     * If copyright selection is not active, there are no copyright presets
     * to choose from, but copyright information can still be added
     * manually to the LOM of objects.
     */
    public function isCopyrightSelectionActive(): bool;

    /**
     * Is the copyright in the LOM of the reader's object selected
     * from the presets? If not, custom copyright information
     * was entered manually. If no copyright is assigned to the
     * object, it is treated like it has the default
     * copyright licence, and true is returned here.
     *
     * Always returns false if copyright selection is not active.
     */
    public function hasPresetCopyright(ReaderInterface $reader): bool;

    /**
     * If possible, returns the preset copyright selected for the
     * reader's object. For objects which have no copyright information
     * in their LOM, the default copyright is returned (as long as
     * copyright selection is active).
     *
     * Returns a null object if copyright selection is not active,
     * or if the object has custom copyright information.
     */
    public function readPresetCopyright(ReaderInterface $reader): CopyrightInterface;

    /**
     * Returns the custom copyright information from the LOM of
     * the reader's object.
     *
     * If copyright selection is inactive, or one of the preset
     * copyright options was selected for the object, this returns
     * an empty string.
     */
    public function readCustomCopyright(ReaderInterface $reader): string;

    /**
     * The preset copyright with the given identifier is set to
     * be selected for the manipulator's object. Note that this
     * will also overwrite custom copyright information.
     *
     * Call {@see \ILIAS\MetaData\Services\Manipulator\ManipulatorInterface::execute()}
     * to carry out the changes.
     */
    public function prepareCreateOrUpdateOfCopyrightFromPreset(
        ManipulatorInterface $manipulator,
        string $copyright_id
    ): ManipulatorInterface;

    /**
     * The given copyright information is set to be written to
     * the LOM of the manipulator's object. Note that this
     * will also overwrite any selected preset copyright.
     *
     * Call {@see \ILIAS\MetaData\Services\Manipulator\ManipulatorInterface::execute()}
     * to carry out the changes.
     */
    public function prepareCreateOrUpdateOfCustomCopyright(
        ManipulatorInterface $manipulator,
        string $custom_copyright
    ): ManipulatorInterface;

    /**
     * Returns all preset copyright entries, or nothing
     * if copyright selection is not active.
     *
     * Returned entries are ordered according to their configured
     * positions.
     *
     * @return CopyrightInterface[]
     */
    public function getAllCopyrightPresets(): \Generator;

    /**
     * Returns all preset copyright entries that are not marked
     * as outdated, or nothing if copyright selection is not active.
     *
     * Returned entries are ordered according to their configured
     * positions.
     *
     * @return CopyrightInterface[]
     */
    public function getNonOutdatedCopyrightPresets(): \Generator;

    /**
     * Get a search clause that finds object with one of the given copyright
     * entries in their LOM, to be used in {@see \ILIAS\MetaData\Services\Search\SearcherInterface::execute()}.
     *
     * If copyright selection is active, objects without any copyright information
     * are treated as if they had the default copyright.
     */
    public function getCopyrightSearchClause(
        string $first_copyright_id,
        string ...$further_copyright_ids
    ): SearchClause;
}
