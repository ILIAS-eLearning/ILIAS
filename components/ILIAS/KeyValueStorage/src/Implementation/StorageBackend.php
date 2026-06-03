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

namespace ILIAS\KeyValueStorage\Implementation;

/**
 * Identifies a persistence backend for key-value storage.
 *
 * @internal Used to wire and resolve contributed backends. Consumers select a
 *           lifetime through {@see \ILIAS\KeyValueStorage\Factory::session()} /
 *           {@see \ILIAS\KeyValueStorage\Factory::persistent()}. Backend providers
 *           use {@see StorageProviderFactory::session()} /
 *           {@see StorageProviderFactory::persistent()} instead.
 */
enum StorageBackend: string
{
    case SESSION = 'session';
    case PERSISTENT = 'persistent';
}
