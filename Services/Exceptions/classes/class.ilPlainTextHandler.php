<?php

/* Copyright (c) 2015 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

/**
* A Whoops error handler that prints the same content as the PrettyPageHandler but as plain text.
*
* This is used for better coexistence with xdebug, see #16627.
*
* @author Richard Klees <richard.klees@concepts-and-training.de>
* @version $Id$
*/

use Whoops\Handler\Handler;
use Whoops\Exception\Formatter;

class ilPlainTextHandler extends Handler
{
    const KEY_SPACE = 25;

    /** @var list<string> */
    private $exclusion_list = [];

    /**
     * @param list<string> $exclusion_list
     */
    public function withExclusionList(array $exclusion_list) : self
    {
        $clone = clone $this;
        $clone->exclusion_list = $exclusion_list;
        return $clone;
    }

    /**
     * Last missing method from HandlerInterface.
     *
     * @return null
     */
    public function handle()
    {
        header("Content-Type: text/plain");
        echo "<pre>\n";
        echo $this->content();
        echo "</pre>\n";
    }

    /**
     * Assemble the output for this handler.
     *
     * @return string
     */
    protected function content()
    {
        return $this->pageHeader()
              . $this->exceptionContent()
              . $this->tablesContent()
              ;
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
        return Formatter::formatExceptionPlain($this->getInspector());
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
