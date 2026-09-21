<?php

namespace ILIAS\TestQuestionPool\ExportImport\Foundation\Normalizing\Pipes;

use ILIAS\TestQuestionPool\ExportImport\Foundation\Normalizing\NormalizingException;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Normalizing\Transformations;

/**
 * Carries a value, context and eventual result through normalization processors.
 */
final class NormalizeCarry
{
    private bool $has_result = false;
    /** @var array<array-key, mixed>|float|bool|int|string|null */
    private array|float|bool|int|string|null $result = null;

    /**
     * @param array<string, mixed> $context
     */
    public function __construct(
        private readonly Transformations $transformations,
        private readonly mixed $value,
        private readonly array $context,
    ) {
    }

    public function transformations(): Transformations
    {
        return $this->transformations;
    }

    public function value(): mixed
    {
        return $this->value;
    }

    /** @return array<string, mixed> */
    public function context(): array
    {
        return $this->context;
    }

    public function hasResult(): bool
    {
        return $this->has_result;
    }

    /** @param array<array-key, mixed>|float|bool|int|string|null $result */
    public function setResult(array|float|bool|int|string|null $result): void
    {
        $this->result = $result;
        $this->has_result = true;
    }

    /**
     * Get the result of the normalization carry. If no result is set, an exception will be thrown.
     *
     * @throws NormalizingException if the result is not set
     */
    /** @return array<array-key, mixed>|float|bool|int|string|null */
    public function result(): array|float|bool|int|string|null
    {
        if ($this->has_result === false) {
            throw new NormalizingException('Unsupported value', $this->value);
        }
        return $this->result;
    }
}
