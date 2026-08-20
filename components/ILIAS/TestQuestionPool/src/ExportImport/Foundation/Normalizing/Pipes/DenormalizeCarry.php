<?php

namespace ILIAS\TestQuestionPool\ExportImport\Foundation\Normalizing\Pipes;

use ILIAS\TestQuestionPool\ExportImport\Foundation\Contracts\Transformations;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Normalizing\NormalizingException;

/**
 * Carry object (passable) for the denormalization pipeline. It wraps the caller object and the value to denormalize.
 * The result will be set by the pipes in the pipeline. If it's not set on the end of the pipeline, an exception will be
 * thrown.
 */
class DenormalizeCarry
{
    protected mixed $result;

    public function __construct(
        public readonly Transformations $transformations,
        public readonly array|float|bool|int|string|null $normalized,
        public readonly string|object $expected,
    ) {
    }

    /**
     * Set the result of the denormalization carry.
     */
    public function setResult(mixed $result): self
    {
        $this->result = $result;
        return $this;
    }

    /**
     * Get the result of the denormalization carry. If no result is set, an exception will be thrown.
     *
     * @throws NormalizingException if the result is not set
     */
    public function result(): mixed
    {
        if (!isset($this->result)) {
            $expected_type = get_debug_type($this->expected);
            $normalized_type = get_debug_type($this->normalized);
            throw new NormalizingException("Unsupported value, expected: {$expected_type}, got: {$normalized_type}");
        }

        return $this->result;
    }
}
