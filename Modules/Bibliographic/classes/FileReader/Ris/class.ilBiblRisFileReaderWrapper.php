<?php

use LibRIS\RISReader;

/**
 * Class ilBiblRisFileReaderWrapper
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilBiblRisFileReaderWrapper
{

    /**
     * @param $content
     * @return array
     */
    public function parseContent($content)
    {
        $RISReader = new RISReader();

        $RISReader->parseString($content);

        return $RISReader->getRecords();
    }
}
