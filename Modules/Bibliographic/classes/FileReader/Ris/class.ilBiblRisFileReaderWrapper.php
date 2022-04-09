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

        $RISReader->parseString($content);

        return $RISReader->getRecords();
    }
}
