<?php
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
* @version $Id$
*
*
* @ingroup ServicesSearch
*
*/
class ilLuceneHighlighterResultParser
{
    private $result_string = '';
    private $result = array();
    
    // begin-patch mime_filter
    private $max_score = 0;
    // end-patch mime_filter
    
    
    /**
     * Contructor
     * @return
     */
    public function __construct()
    {
    }
    
    // begin-patch mime_filter
    public function getMaxScore()
    {
        return $this->max_score;
    }
    
    public function setMaxScore($a_score)
    {
        $this->max_score = $a_score;
    }
    
    /**
     * get relevance
     * @param int obj_id
     * @return int	relevance in percent
     */
    public function getRelevance($a_obj_id, $sub_id)
    {
        if (!$this->getMaxScore()) {
            return 0;
        }
        
        $score = $this->result[$a_obj_id][$sub_id]['score'];
        return $score / $this->getMaxScore() * 100;
    }
    
    // end-patch mime_filter

    /**
     * set result xml string
     * @param
     * @return
     */
    public function setResultString($a_res)
    {
        $this->result_string = $a_res;
    }
    
    /**
     * get result xml string
     * @param
     * @return
     */
    public function getResultString()
    {
        return $this->result_string;
    }
    
    /**
     * parse
     * @return
     */
    public function parse()
    {
        if (!strlen($this->getResultString())) {
            return false;
        }
        ilLoggerFactory::getLogger('src')->debug($this->getResultString());
        $root = new SimpleXMLElement($this->getResultString());
        
        // begin-patch mime_filter
        $this->setMaxScore((string) $root['maxScore']);
        // end-patch mime_filter
        
        
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
    
    /**
     * get title
     * @param int obj_id
     * @param int sub_item
     * @return
     */
    public function getTitle($a_obj_id, $a_sub_id)
    {
        return isset($this->result[$a_obj_id][$a_sub_id]['title']) ? $this->result[$a_obj_id][$a_sub_id]['title'] : null;
    }
    
    /**
     * get description
     * @param int obj_id
     * @param int sub_item
     * @return
     */
    public function getDescription($a_obj_id, $a_sub_id)
    {
        return isset($this->result[$a_obj_id][$a_sub_id]['description']) ? $this->result[$a_obj_id][$a_sub_id]['description'] : null;
    }
    
    /**
     * get content
     * @param int obj_id
     * @param int sub_item
     * @return
     */
    public function getContent($a_obj_id, $a_sub_id)
    {
        return isset($this->result[$a_obj_id][$a_sub_id]['content']) ? $this->result[$a_obj_id][$a_sub_id]['content'] : null;
    }
    
    /**
     * get subitem ids
     * @param
     * @return
     */
    public function getSubItemIds($a_obj_id)
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
        return $sub_item_ids ? $sub_item_ids : array();
    }
}
