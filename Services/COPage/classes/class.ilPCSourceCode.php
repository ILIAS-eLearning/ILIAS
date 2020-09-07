<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once("./Services/COPage/classes/class.ilPCParagraph.php");

/**
 * Class ilPCSourceCode
 *
 * Paragraph of ilPageObject
 *
 * @author Roland Küstermann
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 *
 * @ingroup ServicesCOPage
 */
class ilPCSourceCode extends ilPCParagraph
{
    /**
    * Init page content component.
    */
    public function init()
    {
        $this->setType("src");
    }
    
    /**
     * Get lang vars needed for editing
     * @return array array of lang var keys
     */
    public static function getLangVars()
    {
        return array("ed_insert_code", "pc_code");
    }

    /**
     * Modify page content after xsl
     *
     * @param string $a_output
     * @return string
     */
    public function modifyPageContentPostXsl($a_output, $outputmode = "presentation")
    {
        $dom = $this->getPage()->getDom();

        $xpc = xpath_new_context($dom);
        $path = "//Paragraph"; //"[@Characteristic = 'Code']";
        $res = &xpath_eval($xpc, $path);
        for ($i = 0; $i < count($res->nodeset); $i++) {
            $context_node = $res->nodeset[$i];
            $char = $context_node->get_attribute('Characteristic');

            if ($char != "Code") {
                continue;
            }

            $n = $context_node->parent_node();
            $char = $context_node->get_attribute('Characteristic');
            $subchar = $context_node->get_attribute('SubCharacteristic');
            $showlinenumbers = $context_node->get_attribute('ShowLineNumbers');
            $downloadtitle = $context_node->get_attribute('DownloadTitle');
            $autoindent = $context_node->get_attribute('AutoIndent');

            $content = "";

            // get XML Content
            $childs = $context_node->child_nodes();

            for ($j = 0; $j < count($childs); $j++) {
                $content .= $dom->dump_node($childs[$j]);
            }

            while ($context_node->has_child_nodes()) {
                $node_del = $context_node->first_child();
                $context_node->remove_child($node_del);
            }

            $content = str_replace("<br />", "<br/>", utf8_decode($content));
            $content = str_replace("<br/>", "\n", $content);
            $rownums = count(explode("\n", $content));

            // see #23028
            //$plain_content = html_entity_decode($content);
            $plain_content = $content;

            $plain_content = preg_replace_callback(
                "/\&#x([1-9a-f]{2});?/is",
                function ($hit) {
                    return chr(base_convert($hit[1], 16, 10));
                },
                $plain_content
            );
            $plain_content = preg_replace_callback(
                "/\&#(\d+);?/is",
                function ($hit) {
                    return chr($hit[1]);
                },
                $plain_content
            );
            $content = utf8_encode($this->highlightText($plain_content, $subchar, $autoindent));

            $content = str_replace("&amp;lt;", "&lt;", $content);
            $content = str_replace("&amp;gt;", "&gt;", $content);
            //			$content = str_replace("&", "&amp;", $content);
            //var_dump($content);
            $rows = "<tr valign=\"top\">";
            $rownumbers = "";
            $linenumbers = "";

            //if we have to show line numbers
            if (strcmp($showlinenumbers, "y") == 0) {
                $linenumbers = "<td nowrap=\"nowrap\" class=\"ilc_LineNumbers\" >";
                $linenumbers .= "<pre class=\"ilc_Code ilc_code_block_Code\">";

                for ($j = 0; $j < $rownums; $j++) {
                    $indentno = strlen($rownums) - strlen($j + 1) + 2;
                    $rownumeration = ($j + 1);
                    $linenumbers .= "<span class=\"ilc_LineNumber\">$rownumeration</span>";
                    if ($j < $rownums - 1) {
                        $linenumbers .= "\n";
                    }
                }
                $linenumbers .= "</pre>";
                $linenumbers .= "</td>";
            }

            $rows .= $linenumbers . "<td class=\"ilc_Sourcecode\"><pre class=\"ilc_Code ilc_code_block_Code\">" . $content . "</pre></td>";
            $rows .= "</tr>";

            // fix for ie explorer which is not able to produce empty line feeds with <br /><br />;
            // workaround: add a space after each br.
            $newcontent = str_replace("\n", "<br/>", $rows);
            // fix for IE
            $newcontent = str_replace("<br/><br/>", "<br/> <br/>", $newcontent);
            // falls drei hintereinander...
            $newcontent = str_replace("<br/><br/>", "<br/> <br/>", $newcontent);

            // workaround for preventing template engine
            // from hiding paragraph text that is enclosed
            // in curly brackets (e.g. "{a}", see ilLMEditorGUI::executeCommand())
            $newcontent = str_replace("{", "&#123;", $newcontent);
            $newcontent = str_replace("}", "&#125;", $newcontent);

            //echo htmlentities($newcontent);
            $a_output = str_replace("[[[[[Code;" . ($i + 1) . "]]]]]", $newcontent, $a_output);

            if ($outputmode != "presentation" && is_object($this->getPage()->getOfflineHandler())
                && trim($downloadtitle) != "") {
                // call code handler for offline versions
                $this->getPage()->getOfflineHandler()->handleCodeParagraph($this->getPage()->getId(), $i + 1, $downloadtitle, $plain_content);
            }
        }

        return $a_output;
    }

    /**
     * Highligths Text with given ProgLang
     */
    public function highlightText($a_text, $proglang, $autoindent = "")
    {
        include_once("./Services/UIComponent/SyntaxHighlighter/classes/class.ilSyntaxHighlighter.php");
        $proglang = ilSyntaxHighlighter::getNewLanguageId($proglang);
        if (ilSyntaxHighlighter::isSupported($proglang)) {
            $highl = ilSyntaxHighlighter::getInstance($proglang);
            $a_text = $highl->highlight($a_text);
        }
        return $a_text;
    }
}
