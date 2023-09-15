<?php

declare(strict_types=1);

/*
    +-----------------------------------------------------------------------------+
    | ILIAS open source                                                           |
    +-----------------------------------------------------------------------------+
    | Copyright (c) 1998-2001 ILIAS open source, University of Cologne            |
    |                                                                             |
    | This program is free software; you can redistribute it and/or               |
    | modify it under the terms of the GNU General Public License                 |
    | as published by the Free Software Foundation; either version 2              |
    | of the License, or (at your option) any later version.                      |
    |                                                                             |
    | This program is distributed in the hope that it will be useful,             |
    | but WITHOUT ANY WARRANTY; without even the implied warranty of              |
    | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
    | GNU General Public License for more details.                                |
    |                                                                             |
    | You should have received a copy of the GNU General Public License           |
    | along with this program; if not, write to the Free Software                 |
    | Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
    +-----------------------------------------------------------------------------+
*/

class ilICalWriter
{
    protected const LINEBREAK = "\r\n";

    // minus one to fix multi line breaks.
    protected const LINE_SIZE = 74;
    protected const BEGIN_LINE_WHITESPACE = ' ';

    private string $ical = '';

    public function __construct()
    {
        $this->ical = '';
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
        $chunks = array();
        $len = ilStr::strLen($a_line);
        while ($len) {
            $chunks[] = ilStr::subStr($a_line, 0, self::LINE_SIZE);
            $a_line = ilStr::subStr($a_line, self::LINE_SIZE, $len);
            $len = ilStr::strLen($a_line);
        }

        for ($i = 0; $i < count($chunks); $i++) {
            $this->ical .= $chunks[$i];
            if (isset($chunks[$i + 1])) {
                $this->ical .= self::LINEBREAK;
                $this->ical .= self::BEGIN_LINE_WHITESPACE;
            }
        }
        $this->ical .= self::LINEBREAK;
    }

    /**
     * Return ical string
     */
    public function __toString()
    {
        return $this->ical;
    }
}
