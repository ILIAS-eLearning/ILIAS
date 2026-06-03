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

namespace ILIAS\KeyValueStorage;

/**
 * Entry point for retrieving namespace-scoped key-value storage.
 *
 * The lifetime is chosen through the named accessors below; the underlying backend
 * identifier is an implementation detail and not exposed to consumers.
 */
interface Factory
{
    /**
     * Storages whose contents are tied to the current user session.
     *
     * @throws Exception\StorageNotAvailableException if no provider contributed the backend
     */
    public function session(): Storages;

    /**
     * Storages whose contents survive session boundaries until changed or cleared.
     *
     * @throws Exception\StorageNotAvailableException if no provider contributed the backend
     */
    public function persistent(): Storages;
}
