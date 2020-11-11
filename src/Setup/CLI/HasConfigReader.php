<?php
/* Copyright (c) 2016 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\Setup\CLI;

use ILIAS\Setup\Config;
use ILIAS\Setup\Agent;
use ILIAS\Setup\AgentCollection;
use ILIAS\Setup\Environment;
use Symfony\Component\Console\Input\InputInterface;

/**
 * Add this to an Command that has an config reader.
 */
trait HasConfigReader
{
    /**
     * @var ConfigReader
     */
    protected $config_reader;

    protected function readAgentConfig(Agent $agent, InputInterface $input) : ?Config
    {
        if (!($this->config_reader instanceof ConfigReader)) {
            throw new \LogicException("\$this->config_reader not properly initialized.");
        }

        $config_file = $input->getArgument("config");
        $config_overwrites_raw = $input->getOption("config");
        $config_overwrites = [];
        foreach ($config_overwrites_raw as $o) {
            $vs = explode("=", $o);
            if (count($vs) !== 2) {
                throw new \Exception("Cannot read config-option: '$o'");
            }
            $config_overwrites[$vs[0]] = $vs[1];
        }
        $config_content = $this->config_reader->readConfigFile($config_file, $config_overwrites);
        $trafo = $agent->getArrayToConfigTransformation();
        return $trafo->transform($config_content);
    }

    protected function addAgentConfigsToEnvironment(
        Agent $agent,
        Config $config,
        Environment $environment
    ) : Environment {
        if ($agent instanceof AgentCollection) {
            foreach ($config->getKeys() as $k) {
                $environment = $environment->withConfigFor($k, $config->getConfig($k));
            }
        }

        return $environment;
    }
}
