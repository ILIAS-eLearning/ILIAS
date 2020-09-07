<?php

/**
 * Class ilBiblRisFileReader
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilBiblRisFileReader extends ilBiblFileReaderBase implements ilBiblFileReaderInterface
{

    /**
     * @return array
     */
    public function parseContent()
    {
        global $DIC;
        $ilRisWrapper = new ilBiblRisFileReaderWrapper();

        return $ilRisWrapper->parseFile($DIC->filesystem()
                                            ->storage()
                                            ->readStream($this->path_to_file)
                                            ->getMetadata('uri'));
    }
}
