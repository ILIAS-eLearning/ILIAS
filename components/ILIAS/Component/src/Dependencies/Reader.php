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

namespace ILIAS\Component\Dependencies;

use ILIAS\Component\Component;

class Reader
{
    protected array $defines;
    protected array $implements;
    protected array $uses;
    protected ?array $uses_temp = [];
    protected array $contributes;
    protected array $seeks;
    protected ?array $seeks_temp = [];
    protected array $provides;
    protected array $pulls;
    protected ?array $pulls_temp = [];
    protected array $internal_out;
    protected array $internal_in;
    protected ?array $internal_in_temp = [];

    /**
     * @return Dependency[]
     */
    public function read(Component $component): OfComponent
    {
        $this->defines = [];
        $this->implements = [];
        $this->uses = [];
        $this->contributes = [];
        $this->seeks = [];
        $this->provides = [];
        $this->pulls = [];
        $this->internal_out = [];
        $this->internal_in = [];

        $probes = [
            new SetProbe(fn($n, $v) => $this->addDefine($n, $v)),
            new SetProbe(fn($n, $v) => $this->cacheImplement($n, $v)),
            new GetProbe(fn($n) => $this->addUse($n)),
            new SetProbe(fn($n, $v) => $this->cacheContribute($n, $v)),
            new GetProbe(fn($n) => $this->addSeek($n)),
            new SetProbe(fn($n, $v) => $this->cacheProvide($n, $v)),
            new GetProbe(fn($n) => $this->addPull($n)),
            new SetGetProbe(fn($n, $v) => $this->cacheInternalOut($n, $v), fn($n) => $this->addInternalIn($n))
        ];
        $component->init(...$probes);

        $this->resolveDependencies();
        return $this->compileResult($component);
    }

    protected function addDefine(string $name, $value)
    {
        if (is_callable($value)) {
            $mm = $value();
            if (!($mm instanceof $name)) {
                throw new \LogicException(
                    "Minimal implementation should implement defined interface."
                );
            }
            $has_minimal_implementation = true;
        } elseif (is_null($value)) {
            $has_minimal_implementation = false;
        } else {
            throw new \LogicException(
                "Expected callable or null for \$define."
            );
        }

        $d = new Define(new Name($name), $has_minimal_implementation);
        $this->defines[$name] = $d;
    }

    protected function cacheDefine(string $name, $value)
    {
        $this->defines[$name] = [$name, $value];
    }

    protected function addImplement(int $i, string $name, $value)
    {
        if (!is_callable($value)) {
            throw new \LogicException(
                "\$implements must be set with a callable."
            );
        }

        $this->populateTempArrays();
        $impl = $value();
        $dependencies = $this->flushTempArrays();

        if (!$impl instanceof $name) {
            throw new \LogicException(
                "Implementation for $name does not implement the correct interface."
            );
        }

        $aux = [
            "class" => get_class($impl),
            "position" => $i
        ];
        $d = new Out(OutType::IMPLEMENT, $name, $aux, $dependencies);
        $this->implements[$i] = $d;
    }

    protected function cacheImplement(string $name, $value)
    {
        $this->implements[] = [$name, $value];
    }

    protected function addUse(string $name)
    {
        if (empty($this->uses_temp)) {
            throw new \LogicException(
                "\$use is only allowed when defining other dependencies"
            );
        }

        if (array_key_exists($name, $this->uses)) {
            $d = $this->uses[$name];
        } else {
            $d = new In(InType::USE, $name);
        }

        $this->uses_temp[0][$name] = $d;

        return $this->createMock($name);
    }

    protected function addContribute(int $i, string $name, $value)
    {
        if (!is_callable($value)) {
            throw new \LogicException(
                "\$implements must be set with a callable."
            );
        }

        $this->populateTempArrays();
        $impl = $value();
        $dependencies = $this->flushTempArrays();

        if (!$impl instanceof $name) {
            throw new \LogicException(
                "Contribution for $name does not implement the correct interface."
            );
        }

        $aux = [
            "position" => $i
        ];
        $d = new Out(OutType::CONTRIBUTE, $name, $aux, $dependencies);
        $this->contributes[$i] = $d;
    }

    protected function cacheContribute(string $name, $value)
    {
        $this->contributes[] = [$name, $value];
    }

    protected function addSeek(string $name)
    {
        if (empty($this->seeks_temp)) {
            throw new \LogicException(
                "\$seek is only allowed when defining other dependencies"
            );
        }

        if (array_key_exists($name, $this->seeks)) {
            $d = $this->seeks[$name];
        } else {
            $d = new In(InType::SEEK, $name);
        }

        $this->seeks_temp[0][$name] = $d;

        return [];
    }

    protected function addProvide(int $i, string $name, $value)
    {
        if (!is_callable($value)) {
            throw new \LogicException(
                "\$implements must be set with a callable."
            );
        }

        $this->populateTempArrays();
        $impl = $value();
        $dependencies = $this->flushTempArrays();

        if (!$impl instanceof $name) {
            throw new \LogicException(
                "Provision for $name does not implement the correct interface."
            );
        }

        $d = new Out(OutType::PROVIDE, $name, null, $dependencies);
        $this->provides[$i] = $d;
    }

    protected function cacheProvide(string $name, $value)
    {
        $this->provides[] = [$name, $value];
    }

    protected function addPull(string $name)
    {
        if (empty($this->pulls_temp)) {
            throw new \LogicException(
                "\$pull is only allowed when defining other dependencies"
            );
        }

        if (array_key_exists($name, $this->pulls)) {
            $d = $this->pulls[$name];
        } else {
            $d = new In(InType::PULL, $name);
        }

        $this->pulls_temp[0][$name] = $d;

        return $this->createMock($name);
    }

    protected function addInternalOut(string $name, $value)
    {
        if (!is_callable($value)) {
            throw new \LogicException(
                "\$internal must be set with a callable."
            );
        }

        $this->populateTempArrays();
        $impl = $value();
        $dependencies = $this->flushTempArrays();

        $d = new Out(OutType::INTERNAL, $name, null, $dependencies);
        $this->internal_out[$name] = [$d, $impl];
    }

    protected function cacheInternalOut(string $name, $value)
    {
        $this->internal_out[$name] = [$name, $value];
    }

    protected function resolveInternalOut(string $name)
    {
        if (!array_key_exists($name, $this->internal_out)) {
            throw new \LogicException(
                "Cannot resolve dependency \$internal[$name]. It either does not exist or is defined circular."
            );
        }

        if (!$this->internal_out[$name][0] instanceof Out) {
            $values = $this->internal_out[$name];
            unset($this->internal_out[$name]);
            $this->addInternalOut(...$values);
        }
    }

    protected function addInternalIn(string $name)
    {
        if (is_null($this->internal_in_temp)) {
            throw new \LogicException(
                "getting from \$internal is only allowed when defining other dependencies"
            );
        }

        if (array_key_exists($name, $this->internal_in)) {
            $d = $this->internal_in[$name];
        } else {
            $d = new In(InType::INTERNAL, $name);
        }

        $this->internal_in_temp[0][$name] = $d;

        $this->resolveInternalOut($name);

        return $this->internal_out[$name][1];
    }

    protected function populateTempArrays(): void
    {
        array_unshift($this->uses_temp, []);
        array_unshift($this->seeks_temp, []);
        array_unshift($this->pulls_temp, []);
        array_unshift($this->internal_in_temp, []);
    }

    protected function flushTempArrays(): array
    {
        $uses_temp = array_shift($this->uses_temp);
        $seeks_temp = array_shift($this->seeks_temp);
        $pulls_temp = array_shift($this->pulls_temp);
        $internal_in_temp = array_shift($this->internal_in_temp);


        $this->uses = array_merge($this->uses, $uses_temp);
        $this->seeks = array_merge($this->seeks, $seeks_temp);
        $this->pulls = array_merge($this->pulls, $pulls_temp);
        $this->internal_in = array_merge($this->internal_in, $internal_in_temp);

        $dependencies = array_merge(...array_map("array_values", [$uses_temp, $seeks_temp, $pulls_temp, $internal_in_temp]));

        return $dependencies;
    }

    protected function resolveDependencies(): void
    {
        foreach ($this->implements as $i => $v) {
            $this->addImplement($i, ...$v);
        }
        foreach ($this->contributes as $i => $v) {
            $this->addContribute($i, ...$v);
        }
        foreach ($this->provides as $i => $v) {
            $this->addProvide($i, ...$v);
        }
        foreach (array_keys($this->internal_out) as $i) {
            $this->resolveInternalOut($i);
        }
    }

    protected function compileResult(Component $component): OfComponent
    {
        return new OfComponent(
            $component,
            ...array_merge(
                ...array_map(
                    "array_values",
                    [
                        $this->defines,
                        $this->implements,
                        $this->uses,
                        $this->contributes,
                        $this->seeks,
                        $this->provides,
                        $this->pulls,
                        array_map(fn($a) => $a[0], $this->internal_out),
                        $this->internal_in
                    ]
                )
            )
        );
    }

    protected function createMock(string $class_name): object
    {
        $mock_builder = new \PHPUnit\Framework\MockObject\MockBuilder(new class () extends \PHPUnit\Framework\TestCase {}, $class_name);
        return $mock_builder
            ->disableOriginalConstructor()
            ->disableOriginalClone()
            ->disableArgumentCloning()
            ->disallowMockingUnknownTypes()
            ->getMock();
    }
}
