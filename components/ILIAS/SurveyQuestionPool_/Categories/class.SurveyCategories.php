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

/**
 * Class SurveyCategories
 * @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
 */
class SurveyCategories
{
    protected ilLogger $log;

    /**
     * An array containing the categories of a nominal or
     * ordinal question object
     */
    public array $categories;

    public function __construct()
    {
        $this->categories = array();
        $this->log = ilLoggerFactory::getLogger("svy");
    }

    public function getCategoryCount(): int
    {
        return count($this->categories);
    }

    /**
     * Adds a category at a given position
     * @param int $position The position of the category (starting with index 0)
     */
    public function addCategoryAtPosition(
        string $categoryname,
        int $position,
        int $other = 0,
        int $neutral = 0,
        ?string $label = null
    ): void {
        if (array_key_exists($position, $this->categories)) {
            $head = array_slice($this->categories, 0, $position);
            $tail = array_slice($this->categories, $position);
            $this->categories = array_merge($head, array(new ilSurveyCategory($categoryname, $other, $neutral, $label)), $tail);
        } else {
            $this->categories[] = new ilSurveyCategory($categoryname, $other, $neutral, $label);
        }
    }

    public function moveCategoryUp(int $index): void
    {
        if ($index > 0) {
            $temp = $this->categories[$index - 1];
            $this->categories[$index - 1] = $this->categories[$index];
            $this->categories[$index] = $temp;
        }
    }

    public function moveCategoryDown(int $index): void
    {
        if ($index < (count($this->categories) - 1)) {
            $temp = $this->categories[$index + 1];
            $this->categories[$index + 1] = $this->categories[$index];
            $this->categories[$index] = $temp;
        }
    }

    public function addCategory(
        string $categoryname,
        int $other = 0,
        int $neutral = 0,
        ?string $label = null,
        ?int $scale = null
    ): void {
        $this->categories[] = new ilSurveyCategory($categoryname, $other, $neutral, $label, $scale);
    }

    /**
     * @param array $categories array with categories
     */
    public function addCategoryArray(array $categories): void
    {
        $this->categories = array_merge($this->categories, $categories);
    }

    public function removeCategory(int $index): void
    {
        unset($this->categories[$index]);
        $this->categories = array_values($this->categories);
    }

    /**
     * @param int[] $array index positions
     */
    public function removeCategories(array $array): void
    {
        foreach ($array as $index) {
            unset($this->categories[$index]);
        }
        $this->categories = array_values($this->categories);
    }

    public function removeCategoryWithName(
        string $name
    ): void {
        foreach ($this->categories as $index => $category) {
            if (strcmp($category->title, $name) == 0) {
                $this->removeCategory($index);
                return;
            }
        }
    }

    public function getCategory(
        int $index
    ): ?ilSurveyCategory {
        return $this->categories[$index] ?? null;
    }

    public function getCategoryForScale(
        int $scale
    ): ?ilSurveyCategory {
        foreach ($this->categories as $cat) {
            if ($cat->scale == $scale) {
                return $cat;
            }
        }
        return null;
    }

    public function getCategoryIndex(
        string $name
    ): ?int {
        foreach ($this->categories as $index => $category) {
            if (strcmp($category->title, $name) == 0) {
                return $index;
            }
        }
        return null;
    }

    public function getIndex(ilSurveyCategory $category): ?int
    {
        foreach ($this->categories as $index => $cat) {
            if ($cat == $category) {
                return $index;
            }
        }
        return null;
    }

    public function getNewScale(): int
    {
        $max = 0;
        foreach ($this->categories as $index => $category) {
            if (is_object($category) && $category->scale > 0) {
                if ($category->scale > $max) {
                    $max = $category->scale;
                }
            }
        }
        return $max + 1;
    }

    // note, if the index is not found, we get a new scale back
    public function getScale(
        int $index
    ): int {
        $obj = $this->categories[$index];
        if (is_object($obj) && $obj->scale > 0) {
            $this->log->debug("getScale has scale =" . $obj->scale);
        } else {
            $obj->scale = $this->getNewScale();
            $this->log->debug("getScale needed new scale, scale =" . $obj->scale);
        }
        return $obj->scale;
    }

    public function flushCategories(): void
    {
        $this->categories = array();
    }

    public function getCategories(): array
    {
        return $this->categories;
    }
}
