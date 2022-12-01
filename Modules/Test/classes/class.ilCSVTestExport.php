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

/**
 * @author Fabian Helfer <fhelfer@databay.de>
 */
class ilCSVTestExport extends ilTestExportAbstract
{
    public function export($test_obj): string
    {
        $rows = $this->getDatarows($test_obj);
        $separator = ";";
        $csv = "";
        foreach ($rows as $evalrow) {
            $csvrow = $test_obj->processCSVRow($evalrow, true, $separator);
            $csv .= implode($separator, $csvrow) . "\n";
        }

        if ($this->deliver) {
            ilUtil::deliverData($csv, ilFileUtils::getASCIIFilename($this->test_obj->getTitle() . "_results.csv"));
            exit;
        }

        return $csv;
    }
}
