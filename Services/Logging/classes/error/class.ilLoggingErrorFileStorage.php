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

declare(strict_types=1);

use Whoops\Exception\Formatter;
use Whoops\Exception\Inspector;

class ilLoggingErrorFileStorage
{
    protected const KEY_SPACE = 25;
    protected const FILE_FORMAT = '.log';

    protected Inspector $inspector;
    protected string $file_path;
    protected string $file_name;

    public function __construct(Inspector $inspector, string $file_path, string $file_name)
    {
        $this->inspector = $inspector;
        $this->file_path = $file_path;
        $this->file_name = $file_name;
    }

    private function stripNullBytes(string $ret): string
    {
        return str_replace("\0", '', $ret);
    }

    protected function createDir(): void
    {
        if (!is_dir($this->file_path)) {
            ilFileUtils::makeDirParents($this->file_path);
        }
    }

    protected function content(): string
    {
        return $this->pageHeader()
            . $this->exceptionContent()
            . $this->tablesContent();
    }

    public function write(): void
    {
        $this->createDir();

        $file_name = $this->file_path . '/' . $this->file_name . self::FILE_FORMAT;
        $stream = fopen($file_name, 'wb+');
        fwrite($stream, $this->content());
        fclose($stream);
        chmod($file_name, 0755);
    }

    protected function pageHeader(): string
    {
        return '';
    }

    protected function exceptionContent(): string
    {
        $message = Formatter::formatExceptionPlain($this->inspector);

        $exception = $this->inspector->getException();
        $previous = $exception->getPrevious();
        while ($previous) {
            $message .= "\n\nCaused by\n" . sprintf(
                    '%s: %s in file %s on line %d',
                    get_class($previous),
                    $previous->getMessage(),
                    $previous->getFile(),
                    $previous->getLine()
                );
            $previous = $previous->getPrevious();
        }

        return $message;
    }

    protected function tablesContent(): string
    {
        $ret = '';
        foreach ($this->tables() as $title => $content) {
            $ret .= "\n\n-- $title --\n\n";
            if (count($content) > 0) {
                foreach ($content as $key => $value) {
                    $key = str_pad($key, self::KEY_SPACE);

                    // indent multiline values, first print_r, split in lines,
                    // indent all but first line, then implode again.
                    $first = true;
                    $indentation = str_pad('', self::KEY_SPACE);
                    $value = implode("\n", array_map(static function ($line) use (&$first, $indentation): string {
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

        return $this->stripNullBytes($ret);
    }

    protected function tables(): array
    {
        $post = $_POST;
        $server = $_SERVER;

        $post = $this->hidePassword($post);
        $server = $this->shortenPHPSessionId($server);

        return [
            'GET Data' => $_GET,
            'POST Data' => $post,
            'Files' => $_FILES,
            'Cookies' => $_COOKIE,
            'Session' => $_SESSION ?? [],
            'Server/Request Data' => $server,
            'Environment Variables' => $_ENV
        ];
    }

    private function hidePassword(array $post): array
    {
        if (isset($post['password'])) {
            $post['password'] = 'REMOVED FOR SECURITY';
        }

        return $post;
    }

    private function shortenPHPSessionId(array $server): array
    {
        if (!isset($server['HTTP_COOKIE'])) {
            return $server;
        }
        $cookie_content = $server['HTTP_COOKIE'];
        $cookie_content = explode(';', $cookie_content);

        foreach ($cookie_content as $key => $content) {
            $content_array = explode('=', $content);
            if (trim($content_array[0]) === session_name()) {
                $content_array[1] = substr($content_array[1], 0, 5) . ' (SHORTENED FOR SECURITY)';
                $cookie_content[$key] = implode('=', $content_array);
            }
        }

        $server['HTTP_COOKIE'] = implode(';', $cookie_content);

        return $server;
    }
}
