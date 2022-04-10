<?php declare(strict_types=1);

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

use ILIAS\Setup\Agent;
use ILIAS\Setup\ArrayEnvironment;
use ILIAS\Setup\ObjectiveIterator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Seld\JsonLint\JsonParser;

/**
 * Read a json-formatted config from a file and overwrite some fields.
 */
class ConfigReader
{
    protected JsonParser $json_parser;
    protected string $base_dir;

    public function __construct(JsonParser $json_parser, string $base_dir = null)
    {
        $this->json_parser = $json_parser;
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
        if (!is_readable($name)) {
            throw new \InvalidArgumentException(
                "Config-file '$name' does not exist or is not readable."
            );
        }
        $json = $this->json_parser->parse(
            file_get_contents($name),
            JsonParser::PARSE_TO_ASSOC | JsonParser::DETECT_KEY_CONFLICTS
        );

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
            $subject[$cur] = $replacer($subject[$cur] ?? [], $path, $value);
            return $subject;
        };

        foreach ($overwrites as $path => $value) {
            $path = explode(".", (string) $path);
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
