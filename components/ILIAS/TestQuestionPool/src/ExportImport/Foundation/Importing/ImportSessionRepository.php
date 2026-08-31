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

namespace ILIAS\TestQuestionPool\ExportImport\Foundation\Importing;

/**
 * Persists the import stage index and context across HTTP requests using `\ilSession`. Each instance is scoped by a
 * namespace string so that multiple concurrent import workflows do not interfere with each other.
 */
class ImportSessionRepository
{
    private const string KEY_PREFIX = 'import_stage_';

    private readonly string $key_index;
    private readonly string $key_context;

    public function __construct(string $namespace)
    {
        $base = self::KEY_PREFIX . $namespace . '_';

        $this->key_index = "{$base}index";
        $this->key_context = "{$base}context";
    }

    public function getCurrentStageIndex(): int
    {
        return \ilSession::has($this->key_index) ? (int) \ilSession::get($this->key_index) : 0;
    }

    public function setCurrentStageIndex(int $index): void
    {
        \ilSession::set($this->key_index, $index);
    }

    public function getContext(): ImportContext
    {
        if (\ilSession::has($this->key_context)) {
            return unserialize(
                \ilSession::get($this->key_context),
                ['allowed_classes' => [ImportContext::class]]
            );
        }

        return new ImportContext([]);
    }

    public function setContext(ImportContext $context): void
    {
        \ilSession::set($this->key_context, serialize($context));
    }

    public function clear(): void
    {
        \ilSession::clear($this->key_index);
        \ilSession::clear($this->key_context);
    }
}
