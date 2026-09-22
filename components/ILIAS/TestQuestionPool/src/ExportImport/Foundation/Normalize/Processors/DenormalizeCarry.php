<?php

namespace ILIAS\TestQuestionPool\ExportImport\Foundation\Normalize\Processors;

use ILIAS\TestQuestionPool\ExportImport\Foundation\Normalize\NormalizingException;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Normalize\Transformations;

/**
 * Carries normalized data, its expected type and eventual result through denormalization processors.
 */
final class DenormalizeCarry
{
    private bool $has_result = false;
    private mixed $result;

    /**
     * @param array<array-key, mixed>|float|bool|int|string|null $normalized
     */
    public function __construct(
        private readonly Transformations $transformations,
        private readonly array|float|bool|int|string|null $normalized,
        private readonly string|object $expected,
    ) {
    }

    public function transformations(): Transformations
    {
        return $this->transformations;
    }

    /** @return array<array-key, mixed>|float|bool|int|string|null */
    public function normalized(): array|float|bool|int|string|null
    {
        return $this->normalized;
    }

    public function expected(): string|object
    {
        return $this->expected;
    }

    public function hasResult(): bool
    {
        return $this->has_result;
    }

    public function setResult(mixed $result): void
    {
        $this->result = $result;
        $this->has_result = true;
    }

    /**
     * Get the result of the denormalization carry. If no result is set, an exception will be thrown.
     *
     * @throws NormalizingException if the result is not set
     */
    public function result(): mixed
    {
        if ($this->has_result === false) {
            throw new NormalizingException(sprintf(
                'Unsupported value, expected: %s, got: %s', 
                get_debug_type($this->expected), 
                get_debug_type($this->normalized)
            ));
        }

        return $this->result;
    }
}
