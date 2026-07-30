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

namespace ILIAS\Init\ErrorHandling\Infrastructure\Logging;

use Whoops\Exception\Formatter;
use Whoops\Exception\Inspector;

class ContentProcessor
{
    private const int KEY_SPACE = 25;

    /**
     * @param list<string> $sensitive_fields
     */
    public function collectAndFormatContent(
        Inspector $inspector,
        array $sensitive_fields
    ): string {
        return $this->formatExceptionContent($inspector)
            . $this->formatTables($this->tablesFromSuperGlobals($sensitive_fields));
    }

    private function formatExceptionContent(Inspector $inspector): string
    {
        $message = Formatter::formatExceptionPlain($inspector);

        $exception = $inspector->getException();
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

    /**
     * @param array<string, array<string, mixed>> $tables
     */
    private function formatTables(array $tables): string
    {
        $ret = '';
        foreach ($tables as $title => $content) {
            $ret .= "\n\n-- $title --\n\n";
            if (count($content) > 0) {
                foreach ($content as $key => $value) {
                    $key = str_pad((string) $key, self::KEY_SPACE);

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

    /**
     * @param list<string> $sensitive_fields
     * @return array<string, array<string, mixed>>
     */
    private function tablesFromSuperGlobals(array $sensitive_fields): array
    {
        $post = (array) $_POST;
        $server = (array) $_SERVER;

        $post = $this->hideSensitiveData($post, $sensitive_fields);
        $server = $this->hideSensitiveData($server, $sensitive_fields);
        $server = $this->shortenPHPSessionId($server);

        return [
            'GET Data' => (array) $_GET,
            'POST Data' => $post,
            'Files' => (array) $_FILES,
            'Cookies' => (array) $_COOKIE,
            'Session' => (array) ($_SESSION ?? []),
            'Server/Request Data' => $server,
            'Environment Variables' => (array) $_ENV
        ];
    }

    /**
     * @param array<string, mixed> $super_global
     * @param list<string> $sensitive_fields
     * @return array<string, mixed>
     */
    private function hideSensitiveData(
        array $super_global,
        array $sensitive_fields
    ): array {
        foreach ($sensitive_fields as $parameter) {
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
     * @param array<string, mixed> $server
     * @return array<string, mixed>
     */
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

    private function stripNullBytes(string $ret): string
    {
        return str_replace("\0", '', $ret);
    }
}
