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

namespace ILIAS\Test\ExportImport\Contracts;

/**
 * Provides a contract for objects that delegate their normalization and
 * denormalization logic to a dedicated, context-aware handler.
 *
 * This interface should be implemented when an object's transformation process cannot
 * be self-contained because it relies on external dependencies. Typical use cases
 * include fetching related entities from a repository, accessing a service, or
 * requiring other application-level context that should not be part of the
 * object itself.
 *
 * By implementing this interface, the object signals to the serialization system
 * that a specific handler (a "Normalizer") is responsible for its conversion.
 * This approach enforces a clean separation of concerns, keeping the data object
 * pure and moving complex, infrastructure-aware logic to a dedicated service class.
 *
 * @template T of Normalizable
 */
interface ContextualNormalizable
{
    /**
     * Returns the Fully Qualified Class Name (FQCN) of the dedicated Normalizer.
     *
     * The serialization system will use this class name to obtain an instance
     * of the handler (e.g., from a Dependency Injection container) and delegate
     * the normalization/denormalization task to it.
     *
     * @return class-string<T> where T is the type of the Normalizer
     */
    public function getNormalizerClass(): string;
}
