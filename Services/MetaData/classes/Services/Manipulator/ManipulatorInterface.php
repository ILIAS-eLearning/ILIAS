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

namespace ILIAS\MetaData\Services\Manipulator;

use ILIAS\MetaData\Paths\PathInterface;

interface ManipulatorInterface
{
    /**
     * The values are set to be inserted into the elements specified
     * by the path in order. Previous values of these elements are
     * overwritten, new elements are created if not enough exist.
     *
     * Note that an error is thrown if it is not possible to create
     * enough elements to hold all values. Be careful with unique
     * elements.
     */
    public function prepareCreateOrUpdate(
        PathInterface $path,
        string ...$values
    ): ManipulatorInterface;

    /**
     * New elements are set to be created as specified by the path,
     * and are filled with the values.
     *
     * Note that an error is thrown if it is not possible to create
     * enough elements to hold all values. Be careful with unique
     * elements.
     */
    public function prepareForceCreate(
        PathInterface $path,
        string ...$values
    ): ManipulatorInterface;

    /**
     * All elements specified by the path are set to be deleted.
     */
    public function prepareDelete(PathInterface $path): ManipulatorInterface;

    /**
     * Execute all prepared actions. An error is thrown if
     * the LOM set would become invalid, e.g. because of
     * invalid data values.
     */
    public function execute(): void;
}
