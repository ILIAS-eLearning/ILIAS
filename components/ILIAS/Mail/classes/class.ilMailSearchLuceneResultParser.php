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

/**
 * @author Michael Jansen <mjansen@databay.de>
 * @ingroup ServicesMail
 */
class ilMailSearchLuceneResultParser
{
    public function __construct(protected ilMailSearchResult $result, protected string $xml)
    {
    }

    public function getXml(): string
    {
        return $this->xml;
    }

    public function getResult(): ilMailSearchResult
    {
        return $this->result;
    }

    public function parse(): void
    {
        if ($this->getXml() === '') {
            return;
        }

        $hits = new SimpleXMLElement($this->getXml());
        foreach ($hits->children() as $user) {
            foreach ($user->children() as $item) {
                $fields = [];
                foreach ($item->children() as $field) {
                    $name = (string) $field['name'];
                    $content = (string) $field;
                    $fields[] = [
                        $name, $content,
                    ];
                }
                $this->getResult()->addItem((int) $item['id'], $fields);
            }
        }
    }
}
