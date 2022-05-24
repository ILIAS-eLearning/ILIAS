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
    protected ConfigReader $config_reader;

    protected function readAgentConfig(Agent $agent, InputInterface $input) : ?Config
    {
        if (!($this->config_reader instanceof ConfigReader)) {
            throw new \LogicException("\$this->config_reader not properly initialized.");
        }

        if (!$agent->hasConfig()) {
            return null;
        }

        $config_file = $input->getArgument("config");
        if ($this->isConfigInRoot($config_file)) {
            throw new \LogicException("Thou shall not put your config file in the webroot!!");
        }

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

    protected function isConfigInRoot(string $config_file) : bool
    {
        $webroot = realpath(__DIR__ . "/../../../");
        $config_file = realpath($config_file);

        $common_prefix = substr($config_file, 0, strlen($webroot));
        return $webroot === $common_prefix;
    }
}
