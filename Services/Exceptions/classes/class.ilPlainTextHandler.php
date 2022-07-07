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

use Whoops\Handler\Handler;
use Whoops\Exception\Formatter;

/**
 * A Whoops error handler that prints the same content as the PrettyPageHandler but as plain text.
 * This is used for better coexistence with xdebug, see #16627.
 * @author Richard Klees <richard.klees@concepts-and-training.de>
 */
class ilPlainTextHandler extends Handler
{
    protected const KEY_SPACE = 25;

    /**
     * Last missing method from HandlerInterface.
     */
    public function handle() : ?int
    {
        header("Content-Type: text/plain");
        echo "<pre>\n";
        echo $this->content();
        echo "</pre>\n";
        return null;
    }

    /**
     * Assemble the output for this handler.
     */
    protected function content() : string
    {
        return $this->pageHeader()
            . $this->exceptionContent()
            . $this->tablesContent();
    }

    /**
     * Get the header for the page.
     */
    protected function pageHeader() : string
    {
        return "";
    }

    /**
     * Get a short info about the exception.
     */
    protected function exceptionContent() : string
    {
        return Formatter::formatExceptionPlain($this->getInspector());
    }

    /**
     * Get the header for the page.
     */
    protected function tablesContent() : string
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
                    $value = implode(
                        "\n",
                        array_map(
                            static function ($line) use (&$first, $indentation) : string {
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
        return $ret;
    }

    /**
     * Get the tables that should be rendered.
     */
    protected function tables() : array
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
