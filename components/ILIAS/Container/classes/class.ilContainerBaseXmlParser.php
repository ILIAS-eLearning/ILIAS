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

abstract class ilContainerBaseXmlParser extends ilSaxParser
{
    protected function trimAndStripAttribs(array $attribs): array
    {
        $ret = [];
        foreach ($attribs as $k => $v) {
            $ret[$k] = $this->trimAndStrip((string) $v);
        }
        return $ret;
    }

    protected function trimAndStrip(string $input): string
    {
        return ilUtil::stripSlashes(trim($input));
    }
}
