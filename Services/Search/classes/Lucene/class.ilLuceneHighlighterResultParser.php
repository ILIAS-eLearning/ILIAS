<?php declare(strict_types=1);
/*
    +-----------------------------------------------------------------------------+
    | ILIAS open source                                                           |
    +-----------------------------------------------------------------------------+
    | Copyright (c) 1998-2006 ILIAS open source, University of Cologne            |
    |                                                                             |
    | This program is free software; you can redistribute it and/or               |
    | modify it under the terms of the GNU General Public License                 |
    | as published by the Free Software Foundation; either version 2              |
    | of the License, or (at your option) any later version.                      |
    |                                                                             |
    | This program is distributed in the hope that it will be useful,             |
    | but WITHOUT ANY WARRANTY; without even the implied warranty of              |
    | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
    | GNU General Public License for more details.                                |
    |                                                                             |
    | You should have received a copy of the GNU General Public License           |
    | along with this program; if not, write to the Free Software                 |
    | Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
    +-----------------------------------------------------------------------------+
*/

/**
* Parses result XML from lucene search highlight
*
* @author Stefan Meyer <meyer@leifos.com>
*
*
* @ingroup ServicesSearch
*
*/
class ilLuceneHighlighterResultParser
{
    private string $result_string = '';
    private array $result = [];
    private int $max_score = 0;

    

    public function getMaxScore() : int
    {
        return $this->max_score;
    }
    
    public function setMaxScore(int $a_score) : void
    {
        $this->max_score = $a_score;
    }
    
    public function getRelevance(int $a_obj_id, int $sub_id) : float
    {
        if (!$this->getMaxScore()) {
            return 0;
        }
        
        $score = $this->result[$a_obj_id][$sub_id]['score'];
        return $score / $this->getMaxScore() * 100;
    }
    
    public function setResultString(string $a_res) : void
    {
        $this->result_string = $a_res;
    }
    
    public function getResultString() : string
    {
        return $this->result_string;
    }
    
    /**
     * parse
     * @return bool
     */
    public function parse() : bool
    {
        if (!strlen($this->getResultString())) {
            return false;
        }
        ilLoggerFactory::getLogger('src')->debug($this->getResultString());
        $root = new SimpleXMLElement($this->getResultString());
        
        $this->setMaxScore((int) $root['maxScore']);
        foreach ($root->children() as $object) {
            $obj_id = (string) $object['id'];
            foreach ($object->children() as $item) {
                $sub_id = (string) $item['id'];
                
                // begin-patch mime_filter
                $score = (string) $item['absoluteScore'];
                $this->result[$obj_id][$sub_id]['score'] = $score;
                // end-patch mime_filter
                
                foreach ($item->children() as $field) {
                    $name = (string) $field['name'];
                    $this->result[$obj_id][$sub_id][$name] = (string) $field;
                }
            }
        }
        return true;
    }
    
    public function getTitle(int $a_obj_id, int $a_sub_id) : string
    {
        return $this->result[$a_obj_id][$a_sub_id]['title'] ?? '';
    }
    
    public function getDescription(int $a_obj_id, int $a_sub_id) : string
    {
        return $this->result[$a_obj_id][$a_sub_id]['description'] ?? '';
    }
    
    public function getContent(int $a_obj_id, int $a_sub_id) : string
    {
        return $this->result[$a_obj_id][$a_sub_id]['content'] ?? '';
    }
    
    /**
     * @return int[]
     */
    public function getSubItemIds(int $a_obj_id) : array
    {
        $sub_item_ids = array();
        if (!isset($this->result[$a_obj_id])) {
            return array();
        }
        foreach ($this->result[$a_obj_id] as $sub_item_id => $data) {
            if ($sub_item_id) {
                $sub_item_ids[] = $sub_item_id;
            }
        }
        return $sub_item_ids;
    }
}
