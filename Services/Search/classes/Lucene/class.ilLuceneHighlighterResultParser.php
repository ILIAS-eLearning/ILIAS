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
    private float $max_score = 0;



    public function getMaxScore(): float
    {
        return $this->max_score;
    }

    public function setMaxScore(float $a_score): void
    {
        $this->max_score = $a_score;
    }

    public function getRelevance(int $a_obj_id, int $sub_id): float
    {
        if (!$this->getMaxScore()) {
            return 0;
        }

        $score = $this->result[$a_obj_id][$sub_id]['score'];
        return $score / $this->getMaxScore() * 100;
    }

    public function setResultString(string $a_res): void
    {
        $this->result_string = $a_res;
    }

    public function getResultString(): string
    {
        return $this->result_string;
    }

    /**
     * parse
     * @return bool
     */
    public function parse(): bool
    {
        if (!strlen($this->getResultString())) {
            return false;
        }
        ilLoggerFactory::getLogger('src')->debug($this->getResultString());
        $root = new SimpleXMLElement($this->getResultString());
        $this->setMaxScore((float) $root['maxScore']);
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

    public function getTitle(int $a_obj_id, int $a_sub_id): string
    {
        return $this->result[$a_obj_id][$a_sub_id]['title'] ?? '';
    }

    public function getDescription(int $a_obj_id, int $a_sub_id): string
    {
        return $this->result[$a_obj_id][$a_sub_id]['description'] ?? '';
    }

    public function getContent(int $a_obj_id, int $a_sub_id): string
    {
        return $this->result[$a_obj_id][$a_sub_id]['content'] ?? '';
    }

    /**
     * @return int[]
     */
    public function getSubItemIds(int $a_obj_id): array
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
