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

namespace ILIAS\TestQuestionPool\ExportImport\Foundation;

use ILIAS\DI\Container as ILIASContainer;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Contracts\Pipe;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Contracts\Transformations as TransformationsContract;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Normalizing\Normalizer\Registry;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Normalizing\Pipes\DenormalizingPipe;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Normalizing\Pipes\FinalizeNormalizing;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Normalizing\Pipes\NormalizingPipe;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Normalizing\Transformations;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Setup\NormalizerArtifactObjective;
use Pimple\Container;
use ReflectionClass;
use ReflectionNamedType;
use ReflectionParameter;
use RuntimeException;

/**
 * Builder class to create a Transformations instance for the export and import process.
 */
class Builder
{
    private bool $default_normalizers = true;
    private ?string $legacy_version = null;

    /** @var list<Pipe> $prepend_pipes */
    private array $prepend_pipes = [];

    /** @var list<Pipe> $append_pipes */
    private array $append_pipes = [];

    /** @var list<Container> $containers */
    private array $containers = [];

    public function __construct(
        private readonly ILIASContainer $dic,
        Container ...$local_containers,
    ) {
        $this->containers = $local_containers;
    }

    /*
        Fluent interface methods
    */

    public function withDefaultNormalizers(bool $enable = true): self
    {
        $clone = clone $this;
        $clone->default_normalizers = $enable;
        return $clone;
    }

    public function withLegacyNormalizers(string $version): self
    {
        $clone = clone $this;
        $clone->legacy_version = $version;
        return $clone;
    }

    /**
     * @param list<Pipe> $append
     * @param list<Pipe> $prepend
     */
    public function withAdditionalPipes(array $prepend = [], array $append = []): self
    {
        $clone = clone $this;
        $clone->append_pipes += $append;
        $clone->prepend_pipes += $prepend;
        return $clone;
    }

    /*
        Object creation
    */

    /**
     * Create a Transformations instance which was configured by the builder.
     */

    public function create(): TransformationsContract
    {
        $pipeline = new Pipeline();
        $object = new Transformations(
            $this->dic->refinery(),
            $pipeline
        );

        foreach ($this->prepend_pipes as $pipe) {
            $pipeline->pipe($pipe);
        }

        if ($this->legacy_version !== null) {
            $registry = $this->buildRegistry($object, $this->legacy_version);
            $pipeline->pipe(new NormalizingPipe($registry));
            $pipeline->pipe(new DenormalizingPipe($registry));
        }

        if ($this->default_normalizers) {
            $registry = $this->buildRegistry($object);
            $pipeline->pipe(new NormalizingPipe($registry));
            $pipeline->pipe(new DenormalizingPipe($registry));
        }

        foreach ($this->append_pipes as $pipe) {
            $pipeline->pipe($pipe);
        }

        $pipeline->pipe(new FinalizeNormalizing());

        return $object;
    }

    /**
     * Register all normalizer classes from the type map artifact by checking for the given version and skipping if
     * the normalizer is already registered.
     */
    private function buildRegistry(Transformations $object, string $version = NormalizerArtifactObjective::DEFAULT_KEY): Registry
    {
        $type_map = require NormalizerArtifactObjective::PATH();
        $registry = new Registry();

        foreach ($type_map as $type => $normalizer_classes) {
            if ($registry->hasNormalizer($type)) {
                continue;
            }

            $resolved_key = $this->resolveVersionKey($version, $normalizer_classes);
            if (!isset($normalizer_classes[$resolved_key])) {
                continue;
            }

            $registry->registerNormalizer(
                $type,
                fn() => $this->createInstance($normalizer_classes[$resolved_key], $object)
            );
        }

        return $registry;
    }

    /**
     * Resolve the best matching version key from the available normalizer versions.
     *
     * Priority: exact match > nearest concrete version before requested > wildcard (major.*) > default.
     * Uses version_compare for comparing ILIAS versions (major.minor, optional suffixes like alpha).
     *
     * @param array<string, class-string> $available_versions
     */
    private function resolveVersionKey(string $requested_version, array $available_versions): string
    {
        if (isset($available_versions[$requested_version])) {
            return $requested_version;
        }

        if ($requested_version === NormalizerArtifactObjective::DEFAULT_KEY) {
            return $requested_version;
        }

        $requested_major = strstr($requested_version, '.', true) ?: $requested_version;

        $best_concrete = null;
        foreach (array_keys($available_versions) as $version) {
            if ($version === NormalizerArtifactObjective::DEFAULT_KEY
                || $this->isWildcardVersion($version)) {
                continue;
            }

            $version_major = strstr($version, '.', true) ?: $version;
            if ($version_major !== $requested_major) {
                continue;
            }

            if (version_compare($version, $requested_version, '<=')
                && ($best_concrete === null || version_compare($version, $best_concrete, '>'))) {
                $best_concrete = $version;
            }
        }

        if ($best_concrete !== null) {
            return $best_concrete;
        }

        foreach (["{$requested_major}.*", $requested_major] as $wildcard) {
            if (isset($available_versions[$wildcard])) {
                return $wildcard;
            }
        }

        return NormalizerArtifactObjective::DEFAULT_KEY;
    }

    private function isWildcardVersion(string $version): bool
    {
        return !str_contains($version, '.') || str_ends_with($version, '.*');
    }

    /*
        Factory & Autowiring
    */

    /**
     * Create an instance of a class by resolving the constructor arguments.
     *
     * @template T of object
     *
     * @param class-string<T> $class_name
     * @param Transformations $transformations
     * @return T
     */
    private function createInstance(string $class_name, Transformations $transformations): object
    {
        $reflection_class = new ReflectionClass($class_name);
        $constructor = $reflection_class->getConstructor();

        if ($constructor === null || $constructor->getNumberOfParameters() === 0) {
            return $reflection_class->newInstance();
        }

        $arguments = [];
        foreach ($constructor->getParameters() as $parameter) {
            $arguments[] = $this->resolveConstructorArgument(
                $parameter,
                $transformations,
                $class_name
            );
        }

        return $reflection_class->newInstanceArgs($arguments);
    }

    /**
     * Resolve a constructor argument by trying to resolve it from the global ilias container, the local container or
     * the default value.
     *
     * @throws RuntimeException if the argument cannot be resolved
     */
    private function resolveConstructorArgument(
        ReflectionParameter $parameter,
        Transformations $transformations,
        string $class_name
    ) {
        if ($parameter->isDefaultValueAvailable()) {
            return $parameter->getDefaultValue();
        }

        $type = $parameter->getType();
        if ($type instanceof ReflectionNamedType && $type->allowsNull()) {
            return null;
        }


        if ($type instanceof ReflectionNamedType && !$type->isBuiltin()) {
            $type_name = $type->getName();

            if ($type_name === TransformationsContract::class || in_array(TransformationsContract::class, class_implements($type_name))) {
                return $transformations;
            }

            foreach ([$this->dic, ...$this->containers] as $container) {
                if ($type_name === get_class($container)) {
                    return $container;
                }
            }
        }

        $name = $parameter->getName();
        throw new RuntimeException("Unable to resolve constructor parameter \${$name} for class {$class_name}.");
    }
}
