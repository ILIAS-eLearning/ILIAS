<?php declare(strict_types=1);

/* Copyright (c) 2019 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\Setup;

use ILIAS\Refinery\Factory as Refinery;
use ILIAS\Refinery\Transformation;
use Symfony\Component\Mime\Exception\LogicException;
use Symfony\Component\Console\Input\InputInterface;

/**
 * An agent that is just a collection of some other agents.
 */
class AgentCollection implements Agent
{
    protected Refinery $refinery;

    /**
     * @var Agent[]
     */
    protected array $agents;

    public function __construct(
        Refinery $refinery,
        array $agents
    ) {
        $this->refinery = $refinery;
        $this->agents = $agents;
    }

    public function getAgent(string $key) : ?Agent
    {
        return $this->agents[$key] ?? null;
    }

    public function withRemovedAgent(string $key) : AgentCollection
    {
        $clone = clone $this;
        unset($clone->agents[$key]);
        return $clone;
    }

    public function withAdditionalAgent(string $key, Agent $agent) : AgentCollection
    {
        if (isset($this->agents[$key])) {
            throw new \LogicException("An agent with the name '$key' already exists.");
        }
        $clone = clone $this;
        $clone->agents[$key] = $agent;
        return $clone;
    }

    /**
     * @inheritdocs
     */
    public function hasConfig() : bool
    {
        foreach ($this->agents as $c) {
            if ($c->hasConfig()) {
                return true;
            }
        }
        return false;
    }

    /**
     * @inheritdocs
     */
    public function getArrayToConfigTransformation() : Transformation
    {
        return $this->refinery->in()->series([
            $this->refinery->custom()->transformation(function ($in) {
                $out = [];
                foreach ($this->agents as $key => $agent) {
                    if (!$agent->hasConfig()) {
                        continue;
                    }
                    $val = $in[$key] ?? null;
                    $transformation = $agent->getArrayToConfigTransformation();
                    $out[$key] = $transformation($val);
                }
                return $out;
            }),
            $this->refinery->custom()->transformation(function ($v) {
                return [$v];
            }),
            $this->refinery->to()->toNew(ConfigCollection::class)
        ]);
    }

    /**
     * @inheritdocs
     */
    public function getInstallObjective(Config $config = null) : Objective
    {
        if (!is_null($config)) {
            $this->checkConfig($config);
        }

        return new ObjectiveCollection(
            "Collected Install Objectives",
            false,
            ...array_values(array_map(
                function (string $k, Agent $v) use ($config) {
                    if ($v->hasConfig()) {
                        return $v->getInstallObjective($config->getConfig($k));
                    } else {
                        return $v->getInstallObjective();
                    }
                },
                array_keys($this->agents),
                array_values($this->agents)
            ))
        );
    }

    /**
     * @inheritdocs
     */
    public function getUpdateObjective(Config $config = null) : Objective
    {
        if ($config) {
            $this->checkConfig($config);
        }

        return new ObjectiveCollection(
            "Collected Update Objectives",
            false,
            ...array_values(array_map(
                function (string $k, Agent $v) use ($config) {
                    if ($config) {
                        return $v->getUpdateObjective($config->maybeGetConfig($k));
                    }
                    return $v->getUpdateObjective();
                },
                array_keys($this->agents),
                array_values($this->agents)
            ))
        );
    }

    /**
     * @inheritdocs
     */
    public function getBuildArtifactObjective() : Objective
    {
        return new ObjectiveCollection(
            "Collected Build Artifact Objectives",
            false,
            ...array_values(array_map(
                function (Agent $v) {
                    return $v->getBuildArtifactObjective();
                },
                $this->agents
            ))
        );
    }

    /**
     * @inheritdocs
     */
    public function getStatusObjective(Metrics\Storage $storage) : Objective
    {
        return new ObjectiveCollection(
            "Collected Status Objectives",
            false,
            ...array_values(array_map(
                function (string $k, Agent $v) use ($storage) {
                    return $v->getStatusObjective(
                        new Metrics\StorageOnPathWrapper($k, $storage)
                    );
                },
                array_keys($this->agents),
                array_values($this->agents)
            ))
        );
    }

    /**
     * @inheritDoc
     */
    public function getMigrations() : array
    {
        $migrations = [];
        foreach ($this->agents as $agent_key => $agent) {
            foreach ($agent->getMigrations() as $migration) {
                /**
                 * @var $migration Migration
                 */
                $key = (new \ReflectionClass($migration))->getShortName();
                $migrations[$agent_key . "." . $key] = $migration;
            }
        }

        return $migrations;
    }

    protected function getKey(Migration $migration) : string
    {
        $names = explode("\\", get_class($migration));
        return array_pop($names);
    }

    protected function checkConfig(Config $config)
    {
        if (!($config instanceof ConfigCollection)) {
            throw new \InvalidArgumentException(
                "Expected ConfigCollection for configuration."
            );
        }
    }

    /**
     * @return Agent[]
     */
    public function getAgents() : array
    {
        return $this->agents;
    }

    /** @inheritDoc */
    public function getNamedObjectives(?Config $config = null) : array
    {
        if (!is_null($config)) {
            $this->checkConfig($config);
        }

        $agents = $this->agents;
        $namedObjectives = [];

        foreach ($agents as $k => $agent) {
            if ($config) {
                $objectives = $agent->getNamedObjectives($config->maybeGetConfig($k));
            } else {
                $objectives = $agent->getNamedObjectives();
            }
            foreach ($objectives as $name => $constructor) {
                $namedObjectives["$k.$name"] = $constructor;
            }
        }

        ksort($namedObjectives);
        return $namedObjectives;
    }
}
