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

declare(strict_types=1);

class ilCertificateXlstProcess
{
    /**
     * @param array{"/_xsl": string, "/_xml": string} $args
     * @param array<string, scalar> $params
     */
    public function process(array $args, array $params): string
    {
        $processor = new XSLTProcessor();

        $xslt_domdoc = new DomDocument();
        $xslt_domdoc->loadXML($args['/_xsl']);
        $processor->importStyleSheet($xslt_domdoc);

        foreach ($params as $key => $value) {
            $processor->setParameter('', $key, (string) $value);
        }

        $xml_domdoc = new DomDocument();
        $xml_domdoc->loadXML($args['/_xml']);

        return $processor->transformToXML($xml_domdoc);
    }
}
