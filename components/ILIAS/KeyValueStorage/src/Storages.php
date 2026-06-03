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
 * Access to the namespace-scoped storages of one lifetime (session or persistent).
 *
 * Consumers obtain this from {@see Factory::session()} or {@see Factory::persistent()}
 * and therefore never need to reference the internal backend identifier.
 */
interface Storages
{
    /**
     * Namespace-scoped storage for the selected lifetime.
     *
     * An in-request (first-level) cache is applied internally; consumers do not
     * configure it. This is not a cross-request cache — use ILIAS\Cache for that.
     */
    public function storage(StorageNamespace $namespace): Storage;
}
