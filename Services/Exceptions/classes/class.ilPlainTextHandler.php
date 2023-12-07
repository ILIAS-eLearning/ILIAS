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

/**
 * A Whoops error handler that prints the same content as the PrettyPageHandler but as plain text.
 * This is used for better coexistence with xdebug, see #16627.
 * @author Richard Klees <richard.klees@concepts-and-training.de>
 */
class ilPlainTextHandler extends \Whoops\Handler\PlainTextHandler
{
    protected const KEY_SPACE = 25;

    private function stripNullBytes(string $ret): string
    {
        return str_replace("\0", '', $ret);
    }

    public function generateResponse(): string
    {
        return $this->getExceptionOutput() . $this->tablesContent() . "\n";
    }

    /**
     * Get a short info about the exception.
     */
    protected function getExceptionOutput(): string
    {
        return Formatter::formatExceptionPlain($this->getInspector());
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
                    $key = str_pad((string) $key, self::KEY_SPACE);

                    // indent multiline values, first print_r, split in lines,
                    // indent all but first line, then implode again.
                    $first = true;
                    $indentation = str_pad("", self::KEY_SPACE);
                    $value = implode(
                        "\n",
                        array_map(
                            static function ($line) use (&$first, $indentation): string {
                                if ($first) {
                                    $first = false;
                                    return $line;
                                }
                                return $indentation . $line;
                            },
                            explode("\n", print_r($value, true))
                        )
                    );

                    $ret .= "$key: $value\n";
                }
            } else {
                $ret .= "empty\n";
            }
        }

        return $this->stripNullBytes($ret);
    }

    /**
     * Get the tables that should be rendered.
     */
    protected function tables(): array
    {
        return [
            "GET Data" => $_GET,
            "POST Data" => $_POST,
            "Files" => $_FILES,
            "Cookies" => $_COOKIE,
            "Session" => $_SESSION ?? [],
            "Server/Request Data" => $_SERVER,
            "Environment Variables" => $_ENV,
        ];
    }
}
