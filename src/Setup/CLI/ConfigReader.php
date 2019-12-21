<?php
/* Copyright (c) 2016 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\Setup\CLI;

use ILIAS\Setup\Agent;
use ILIAS\Setup\ArrayEnvironment;
use ILIAS\Setup\ObjectiveIterator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Read a json-formatted config from a file and overwrite some fields.
 */
class ConfigReader
{
    /**
     * @var string
     */
    protected $base_dir;

    public function __construct($base_dir = null)
    {
        $this->base_dir = $base_dir ?? getcwd();
    }

    /**
     * TODO: We could use the "give me a transformation and I'll give you your
     *       result" pattern from the input paper here.
     *
     * @param array $overwrites is a list of fields that should be overwritten with
     *                          with the contained variables. The keys define which
     *                          field (e.g. "a.b.c") should be overwritten with which
     *                          value.
     */
    public function readConfigFile(string $name, array $overwrites = []) : array
    {
        $name = $this->getRealFilename($name);
        if (!file_exists($name) || !is_readable($name)) {
            throw new \InvalidArgumentException(
                "Config-file '$name' does not exist or is not readable."
            );
        }
        $json = json_decode(file_get_contents($name), JSON_OBJECT_AS_ARRAY);
        if (!is_array($json)) {
            throw new \InvalidArgumentException(
                "Could not find JSON-array in '$name'."
            );
        }
        return $this->applyOverwrites($json, $overwrites);
    }

    protected function applyOverwrites(array $json, array $overwrites) : array
    {
        $replacer = null;
        $replacer = function ($subject, $path, $value) use (&$replacer) {
            if (count($path) === 0) {
                return $value;
            }
            $cur = array_shift($path);
            $subject[$cur] = $replacer($subject[$cur], $path, $value);
            return $subject;
        };

        foreach ($overwrites as $path => $value) {
            $path = explode(".", $path);
            $json = $replacer($json, $path, $value);
        }

        return $json;
    }

    protected function getRealFilename(string $name) : string
    {
        if (in_array($name[0], ["/", "\\"])) {
            return $name;
        }
        return $this->base_dir . "/" . $name;
    }
}
