<?php
/* Copyright (c) 2016 Stefan Hecken, Extended GPL, see docs/LICENSE */
require_once './libs/composer/vendor/autoload.php';

use Whoops\Exception\Formatter;

/**
 * Saves error informations into file
 *
 * @author Stefan Hecken <stefan.hecken@concepts-and-training.de>
 */
class ilLoggingErrorFileStorage
{
    const KEY_SPACE = 25;
    const FILE_FORMAT = ".log";

    /** @var list<string> */
    private $exclusion_list = [];

    public function __construct($inspector, $file_path, $file_name)
    {
        $this->inspector = $inspector;
        $this->file_path = $file_path;
        $this->file_name = $file_name;
    }

    /**
     * @param list<string> $exclusion_list
     */
    public function withExclusionList(array $exclusion_list) : self
    {
        $clone = clone $this;
        $clone->exclusion_list = $exclusion_list;
        return $clone;
    }

    protected function createDir($path)
    {
        if (!is_dir($this->file_path)) {
            ilUtil::makeDirParents($this->file_path);
        }
    }

    protected function content()
    {
        return $this->pageHeader()
              . $this->exceptionContent()
              . $this->tablesContent()
              ;
    }

    public function write()
    {
        $this->createDir($this->file_path);

        $file_name = $this->file_path . "/" . $this->file_name . self::FILE_FORMAT;
        $stream = fopen($file_name, 'w+');
        fwrite($stream, $this->content());
        fclose($stream);
        chmod($file_name, 0755);
    }

    /**
     * Get the header for the page.
     *
     * @return string
     */
    protected function pageHeader()
    {
        return "";
    }

    /**
     * Get a short info about the exception.
     *
     * @return string
     */
    protected function exceptionContent()
    {
        return Formatter::formatExceptionPlain($this->inspector);
    }

    /**
     * Get the header for the page.
     *
     * @return string
     */
    protected function tablesContent()
    {
        $ret = "";
        foreach ($this->tables() as $title => $content) {
            $ret .= "\n\n-- $title --\n\n";
            if (count($content) > 0) {
                foreach ($content as $key => $value) {
                    $key = str_pad($key, self::KEY_SPACE);

                    // indent multiline values, first print_r, split in lines,
                    // indent all but first line, then implode again.
                    $first = true;
                    $indentation = str_pad("", self::KEY_SPACE);
                    $value = implode("\n", array_map(function ($line) use (&$first, $indentation) {
                        if ($first) {
                            $first = false;
                            return $line;
                        }
                        return $indentation . $line;
                    }, explode("\n", print_r($value, true))));

                    $ret .= "$key: $value\n";
                }
            } else {
                $ret .= "empty\n";
            }
        }
        return $ret;
    }

    /**
     * Get the tables that should be rendered.
     *
     * @return array 	$title => $table
     */
    protected function tables()
    {
        $post = $_POST;
        $server = $_SERVER;

        $post = $this->hideSensitiveData($post);
        $server = $this->hideSensitiveData($server);
        $server = $this->shortenPHPSessionId($server);

        return array( "GET Data" => $_GET
            , "POST Data" => $post
            , "Files" => $_FILES
            , "Cookies" => $_COOKIE
            , "Session" => isset($_SESSION) ? $_SESSION : array()
            , "Server/Request Data" => $server
            , "Environment Variables" => $_ENV
            );
    }

    /**
     * @param array<string, mixed> $super_global
     * @return array<string, mixed>
     */
    private function hideSensitiveData(array $super_global) : array
    {
        foreach ($this->exclusion_list as $parameter) {
            if (isset($super_global[$parameter])) {
                $super_global[$parameter] = 'REMOVED FOR SECURITY';
            }

            if (isset($super_global['post_vars'][$parameter])) {
                $super_global['post_vars'][$parameter] = 'REMOVED FOR SECURITY';
            }
        }

        return $super_global;
    }

    /**
     * Shorts the php session id
     *
     * @param array 	$server
     *
     * @return array
     */
    private function shortenPHPSessionId(array $server)
    {
        $cookie_content = $server["HTTP_COOKIE"];
        $cookie_content = explode(";", $cookie_content);

        foreach ($cookie_content as $key => $content) {
            $content_array = explode("=", $content);
            if (trim($content_array[0]) == session_name()) {
                $content_array[1] = substr($content_array[1], 0, 5) . " (SHORTENED FOR SECURITY)";
                $cookie_content[$key] = implode("=", $content_array);
            }
        }

        $server["HTTP_COOKIE"] = implode(";", $cookie_content);

        return $server;
    }
}
