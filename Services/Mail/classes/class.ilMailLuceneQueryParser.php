<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once 'Services/Search/classes/Lucene/class.ilLuceneQueryParser.php';

/**
 * @author  Michael Jansen <mjansen@databay.de>
 * @version $Id$
 * @ingroup ServicesMail
 */
class ilMailLuceneQueryParser extends ilLuceneQueryParser
{
    /**
     * @var array
     */
    protected $fields = array();
    
    /**
     *
     */
    public function parse()
    {
        if ($this->getFields()) {
            $queried_fields = array();
            foreach ($this->getFields() as $field => $status) {
                if ($status) {
                    $queried_fields[] = $field . ':' . $this->query_string;
                }
            }

            if ($queried_fields) {
                $this->parsed_query = implode(' OR ', $queried_fields);
            } else {
                $this->parsed_query = $this->query_string;
            }
        } else {
            $this->parsed_query = $this->query_string;
        }
    }

    /**
     * @param array $fields
     */
    public function setFields($fields)
    {
        $this->fields = $fields;
    }

    /**
     * @return array
     */
    public function getFields()
    {
        return $this->fields;
    }
}
