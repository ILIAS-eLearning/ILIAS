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
 * Class ilPCSourceCode
 *
 * Paragraph of ilPageObject
 *
 * @author Roland Küstermann
 * @author Alex Killing <alex.killing@gmx.de>
 */
class ilPCSourceCode extends ilPCParagraph
{
    public function init(): void
    {
        $this->setType("src");
    }

    public static function getLangVars(): array
    {
        return array("ed_insert_code", "pc_code");
    }

    public function modifyPageContentPostXsl(
        string $a_output,
        string $a_mode = "presentation",
        bool $a_abstract_only = false
    ): string {
        $nodes = $this->dom_util->path($this->dom_doc, "//Paragraph");
        $i = 0;
        foreach ($nodes as $context_node) {
            $char = $context_node->getAttribute('Characteristic');

            if ($char != "Code") {
                continue;
            }

            $n = $context_node->parentNode;
            $char = $context_node->getAttribute('Characteristic');
            $subchar = $context_node->getAttribute('SubCharacteristic');
            $showlinenumbers = $context_node->getAttribute('ShowLineNumbers');
            $downloadtitle = $context_node->getAttribute('DownloadTitle');
            $autoindent = $context_node->getAttribute('AutoIndent');

            // get XML Content
            $content = "";
            foreach ($context_node->childNodes as $child) {
                $content .= $this->dom_util->dump($child);
            }

            while ($context_node->firstChild) {
                $node_del = $context_node->firstChild;
                $node_del->parentNode->removeChild($node_del);
            }

            //$content = str_replace("<br />", "<br/>", utf8_decode($content));
            $content = str_replace("<br />", "<br/>", $content);
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
            //$content = utf8_encode($this->highlightText($plain_content, $subchar));
            $content = $this->highlightText($plain_content, $subchar);

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

            $a_output = str_replace("[[[[[Code;" . ($i + 1) . "]]]]]", $newcontent, $a_output);

            if ($a_mode != "presentation" && is_object($this->getPage()->getOfflineHandler())
                && trim($downloadtitle) != "") {
                // call code handler for offline versions
                $this->getPage()->getOfflineHandler()->handleCodeParagraph($this->getPage()->getId(), $i + 1, $downloadtitle, $plain_content);
            }
            $i++;
        }

        return $a_output;
    }

    /**
     * Highlights Text with given ProgLang
     */
    public function highlightText(
        string $a_text,
        string $proglang
    ): string {
        $proglang = ilSyntaxHighlighter::getNewLanguageId($proglang);
        if (ilSyntaxHighlighter::isSupported($proglang)) {
            $highl = ilSyntaxHighlighter::getInstance($proglang);
            $a_text = $highl->highlight($a_text);
        }
        return $a_text;
    }

    public function importFile(string $tmpname): void
    {
        if ($tmpname !== "") {
            $tmpfs = $this->domain->filesystem()->temp();
            $this->setText(
                $this->input2xml($tmpfs->read($tmpname), 0, false)
            );
            $tmpfs->delete($tmpname);
        }
    }
}
