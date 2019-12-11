<?php
include_once("Services/Style/System/classes/Icons/class.ilSystemStyleIconColor.php");


/***
 * Bundles a set of colors into one unit to be handled in one object. Colorsets can be merged and transferred to array or strings.
 *
 * @author            Timon Amstutz <timon.amstutz@ilub.unibe.ch>
 * @version           $Id$
 *
 */
class ilSystemStyleIconColorSet
{
    /**
     * Set of colors used in this set.
     *
     * @var ilSystemStyleIconColor[]
     */
    protected $colors = [];

    /**
     * @param ilSystemStyleIconColor $color
     */
    public function addColor(ilSystemStyleIconColor $color)
    {
        $this->colors[$color->getId()] = $color;
    }

    /**
     * @return ilSystemStyleIconColor[]
     */
    public function getColors()
    {
        return $this->colors;
    }

    /**
     * @param ilSystemStyleIconColor[] $colors
     */
    public function setColors(array $colors)
    {
        $this->colors = $colors;
    }

    /**
     * @param string $id
     * @return ilSystemStyleIconColor
     * @throws ilSystemStyleException
     */
    public function getColorById($id = "")
    {
        if (!array_key_exists($id, $this->colors)) {
            throw new ilSystemStyleException(ilSystemStyleException::INVALID_ID, $id);
        }
        return $this->colors[$id];
    }

    /**
     * @param string $id
     * @return bool
     */
    public function doesColorExist($id)
    {
        return array_key_exists($id, $this->colors);
    }

    /**
     * Merges an other colorset into this one
     *
     * @param ilSystemStyleIconColorSet $color_set
     */
    public function mergeColorSet(ilSystemStyleIconColorSet $color_set)
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
    public function getColorsSortedAsArray()
    {
        $colors_categories = [];
        foreach ($this->getColors() as $color) {
            $colors_categories[$color->getDominatAspect()][] = $color;
        }
        ksort($colors_categories);
        foreach ($colors_categories as $category => $colors) {
            usort($colors_categories[$category], array("ilSystemStyleIconColor","compareColors"));
        }

        return $colors_categories;
    }

    /**
     * Returns the ids of the colors of this color set as array
     *
     * @return array [color_id]
     */
    public function asArray()
    {
        $colors = [];
        foreach ($this->getColors() as $color) {
            $colors[] = $color->getId();
        }
        return $colors;
    }

    /**
     * Returns the ids of the colors of this color set as string
     *
     * @return array
     */
    public function asString()
    {
        $colors = "";
        foreach ($this->getColors() as $color) {
            $colors .= $color->getId() . "; ";
        }
        return $colors;
    }
}
