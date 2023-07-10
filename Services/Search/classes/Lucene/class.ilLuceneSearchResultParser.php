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
 * Parses Lucene search results
 * @author  Stefan Meyer <meyer@leifos.com>
 * @ingroup ServicesSearch
 */
class ilLuceneSearchResultParser
{
    private string $xml;

    /**
     * Constructor
     */
    public function __construct(string $a_xml)
    {
        $this->xml = $a_xml;
    }


    /**
     * get xml
     * @param
     * @return string
     */
    public function getXML(): string
    {
        return $this->xml;
    }

    /**
     * Parse XML
     */
    public function parse(ilLuceneSearchResult $result): ilLuceneSearchResult
    {
        if (!strlen($this->getXML())) {
            return $result;
        }
        $hits = new SimpleXMLElement($this->getXML());
        $result->setLimit($result->getLimit() + (int) ((string) $hits['limit']));
        $result->setMaxScore((float) $hits['maxScore']);
        $result->setTotalHits((int) $hits['totalHits']);

        foreach ($hits->children() as $object) {
            if (isset($object['absoluteScore'])) {
                $result->addObject((int) $object['id'], (float) $object['absoluteScore']);
            } else {
                $result->addObject((int) $object['id'], (float) $object->Item[0]['absoluteScore']);
            }
        }
        return $result;
    }
}
