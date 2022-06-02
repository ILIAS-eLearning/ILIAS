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
 
namespace ILIAS\UI\Implementation\Crawler;

use Symfony\Component\Yaml;
use ILIAS\UI\Implementation\Crawler\Entry as Entry;

/***
 * @author Timon Amstutz <timon.amstutz@ilub.unibe.ch>
 * @version $Id$
 */
class EntriesYamlParser implements YamlParser
{
    public const PARSER_STATE_OUTSIDE = 1;
    public const PARSER_STATE_ENTRY = 2;
    public const PARSER_STATE_SEEKING_RETURN = 3;
    public const PARSER_STATE_SEEKING_FUNCTION_NAME = 4;

    protected array $items = array();
    protected ?Exception\Factory $ef = null;

    /**
     * Used to add for Information in Exceptions
     */
    protected string $file_path = "none";

    /**
     * FactoryCrawler constructor.
     */
    public function __construct()
    {
        $this->ef = new Exception\Factory();
    }

    /**
     * @throws	Exception\CrawlerException
     */
    public function parseYamlStringArrayFromFile(string $filePath) : array
    {
        $this->file_path = $filePath;
        $content = $this->getFileContentAsString($filePath);
        return $this->parseYamlStringArrayFromString($content);
    }

    /**
     * @throws	Exception\CrawlerException
     */
    public function parseArrayFromFile(string $filePath) : array
    {
        $this->file_path = $filePath;
        $content = $this->getFileContentAsString($filePath);
        return $this->parseArrayFromString($content);
    }

    /**
     * @throws	Exception\CrawlerException
     */
    public function parseEntriesFromFile(string $filePath) : Entry\ComponentEntries
    {
        $this->file_path = $filePath;
        $content = $this->getFileContentAsString($filePath);
        return $this->parseEntriesFromString($content);
    }

    /**
     * @throws	Exception\CrawlerException
     */
    public function parseYamlStringArrayFromString(string $content) : array
    {
        return $this->getYamlEntriesFromString($content);
    }

    /**
     * @throws	Exception\CrawlerException
     */
    public function parseArrayFromString(string $content) : array
    {
        return $this->getPHPArrayFromYamlArray(
            $this->getYamlEntriesFromString($content)
        );
    }

    public function parseEntriesFromString(string $content) : Entry\ComponentEntries
    {
        $entries_array = $this->parseArrayFromString($content);
        return $this->getEntriesFromArray($entries_array);
    }

    /**
     * @throws	Exception\CrawlerException
     */
    protected function getFileContentAsString(string $filePath) : string
    {
        if (!file_exists($filePath)) {
            throw $this->ef->exception(Exception\CrawlerException::INVALID_FILE_PATH, $filePath);
        }
        $content = file_get_contents($filePath);
        if (!$content) {
            throw $this->ef->exception(Exception\CrawlerException::FILE_OPENING_FAILED, $filePath);
        }
        return $content;
    }

    /**
     * @throws	Exception\CrawlerException
     */
    protected function getYamlEntriesFromString(string $content) : array
    {
        $parser_state = self::PARSER_STATE_OUTSIDE;
        $current_entry = "";
        $yaml_entries = array();

        foreach (preg_split("/((\r?\n)|(\r\n?))/", $content) as $line) {
            if ($parser_state === self::PARSER_STATE_OUTSIDE) {
                if (preg_match('/---/', $line)) {
                    $current_entry = "";
                    $parser_state = self::PARSER_STATE_ENTRY;
                }
                if (preg_match('/\@return/', $line)) {
                    throw $this->ef->exception(
                        Exception\CrawlerException::ENTRY_WITH_NO_YAML_DESCRIPTION,
                        " in file: " . $this->file_path . ", " . $line
                    );
                }
                if (preg_match('/public function (.*)\(/', $line)) {
                    throw $this->ef->exception(
                        Exception\CrawlerException::ENTRY_WITH_NO_YAML_DESCRIPTION,
                        " in file: " . $this->file_path . ", " . $line
                    );
                }
            } elseif ($parser_state === self::PARSER_STATE_ENTRY) {
                if (!preg_match('/(\*$)|(---)/', $line)) {
                    $current_entry .= $this->purifyYamlLine($line);
                }
                if (preg_match('/---/', $line)) {
                    $parser_state = self::PARSER_STATE_SEEKING_RETURN;
                }
                if (preg_match('/\@return/', $line)) {
                    throw $this->ef->exception(
                        Exception\CrawlerException::ENTRY_WITH_NO_YAML_DESCRIPTION,
                        " in file: " . $this->file_path . ", " . $line
                    );
                }
                if (preg_match('/public function (.*)\(/', $line)) {
                    throw $this->ef->exception(
                        Exception\CrawlerException::ENTRY_WITH_NO_YAML_DESCRIPTION,
                        " in file: " . $this->file_path . ", " . $line
                    );
                }
            } elseif ($parser_state === self::PARSER_STATE_SEEKING_RETURN) {
                if (preg_match('/\@return/', $line)) {
                    $current_entry .= "namespace: " . ltrim($this->purifyYamlLine($line), '@return');
                    $parser_state = self::PARSER_STATE_SEEKING_FUNCTION_NAME;
                }
                if (preg_match('/---/', $line)) {
                    throw $this->ef->exception(
                        Exception\CrawlerException::ENTRY_WITH_NO_VALID_RETURN_STATEMENT,
                        " in file: " . $this->file_path . " line " . $current_entry
                    );
                }
                if (preg_match('/public function (.*)\(/', $line)) {
                    throw $this->ef->exception(
                        Exception\CrawlerException::ENTRY_WITH_NO_VALID_RETURN_STATEMENT,
                        " in file: " . $this->file_path . " line " . $current_entry
                    );
                }
            } else {
                if (preg_match('/public function (.*)\(/', $line, $matches)) {
                    preg_match('/public function (.*)\(/', $line, $matches);
                    $current_entry .= "function_name: " . $matches[1];
                    $yaml_entries[] = $current_entry;
                    $parser_state = self::PARSER_STATE_OUTSIDE;
                }
                if (preg_match('/---/', $line)) {
                    throw $this->ef->exception(
                        Exception\CrawlerException::ENTRY_WITHOUT_FUNCTION,
                        " in file: " . $this->file_path . " line " . $current_entry
                    );
                }
            }
        }
        if ($parser_state === self::PARSER_STATE_SEEKING_RETURN) {
            throw $this->ef->exception(
                Exception\CrawlerException::ENTRY_WITH_NO_VALID_RETURN_STATEMENT,
                " in file: " . $this->file_path . " line " . $current_entry
            );
        } elseif ($parser_state === self::PARSER_STATE_ENTRY) {
            throw $this->ef->exception(
                Exception\CrawlerException::ENTRY_WITH_NO_YAML_DESCRIPTION,
                " in file: " . $this->file_path
            );
        } elseif ($parser_state === self::PARSER_STATE_SEEKING_FUNCTION_NAME) {
            throw $this->ef->exception(
                Exception\CrawlerException::ENTRY_WITHOUT_FUNCTION,
                " in file: " . $this->file_path
            );
        }
        return $yaml_entries;
    }

    protected function purifyYamlLine(string $line) : string
    {
        return str_replace("* ", "", ltrim($line)) . PHP_EOL;
    }

    /**
     * @throws	Exception\CrawlerException
     */
    protected function getPHPArrayFromYamlArray(array $yaml_entries) : array
    {
        $entries = array();
        $parser = new Yaml\Parser();

        foreach ($yaml_entries as $yaml_entry) {
            try {
                $entries[] = $parser->parse($yaml_entry);
            } catch (\Exception $e) {
                throw $this->ef->exception(Exception\CrawlerException::PARSING_YAML_ENTRY_FAILED, " file: " . $this->file_path . "; " . $e);
            }
        }


        array_walk_recursive($entries, function (&$item) {
            if (!is_null($item)) {
                $item = rtrim($item, PHP_EOL);
            } else {
                $item = '';
            }
        });

        return $entries;
    }

    protected function getEntriesFromArray(array $entries_array) : Entry\ComponentEntries
    {
        $entries = new Entry\ComponentEntries();

        foreach ($entries_array as $entry_data) {
            $entries->addEntry($this->getEntryFromData($entry_data));
        }

        return $entries;
    }

    /**
     * @throws	Exception\CrawlerException
     */
    protected function getEntryFromData(array $entry_data) : Entry\ComponentEntry
    {
        $entry_data['title'] = self::fromCamelCaseToWords($entry_data['function_name']);

        if (!array_key_exists("title", $entry_data) || !$entry_data['title'] || $entry_data['title'] == "") {
            throw $this->ef->exception(Exception\CrawlerException::ENTRY_TITLE_MISSING, " File: " . $this->file_path);
        }
        if (!array_key_exists("namespace", $entry_data) || !$entry_data['namespace'] || $entry_data['namespace'] == "") {
            throw $this->ef->exception(Exception\CrawlerException::ENTRY_WITH_NO_VALID_RETURN_STATEMENT, " File: " . $this->file_path);
        }

        $entry_data['id'] = str_replace(
            "\\",
            "",
            str_replace("\\ILIAS\\UI\\", "", str_replace("\\ILIAS\\UI\\Component\\", "", $entry_data['namespace']))
        )
                . self::toUpperCamelCase($entry_data['title'], ' ');
        $entry_data['abstract'] = preg_match("/Factory/", $entry_data['namespace']);
        $entry_data['path'] = str_replace("/ILIAS", "src", str_replace("\\", "/", $entry_data['namespace']));

        try {
            $entry = new Entry\ComponentEntry($entry_data);
        } catch (\Exception $e) {
            throw $this->ef->exception(
                Exception\CrawlerException::PARSING_YAML_ENTRY_FAILED,
                " could not convert data to entry, message: '" . $e->getMessage() . "' file: " . $this->file_path
            );
        }

        return $entry;
    }

    /**
     * @return string|string[]
     */
    public static function toUpperCamelCase(string $string, string $seperator)
    {
        return str_replace($seperator, '', ucwords($string));
    }

    /**
     * @return string|string[]
     */
    public static function toLowerCamelCase(string $string, string $seperator)
    {
        return str_replace($seperator, '', lcfirst(ucwords($string)));
    }

    public static function fromCamelCaseToWords(string $camelCaseString) : string
    {
        return implode(' ', preg_split('/(?<=[a-z])(?=[A-Z])/x', ucwords($camelCaseString)));
    }
}
