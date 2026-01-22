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

use Phiki\Phiki;
use Phiki\Grammar\Grammar;
use Phiki\Theme\Theme;

class ilPCSourceCode extends ilPCParagraph
{
    public const string JAVA = "java";
    public const string PHP = "php";
    public const string C = "c";
    public const string CPP = "cpp";
    public const string HTML = "html4strict";
    public const string XML = "xml";
    public const string VISUAL_BASIC = "vb";
    public const string LATEX = "latex";
    public const string DELPHI = "delphi";
    public const string PYTHON = "python";
    public const string CSS = "css";
    public const string JAVASCRIPT = "javascript";
    public const string SQL = "sql";
    public const string BASH = "bash";
    public const string POWERSHELL = "powershell";

    /**
     * @var string[]
     */
    protected static array $langs = array(
        self::BASH => "Bash",
        self::C => "C",
        self::CPP => "C++",
        self::CSS => "CSS",
        self::DELPHI => "Delphi",
        self::HTML => "HTML",
        self::JAVA => "Java",
        self::JAVASCRIPT => "Javascript",
        self::LATEX => "LaTeX",
        self::PHP => "PHP",
        self::POWERSHELL => "Powershell",
        self::PYTHON => "Python",
        self::SQL => "SQL",
        self::VISUAL_BASIC => "Visual Basic",
        self::XML => "XML"
    );

    protected static array $v51_map = array(
        "php3" => "php",
        "java122" => "java",
        "html" => "html4strict"
    );

    public static function getSupportedLanguages(): array
    {
        $langs = array();
        $map = array_flip(self::$v51_map);
        foreach (self::$langs as $k => $v) {
            if (isset($map[$k])) {
                $k = $map[$k];
            }
            $langs[$k] = $v;
        }
        return $langs;
    }

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

            if ($char !== "Code") {
                $i++;
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
            if ($subchar === "") {
                $subchar = "other";
            }
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
                $linenumbers .= "<pre class=\"ilc_Code ilc_code_block_Code\"><code>";

                for ($j = 0; $j < $rownums; $j++) {
                    $indentno = strlen($rownums) - strlen($j + 1) + 2;
                    $rownumeration = ($j + 1);
                    $linenumbers .= "<span class=\"ilc_LineNumber\">$rownumeration</span>";
                    if ($j < $rownums - 1) {
                        $linenumbers .= "\n";
                    }
                }
                $linenumbers .= "</code></pre>";
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

    protected function phikiMap(string $lang): ?Grammar
    {
        $grammar = match ($lang) {
            "java" => Grammar::Java,
            "php" => Grammar::Php,
            "c" => Grammar::C,
            "cpp" => Grammar::Cpp,
            "html4strict" => Grammar::Html,
            "xml" => Grammar::Xml,
            "vb" => Grammar::Vb,
            "latex" => Grammar::Latex,
            "delphi" => Grammar::Pascal,
            "python" => Grammar::Python,
            "css" => Grammar::Css,
            "javascript" => Grammar::Javascript,
            "sql" => Grammar::Sql,
            "bash" => Grammar::Shellscript,
            "powershell" => Grammar::Powershell,
            default => Grammar::Markdown
        };

        return $grammar;
    }

    /**
     * Highlights Text with given ProgLang
     */
    public function highlightText(
        string $a_text,
        string $proglang
    ): string {
        $map = ["php3" => "php",
        "java122" => "java",
        "html" => "html4strict"];
        if (isset($map[$proglang])) {
            $proglang = $map[$proglang];
        }

        $phiki = new Phiki();
        $grammar = $this->phikiMap($proglang);
        if (!is_null($grammar)) {
            $a_code = $phiki->codeToHtml(html_entity_decode($a_text), $grammar, Theme::GithubLight);
        } else {
            $a_code = $a_text;
        }

        $a_code = substr($a_code, strpos($a_code, ">") + 1);
        $a_code = substr($a_code, 0, strrpos($a_code, "<"));
        return $a_code;
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
