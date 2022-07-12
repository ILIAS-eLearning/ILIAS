<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 */

/**
 * Chart legend
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 */
class ilChartLegend
{
    protected string $position = "";
    protected int $columns = 0;
    protected int $margin_x = 0;
    protected int $margin_y = 0;
    protected string $background = "";
    protected float $opacity = 0;
    protected string $border = "";
    protected string $container = "";

    public function __construct()
    {
        $this->setPosition("ne");
        $this->setColumns(1);
        $this->setMargin(5, 5);
        $this->setBackground("#888");
        $this->setOpacity(0.1);
        $this->setLabelBorder("#bbb");
    }

    public function setPosition(string $a_position) : void
    {
        $all = array("ne", "nw", "se", "sw");
        if (in_array($a_position, $all)) {
            $this->position = $a_position;
        }
    }

    public function getPosition() : string
    {
        return $this->position;
    }

    /**
     * Set number of columns
     */
    public function setColumns(int $a_value) : void
    {
        $this->columns = $a_value;
    }

    /**
     * Get number of columns
     */
    public function getColumns() : int
    {
        return $this->columns;
    }

    public function setMargin(int $a_x, int $a_y) : void
    {
        $this->margin_x = $a_x;
        $this->margin_y = $a_y;
    }

    public function getMargin() : array
    {
        return array("x" => $this->margin_x, "y" => $this->margin_y);
    }

    /**
     * Set background color
     */
    public function setBackground(string $a_color) : void
    {
        if (ilChart::isValidColor($a_color)) {
            $this->background = $a_color;
        }
    }

    /**
     * Get background color
     */
    public function getBackground() : string
    {
        return $this->background;
    }

    public function setOpacity(float $a_value) : void
    {
        if ($a_value >= 0 && $a_value <= 1) {
            $this->opacity = $a_value;
        }
    }

    public function getOpacity() : float
    {
        return $this->opacity;
    }

    public function setLabelBorder(string $a_color) : void
    {
        if (ilChart::isValidColor($a_color)) {
            $this->border = $a_color;
        }
    }

    public function getLabelBorder() : string
    {
        return $this->border;
    }
    
    /**
     * Set container id
     */
    public function setContainer(string $a_value) : void
    {
        $this->container = trim($a_value);
    }
    
    /**
     * Get container id
     */
    public function getContainer() : string
    {
        return $this->container;
    }
    
    /**
     * Convert (global) properties to flot config
     */
    public function parseOptions(stdClass $a_options) : void
    {
        $a_options->show = true;
        
        $a_options->noColumns = $this->getColumns();
        $a_options->position = $this->getPosition();
        
        $margin = $this->getMargin();
        $a_options->margin = array($margin["x"], $margin["y"]);
        
        $a_options->backgroundColor = ilChart::renderColor($this->getBackground());
        $a_options->backgroundOpacity = str_replace(",", ".", (string) $this->getOpacity());
        $a_options->labelBoxBorderColor = ilChart::renderColor($this->getLabelBorder());
        
        $container = $this->getContainer();
        if ($container) {
            $a_options->container = '#' . $container;
        }
    }
}
