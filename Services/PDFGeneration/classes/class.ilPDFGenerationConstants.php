<?php


class ilPDFGenerationConstants
{
    const HEADER_NONE = 0;
    const HEADER_TEXT = 1;
    const HEADER_HTML = 2;

    const FOOTER_NONE = 0;
    const FOOTER_TEXT = 1;
    const FOOTER_HTML = 2;

    public static function getPageSizesNames()
    {
        return array(
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
        );
    }


    public static function getOrientations()
    {
        return array(
            'Portrait' => 'Portrait' ,
            'Landscape' => 'Landscape'
        );
    }
}
