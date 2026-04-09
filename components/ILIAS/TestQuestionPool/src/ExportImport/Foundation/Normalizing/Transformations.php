<?php

namespace ILIAS\TestQuestionPool\ExportImport\Foundation\Normalizing;

use Generator;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Contracts\Pipe;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Contracts\Pipeline;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Normalizing\Pipes\DenormalizeCarry;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Normalizing\Pipes\NormalizeCarry;
use ILIAS\Refinery\Custom\Group;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Contracts\Transformations as TransformationsContract;
use InvalidArgumentException;

/**
 * Provides a set of transformations for normalizing and denormalizing values. It uses the Refinery library to perform
 * the transformations. It also provides a registry of normalizers, which are used to handle the normalization and
 * denormalization of complex objects.
 */
class Transformations implements TransformationsContract
{
    public function __construct(
        protected readonly Refinery $refinery,
        protected readonly Pipeline $pipeline
    ) {
    }

    /*
        Normalization/Denormalization
    */

    /**
     * @inheritDoc
     */
    public function normalize(mixed $value, array $context = []): array|float|bool|int|string|null
    {
        if ($value === null) {
            return null;
        }

        if (is_object($value) && $value instanceof Generator) {
            $value = iterator_to_array($value);
        }

        if (is_array($value)) {
            return array_map(fn(mixed $value) => $this->normalize($value, $context), $value);
        }

        return $this->pipeline->send(new NormalizeCarry($this, $value, $context))
            ->then(fn(NormalizeCarry $carry) => $carry->result());
    }

    /**
     * @inheritDoc
     */
    public function denormalize(array|float|bool|int|string|null $normalized, string|object $expected): mixed
    {
        if ($normalized === null) {
            return null;
        }

        return $this->pipeline->send(new DenormalizeCarry($this, $normalized, $expected))
            ->then(fn(DenormalizeCarry $carry) => $carry->result());
    }

    /*
        Transformations
    */

    /**
     * @inheritDoc
     */
    public function context(string $pipe_class): Pipe
    {
        foreach ($this->pipeline->pipes() as $pipe) {
            if ($pipe instanceof $pipe_class) {
                return $pipe;
            }
        }
        throw new InvalidArgumentException("Pipe {$pipe_class} not found");
    }

    public function custom(): Group
    {
        return $this->refinery->custom();
    }

    /**
     * @throws InvalidArgumentException if the value cannot be transformed into an integer
     */
    public function int(mixed $value): int
    {
        return $this->refinery->kindlyTo()->int()->transform($value);
    }

    /**
     * @throws InvalidArgumentException if the value cannot be transformed into a float
     */
    public function float(mixed $value): float
    {
        return $this->refinery->kindlyTo()->float()->transform($value);
    }

    /**
     * @throws InvalidArgumentException if the value cannot be transformed into a string
     */
    public function string(mixed $value): string
    {
        return $this->refinery->kindlyTo()->string()->transform($value);
    }

    /**
     * @throws InvalidArgumentException if the value cannot be transformed into a boolean
     */
    public function bool(mixed $value): bool
    {
        return $this->refinery->kindlyTo()->bool()->transform($value);
    }

    public function nullableInt(mixed $value): ?int
    {
        return $this->refinery->byTrying([
            $this->refinery->kindlyTo()->int(),
            $this->refinery->always(null)
        ])->transform($value);
    }

    public function nullableFloat(mixed $value): ?float
    {
        return $this->refinery->byTrying([
            $this->refinery->kindlyTo()->float(),
            $this->refinery->always(null)
        ])->transform($value);
    }

    public function nullableString(mixed $value): ?string
    {
        return $this->refinery->byTrying([
            $this->refinery->kindlyTo()->string(),
            $this->refinery->always(null)
        ])->transform($value);
    }

    public function nullableBool(mixed $value): ?bool
    {
        return $this->refinery->byTrying([
            $this->refinery->kindlyTo()->bool(),
            $this->refinery->always(null)
        ])->transform($value);
    }
}
