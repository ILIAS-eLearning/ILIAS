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

namespace ILIAS\Style\Content\Style;

class CSSBuilder
{
    public function __construct(
        protected \ilObjStyleSheet $style,
        protected string $image_dir
    ) {

    }

    public function getCss(): string
    {
        $style = $this->style->getStyle();

        $css = "";
        $page_background = "";

        $mqs = array(array("mquery" => "", "id" => 0));
        foreach ($this->style->getMediaQueries() as $mq) {
            $mqs[] = $mq;
        }

        // iterate all media queries
        foreach ($mqs as $mq) {
            if ($mq["id"] > 0) {
                $css .= "@media " . $mq["mquery"] . " {\n";
            }
            reset($style);
            foreach ($style as $tag) {
                if ($tag[0]["mq_id"] != $mq["id"]) {
                    continue;
                }
                if (is_int(strpos($tag[0]["class"], "before")) && !is_int(strpos($tag[0]["class"], "::before"))) {
                    $tag[0]["class"] = str_replace(":before", "::before", $tag[0]["class"]);
                }
                $css .= $tag[0]["tag"] . ".ilc_" . $tag[0]["type"] . "_" . $tag[0]["class"] . "\n";
                //				echo "<br>";
                //				var_dump($tag[0]["type"]);
                if ($tag[0]["tag"] == "td") {
                    $css .= ",th" . ".ilc_" . $tag[0]["type"] . "_" . $tag[0]["class"] . "\n";
                }
                if (in_array($tag[0]["tag"], array("h1", "h2", "h3"))) {
                    $css .= ",div.ilc_text_block_" . $tag[0]["class"] . "\n";
                    $css .= ",html.il-no-tiny-bg body#tinymce.ilc_text_block_" . $tag[0]["class"] . "\n";
                }
                if ($tag[0]["type"] == "section") {	// sections can use a tags, if links are used
                    $css .= ",a.ilc_" . $tag[0]["type"] . "_" . $tag[0]["class"] . "\n";
                }
                if ($tag[0]["type"] == "strong") {
                    $css .= ",span.ilc_text_inline_" . $tag[0]["class"] . "\n";
                }
                if ($tag[0]["type"] == "em") {
                    $css .= ",span.ilc_text_inline_" . $tag[0]["class"] . "\n";
                }
                if ($tag[0]["type"] == "text_block") {
                    $css .= ",html.il-no-tiny-bg body#tinymce.ilc_text_block_" . $tag[0]["class"] . "\n";
                }
                if ($tag[0]["class"] == "VAccordCntr") {
                    $css .= ",div.ilc_va_cntr_AccordCntr\n";
                }
                if ($tag[0]["class"] == "VAccordICntr") {
                    $css .= ",div.ilc_va_icntr_AccordICntr\n";
                }
                if ($tag[0]["class"] == "VAccordICont") {
                    $css .= ",div.ilc_va_icont_AccordICont\n";
                }
                if ($tag[0]["class"] == "VAccordIHead") {
                    $css .= ",div.ilc_va_ihead_AccordIHead\n";
                }
                if ($tag[0]["class"] == "VAccordIHead:hover") {
                    $css .= ",div.ilc_va_ihead_AccordIHead:hover\n";
                }
                if ($tag[0]["class"] == "VAccordIHeadActive") {
                    $css .= ",div.ilc_va_iheada_AccordIHeadActive\n";
                }
                if ($tag[0]["class"] == "VAccordIHeadActive:hover") {
                    $css .= ",div.ilc_va_iheada_AccordIHeadActive:hover\n";
                }
                $css .= "{\n";

                // collect table border attributes
                $t_border = array();

                foreach ($tag as $par) {
                    $cur_par = $par["parameter"];
                    $cur_val = $par["value"];

                    // replace named colors
                    if (is_int(strpos($cur_par, "color")) && substr(trim($cur_val), 0, 1) == "!") {
                        $cur_val = $this->style->getColorCodeForName(substr($cur_val, 1));
                    }

                    if ($tag[0]["type"] == "table" && is_int(strpos($par["parameter"], "border"))) {
                        $t_border[$cur_par] = $cur_val;
                    }

                    if (in_array($cur_par, array("background-image", "list-style-image"))) {
                        if (is_int(strpos($cur_val, "/"))) {	// external
                            $cur_val = "url('" . $cur_val . "')";
                        } else {		// internal
                            if ($this->image_dir == "") {
                                $cur_val = "url('images/" . $cur_val . "')";
                            } else {
                                $cur_val = "url('" . $this->image_dir . "/" . $cur_val . "')";
                            }
                        }
                    }

                    if ($cur_par == "opacity") {
                        $cur_val = ((int) $cur_val) / 100;
                    }

                    $css .= "\t" . $cur_par . ": " . $cur_val . ";\n";

                    // opacity fix
                    if ($cur_par == "opacity") {
                        $css .= "\t" . '-ms-filter:"progid:DXImageTransform.Microsoft.Alpha(Opacity=' . ($cur_val * 100) . ')"' . ";\n";
                        $css .= "\t" . 'filter: alpha(opacity=' . ($cur_val * 100) . ')' . ";\n";
                        $css .= "\t" . '-moz-opacity: ' . $cur_val . ";\n";
                    }

                    // transform fix
                    if ($cur_par == "transform") {
                        $css .= "\t" . '-webkit-transform: ' . $cur_val . ";\n";
                        $css .= "\t" . '-moz-transform: ' . $cur_val . ";\n";
                        $css .= "\t" . '-ms-transform: ' . $cur_val . ";\n";
                    }

                    // transform-origin fix
                    if ($cur_par == "transform-origin") {
                        $css .= "\t" . '-webkit-transform-origin: ' . $cur_val . ";\n";
                        $css .= "\t" . '-moz-transform-origin: ' . $cur_val . ";\n";
                        $css .= "\t" . '-ms-transform-origin: ' . $cur_val . ";\n";
                    }

                    // save page background
                    if ($tag[0]["tag"] == "div" && $tag[0]["class"] == "Page"
                        && $cur_par == "background-color") {
                        $page_background = $cur_val;
                    }
                }
                $css .= "}\n";
                $css .= "\n";
            }

            if ($page_background != "") {
                $css .= "td.ilc_Page\n";
                $css .= "{\n";
                $css .= "\t" . "background-color: " . $page_background . ";\n";
                $css .= "}\n";
            }
            if ($mq["id"] > 0) {
                $css .= "}\n";
            }
        }
        return $css;
    }

}
