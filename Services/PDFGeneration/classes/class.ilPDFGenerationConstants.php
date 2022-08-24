<?php

declare(strict_types=1);

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

class ilPDFGenerationConstants
{
    public const HEADER_NONE = 0;
    public const HEADER_TEXT = 1;
    public const HEADER_HTML = 2;

    public const FOOTER_NONE = 0;
    public const FOOTER_TEXT = 1;
    public const FOOTER_HTML = 2;

    /**
     * @return array<string, string>
     */
    public static function getPageSizesNames(): array
    {
        return [
            'A4' => 'A4',
            'A3' => 'A3',
            'A2' => 'A2',
            'A1' => 'A1',
            'A0' => 'A0',
            'B4' => 'B4',
            'B3' => 'B3',
            'B2' => 'B2',
            'B1' => 'B1',
            'B0' => 'B0',
            'C4' => 'C4',
            'C3' => 'C3',
            'C2' => 'C2',
            'C1' => 'C1',
            'C0' => 'C0',
        ];
    }


    /**
     * @return array<string, string>
     */
    public static function getOrientations(): array
    {
        return [
            'Portrait' => 'Portrait' ,
            'Landscape' => 'Landscape'
        ];
    }
}
