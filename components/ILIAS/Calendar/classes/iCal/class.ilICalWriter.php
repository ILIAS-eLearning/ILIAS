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

class ilICalWriter
{
    protected const LINEBREAK = "\r\n";

    // minus one to fix multi line breaks.
    protected const LINE_SIZE = 74;
    protected const BEGIN_LINE_WHITESPACE = ' ';
    protected const EMPTY = '';


    /**
     * @var string[]
     */
    protected array $lines;

    public function __construct()
    {
        $this->lines = [];
    }

    public static function escapeText(string $a_text): string
    {
        $a_text = str_replace("\r\n", '\\n', $a_text);

        return preg_replace(
            array(
                '/\\\/',
                '/;/',
                '/,/',
            ),
            array(
                '\\',
                '\;',
                '\,',
            ),
            $a_text
        );
    }

    public function addLine(string $a_line): void
    {
        // use multibyte split
        $chunks = [];
        $len = ilStr::strLen($a_line);
        while ($len) {
            $chunks[] = ilStr::subStr($a_line, 0, self::LINE_SIZE);
            $a_line = ilStr::subStr($a_line, self::LINE_SIZE, $len);
            $len = ilStr::strLen($a_line);
        }

        for ($i = 0; $i < count($chunks); $i++) {
            $line = ($i > 0) ? self::BEGIN_LINE_WHITESPACE : self::EMPTY;
            $line .= $chunks[$i];
            $line .= (isset($chunks[$i + 1]) || ($i + 1) === count($chunks)) ? self::LINEBREAK : self::EMPTY;
            $this->lines[] = $line;
        }
    }

    public function byteCount(): int
    {
        return strlen($this->__toString());
    }

    public function clear(): void
    {
        $this->lines = [];
    }

    public function append(ilICalWriter $other): void
    {
        $this->lines = array_merge($this->lines, $other->lines);
    }

    public function __toString(): string
    {
        return implode('', $this->lines);
    }
}
