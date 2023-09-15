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

/***
 * Bundles a set of colors into one unit to be handled in one object. Colorsets can be merged and transferred to array or strings.
 */
class ilSystemStyleIconColorSet
{
    /**
     * Set of colors used in this set.
     *
     * @var ilSystemStyleIconColor[]
     */
    protected array $colors = [];


    public function addColor(ilSystemStyleIconColor $color): void
    {
        $this->colors[$color->getId()] = $color;
    }

    /**
     * @return ilSystemStyleIconColor[]
     */
    public function getColors(): array
    {
        return $this->colors;
    }

    /**
     * @param ilSystemStyleIconColor[] $colors
     */
    public function setColors(array $colors): void
    {
        $this->colors = $colors;
    }

    /**
     * @throws ilSystemStyleException
     */
    public function getColorById(string $id = ''): ilSystemStyleIconColor
    {
        if (!array_key_exists($id, $this->colors)) {
            throw new ilSystemStyleException(ilSystemStyleException::INVALID_ID, $id);
        }
        return $this->colors[$id];
    }

    public function doesColorExist(string $id): bool
    {
        return array_key_exists($id, $this->colors);
    }

    /**
     * Merges an other colorset into this one
     */
    public function mergeColorSet(ilSystemStyleIconColorSet $color_set): void
    {
        foreach ($color_set->getColors() as $color) {
            if (!$this->doesColorExist($color->getId())) {
                $this->addColor($color);
            }
        }
    }

    /**
     * Orders and sorts the colors to be displayed in GUI (form)
     * @return array [CategoryOfColor][color]
     */
    public function getColorsSortedAsArray(): array
    {
        $colors_categories = [];
        foreach ($this->getColors() as $color) {
            $colors_categories[$color->getDominatAspect()][] = $color;
        }
        ksort($colors_categories);
        foreach ($colors_categories as $category => $colors) {
            usort($colors_categories[$category], ['ilSystemStyleIconColor','compareColors']);
        }

        return $colors_categories;
    }

    /**
     * Returns the ids of the colors of this color set as array
     */
    public function asArray(): array
    {
        $colors = [];
        foreach ($this->getColors() as $color) {
            $colors[] = $color->getId();
        }
        return $colors;
    }

    /**
     * Returns the ids of the colors of this color set as string
     */
    public function asString(): string
    {
        $colors = '';
        foreach ($this->getColors() as $color) {
            $colors .= $color->getId() . '; ';
        }
        return $colors;
    }
}
