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

namespace ILIAS\COPage\Xsl;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class XslManager
{
    public function __construct()
    {
    }

    public function process(
        string $xml,
        array $params
    ): string {
        $xslt = new \XSLTProcessor();
        $xsl = file_get_contents("./Services/COPage/xsl/page.xsl");
        $xslt_domdoc = new \DomDocument();
        $xslt_domdoc->loadXML($xsl);
        $xslt->importStylesheet($xslt_domdoc);
        foreach ($params as $key => $value) {
            $xslt->setParameter("", $key, (string) $value);
        }
        $xml_domdoc = new \DomDocument();
        $xml_domdoc->loadXML($xml);
        // show warnings again due to discussion in #12866
        $result = $xslt->transformToXml($xml_domdoc);
        unset($xslt);
        return $result;
    }
}
