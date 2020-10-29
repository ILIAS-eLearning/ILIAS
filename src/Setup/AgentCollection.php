<?php

/* Copyright (c) 2019 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\Setup;

use ILIAS\UI\Component\Input\Field\Factory as FieldFactory;
use ILIAS\UI\Component\Input\Field\Input as Input;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\Refinery\Transformation;
use Symfony\Component\Mime\Exception\LogicException;

/**
 * An agent that is just a collection of some other agents.
 */
class AgentCollection implements Agent
{
    /**
     * @var Refinery
     */
    protected $refinery;

    /**
     * @var Agent[]
     */
    protected $agents;

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
        $this->checkConfig($config);

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

    protected function checkConfig(Config $config)
    {
        if (!($config instanceof ConfigCollection)) {
            throw new \InvalidArgumentException(
                "Expected ConfigCollection for configuration."
            );
        }
    }
}
