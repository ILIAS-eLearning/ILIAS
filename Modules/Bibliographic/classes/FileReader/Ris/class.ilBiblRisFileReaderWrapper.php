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
 
use LibRIS\RISReader;

/**
 * Class ilBiblRisFileReaderWrapper
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilBiblRisFileReaderWrapper
{

    /**
     * @return mixed[]
     */
    public function parseContent(string $content) : array
    {
        $RISReader = new RISReader();
        $re = '/\n|\r/m';
        $content = preg_replace($re, RISReader::RIS_EOL, $content);
        $RISReader->parseString($content);

        return $RISReader->getRecords();
    }
}
