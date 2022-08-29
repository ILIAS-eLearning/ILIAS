<?php

/* Copyright (c) 2016 Stefan Hecken, Extended GPL, see docs/LICENSE */

use Whoops\Exception\Formatter;
use Whoops\Exception\Inspector;

/**
 * Saves error informations into file
 *
 * @author Stefan Hecken <stefan.hecken@concepts-and-training.de>
 */
class ilLoggingErrorFileStorage
{
    protected const KEY_SPACE = 25;
    protected const FILE_FORMAT = ".log";

    protected Inspector $inspector;
    protected string $file_path;
    protected string $file_name;


    public function __construct(Inspector $inspector, string $file_path, string $file_name)
    {
        $this->inspector = $inspector;
        $this->file_path = $file_path;
        $this->file_name = $file_name;
    }

    protected function createDir(string $path): void
    {
        if (!is_dir($this->file_path)) {
            ilFileUtils::makeDirParents($this->file_path);
        }
    }

    protected function content(): string
    {
        return $this->pageHeader()
              . $this->exceptionContent()
              . $this->tablesContent()
        ;
    }

    public function write(): void
    {
        $this->createDir($this->file_path);

        $file_name = $this->file_path . "/" . $this->file_name . self::FILE_FORMAT;
        $stream = fopen($file_name, 'w+');
        fwrite($stream, $this->content());
        fclose($stream);
        chmod($file_name, 0755);
    }

    protected function pageHeader(): string
    {
        return "";
    }

    /**
     * Get a short info about the exception.
     */
    protected function exceptionContent(): string
    {
        return Formatter::formatExceptionPlain($this->inspector);
    }

    /**
     * Get the header for the page.
     */
    protected function tablesContent(): string
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
     */
    protected function tables(): array
    {
        $post = $_POST;
        $server = $_SERVER;

        $post = $this->hidePassword($post);
        $server = $this->shortenPHPSessionId($server);

        return array( "GET Data" => $_GET
            , "POST Data" => $post
            , "Files" => $_FILES
            , "Cookies" => $_COOKIE
            , "Session" => $_SESSION ?? array()
            , "Server/Request Data" => $server
            , "Environment Variables" => $_ENV
            );
    }

    /**
     * Replace passwort from post array with security message
     */
    private function hidePassword(array $post): array
    {
        ilSystemStyleLessVariable::class;
        if (isset($post["password"])) {
            $post["password"] = "REMOVED FOR SECURITY";
        }

        return $post;
    }

    /**
     * Shorts the php session id
     */
    private function shortenPHPSessionId(array $server): array
    {
        if (!isset($server["HTTP_COOKIE"])) {
            return $server;
        }
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
