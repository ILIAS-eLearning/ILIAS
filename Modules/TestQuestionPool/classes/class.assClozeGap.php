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

use ILIAS\Refinery\Transformation;
use ILIAS\Refinery\Random\Transformation\ShuffleTransformation;

include_once "./Modules/Test/classes/inc.AssessmentConstants.php";

/**
 * Class for cloze question gaps
 *
 * assClozeGap is a class for the abstraction of cloze gaps. It represents a text gap.
 *
 * @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
 * @author		Maximilian Becker <mbecker@databay.de>
 *
 * @version	$Id$
 *
 * @ingroup ModulesTestQuestionPool
*/
class assClozeGap
{
    public const TYPE_TEXT = 0;
    public const TYPE_SELECT = 1;
    public const TYPE_NUMERIC = 2;
    private ?Transformation $shuffler = null;

    public int $type;

    /**
     * List of items in the gap
     *
     * List of items in the gap
     *
     * @var array
     */
    public $items;

    /**
     * Indicates if the items should be shuffled in the output
     *
     * @var boolean
     */
    public $shuffle;

    private $gap_size = 0;

    /**
     * assClozeGap constructor
     *
     * @param int $a_type
     *
     */
    public function __construct($a_type)
    {
        $this->type = (int) $a_type;
        $this->items = [];
        $this->shuffle = true;
    }

    /**
     * @see $type for mapping.
     */
    public function getType(): int
    {
        return $this->type;
    }

    public function isTextGap(): bool
    {
        return $this->type === self::TYPE_TEXT;
    }

    public function isSelectGap(): bool
    {
        return $this->type === self::TYPE_SELECT;
    }

    public function isNumericGap(): bool
    {
        return $this->type === self::TYPE_NUMERIC;
    }

    /**
     * Sets the cloze gap type
     *
     * @param integer $a_type cloze gap type
     *
     * @see $type for mapping.
     */
    public function setType($a_type = 0): void
    {
        $this->type = $a_type;
    }

    /**
     * Gets the items of a cloze gap
     *
     * @param Transformation $shuffler
     * @return assAnswerCloze[] The list of items
     */
    public function getItems(Transformation $shuffler, ?int $gap_index = null): array
    {
        if (!$this->getShuffle()) {
            return $this->items;
        }

        if ($gap_index === null) {
            return $shuffler->transform($this->items);
        }

        $items = $this->items;
        for ($i = -2; $i < $gap_index; $i++) {
            $items = $shuffler->transform($items);
        }

        return $items;
    }

    /**
    * Gets the items of a cloze gap
    *
    * Gets the items of a cloze gap
    *
    * @return array The list of items
    * @access public
    * @see $items
    */
    public function getItemsRaw(): array
    {
        return $this->items;
    }

    /**
    * Gets the item count
    *
    * Gets the item count
    *
    * @return integer The item count
    * @access public
    * @see $items
    */
    public function getItemCount(): int
    {
        return count($this->items);
    }

    /**
    * Adds a gap item
    *
    * Adds a gap item
    *
    * @param object $a_item Cloze gap item
    * @access public
    * @see $items
    */
    public function addItem($a_item): void
    {
        $order = $a_item->getOrder();
        if (array_key_exists($order, $this->items)) {
            $newitems = [];
            for ($i = 0; $i < $order; $i++) {
                array_push($newitems, $this->items[$i]);
            }
            array_push($newitems, $a_item);
            for ($i = $order, $iMax = count($this->items); $i < $iMax; $i++) {
                array_push($newitems, $this->items[$i]);
            }
            $i = 0;
            foreach ($newitems as $idx => $item) {
                $newitems[$idx]->setOrder($i);
                $i++;
            }
            $this->items = $newitems;
        } else {
            array_push($this->items, $a_item);
        }
    }

    /**
    * Sets the points for a given item
    *
    * Sets the points for a given item
    *
    * @param integer $order Order of the item
    * @param double $points Points of the item
    * @access public
    * @see $items
    */
    public function setItemPoints($order, $points): void
    {
        foreach ($this->items as $key => $item) {
            if ($item->getOrder() == $order) {
                $item->setPoints($points);
            }
        }
    }

    /**
    * Deletes an item at a given index
    *
    * Deletes an item at a given index
    *
    * @param integer $0order Order of the item
    * @access public
    * @see $items
    */
    public function deleteItem($order): void
    {
        if (array_key_exists($order, $this->items)) {
            unset($this->items[$order]);
            $order = 0;
            foreach ($this->items as $key => $item) {
                $this->items[$key]->setOrder($order);
                $order++;
            }
        }
    }

    /**
    * Sets the lower bound for a given item
    *
    * Sets the lower bound for a given item
    *
    * @param integer $order Order of the item
    * @param double $bound Lower bounds of the item
    * @access public
    * @see $items
    */
    public function setItemLowerBound($order, $bound): void
    {
        foreach ($this->items as $key => $item) {
            if ($item->getOrder() == $order) {
                $item->setLowerBound($bound);
            }
        }
    }

    /**
    * Sets the upper bound for a given item
    *
    * Sets the upper bound for a given item
    *
    * @param integer $order Order of the item
    * @param double $bound Upper bound of the item
    * @access public
    * @see $items
    */
    public function setItemUpperBound($order, $bound): void
    {
        foreach ($this->items as $key => $item) {
            if ($item->getOrder() == $order) {
                $item->setUpperBound($bound);
            }
        }
    }

    /**
     * Gets the item with a given index
     *
     * Gets the item with a given index
     *
     * @param integer $a_index Item index
     * @access public
     * @see $items
     * @return assAnswerCloze|null
     */
    public function getItem($a_index): ?assAnswerCloze
    {
        if (array_key_exists($a_index, $this->items)) {
            return $this->items[$a_index];
        } else {
            return null;
        }
    }

    /**
    * Removes all gap items
    *
    * Removes all gap items
    *
    * @access public
    * @see $items
    */
    public function clearItems(): void
    {
        $this->items = [];
    }

    /**
     * Sets the shuffle state of the items
     *
     * Sets the shuffle state of the items
     *
     * @param boolean $a_shuffle Shuffle state
     */
    public function setShuffle($a_shuffle = true): void
    {
        $this->shuffle = (bool) $a_shuffle;
    }

    /**
     * Gets the shuffle state of the items
     *
     * @return boolean Shuffle state
     */
    public function getShuffle(): bool
    {
        return $this->shuffle;
    }

    /**
    * Returns the maximum width of the gap
    *
    * Returns the maximum width of the gap
    *
    * @return integer The maximum width of the gap defined by the longest answer
    * @access public
    */
    public function getMaxWidth(): int
    {
        $maxwidth = 0;
        foreach ($this->items as $item) {
            if (strlen($item->getAnswertext()) > $maxwidth) {
                $maxwidth = strlen($item->getAnswertext());
            }
        }
        return $maxwidth;
    }

    /**
    * Returns the indexes of the best solutions for the gap
    *
    * Returns the indexes of the best solutions for the gap
    *
    * @return array The indexs of the best solutions
    * @access public
    */
    public function getBestSolutionIndexes(): array
    {
        $maxpoints = 0;
        foreach ($this->items as $key => $item) {
            if ($item->getPoints() > $maxpoints) {
                $maxpoints = $item->getPoints();
            }
        }
        $keys = [];
        foreach ($this->items as $key => $item) {
            if ($item->getPoints() == $maxpoints) {
                array_push($keys, $key);
            }
        }
        return $keys;
    }

    /**
     * @param Transformation $shuffler
     * @param null | array $combinations
     * @return string
     */
    public function getBestSolutionOutput(Transformation $shuffler, $combinations = null): string
    {
        global $DIC;
        $lng = $DIC['lng'];
        switch ($this->getType()) {
            case CLOZE_TEXT:
            case CLOZE_SELECT:
                $best_solutions = [];
                if ($combinations !== null && $combinations['best_solution'] == 1) {
                    $best_solutions[$combinations['points']] = [];
                    array_push($best_solutions[$combinations['points']], $combinations['answer']);
                } else {
                    foreach ($this->getItems($shuffler) as $answer) {
                        $points_string_for_key = (string) $answer->getPoints();
                        if (isset($best_solutions[$points_string_for_key]) && is_array($best_solutions[$points_string_for_key])) {
                            array_push($best_solutions[$points_string_for_key], $answer->getAnswertext());
                        } else {
                            $best_solutions[$points_string_for_key] = [];
                            array_push($best_solutions[$points_string_for_key], $answer->getAnswertext());
                        }
                    }
                }

                krsort($best_solutions, SORT_NUMERIC);
                reset($best_solutions);
                $found = current($best_solutions);
                return join(" " . $lng->txt("or") . " ", $found);
                break;
            case CLOZE_NUMERIC:
                $maxpoints = 0;
                $foundvalue = "";
                foreach ($this->getItems($shuffler) as $answer) {
                    if ($answer->getPoints() >= $maxpoints) {
                        $maxpoints = $answer->getPoints();
                        $foundvalue = $answer->getAnswertext();
                    }
                }
                return $foundvalue;
                break;
            default:
                return "";
        }
    }

    public function setGapSize(int $gap_size): void
    {
        $this->gap_size = $gap_size;
    }

    /**
     * @return int
     */
    public function getGapSize(): int
    {
        return (int)$this->gap_size;
    }

    public function numericRangeExists(): bool
    {
        if ($this->getType() != CLOZE_NUMERIC) {
            return false;
        }

        require_once 'Services/Math/classes/class.EvalMath.php';
        $math = new EvalMath();

        $item = $this->getItem(0);
        $lowerBound = $math->evaluate($item->getLowerBound());
        $upperBound = $math->evaluate($item->getUpperBound());
        $preciseValue = $math->evaluate($item->getAnswertext());

        if ($lowerBound < $preciseValue || $upperBound > $preciseValue) {
            return true;
        }

        return false;
    }
}
