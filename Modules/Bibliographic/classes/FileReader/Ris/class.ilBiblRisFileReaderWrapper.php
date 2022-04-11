<?php

use LibRIS\RISReader;

/**
 * Class ilBiblRisFileReaderWrapper
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilBiblRisFileReaderWrapper
{

    /**
     * @param string $content
     * @return mixed[]
     */
    public function parseContent($content) : array
    {
        $RISReader = new RISReader();
        $re = '/\n|\r/m';
        $content = preg_replace($re, RISReader::RIS_EOL, $content);
        $RISReader->parseString($content);

        return $RISReader->getRecords();
    }
}
