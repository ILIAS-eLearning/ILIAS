<?php

/**
 * Class ilBiblRisFileReader
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilBiblRisFileReader extends ilBiblFileReaderBase implements ilBiblFileReaderInterface
{

    /**
     * @return array
     */
    public function parseContent() : array
    {
        return (new ilBiblRisFileReaderWrapper())->parseContent($this->file_content);
    }
}
