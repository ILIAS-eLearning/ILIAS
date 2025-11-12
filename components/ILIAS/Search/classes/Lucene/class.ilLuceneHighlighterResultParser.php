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

    public function setMaxScore(float $score): void
    {
        $this->max_score = $score;
    }

    public function getRelevance(int $obj_id, int $sub_id, string $sub_type): float
    {
        if (!$this->getMaxScore()) {
            return 0;
        }

        $score = $this->result[$obj_id][$sub_type . '__' . $sub_id]['score'] ?? 0;
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
                $sub_type = (string) $item['type'];
                $sub_id = (string) $item['id'];

                $this->result[$obj_id][$sub_type . '__' . $sub_id]['id'] = $sub_id;
                $this->result[$obj_id][$sub_type . '__' . $sub_id]['type'] = $sub_type;

                // begin-patch mime_filter
                $score = (string) $item['absoluteScore'];
                $this->result[$obj_id][$sub_type . '__' . $sub_id]['score'] = $score;
                // end-patch mime_filter

                foreach ($item->children() as $field) {
                    $name = (string) $field['name'];
                    $this->result[$obj_id][$sub_type . '__' . $sub_id][$name] = (string) $field;
                }
            }
        }
        return true;
    }

    public function getTitle(int $obj_id, int $sub_id, string $sub_type): string
    {
        return $this->result[$obj_id][$sub_type . '__' . $sub_id]['title'] ?? '';
    }

    public function getDescription(int $obj_id, int $sub_id, string $sub_type): string
    {
        return $this->result[$obj_id][$sub_type . '__' . $sub_id]['description'] ?? '';
    }

    public function getContent(int $obj_id, int $sub_id, string $sub_type): string
    {
        return $this->result[$obj_id][$sub_type . '__' . $sub_id]['content'] ?? '';
    }

    /**
     * @return list<array{id: int, type: string}>
     */
    public function getSubItemIds(int $obj_id): array
    {
        $sub_item_ids = [];
        if (!isset($this->result[$obj_id])) {
            return [];
        }
        foreach ($this->result[$obj_id] as $data) {
            if ($data['id'] <= 0) {
                continue;
            }
            $sub_item_ids[] = ['id' => (int) $data['id'], 'type' => (string) $data['type']];
        }
        return $sub_item_ids;
    }
}
