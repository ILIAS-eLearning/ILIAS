<?php
/***
 * @author            Timon Amstutz <timon.amstutz@ilub.unibe.ch>
 * @version           $Id$
 *
 */
namespace ILIAS\UI\Implementation\Crawler;

use Symfony\Component\Yaml;
use ILIAS\UI\Implementation\Crawler\Entry as Entry;

class EntriesYamlParser implements YamlParser
{
    const PARSER_STATE_OUTSIDE = 1;
    const PARSER_STATE_ENTRY = 2;
    const PARSER_STATE_SEEKING_RETURN = 3;

    /**
     * @var array
     */
    protected $items = array();


    /**
     * @var Exception\Factory
     */
    protected $ef = null;

    /**
     * Used to add for Information in Exceptions
     * @var string
     */
    protected $file_path = "none";
    /**
     * FactoryCrawler constructor.
     */
    public function __construct(){
        $this->ef = new Exception\Factory();
    }

    /**
     * @param string $filePath
     * @return array|string
     * @throws Exception\CrawlerException
     */
    public function parseYamlStringArrayFromFile($filePath){
        $this->file_path = $filePath;
        $content = $this->getFileContentAsString($filePath);
        return $this->parseYamlStringArrayFromString($content);
    }

    /**
     * @param string $filePath
     * @return array
     * @throws Exception\CrawlerException
     */
    public function parseArrayFromFile($filePath){
        $this->file_path = $filePath;
        $content = $this->getFileContentAsString($filePath);
        return $this->parseArrayFromString($content);
    }

    /**
     * @param string $filePath
     * @return Entry\ComponentEntries
     * @throws Exception\CrawlerException
     */
    public function parseEntriesFromFile($filePath){
        $this->file_path = $filePath;
        $content = $this->getFileContentAsString($filePath);
        return $this->parseEntriesFromString($content);
    }

    /**
     * @param string $content
     * @return array
     * @throws Exception\CrawlerException
     */
    public function parseYamlStringArrayFromString($content){
        return $this->getYamlEntriesFromString($content);
    }

    /**
     * @param string $content
     * @return array
     * @throws Exception\CrawlerException
     */
    public function parseArrayFromString($content){
        return $this->getPHPArrayFromYamlArray(
            $this->getYamlEntriesFromString($content)
        );
    }

    /**
     * @param string $content
     * @return Entry\ComponentEntries
     */
    public function parseEntriesFromString($content){
        $entries_array = $this->parseArrayFromString($content);
        return $this->getEntriesFromArray($entries_array);
    }

    /**
     * @param $filePath
     * @return string
     * @throws Exception\CrawlerException
     */
    protected function getFileContentAsString($filePath){
        if ( !file_exists($filePath) ) {
            throw $this->ef->exception(Exception\CrawlerException::INVALID_FILE_PATH,$filePath);
        }
        $content = file_get_contents($filePath);
        if ( !$content ) {
            throw $this->ef->exception(Exception\CrawlerException::FILE_OPENING_FAILED,$filePath);
        }
        return $content;
    }

    /**
     * @param $content
     * @return array
     * @throws Exception\CrawlerException
     */
    protected function getYamlEntriesFromString($content){
        $parser_state = self::PARSER_STATE_OUTSIDE;
        $current_entry = "";
        $yaml_entries = array();

        foreach(preg_split("/((\r?\n)|(\r\n?))/", $content) as $line){
            if($parser_state === self::PARSER_STATE_OUTSIDE ){
                if(preg_match('/---/', $line)) {
                    $current_entry = "";
                    $parser_state = self::PARSER_STATE_ENTRY;

                }
                if(preg_match('/\@return/', $line)) {
                    throw $this->ef->exception(Exception\CrawlerException::ENTRY_WITH_NO_YAML_DESCRIPTION,
                        " in file: ".$this->file_path.", ".$line);
                }
            }else if($parser_state === self::PARSER_STATE_ENTRY){
                if(!preg_match('/(\*$)|(---)/', $line)){
                    $current_entry .= $this->purifyYamlLine($line);
                }
                if(preg_match('/---/', $line)) {
                    $parser_state = self::PARSER_STATE_SEEKING_RETURN;
                }
                if(preg_match('/\@return/', $line)) {
                    throw $this->ef->exception(Exception\CrawlerException::ENTRY_WITH_NO_YAML_DESCRIPTION,
                        " in file: ".$this->file_path.", ".$line);
                }
            }else{
                if(preg_match('/\@return/', $line)) {
                    $current_entry .= "namespace: ".ltrim($this->purifyYamlLine($line),'@return');
                    $yaml_entries[] = $current_entry;
                    $parser_state = self::PARSER_STATE_OUTSIDE;
                }
                if(preg_match('/---/', $line)) {
                        throw $this->ef->exception(Exception\CrawlerException::ENTRY_WITH_NO_VALID_RETURN_STATEMENT,
                            " in file: ".$this->file_path." line ".$current_entry);
                }
            }

        }
        if($parser_state === self::PARSER_STATE_SEEKING_RETURN ){
            throw $this->ef->exception(Exception\CrawlerException::ENTRY_WITH_NO_VALID_RETURN_STATEMENT,
                " in file: ".$this->file_path." line ".$current_entry);
        }else if($parser_state === self::PARSER_STATE_ENTRY){
            throw $this->ef->exception(Exception\CrawlerException::ENTRY_WITH_NO_YAML_DESCRIPTION,
                " in file: ".$this->file_path);
        }
        return $yaml_entries;
    }

    /**
     * @param string $line
     * @return string
     */
    protected function purifyYamlLine($line){
        return str_replace("* ", "", ltrim($line)).PHP_EOL;
    }

    /**
     * @param $yaml_entries
     * @return array
     * @throws Exception\CrawlerException
     */
    protected function getPHPArrayFromYamlArray($yaml_entries){
        $entries = array();
        $parser = new Yaml\Parser();

        foreach($yaml_entries as $yaml_entry){
            try{
                $entries[] = $parser->parse($yaml_entry);
            }catch(\Exception $e){

                throw $this->ef->exception(Exception\CrawlerException::PARSING_YAML_ENTRY_FAILED," file: ".$this->file_path."; ".$e);
            }
        }

        array_walk_recursive($entries, function(&$item){
            $item = rtrim($item,PHP_EOL);
        });


        return $entries;
    }

    /**
     * @param $entries_array
     * @return Entry\ComponentEntries
     */
    protected function getEntriesFromArray($entries_array){
        $entries = new Entry\ComponentEntries();

        foreach($entries_array as $entry_data){
            $entries->addEntry($this->getEntryFromData($entry_data));
        }

        return $entries;
    }

    /**
     * @param $entry_data
     * @return Entry\ComponentEntry
     * @throws Exception\CrawlerException
     */
    protected function getEntryFromData($entry_data){
        if(!array_key_exists("title",$entry_data) || !$entry_data['title'] || $entry_data['title'] ==""){
            throw $this->ef->exception(Exception\CrawlerException::ENTRY_TITLE_MISSING," File: ".$this->file_path);
        }
        if(!array_key_exists("namespace",$entry_data) || !$entry_data['namespace'] || $entry_data['namespace'] ==""){
            throw $this->ef->exception(Exception\CrawlerException::ENTRY_WITH_NO_VALID_RETURN_STATEMENT," File: ".$this->file_path);
        }

        $entry_data['id'] = self::toLowerCamelCase($entry_data['title'], ' ');
        $entry_data['abstract'] = preg_match("/Factory/",$entry_data['namespace']);
        $entry_data['path'] = str_replace("/ILIAS","src",str_replace("\\","/",$entry_data['namespace']));

        $entry = null;

        try{
            $entry = new Entry\ComponentEntry($entry_data);
        }catch(\Exception $e){
            throw $this->ef->exception(Exception\CrawlerException::PARSING_YAML_ENTRY_FAILED,
                " could not convert data to entry, message: '".$e->getMessage()."' file: ".$this->file_path);
        }

        return $entry;
    }

    /**
     * @param $string
     * @param $seperator
     * @return mixed
     */
    public static function toUpperCamelCase($string,$seperator){
        return str_replace($seperator, '', ucwords($string));
    }

    /**
     * @param $string
     * @param $seperator
     * @return mixed
     */
    public static function toLowerCamelCase($string,$seperator){
        return str_replace($seperator, '', lcfirst(ucwords($string)));
    }
}
