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

namespace ILIAS\Style\Content\Preview;

use ILIAS\Style\Content\InternalDomainService;
use ILIAS\Style\Content\InternalGUIService;

class PreviewGUI
{
    public function __construct(
        protected InternalDomainService $domain,
        protected InternalGUIService $gui
    ) {
    }

    public function getTemplatePreview(
        int $style_id,
        string $a_type,
        int $a_t_id,
        bool $a_small_mode = false
    ): string {

        $a_style = new \ilObjStyleSheet($style_id);
        $html = $a_style->lookupTemplatePreview($a_t_id);
        if ($html !== "") {
            //            return $html;
        }
        $lng = $this->domain->lng();
        $p_content = "";
        $kr = $kc = 5;
        if ($a_small_mode) {
            $kr = 5;
            $kc = 4;
        }

        $ts = $a_style->getTemplate($a_t_id);
        $t = $ts["classes"];

        // preview
        if ($a_type == "table") {
            $p_content = '<PageContent><Table DataTable="y"';
            $t["row_head"] = $t["row_head"] ?? "";
            $t["row_foot"] = $t["row_foot"] ?? "";
            $t["col_head"] = $t["col_head"] ?? "";
            $t["col_foot"] = $t["col_foot"] ?? "";
            if ($t["row_head"] != "") {
                $p_content .= ' HeaderRows="1"';
            }
            if ($t["row_foot"] != "") {
                $p_content .= ' FooterRows="1"';
            }
            if ($t["col_head"] != "") {
                $p_content .= ' HeaderCols="1"';
            }
            if ($t["col_foot"] != "") {
                $p_content .= ' FooterCols="1"';
            }
            $p_content .= ' Template="' . $a_style->lookupTemplateName($a_t_id) . '">';
            if (!$a_small_mode) {
                $p_content .= '<Caption>' . $lng->txt("sty_caption") . '</Caption>';
            }
            for ($i = 1; $i <= $kr; $i++) {
                $p_content .= '<TableRow>';
                for ($j = 1; $j <= $kc; $j++) {
                    if ($a_small_mode) {
                        $cell = '&lt;div style="height:2px;"&gt;&lt;/div&gt;';
                    } else {
                        $cell = 'xxx';
                    }
                    $p_content .= '<TableData><PageContent><Paragraph Characteristic="TableContent">' . $cell . '</Paragraph></PageContent></TableData>';
                }
                $p_content .= '</TableRow>';
            }
            $p_content .= '</Table></PageContent>';
        }
        if ($a_type == "vaccordion" || $a_type == "haccordion" || $a_type == "carousel") {
            \ilAccordionGUI::addCss();

            if ($a_small_mode) {
                $c = '&amp;nbsp;';
                $h = '&amp;nbsp;';
            } else {
                $c = 'xxx';
                $h = 'head';
            }
            if ($a_type == "vaccordion") {
                $p_content = '<PageContent><Tabs HorizontalAlign="Left" Type="VerticalAccordion" ';
                if ($a_small_mode) {
                    $p_content .= ' ContentWidth="70"';
                }
            } elseif ($a_type == "haccordion") {
                $p_content = '<PageContent><Tabs Type="HorizontalAccordion"';
                $p_content .= ' ContentHeight="40"';
                if ($a_small_mode) {
                    $p_content .= ' ContentWidth="70"';
                    $c = '&amp;nbsp;&amp;nbsp;&amp;nbsp;&amp;nbsp;';
                }
            } elseif ($a_type == "carousel") {
                $p_content = '<PageContent><Tabs HorizontalAlign="Left" Type="Carousel" ';
                if ($a_small_mode) {
                    $p_content .= ' ContentWidth="70"';
                }
            }


            $p_content .= ' Template="' . $a_style->lookupTemplateName($a_t_id) . '">';
            $p_content .= '<Tab><PageContent><Paragraph>' . $c . '</Paragraph></PageContent>';
            $p_content .= '<TabCaption>' . $h . '</TabCaption>';
            $p_content .= '</Tab>';
            $p_content .= '</Tabs></PageContent>';
        }
        //echo htmlentities($p_content);
        $txml = $a_style->getTemplateXML();
        //echo htmlentities($txml); exit;
        $p_content .= $txml;
        $r_content = \ilPCTableGUI::_renderTable($p_content, "");

        // fix carousel template visibility
        if ($a_type == "carousel") {
            $r_content .= "<style>.owl-carousel{ display:block !important; }</style>";
        }

        //echo htmlentities($r_content); exit;
        return $r_content;
    }

}
