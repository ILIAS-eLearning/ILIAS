<?php declare(strict_types=1);
/* Copyright (c) 1998-2021 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author Michael Jansen <mjansen@databay.de>
 * @ingroup ServicesMail
 */
class ilMailSearchLuceneResultParser
{
    protected ilMailSearchResult $result;
    protected string $xml;

    public function __construct(ilMailSearchResult $result, string $xml)
    {
        $this->result = $result;
        $this->xml = $xml;
    }

    public function getXml() : string
    {
        return $this->xml;
    }

    public function getResult() : ilMailSearchResult
    {
        return $this->result;
    }

    public function parse() : void
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
