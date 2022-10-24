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
 * Simple panel class
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilPanelGUI
{
    public const PANEL_STYLE_PRIMARY = 0;
    public const PANEL_STYLE_SECONDARY = 1;
    public const HEADING_STYLE_SUBHEADING = 0;
    public const HEADING_STYLE_BLOCK = 1;
    public const FOOTER_STYLE_BLOCK = 0;

    protected string $heading = "";
    protected string $body = "";
    protected string $footer = "";
    protected int $panel_style = 0;
    protected int $heading_style = 0;
    protected int $footer_style = 0;

    protected function __construct()
    {
    }

    public static function getInstance(): self
    {
        return new ilPanelGUI();
    }

    public function setHeading(string $a_val): void
    {
        $this->heading = $a_val;
    }

    public function getHeading(): string
    {
        return $this->heading;
    }

    public function setBody(string $a_val): void
    {
        $this->body = $a_val;
    }

    public function getBody(): string
    {
        return $this->body;
    }

    public function setFooter(string $a_val): void
    {
        $this->footer = $a_val;
    }

    public function getFooter(): string
    {
        return $this->footer;
    }

    public function setPanelStyle(int $a_val): void
    {
        $this->panel_style = $a_val;
    }

    public function getPanelStyle(): int
    {
        return $this->panel_style;
    }

    public function setHeadingStyle(int $a_val): void
    {
        $this->heading_style = $a_val;
    }

    public function getHeadingStyle(): int
    {
        return $this->heading_style;
    }

    public function setFooterStyle(int $a_val): void
    {
        $this->footer_style = $a_val;
    }

    public function getFooterStyle(): int
    {
        return $this->footer_style;
    }

    public function getHTML(): string
    {
        $tpl = new ilTemplate("tpl.panel.html", true, true, "Services/UIComponent/Panel");

        $head_outer_div_style = "";
        if ($this->getHeading() !== "") {
            $tpl->setCurrentBlock("heading");
            $tpl->setVariable("HEADING", $this->getHeading());

            switch ($this->getHeadingStyle()) {
                case self::HEADING_STYLE_BLOCK:
                    $tpl->setVariable("HEAD_DIV_STYLE", "panel-heading ilBlockHeader");
                    $tpl->setVariable("HEAD_H3_STYLE", "ilBlockHeader");
                    $head_outer_div_style = "il_Block";
                    break;

                case self::HEADING_STYLE_SUBHEADING:
                    $tpl->setVariable("HEAD_DIV_STYLE", "panel-heading ilHeader");
                    $tpl->setVariable("HEAD_H3_STYLE", "ilHeader");
                    break;
            }

            $tpl->parseCurrentBlock();
        }

        $tpl->setVariable("BODY", $this->getBody());

        if ($this->getFooter() !== "") {
            $tpl->setCurrentBlock("footer");
            $tpl->setVariable("FOOTER", $this->getFooter());

            switch ($this->getFooterStyle()) {
                case self::FOOTER_STYLE_BLOCK:
                    $tpl->setVariable("FOOT_DIV_STYLE", "panel-footer ilBlockInfo");
                    break;
            }

            $tpl->parseCurrentBlock();
        }

        switch ($this->getPanelStyle()) {
            case self::PANEL_STYLE_SECONDARY:
                $tpl->setVariable("PANEL_STYLE", "panel panel-default " . $head_outer_div_style);
                break;

            default:
                $tpl->setVariable("PANEL_STYLE", "panel panel-primary " . $head_outer_div_style);
                break;
        }

        return $tpl->get();
    }
}
