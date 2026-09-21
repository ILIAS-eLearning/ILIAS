<?php

namespace ILIAS\TestQuestionPool\ExportImport\Foundation\Normalizing;

use Generator;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Normalizing\Pipes\DenormalizeCarry;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Normalizing\Pipes\NormalizeCarry;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Queue\Processor;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Queue\Queue;
use ILIAS\Refinery\Factory as Refinery;

/**
 * Provides a set of transformations for normalizing and denormalizing values. It uses the Refinery library to perform
 * the transformations. It also provides a registry of normalizers, which are used to handle the normalization and
 * denormalization of complex objects.
 */
final class Transformations
{
    public function __construct(
        private readonly Refinery $refinery,
        private readonly Queue $normalization_queue,
        private readonly Queue $denormalization_queue
    ) {
    }

    /*
        Normalization/Denormalization
    */

    /**
     * @param array<string, mixed> $context
     * @return array<array-key, mixed>|float|bool|int|string|null
     */
    public function normalize(mixed $value, array $context = []): array|float|bool|int|string|null
    {
        if (is_object($value) && $value instanceof Generator) {
            $value = iterator_to_array($value);
        }

        if (is_array($value)) {
            return array_map(fn(mixed $value) => $this->normalize($value, $context), $value);
        }

        $carry = new NormalizeCarry($this, $value, $context);
        $this->normalization_queue->process($carry);
        return $carry->result();
    }

    /**
     * @param array<array-key, mixed>|float|bool|int|string|null $normalized
     */
    public function denormalize(array|float|bool|int|string|null $normalized, string|object $expected): mixed
    {
        $carry = new DenormalizeCarry($this, $normalized, $expected);
        $this->denormalization_queue->process($carry);
        return $carry->result();
    }

    public function normalizationProcessor(string $processor_class): Processor
    {
        return $this->normalization_queue->get($processor_class);
    }

    /**
     * @throws \InvalidArgumentException if the value cannot be transformed into an integer
     */
    public function int(mixed $value): int
    {
        return $this->refinery->kindlyTo()->int()->transform($value);
    }

    /**
     * @throws \InvalidArgumentException if the value cannot be transformed into a float
     */
    public function float(mixed $value): float
    {
        return $this->refinery->kindlyTo()->float()->transform($value);
    }

    /**
     * @throws \InvalidArgumentException if the value cannot be transformed into a string
     */
    public function string(mixed $value): string
    {
        return $this->refinery->kindlyTo()->string()->transform($value);
    }

    /**
     * @throws \InvalidArgumentException if the value cannot be transformed into a boolean
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
