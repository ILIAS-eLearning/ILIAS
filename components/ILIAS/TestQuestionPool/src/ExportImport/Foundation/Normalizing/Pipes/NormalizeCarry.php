<?php

namespace ILIAS\TestQuestionPool\ExportImport\Foundation\Normalizing\Pipes;

use ILIAS\TestQuestionPool\ExportImport\Foundation\Contracts\Transformations;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Normalizing\NormalizingException;

/**
 * Carry object (passable) for the normalization pipeline. It wraps the caller object and the value to normalize.
 * The result will be set by the pipes in the pipeline. If it's not set on the end of the pipeline, an exception will be
 * thrown.
 */
class NormalizeCarry
{
    protected array|float|bool|int|string|null $result = null;

    public function __construct(
        public readonly Transformations $transformations,
        public readonly mixed $value,
        public readonly array $context,
    ) {
    }

    /**
     * Set the result of the normalization carry.
     */
    public function setResult(array|float|bool|int|string|null $result): self
    {
        $this->result = $result;
        return $this;
    }

    /**
     * Get the result of the normalization carry. If no result is set, an exception will be thrown.
     *
     * @throws NormalizingException if the result is not set
     */
    public function result(): array|float|bool|int|string|null
    {
        if ($this->result === null) {
            throw new NormalizingException('Unsupported value', $this->value);
        }
        return $this->result;
    }
}
