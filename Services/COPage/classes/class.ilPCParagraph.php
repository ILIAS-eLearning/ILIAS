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
 * Class ilPCParagraph
 *
 * Paragraph of ilPageObject
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilPCParagraph extends ilPageContent
{
    protected string $inserted_pc_id;
    protected ilObjUser $user;
    public ?php4DOMElement $par_node = null;

    protected ilLanguage $lng;

    protected static array $bb_tags = array(
            "com" => "Comment",
            "emp" => "Emph",
            "str" => "Strong",
            "fn" => "Footnote",
            "code" => "Code",
            "acc" => "Accent",
            "imp" => "Important",
            "kw" => "Keyw",
            "sub" => "Sub",
            "sup" => "Sup",
            "quot" => "Quotation",
            );

    /**
    * converts a string of format var1 = "val1" var2 = "val2" ... into an array
    *
    * @param string $a_str string in format: var1 = "val1" var2 = "val2" ...
    *
    * @return    array        array of variable value pairs
    * @static
    *
    */
    public static function attribsToArray(string $a_str): array
    {
        $attribs = [];
        while (is_int(strpos($a_str, "="))) {
            $eq_pos = strpos($a_str, "=");
            $qu1_pos = strpos($a_str, "\"");
            $qu2_pos = strpos(substr($a_str, $qu1_pos + 1), "\"") + $qu1_pos + 1;
            if (is_int($eq_pos) && is_int($qu1_pos) && is_int($qu2_pos)) {
                $var = trim(substr($a_str, 0, $eq_pos));
                $val = trim(substr($a_str, $qu1_pos + 1, ($qu2_pos - $qu1_pos) - 1));
                $attribs[$var] = $val;
                $a_str = substr($a_str, $qu2_pos + 1);
            } else {
                $a_str = "";
            }
        }
        return $attribs;
    }

    public function init(): void
    {
        global $DIC;

        $this->user = $DIC->user();
        $this->lng = $DIC->language();
        $this->setType("par");
    }

    /**
     * Get bb to xml tag map
     */
    protected static function getBBMap(): array
    {
        return self::$bb_tags;
    }

    /**
     * Get tag to bb map
     */
    protected static function getXMLTagMap(): array
    {
        return array_flip(self::$bb_tags);
    }

    public function setNode(php4DOMElement $a_node): void
    {
        parent::setNode($a_node);		// this is the PageContent node

        $childs = $a_node->child_nodes();
        for ($i = 0, $iMax = count($childs); $i < $iMax; $i++) {
            if ($childs[$i]->node_name() == "Paragraph") {
                $this->par_node = $childs[$i];		//... and this the Paragraph node
            }
        }
    }

    /**
     * Create new page content (incl. paragraph) node at node
     */
    public function createAtNode(php4DOMElement $node): void
    {
        $this->node = $this->createPageContentNode();
        $this->par_node = $this->dom->create_element("Paragraph");
        $this->par_node = $this->node->append_child($this->par_node);
        $this->par_node->set_attribute("Language", "");
        $node->append_child($this->node);
    }

    /**
     * Create new page content (incl. paragraph) node at node
     */
    public function createBeforeNode(php4DOMElement $node): void
    {
        $this->node = $this->createPageContentNode();
        $this->par_node = $this->dom->create_element("Paragraph");
        $this->par_node = $this->node->append_child($this->par_node);
        $this->par_node->set_attribute("Language", "");
        $node->insert_before($this->node, $node);
    }

    /**
     * Create paragraph node (incl. page content node)
     * after given node.
     */
    public function createAfter(php4DOMElement $node): void
    {
        $this->node = $this->createPageContentNode(false);
        if ($succ_node = $node->next_sibling()) {
            $this->node = $succ_node->insert_before($this->node, $succ_node);
        } else {
            $parent_node = $node->parent_node();
            $this->node = $parent_node->append_child($this->node);
        }
        $this->par_node = $this->dom->create_element("Paragraph");
        $this->par_node = $this->node->append_child($this->par_node);
        $this->par_node->set_attribute("Language", "");
    }

    /**
     * Create paragraph node (incl. page content node)
     * at given hierarchical ID.
     */
    public function create(
        ilPageObject $a_pg_obj,
        string $a_hier_id,
        string $a_pc_id = "",
        bool $from_placeholder = false
    ): void {
        //echo "-$a_pc_id-";
        //echo "<br>-".htmlentities($a_pg_obj->getXMLFromDom())."-<br><br>"; mk();
        $this->node = $this->dom->create_element("PageContent");

        // this next line kicks out placeholders, if something is inserted
        $a_pg_obj->insertContent(
            $this,
            $a_hier_id,
            IL_INSERT_AFTER,
            $a_pc_id,
            $from_placeholder
        );

        $this->par_node = $this->dom->create_element("Paragraph");
        $this->par_node = $this->node->append_child($this->par_node);
        $this->par_node->set_attribute("Language", "");
    }

    /**
     * Set (xml) content of text paragraph.
     * @param	string $a_text       text content
     * @param bool      $a_auto_split auto split paragraph at headlines true/false
     * @return bool|string
     */
    public function setText(
        string $a_text,
        bool $a_auto_split = false
    ) {
        $text = $a_text;
        if ($a_auto_split) {
            $text = $this->autoSplit($a_text);
        } else {
            $text = [["level" => 0, "text" => $text]];
        }
        $error = $this->checkTextArray($text);

        $orig_characteristic = "";

        // remove all childs
        if (empty($error)) {
            $t = $text[0]["text"] ?? "";
            $temp_dom = domxml_open_mem(
                '<?xml version="1.0" encoding="UTF-8"?><Paragraph>' . $t . '</Paragraph>',
                DOMXML_LOAD_PARSING,
                $error
            );

            // delete children of paragraph node
            $children = $this->par_node->child_nodes();
            for ($i = 0, $iMax = count($children); $i < $iMax; $i++) {
                $this->par_node->remove_child($children[$i]);
            }

            // copy new content children in paragraph node
            $xpc = xpath_new_context($temp_dom);
            $path = "//Paragraph";
            $res = xpath_eval($xpc, $path);
            if (count($res->nodeset) == 1) {
                $new_par_node = $res->nodeset[0];
                $new_childs = $new_par_node->child_nodes();

                for ($i = 0, $iMax = count($new_childs); $i < $iMax; $i++) {
                    $cloned_child = $new_childs[$i]->clone_node(true);
                    $this->par_node->append_child($cloned_child);
                }
                $orig_characteristic = $this->getCharacteristic();

                // if headlines are entered and current characteristic is a headline
                // use no characteristic as standard
                if ((count($text) > 1) && (substr($orig_characteristic, 0, 8) == "Headline")) {
                    $orig_characteristic = "";
                }
                if (isset($text[0]["level"]) && $text[0]["level"] > 0) {
                    $this->par_node->set_attribute("Characteristic", 'Headline' . $text[0]["level"]);
                }
            }

            $ok = true;

            $c_node = $this->node;
            // add other chunks afterwards
            for ($i = 1, $iMax = count($text); $i < $iMax; $i++) {
                if ($ok) {
                    $next_par = new ilPCParagraph($this->getPage());
                    $next_par->createAfter($c_node);
                    $next_par->setLanguage($this->getLanguage());
                    if ($text[$i]["level"] > 0) {
                        $next_par->setCharacteristic("Headline" . $text[$i]["level"]);
                    } else {
                        $next_par->setCharacteristic($orig_characteristic);
                    }
                    $ok = $next_par->setText($text[$i]["text"], false);
                    $c_node = $next_par->node;
                }
            }

            return true;
        } else {
            // We want the correct number of \n here to have the real lines numbers
            $check = array_reduce($text, function ($t, $i) {
                return $t . $i["text"];
            });
            $text = str_replace("<br>", "\n", $check);		// replace <br> with \n to get correct line
            $text = str_replace("<br/>", "\n", $text);
            $text = str_replace("<br />", "\n", $text);
            $text = str_replace("</SimpleListItem>", "</SimpleListItem>\n", $text);
            $text = str_replace("<SimpleBulletList>", "\n<SimpleBulletList>", $text);
            $text = str_replace("<SimpleNumberedList>", "\n<SimpleNumberedList>", $text);
            $text = str_replace("<Paragraph>\n", "<Paragraph>", $text);
            $text = str_replace("</Paragraph>", "</Paragraph>\n", $text);
            $doc = new ilDOMDocument();
            $text = '<?xml version="1.0" encoding="UTF-8"?><Paragraph>' . $text . '</Paragraph>';
            //echo htmlentities($text);
            $doc->loadXML($text);
            $error = $doc->getErrors();
            $estr = "";
            foreach ($error as $e) {
                $e = str_replace(" in Entity", "", $e);
                $estr .= $e . "<br />";
            }
            if (DEVMODE) {
                $estr .= "<br />" . $text;
            }

            return $estr;
        }
    }

    /**
     * Check text array
     */
    protected function checkTextArray(array $text): ?array
    {
        $check = "";
        foreach ($text as $t) {
            $check .= "<Paragraph>" . $t["text"] . "</Paragraph>";
        }
        $error = null;
        //try {
        $temp_dom = domxml_open_mem(
            '<?xml version="1.0" encoding="UTF-8"?><Paragraph>' . $check . '</Paragraph>',
            DOMXML_LOAD_PARSING,
            $error
        );
        //} catch (Exception $e) {

        //}
        return $error;
    }

    protected function fixTextArray(array $text): array
    {
        $dom = new DOMDocument();
        $dom->recover = true;
        // try to fix
        for ($i = 0, $iMax = count($text); $i < $iMax; $i++) {
            $dom->loadXML(
                '<?xml version="1.0" encoding="UTF-8"?><Paragraph>' . $text[$i]["text"] . '</Paragraph>',
                LIBXML_NOWARNING | LIBXML_NOERROR
            );
            foreach ($dom->childNodes as $node) {
                if ($node->nodeName == "Paragraph") {
                    $inner = "";
                    foreach ($node->childNodes as $child) {
                        $inner .= $dom->saveXML($child);
                    }
                    $text[$i]["text"] = $inner;
                }
            }
        }
        return $text;
    }

    /**
     * Get (xml) content of paragraph.
     */
    public function getText(bool $a_short_mode = false): string
    {
        if (is_object($this->par_node)) {
            $content = "";
            $childs = $this->par_node->child_nodes();
            for ($i = 0, $iMax = count($childs); $i < $iMax; $i++) {
                $content .= $this->dom->dump_node($childs[$i]);
            }
            return $content;
        } else {
            return "";
        }
    }

    /**
     * Get paragraph sequenc of current paragraph
     */
    public function getParagraphSequenceContent(
        ilPageObject $a_pg_obj
    ): string {
        $childs = $this->par_node->parent_node()->parent_node()->child_nodes();
        $seq = array();
        $cur_seq = array();
        $found = false;
        $pc_id = $this->readPCId();
        $hier_id = $this->readHierId();
        for ($i = 0, $iMax = count($childs); $i < $iMax; $i++) {
            $pchilds = $childs[$i]->child_nodes();
            if ($pchilds[0]->node_name() == "Paragraph" &&
                $pchilds[0]->get_attribute("Characteristic") != "Code") {
                $cur_seq[] = $childs[$i];

                // check whether this is the sequence of the current paragraph
                if ($childs[$i]->get_attribute("PCID") == $pc_id &&
                    $childs[$i]->get_attribute("HierId") == $hier_id) {
                    $found = true;
                }

                // if this is the current sequenc, get it
                if ($found) {
                    $seq = $cur_seq;
                }
            } else {
                // non-paragraph element found -> init the current sequence
                $cur_seq = array();
                $found = false;
            }
        }

        $content = "";
        $ids = "###";
        $id_sep = "";
        foreach ($seq as $p_node) {
            $ids .= $id_sep . $p_node->get_attribute("HierId") . ":" . $p_node->get_attribute("PCID");
            $po = $a_pg_obj->getContentObject(
                $p_node->get_attribute("HierId"),
                $p_node->get_attribute("PCID")
            );
            $s_text = $po->getText();
            $s_text = $po->xml2output($s_text, true, false);
            $char = $po->getCharacteristic();
            if ($char == "") {
                $char = "Standard";
            }
            $s_text = ilPCParagraphGUI::xml2outputJS($s_text);
            $content .= $s_text;
            $id_sep = ";";
        }
        $ids .= "###";

        return $ids . $content;
    }

    /**
     * Set Characteristic of paragraph
     */
    public function setCharacteristic(string $a_char): void
    {
        if (!empty($a_char)) {
            $this->par_node->set_attribute("Characteristic", $a_char);
        } else {
            if ($this->par_node->has_attribute("Characteristic")) {
                $this->par_node->remove_attribute("Characteristic");
            }
        }
    }

    public function getCharacteristic(): string
    {
        if (is_object($this->par_node)) {
            return $this->par_node->get_attribute("Characteristic");
        }
        return "";
    }

    public function setSubCharacteristic(string $a_char): void
    {
        if (!empty($a_char)) {
            $this->par_node->set_attribute("SubCharacteristic", $a_char);
        } else {
            if ($this->par_node->has_attribute("SubCharacteristic")) {
                $this->par_node->remove_attribute("SubCharacteristic");
            }
        }
    }

    public function getAutoIndent(): string
    {
        return (string) $this->par_node->get_attribute("AutoIndent");
    }

    public function setAutoIndent(string $a_char): void
    {
        if (!empty($a_char)) {
            $this->par_node->set_attribute("AutoIndent", $a_char);
        } else {
            if ($this->par_node->has_attribute("AutoIndent")) {
                $this->par_node->remove_attribute("AutoIndent");
            }
        }
    }

    public function getSubCharacteristic(): string
    {
        return $this->par_node->get_attribute("SubCharacteristic");
    }

    public function setDownloadTitle(string $a_char): void
    {
        if (!empty($a_char)) {
            $this->par_node->set_attribute("DownloadTitle", $a_char);
        } else {
            if ($this->par_node->has_attribute("DownloadTitle")) {
                $this->par_node->remove_attribute("DownloadTitle");
            }
        }
    }

    public function getDownloadTitle(): string
    {
        return $this->par_node->get_attribute("DownloadTitle");
    }

    public function setShowLineNumbers(string $a_char): void
    {
        $a_char = empty($a_char)
            ? "n"
            : $a_char;

        $this->par_node->set_attribute("ShowLineNumbers", $a_char);
    }

    public function getShowLineNumbers(): string
    {
        return $this->par_node->get_attribute("ShowLineNumbers");
    }

    public function setLanguage(string $a_lang): void
    {
        $this->par_node->set_attribute("Language", $a_lang);
    }

    public function getLanguage(): string
    {
        return $this->par_node->get_attribute("Language");
    }

    public function input2xml(
        string $a_text,
        bool $a_wysiwyg = false,
        bool $a_handle_lists = true
    ): string {
        return $this->_input2xml($a_text, $this->getLanguage(), $a_wysiwyg, $a_handle_lists);
    }

    protected static function replaceBBCode(
        string $a_text,
        string $a_bb,
        string $a_tag
    ): string {
        $a_text = preg_replace('/\[' . $a_bb . '\]/i', "<" . $a_tag . ">", $a_text);
        $a_text = preg_replace('/\[\/' . $a_bb . '\]/i', "</" . $a_tag . ">", $a_text);
        return $a_text;
    }

    /**
     * Converts user input to xml
     * User input comes as bb code information, e.g.
     * "Hello [str]world[/str]."
     */
    public static function _input2xml(
        string $a_text,
        string $a_lang,
        bool $a_wysiwyg = false,
        bool $a_handle_lists = true
    ): string {
        $log = null;
        if (!defined('COPAGE_TEST')) {
            $log = ilLoggerFactory::getLogger('copg');
            $log->debug("_input2xml");
            $log->debug("...start ");
            $log->debug("...text: " . substr($a_text, 0, 1000));
            $log->debug("...lang: " . $a_lang);
            $log->debug("...wysiwyg: " . $a_wysiwyg);
            $log->debug("...handle_lists: " . $a_handle_lists);
        }

        if (!$a_wysiwyg) {
            $a_text = ilUtil::stripSlashes($a_text, false);
        }

        if ($a_wysiwyg) {
            $a_text = str_replace("<br />", chr(10), $a_text);
        }

        // note: the order of the processing steps is crucial
        // and should be the same as in xml2output() in REVERSE order!
        $a_text = trim($a_text);

        //echo "<br>between:".htmlentities($a_text);

        // mask html
        if (!$a_wysiwyg) {
            $a_text = str_replace("&", "&amp;", $a_text);
        }
        $a_text = str_replace("<", "&lt;", $a_text);
        $a_text = str_replace(">", "&gt;", $a_text);

        // mask curly brackets
        /*
        echo htmlentities($a_text);
                $a_text = str_replace("{", "&#123;", $a_text);
                $a_text = str_replace("}", "&#125;", $a_text);
        echo htmlentities($a_text);*/

        // linefeed to br
        $a_text = str_replace(chr(13) . chr(10), "<br />", $a_text);
        $a_text = str_replace(chr(13), "<br />", $a_text);
        $a_text = str_replace(chr(10), "<br />", $a_text);

        if ($a_handle_lists) {
            $a_text = ilPCParagraph::input2xmlReplaceLists($a_text);
        }

        foreach (self::getBBMap() as $bb => $tag) {
            // remove empty tags
            $a_text = str_replace("[" . $bb . "][/" . $bb . "]", "", $a_text);

            // replace bb code by tag
            $a_text = self::replaceBBCode($a_text, $bb, $tag);
        }

        $a_text = self::intLinks2xml($a_text);

        // external link
        $ws = "[ \t\r\f\v\n]*";
        // remove empty external links
        while (preg_match("~\[(xln$ws(url$ws=$ws\"([^\"])*\")$ws(target$ws=$ws(\"(Glossary|FAQ|Media)\"))?$ws)\]\[\/xln\]~i", $a_text, $found)) {
            $a_text = str_replace($found[0], "", $a_text);
        }
        while (preg_match('~\[(xln$ws(url$ws=$ws(([^]])*)))$ws\]\[\/xln\]~i', $a_text, $found)) {
            $a_text = str_replace($found[0], "", $a_text);
        }
        // external links
        while (preg_match("~\[(xln$ws(url$ws=$ws\"([^\"])*\")$ws(target$ws=$ws(\"(Glossary|FAQ|Media)\"))?$ws)\]~i", $a_text, $found)) {
            $attribs = self::attribsToArray($found[2]);
            if (isset($attribs["url"])) {
                $a_text = self::replaceBBTagByMatching(
                    "[" . $found[1] . "]",
                    "[/xln]",
                    "ExtLink",
                    $a_text,
                    [
                        "Href" => $attribs["url"]
                    ]
                );
            } else {
                $a_text = str_replace("[" . $found[1] . "]", "[error: xln" . $found[1] . "]", $a_text);
            }
        }

        // ie/tinymce fix for links without "", see bug #8391
        /* deprecated, should be removed soon, old IE not supported anymore
        while (preg_match('~\[(xln$ws(url$ws=$ws(([^]])*)))$ws\]~i', $a_text, $found)) {
            if ($found[3] != "") {
                $a_text = str_replace("[" . $found[1] . "]", "<ExtLink Href=\"" . $found[3] . "\">", $a_text);
            } else {
                $a_text = str_replace("[" . $found[1] . "]", "[error: xln" . $found[1] . "]", $a_text);
            }
        }
        $a_text = preg_replace('~\[\/xln\]~i', "</ExtLink>", $a_text);*/

        // anchor
        $ws = "[ \t\r\f\v\n]*";
        while (preg_match("~\[(anc$ws(name$ws=$ws\"([^\"])*\")$ws)\]~i", $a_text, $found)) {
            $attribs = self::attribsToArray($found[2]);
            $a_text = self::replaceBBTagByMatching(
                "[" . $found[1] . "]",
                "[/anc]",
                "Anchor",
                $a_text,
                [
                    "Name" => $attribs["name"]
                ]
            );
        }

        // marked text
        while (preg_match("~\[(marked$ws(class$ws=$ws\"([^\"])*\")$ws)\]~i", $a_text, $found)) {
            $attribs = self::attribsToArray($found[2]);
            if (isset($attribs["class"])) {
                $a_text = self::replaceBBTagByMatching(
                    "[" . $found[1] . "]",
                    "[/marked]",
                    "Marked",
                    $a_text,
                    [
                        "Class" => $attribs["class"]
                    ]
                );
            } else {
                $a_text = str_replace("[" . $found[1] . "]", "[error:marked" . $found[1] . "]", $a_text);
            }
        }

        if (!is_null($log)) {
            $log->debug("...finish: " . substr($a_text, 0, 1000));
        }

        return $a_text;
    }

    protected static function isValidTagContent(string $content): bool
    {
        $use_internal_errors = libxml_use_internal_errors(true);
        $sxe = simplexml_load_string("<?xml version='1.0'?><dummy>" . $content . "</dummy>");
        libxml_use_internal_errors($use_internal_errors);
        return ($sxe !== false);
    }

    /**
     * Transforms [iln...]...[\iln] to <IntLink...>...</IntLink>, if content is valid,
     * otherwise it removes the bb tags
     * @param string $start_tag e.g. [iln page="30"]
     */
    protected static function replaceBBTagByMatching(
        string $start_tag,
        string $end_tag,
        string $xml_tag_name,
        string $text,
        array $attribs
    ): string {
        $attrib_str = "";
        foreach ($attribs as $key => $value) {
            if ($value != "") {
                $attrib_str .= ' ' . $key . '="' . $value . '"';
            }
        }

        $ok = false;

        $pos1 = strpos($text, $start_tag);

        $pos2 = null;
        if ($end_tag != "") {
            $pos2 = strpos($text, $end_tag, $pos1 + strlen($start_tag));
            if (is_int($pos1) && is_int($pos2)) {
                $between = substr($text, $pos1 + strlen($start_tag), $pos2 - ($pos1 + strlen($start_tag)));
                $ok = self::isValidTagContent($between);
            }
        } else {    // no end tag
            if (is_int($pos1)) {
                $ok = true;
            }
        }
        $short = ($end_tag == "")
            ? "/"
            : "";

        $slash_chars = '/[]?';

        if ($ok) {
            // replace start tag
            $text = preg_replace(
                '/' . addcslashes($start_tag, $slash_chars) . '/i',
                "<" . $xml_tag_name . $attrib_str . $short . ">",
                $text,
                1
            );

            // replace end tag
            if ($end_tag != "") {
                $text = preg_replace('~' . addcslashes($end_tag, $slash_chars) . '~i', "</" . $xml_tag_name . ">", $text, 1);
            }
        } else {
            // remove start tag
            if (is_int($pos1)) {
                $text = preg_replace(
                    '/' . addcslashes($start_tag, $slash_chars) . '/i',
                    "",
                    $text,
                    1
                );
            }
            // remove end tag
            if (is_int($pos2)) {
                $text = preg_replace(
                    '~' . addcslashes($end_tag, $slash_chars) . '~i',
                    "",
                    $text,
                    1
                );
            }
        }
        return $text;
    }

    /**
     * internal links to xml
     */
    public static function intLinks2xml(
        string $a_text
    ): string {
        global $DIC;

        $objDefinition = $DIC["objDefinition"];
        $obj_id = 0;
        $rtypes = $objDefinition->getAllRepositoryTypes();

        // internal links
        //$any = "[^\]]*";	// this doesn't work :-(
        $ws = "[ \t\r\f\v\n]*";
        $ltypes = "page|chap|term|media|obj|dfile|sess|wpage|ppage|" . implode("|", $rtypes);
        // empty internal links
        while (preg_match('~\[(iln' . $ws . '((inst' . $ws . '=' . $ws . '([\"0-9])*)?' . $ws .
            "((" . $ltypes . ")$ws=$ws([\"0-9])*)$ws" .
            "(target$ws=$ws(\"(New|FAQ|Media)\"))?$ws(anchor$ws=$ws(\"([^\"])*\"))?$ws))\]\[\/iln\]~i", $a_text, $found)) {
            $a_text = str_replace($found[0], "", $a_text);
        }
        while (preg_match('~\[(iln' . $ws . '((inst' . $ws . '=' . $ws . '([\"0-9])*)?' . $ws .
            "((" . $ltypes . ")$ws=$ws([\"0-9])*)$ws" .
            "(target$ws=$ws(\"(New|FAQ|Media)\"))?$ws(anchor$ws=$ws(\"([^\"])*\"))?$ws))\]~i", $a_text, $found)) {
            $attribs = self::attribsToArray($found[2]);
            $inst_str = $attribs["inst"] ?? "";
            // pages
            if (isset($attribs["page"])) {
                $a_text = self::replaceBBTagByMatching(
                    "[" . $found[1] . "]",
                    "[/iln]",
                    "IntLink",
                    $a_text,
                    [
                        "Target" => "il_" . $inst_str . "_pg_" . $attribs['page'],
                        "Type" => "PageObject",
                        "TargetFrame" => $found[10] ?? "",
                        "Anchor" => $attribs["anchor"] ?? ""
                    ]
                );
            }
            // chapters
            elseif (isset($attribs["chap"])) {
                $a_text = self::replaceBBTagByMatching(
                    "[" . $found[1] . "]",
                    "[/iln]",
                    "IntLink",
                    $a_text,
                    [
                        "Target" => "il_" . $inst_str . "_st_" . $attribs['chap'],
                        "Type" => "StructureObject",
                        "TargetFrame" => $found[10] ?? ""
                    ]
                );
            }
            // glossary terms
            elseif (isset($attribs["term"])) {
                $a_text = self::replaceBBTagByMatching(
                    "[" . $found[1] . "]",
                    "[/iln]",
                    "IntLink",
                    $a_text,
                    [
                        "Target" => "il_" . $inst_str . "_git_" . $attribs['term'],
                        "Type" => "GlossaryItem",
                        "TargetFrame" => (($found[10] ?? "") == "New")
                            ? "New"
                            : "Glossary"
                    ]
                );
            }
            // wiki pages
            elseif (isset($attribs["wpage"])) {
                $a_text = self::replaceBBTagByMatching(
                    "[" . $found[1] . "]",
                    "[/iln]",
                    "IntLink",
                    $a_text,
                    [
                        "Target" => "il_" . $inst_str . "_wpage_" . $attribs['wpage'],
                        "Type" => "WikiPage"
                    ]
                );
            }
            // portfolio pages
            elseif (isset($attribs["ppage"])) {
                $a_text = self::replaceBBTagByMatching(
                    "[" . $found[1] . "]",
                    "[/iln]",
                    "IntLink",
                    $a_text,
                    [
                        "Target" => "il_" . $inst_str . "_ppage_" . $attribs['ppage'],
                        "Type" => "PortfolioPage"
                    ]
                );
            }
            // media object
            elseif (isset($attribs["media"])) {
                $a_text = self::replaceBBTagByMatching(
                    "[" . $found[1] . "]",
                    "[/iln]",
                    "IntLink",
                    $a_text,
                    [
                        "Target" => "il_" . $inst_str . "_mob_" . $attribs['media'],
                        "Type" => "MediaObject",
                        "TargetFrame" => $found[10] ?? ""
                    ]
                );
            }
            // direct download file (no repository object)
            elseif (isset($attribs["dfile"])) {
                $a_text = self::replaceBBTagByMatching(
                    "[" . $found[1] . "]",
                    "[/iln]",
                    "IntLink",
                    $a_text,
                    [
                        "Target" => "il_" . $inst_str . "_dfile_" . $attribs['dfile'],
                        "Type" => "File"
                    ]
                );
            }
            // repository items (id is ref_id (will be used internally but will
            // be replaced by object id for export purposes)
            else {
                foreach ($objDefinition->getAllRepositoryTypes() as $t) {
                    if (isset($attribs[$t])) {
                        $obj_id = $attribs[$t];
                    }
                }
                if (isset($attribs["obj"])) {
                    $obj_id = $attribs["obj"];
                }

                if ($obj_id > 0) {
                    if ($inst_str == "") {
                        $a_text = self::replaceBBTagByMatching(
                            "[" . $found[1] . "]",
                            "[/iln]",
                            "IntLink",
                            $a_text,
                            [
                                "Target" => "il_" . $inst_str . "_obj_" . $obj_id,
                                "Type" => "RepositoryItem"
                            ]
                        );
                    } else {
                        $a_text = self::replaceBBTagByMatching(
                            "[" . $found[1] . "]",
                            "[/iln]",
                            "IntLink",
                            $a_text,
                            [
                                "Target" => "il_" . $inst_str . "_" . $found[6] . "_" . $obj_id,
                                "Type" => "RepositoryItem"
                            ]
                        );
                    }
                } else {
                    $a_text = preg_replace('/\[' . $found[1] . '\]/i', "[error: iln" . $found[1] . "]", $a_text);
                }
            }
        }

        while (preg_match("~\[(iln$ws((inst$ws=$ws([\"0-9])*)?" . $ws . "media$ws=$ws([\"0-9])*)$ws)/\]~i", $a_text, $found)) {
            $attribs = self::attribsToArray($found[2]);
            $inst_str = $attribs["inst"] ?? "";
            $a_text = self::replaceBBTagByMatching(
                "[" . $found[1] . "/]",
                "",
                "IntLink",
                $a_text,
                [
                    "Target" => "il_" . $inst_str . "_mob_" . $attribs['media'],
                    "Type" => "MediaObject"
                ]
            );
        }

        // user
        while (preg_match("~\[(iln$ws((inst$ws=$ws([\"0-9])*)?" . $ws . "user$ws=$ws(\"([^\"])*)\")$ws)/\]~i", $a_text, $found)) {
            $attribs = self::attribsToArray($found[2]);
            $inst_str = $attribs["inst"] ?? "";
            $user_id = ilObjUser::_lookupId($attribs['user']);
            $a_text = self::replaceBBTagByMatching(
                "[" . $found[1] . "/]",
                "",
                "IntLink",
                $a_text,
                [
                    "Target" => "il_" . $inst_str . "_user_" . $user_id,
                    "Type" => "User"
                ]
            );
        }

        return $a_text;
    }


    /**
     * Converts xml from DB to output in edit textarea.
     * @param	string	$a_text		xml from db
     * @return	string	string ready for edit textarea
     */
    public static function input2xmlReplaceLists(
        string $a_text
    ): string {
        $rows = explode("<br />", $a_text . "<br />");
        //var_dump($a_text);

        $old_level = 0;

        $text = "";
        $clist = [];

        foreach ($rows as $row) {
            $level = 0;
            if (str_replace("#", "*", substr($row, 0, 3)) == "***") {
                $level = 3;
            } elseif (str_replace("#", "*", substr($row, 0, 2)) == "**") {
                $level = 2;
            } elseif (str_replace("#", "*", substr($row, 0, 1)) == "*") {
                $level = 1;
            }

            // end previous line
            if ($level < $old_level) {
                for ($i = $old_level; $i > $level; $i--) {
                    $text .= "</SimpleListItem></" . $clist[$i] . ">";
                }
                if ($level > 0) {
                    $text .= "</SimpleListItem>";
                }
            } elseif ($old_level > 0 && $level > 0 && ($level == $old_level)) {
                $text .= "</SimpleListItem>";
            } elseif (($level == $old_level) && $text != "") {
                $text .= "<br />";
            }

            // start next line
            if ($level > $old_level) {
                for ($i = $old_level + 1; $i <= $level; $i++) {
                    if (substr($row, $i - 1, 1) == "*") {
                        $clist[$i] = "SimpleBulletList";
                    } else {
                        $clist[$i] = "SimpleNumberedList";
                    }
                    $text .= "<" . $clist[$i] . "><SimpleListItem>";
                }
            } elseif ($old_level > 0 && $level > 0) {
                $text .= "<SimpleListItem>";
            }
            $text .= substr($row, $level);

            $old_level = $level;
        }

        // remove "<br />" at the end
        if (substr($text, strlen($text) - 6) == "<br />") {
            $text = substr($text, 0, strlen($text) - 6);
        }

        return $text;
    }

    /**
     * Replaces <list> tags with *
     * @param string $a_text xml from db
     * @return string string containing * for lists
     */
    public static function xml2outputReplaceLists(
        string $a_text
    ): string {
        $list_start = false;
        $li = false;

        $segments = ilPCParagraph::segmentString($a_text, array("<SimpleBulletList>", "</SimpleBulletList>",
            "</SimpleListItem>", "<SimpleListItem>", "<SimpleListItem/>", "<SimpleNumberedList>", "</SimpleNumberedList>"));

        $current_list = array();
        $text = "";
        for ($i = 0, $iMax = count($segments); $i < $iMax; $i++) {
            if ($segments[$i] == "<SimpleBulletList>") {
                if (count($current_list) == 0) {
                    $list_start = true;
                }
                $current_list[] = "*";
                $li = false;
            } elseif ($segments[$i] == "<SimpleNumberedList>") {
                if (count($current_list) == 0) {
                    $list_start = true;
                }
                $current_list[] = "#";
                $li = false;
            } elseif ($segments[$i] == "</SimpleBulletList>") {
                array_pop($current_list);
                $li = false;
            } elseif ($segments[$i] == "</SimpleNumberedList>") {
                array_pop($current_list);
                $li = false;
            } elseif ($segments[$i] == "<SimpleListItem>") {
                $li = true;
            } elseif ($segments[$i] == "</SimpleListItem>") {
                $li = false;
            } elseif ($segments[$i] == "<SimpleListItem/>") {
                if ($list_start) {
                    $text .= "<br />";
                    $list_start = false;
                }
                foreach ($current_list as $list) {
                    $text .= $list;
                }
                $text .= "<br />";
                $li = false;
            } else {
                if ($li) {
                    if ($list_start) {
                        $text .= "<br />";
                        $list_start = false;
                    }
                    foreach ($current_list as $list) {
                        $text .= $list;
                    }
                }
                $text .= $segments[$i];
                if ($li) {
                    $text .= "<br />";
                }
                $li = false;
            }
        }

        // remove trailing <br />, if text ends with list
        if ((($segments[count($segments) - 1] ?? "") === "</SimpleBulletList>" ||
            ($segments[count($segments) - 1] ?? "") === "</SimpleNumberedList>") &&
            substr($text, strlen($text) - 6) === "<br />") {
            $text = substr($text, 0, -6);
        }

        return $text;
    }

    /**
     * Segments a string into an array at each position of a substring
     */
    public static function segmentString(
        string $a_haystack,
        array $a_needles
    ): array {
        $segments = array();
        $found_needle = "";

        $nothing_found = false;
        while (!$nothing_found) {
            $nothing_found = true;
            $found = -1;
            foreach ($a_needles as $needle) {
                $pos = stripos($a_haystack, $needle);
                if (is_int($pos) && ($pos < $found || $found == -1)) {
                    $found = $pos;
                    $found_needle = $needle;
                    $nothing_found = false;
                }
            }
            if ($found > 0) {
                $segments[] = substr($a_haystack, 0, $found);
                $a_haystack = substr($a_haystack, $found);
            }
            if ($found > -1) {
                $segments[] = substr($a_haystack, 0, strlen($found_needle));
                $a_haystack = substr($a_haystack, strlen($found_needle));
            }
        }
        if ($a_haystack != "") {
            $segments[] = $a_haystack;
        }

        return $segments;
    }

    /**
     * Converts xml from DB to output in edit textarea.
     * @param string $a_text xml from db
     * @return string string ready for edit textarea
     */
    public static function xml2output(
        string $a_text,
        bool $a_wysiwyg = false,
        bool $a_replace_lists = true,
        bool $unmask = true
    ): string {
        // note: the order of the processing steps is crucial
        // and should be the same as in input2xml() in REVERSE order!

        // xml to bb code
        $any = "[^>]*";
        $tframestr = "";

        foreach (self::getBBMap() as $bb => $tag) {
            $a_text = preg_replace('~<' . $tag . '[^>]*>~i', "[" . $bb . "]", $a_text);
            $a_text = preg_replace('~</' . $tag . '>~i', "[/" . $bb . "]", $a_text);
            $a_text = preg_replace('~<' . $tag . '/>~i', "[" . $bb . "][/" . $bb . "]", $a_text);
        }

        // replace lists
        if ($a_replace_lists) {
            //echo "<br>".htmlentities($a_text);
            $a_text = ilPCParagraph::xml2outputReplaceLists($a_text);
            //echo "<br>".htmlentities($a_text);
        }

        // internal links
        while (preg_match('~<IntLink(' . $any . ')>~i', $a_text, $found)) {
            $attribs = self::attribsToArray($found[1]);
            $target = explode("_", $attribs["Target"]);
            $target_id = $target[count($target) - 1];
            $inst_str = (!is_int(strpos($attribs["Target"], "__")))
                ? $inst_str = "inst=\""     . ($target[1] ?? ''). "\" "
                : $inst_str = "";
            switch ($attribs["Type"]) {
                case "PageObject":
                    $tframestr = (!empty($attribs["TargetFrame"]))
                        ? " target=\"" . $attribs["TargetFrame"] . "\""
                        : "";
                    $ancstr = (!empty($attribs["Anchor"]))
                        ? ' anchor="' . $attribs["Anchor"] . '"'
                        : "";
                    $a_text = preg_replace('~<IntLink' . $found[1] . '>~i', "[iln " . $inst_str . "page=\"" . $target_id . "\"$tframestr$ancstr]", $a_text);
                    break;

                case "StructureObject":
                    $tframestr = (!empty($attribs["TargetFrame"]))
                        ? " target=\"" . $attribs["TargetFrame"] . "\""
                        : "";
                    $a_text = preg_replace('~<IntLink' . $found[1] . '>~i', "[iln " . $inst_str . "chap=\"" . $target_id . "\"$tframestr]", $a_text);
                    break;

                case "GlossaryItem":
                    $tframestr = (empty($attribs["TargetFrame"]) || $attribs["TargetFrame"] == "Glossary")
                        ? ""
                        : " target=\"" . $attribs["TargetFrame"] . "\"";
                    $a_text = preg_replace('~<IntLink' . $found[1] . '>~i', "[iln " . $inst_str . "term=\"" . $target_id . "\"" . $tframestr . "]", $a_text);
                    break;

                case "WikiPage":
                    $tframestr = "";
                    $a_text = preg_replace('~<IntLink' . $found[1] . '>~i', "[iln " . $inst_str . "wpage=\"" . $target_id . "\"" . $tframestr . "]", $a_text);
                    break;

                case "PortfolioPage":
                    $tframestr = "";
                    $a_text = preg_replace('~<IntLink' . $found[1] . '>~i', "[iln " . $inst_str . "ppage=\"" . $target_id . "\"" . $tframestr . "]", $a_text);
                    break;

                case "MediaObject":
                    if (empty($attribs["TargetFrame"])) {
                        $a_text = preg_replace('~<IntLink' . $found[1] . '>~i', "[iln " . $inst_str . "media=\"" . $target_id . "\"/]", $a_text);
                    } else {
                        $a_text = preg_replace('~<IntLink' . $found[1] . '>~i', "[iln media=\"" . $target_id . "\"" .
                            " target=\"" . $attribs["TargetFrame"] . "\"]", $a_text);
                    }
                    break;

                // Repository Item (using ref id)
                case "RepositoryItem":
                    if ($inst_str == "") {
                        $target_type = ilObject::_lookupType($target_id, true);
                    } else {
                        $rtype = ($target[count($target) - 2] ?? "");
                        $target_type = $rtype;
                    }
                    $a_text = preg_replace('~<IntLink' . $found[1] . '>~i', "[iln " . $inst_str . "$target_type=\"" . $target_id . "\"" . $tframestr . "]", $a_text);
                    break;

                // Download File (not in repository, Object ID)
                case "File":
                    $a_text = preg_replace('~<IntLink' . $found[1] . '>~i', "[iln " . $inst_str . "dfile=\"" . $target_id . "\"" . $tframestr . "]", $a_text);
                    break;

                // User
                case "User":
                    $a_text = preg_replace('~<IntLink' . $found[1] . '>~i', "[iln " . $inst_str . "user=\"" . ilObjUser::_lookupLogin((int) $target_id) . "\"/]", $a_text);
                    break;

                default:
                    $a_text = preg_replace('~<IntLink' . $found[1] . '>~i', "[iln]", $a_text);
                    break;
            }
        }
        $a_text = str_replace("</IntLink>", "[/iln]", $a_text);

        // external links
        while (preg_match('~<ExtLink(' . $any . ')>~i', $a_text, $found)) {
            $attribs = self::attribsToArray($found[1]);
            //$found[1] = str_replace("?", "\?", $found[1]);
            $tstr = "";
            if (in_array(($attribs["TargetFrame"] ?? ""), array("FAQ", "Glossary", "Media"))) {
                $tstr = ' target="' . $attribs["TargetFrame"] . '"';
            }
            $a_text = str_replace("<ExtLink" . $found[1] . ">", "[xln url=\"" . $attribs["Href"] . "\"$tstr]", $a_text);
        }
        $a_text = str_replace("</ExtLink>", "[/xln]", $a_text);

        // anchor
        while (preg_match('~<Anchor(' . $any . '/)>~i', $a_text, $found)) {
            $attribs = self::attribsToArray($found[1]);
            $a_text = str_replace("<Anchor" . $found[1] . ">", "[anc name=\"" . $attribs["Name"] . "\"][/anc]", $a_text);
        }
        while (preg_match('~<Anchor(' . $any . ')>~i', $a_text, $found)) {
            $attribs = self::attribsToArray($found[1]);
            $a_text = str_replace("<Anchor" . $found[1] . ">", "[anc name=\"" . $attribs["Name"] . "\"]", $a_text);
        }
        $a_text = str_replace("</Anchor>", "[/anc]", $a_text);

        // marked text
        while (preg_match('~<Marked(' . $any . ')>~i', $a_text, $found)) {
            $attribs = self::attribsToArray($found[1]);
            $a_text = str_replace("<Marked" . $found[1] . ">", "[marked class=\"" . $attribs["Class"] . "\"]", $a_text);
        }
        $a_text = str_replace("</Marked>", "[/marked]", $a_text);

        // br to linefeed
        if (!$a_wysiwyg) {
            $a_text = str_replace("<br />", "\n", $a_text);
            $a_text = str_replace("<br/>", "\n", $a_text);
        }

        if (!$a_wysiwyg) {
            // prevent curly brackets from being swallowed up by template engine
            $a_text = str_replace("{", "&#123;", $a_text);
            $a_text = str_replace("}", "&#125;", $a_text);

            // unmask html
            if ($unmask) {
                $a_text = str_replace("&lt;", "<", $a_text);
                $a_text = str_replace("&gt;", ">", $a_text);
            }

            // this is needed to allow html like <tag attribute="value">... in paragraphs
            $a_text = str_replace("&quot;", "\"", $a_text);

            // make ampersands in (enabled) html attributes work
            // e.g. <a href="foo.php?n=4&t=5">hhh</a>
            $a_text = str_replace("&amp;", "&", $a_text);

            // make &gt; and $lt; work to allow (disabled) html descriptions
            if ($unmask) {
                $a_text = str_replace("&lt;", "&amp;lt;", $a_text);
                $a_text = str_replace("&gt;", "&amp;gt;", $a_text);
            }
        }
        return $a_text;
    }

    /**
     * This function splits a paragraph text that has been already
     * processed with input2xml at each header position =header1=,
     * ==header2== or ===header3=== and returns an array that contains
     * the single chunks.
     */
    public function autoSplit(string $a_text): array
    {
        $a_text = str_replace("=<SimpleBulletList>", "=<br /><SimpleBulletList>", $a_text);
        $a_text = str_replace("=<SimpleNumberedList>", "=<br /><SimpleNumberedList>", $a_text);
        $a_text = str_replace("</SimpleBulletList>=", "</SimpleBulletList><br />=", $a_text);
        $a_text = str_replace("</SimpleNumberedList>=", "</SimpleNumberedList><br />=", $a_text);
        $a_text = "<br />" . $a_text . "<br />";		// add preceding and trailing br

        $chunks = array();
        $c_text = $a_text;
        $head = "";
        while ($c_text != "") {
            //var_dump($c_text); flush();
            //echo "1";
            $s1 = strpos($c_text, "<br />=");
            if (is_int($s1)) {
                //echo "2";
                $s2 = strpos($c_text, "<br />==");
                if (is_int($s2) && $s2 <= $s1) {
                    //echo "3";
                    $s3 = strpos($c_text, "<br />===");
                    if (is_int($s3) && $s3 <= $s2) {		// possible level three header
                        //echo "4";
                        $n = strpos($c_text, "<br />", $s3 + 1);
                        if ($n > ($s3 + 9) && substr($c_text, $n - 3, 9) == "===<br />") {
                            //echo "5";
                            // found level three header
                            if ($s3 > 0 || $head != "") {
                                //echo "6";
                                $chunks[] = array("level" => 0,
                                    "text" => $this->removeTrailingBr($head . substr($c_text, 0, $s3)));
                                $head = "";
                            }
                            $chunks[] = array("level" => 3,
                                "text" => trim(substr($c_text, $s3 + 9, $n - $s3 - 12)));
                            $c_text = $this->handleNextBr(substr($c_text, $n + 6));
                        } else {
                            //echo "7";
                            $head .= substr($c_text, 0, $n);
                            $c_text = substr($c_text, $n);
                        }
                    } else {	// possible level two header
                        //echo "8";
                        $n = strpos($c_text, "<br />", $s2 + 1);
                        if ($n > ($s2 + 8) && substr($c_text, $n - 2, 8) == "==<br />") {
                            //echo "9";
                            // found level two header
                            if ($s2 > 0 || $head != "") {
                                //echo "A";
                                $chunks[] = array("level" => 0,
                                    "text" => $this->removeTrailingBr($head . substr($c_text, 0, $s2)));
                                $head = "";
                            }
                            $chunks[] = array("level" => 2, "text" => trim(substr($c_text, $s2 + 8, $n - $s2 - 10)));
                            $c_text = $this->handleNextBr(substr($c_text, $n + 6));
                        } else {
                            //echo "B";
                            $head .= substr($c_text, 0, $n);
                            $c_text = substr($c_text, $n);
                        }
                    }
                } else {	// possible level one header
                    $n = strpos($c_text, "<br />", $s1 + 1);
                    if ($n > ($s1 + 7) && substr($c_text, $n - 1, 7) == "=<br />") {
                        // found level one header
                        if ($s1 > 0 || $head != "") {
                            $chunks[] = array("level" => 0,
                                "text" => $this->removeTrailingBr($head . substr($c_text, 0, $s1)));
                            $head = "";
                        }
                        $chunks[] = array("level" => 1, "text" => trim(substr($c_text, $s1 + 7, $n - $s1 - 8)));
                        $c_text = $this->handleNextBr(substr($c_text, $n + 6));
                    //echo "<br>ctext:".htmlentities($c_text)."<br>";
                    } else {
                        $head .= substr($c_text, 0, $n);
                        $c_text = substr($c_text, $n);
                        //echo "<br>head:".$head."c_text:".$c_text."<br>";
                    }
                }
            } else {
                //echo "G";
                $chunks[] = array("level" => 0, "text" => $head . $c_text);
                $head = "";
                $c_text = "";
            }
        }
        if (count($chunks) == 0) {
            $chunks[] = array("level" => 0, "text" => "");
        }


        // remove preceding br
        if (substr($chunks[0]["text"], 0, 6) == "<br />") {
            $chunks[0]["text"] = substr($chunks[0]["text"], 6);
        }

        // remove trailing br
        if (substr(
            $chunks[count($chunks) - 1]["text"],
            strlen($chunks[count($chunks) - 1]["text"]) - 6,
            6
        ) == "<br />") {
            $chunks[count($chunks) - 1]["text"] =
                substr($chunks[count($chunks) - 1]["text"], 0, strlen($chunks[count($chunks) - 1]["text"]) - 6);
            if ($chunks[count($chunks) - 1]["text"] == "") {
                unset($chunks[count($chunks) - 1]);
            }
        }
        return $chunks;
    }

    /**
     * Remove preceding <br />
     */
    public function handleNextBr(string $a_str): string
    {
        // do not remove, if next line starts with a "=", otherwise two
        // headlines in a row will not be recognized
        if (substr($a_str, 0, 6) == "<br />" && substr($a_str, 6, 1) != "=") {
            $a_str = substr($a_str, 6);
        } else {
            // if next line starts with a "=" we need to reinsert the <br />
            // otherwise it will not be recognized
            if (substr($a_str, 0, 1) == "=") {
                $a_str = "<br />" . $a_str;
            }
        }
        return $a_str;
    }

    /**
     * Remove trailing <br />
     */
    public function removeTrailingBr(string $a_str): string
    {
        if (substr($a_str, strlen($a_str) - 6) == "<br />") {
            $a_str = substr($a_str, 0, strlen($a_str) - 6);
        }
        return $a_str;
    }

    /**
     * Need to override getType from ilPageContent to distinguish between Pararagraph and Source
     */
    public function getType(): string
    {
        return ($this->getCharacteristic() == "Code")
            ? "src"
            : parent::getType();
    }

    ////
    //// Ajax related procedures
    ////

    /**
     * Save input coming from ajax
     * @return bool|string
     * @throws ilCOPagePCEditException
     * @throws ilCOPageUnknownPCTypeException
     */
    public function saveJS(
        ilPageObject $a_pg_obj,
        string $a_content,
        string $a_char,
        string $a_pc_id,
        string $a_insert_at = "",
        bool $from_placeholder = false
    ) {
        $ilUser = $this->user;

        $a_content = str_replace("<br>", "<br />", $a_content);

        $this->log->debug("step 1: " . substr($a_content, 0, 1000));
        try {
            $t = self::handleAjaxContent($a_content);
        } catch (Exception $ex) {
            return $ex->getMessage() . ": " . htmlentities($a_content);
        }
        $this->log->debug("step 2: " . substr($t["text"], 0, 1000));
        if ($t === false) {
            return false;
        }
        $pc_id = explode(":", $a_pc_id);
        $insert_at = explode(":", $a_insert_at);
        $t_id = explode(":", $t["id"]);

        // insert new paragraph
        if ($a_insert_at != "") {
            $par = new ilPCParagraph($this->getPage());
            $par->create($a_pg_obj, $insert_at[0], $insert_at[1], $from_placeholder);
            $par->writePCId($pc_id[1]);
        } else {
            $par = $a_pg_obj->getContentObject($pc_id[0], $pc_id[1]);

            if (!$par) {
                return $this->lng->txt("copg_page_element_not_found") . " (saveJS): " . $pc_id[0] . ":" . $pc_id[1] . ".";
            }
        }
        /*
                if ($a_insert_at != "") {
                    $pc_id = $a_pg_obj->generatePCId();
                    $par->writePCId($pc_id);
                    $this->inserted_pc_id = $pc_id;
                } else {
                    $this->inserted_pc_id = $pc_id[1];
                }*/

        $par->setLanguage($ilUser->getLanguage());
        $par->setCharacteristic($t["class"]);

        $t2 = $par->input2xml($t["text"], true, false);
        $this->log->debug("step 3: " . substr($t2, 0, 1000));

        $t2 = ilPCParagraph::handleAjaxContentPost($t2);
        $this->log->debug("step 4: " . substr($t2, 0, 1000));

        $updated = $par->setText($t2, true);

        if ($updated !== true) {
            echo $updated;
            exit;
        }
        $updated = $par->updatePage($a_pg_obj);
        return $updated;
    }

    /**
     * Get last inserted pc ids
     */
    public function getLastSavedPCId(
        ilPageObject $a_pg_obj,
        bool $a_as_ajax_str = false
    ): string {
        $sep = "";

        if ($a_as_ajax_str) {
            $a_pg_obj->stripHierIDs();
            $a_pg_obj->addHierIDs();
            $ids = "###";
            $combined = $a_pg_obj->getHierIdsForPCIds(
                array($this->inserted_pc_id)
            );
            foreach ($combined as $pc_id => $hier_id) {
                //echo "1";
                $ids .= $sep . $hier_id . ":" . $pc_id;
                $sep = ";";
            }
            $ids .= "###";
            return $ids;
        }

        return $this->inserted_pc_id;
    }


    /**
     * Handle ajax content
     */
    public static function handleAjaxContent(
        string $a_content
    ): ?array {
        $a_content = "<dummy>" . $a_content . "</dummy>";

        $doc = new DOMDocument();

        $content = ilUtil::stripSlashes($a_content, false);

        //		$content = str_replace("&lt;", "<", $content);
        //		$content = str_replace("&gt;", ">", $content);
        //echo "\n\n".$content;
        $res = $doc->loadXML($content);

        if (!$res) {
            return null;
        }

        // convert tags
        $xpath = new DOMXpath($doc);

        $tags = self::getXMLTagMap();

        $elements = $xpath->query("//span");
        while (!is_null($elements) && !is_null($element = $elements->item(0))) {
            //$element = $elements->item(0);
            $class = $element->getAttribute("class");
            if (substr($class, 0, 16) == "ilc_text_inline_") {
                $class_arr = explode(" ", $class);
                $tag = substr($class_arr[0], 16);
                if (isset($tags[$tag])) {		// known tag like strong
                    $cnode = ilDOM2Util::changeName($element, "il" . substr($class_arr[0], 16), false);
                } else {		// unknown tag -> marked text
                    $cnode = ilDOM2Util::changeName($element, "ilMarked", false);
                    $cnode->setAttribute("Class", substr($class_arr[0], 16));
                }
                for ($i = 1, $iMax = count($class_arr); $i < $iMax; $i++) {
                    $tag = substr($class_arr[$i], 16);
                    if (isset($tags[$tag])) {		// known tag like strong
                        $cnode = ilDOM2Util::addParent($cnode, "il" . substr($class_arr[$i], 16));
                    } else {	// unknown tag -> marked element
                        $cnode = ilDOM2Util::addParent($cnode, "ilMarked");
                        $cnode->setAttribute("Class", substr($class_arr[$i], 16));
                    }
                }
            } else {
                ilDOM2Util::replaceByChilds($element);
            }

            $elements = $xpath->query("//span");
        }

        // convert tags
        $xpath = new DOMXpath($doc);
        $elements = $xpath->query("/dummy/div");

        $ret = array();
        if (!is_null($elements)) {
            foreach ($elements as $element) {
                $id = $element->getAttribute("id");
                $class = $element->getAttribute("class");
                $class = substr($class, 15);
                if (trim($class) == "") {
                    $class = "Standard";
                }

                $text = $doc->saveXML($element);
                $text = str_replace("<br/>", "\n", $text);

                // remove wrapping div
                $pos = strpos($text, ">");
                $text = substr($text, $pos + 1);
                $pos = strrpos($text, "<");
                $text = substr($text, 0, $pos);

                // todo: remove empty spans <span ...> </span>

                // replace tags by bbcode
                foreach (ilPageContentGUI::_getCommonBBButtons() as $bb => $cl) {
                    if (!in_array($bb, array("code", "tex", "fn", "xln"))) {
                        $text = str_replace(
                            "<il" . $cl . ">",
                            "[" . $bb . "]",
                            $text
                        );
                        $text = str_replace(
                            "</il" . $cl . ">",
                            "[/" . $bb . "]",
                            $text
                        );
                        $text = str_replace("<il" . $cl . "/>", "", $text);
                    }
                }
                $text = str_replace(
                    array("<code>", "</code>"),
                    array("[code]", "[/code]"),
                    $text
                );
                $text = str_replace(
                    array('<sup class="ilc_sup_Sup">', "</sup>"),
                    array("[sup]", "[/sup]"),
                    $text
                );
                $text = str_replace(
                    array('<sub class="ilc_sub_Sub">', "</sub>"),
                    array("[sub]", "[/sub]"),
                    $text
                );

                $text = str_replace("<code/>", "", $text);
                $text = str_replace('<ul class="ilc_list_u_BulletedList"/>', "", $text);
                $text = str_replace('<ul class="ilc_list_o_NumberedList"/>', "", $text);

                // replace marked text
                // external links
                $any = "[^>]*";
                while (preg_match('~<ilMarked(' . $any . ')>~i', $text, $found)) {
                    $attribs = self::attribsToArray($found[1]);
                    $text = str_replace("<ilMarked" . $found[1] . ">", "[marked class=\"" . $attribs["Class"] . "\"]", $text);
                }
                $text = str_replace("</ilMarked>", "[/marked]", $text);


                $ret[] = array("text" => $text, "id" => $id, "class" => $class);
            }
        }

        // we should only have one here!
        return $ret[0];
    }

    /**
     * Post input2xml handling of ajax content
     */
    public static function handleAjaxContentPost(string $text): string
    {
        $text = str_replace(
            array("&lt;ul&gt;", "&lt;/ul&gt;"),
            array("<SimpleBulletList>", "</SimpleBulletList>"),
            $text
        );
        $text = str_replace(
            array("&lt;ul class='ilc_list_u_BulletedList'&gt;", "&lt;/ul&gt;"),
            array("<SimpleBulletList>", "</SimpleBulletList>"),
            $text
        );
        $text = str_replace(
            array("&lt;ul class=\"ilc_list_u_BulletedList\"&gt;", "&lt;/ul&gt;"),
            array("<SimpleBulletList>", "</SimpleBulletList>"),
            $text
        );
        $text = str_replace(
            array("&lt;ol&gt;", "&lt;/ol&gt;"),
            array("<SimpleNumberedList>", "</SimpleNumberedList>"),
            $text
        );
        $text = str_replace(
            array("&lt;ol class='ilc_list_o_NumberedList'&gt;", "&lt;/ol&gt;"),
            array("<SimpleNumberedList>", "</SimpleNumberedList>"),
            $text
        );
        $text = str_replace(
            array("&lt;ol class=\"ilc_list_o_NumberedList\"&gt;", "&lt;/ol&gt;"),
            array("<SimpleNumberedList>", "</SimpleNumberedList>"),
            $text
        );
        $text = str_replace(
            array("&lt;li&gt;", "&lt;/li&gt;"),
            array("<SimpleListItem>", "</SimpleListItem>"),
            $text
        );
        $text = str_replace(
            array("&lt;li class='ilc_list_item_StandardListItem'&gt;", "&lt;/li&gt;"),
            array("<SimpleListItem>", "</SimpleListItem>"),
            $text
        );
        $text = str_replace(
            array("&lt;li class=\"ilc_list_item_StandardListItem\"&gt;", "&lt;/li&gt;"),
            array("<SimpleListItem>", "</SimpleListItem>"),
            $text
        );

        $text = str_replace(
            array("&lt;li class=\"ilc_list_item_StandardListItem\"/&gt;"),
            array("<SimpleListItem></SimpleListItem>"),
            $text
        );

        $text = str_replace("<SimpleBulletList><br />", "<SimpleBulletList>", $text);
        $text = str_replace("<SimpleNumberedList><br />", "<SimpleNumberedList>", $text);
        $text = str_replace("<br /><SimpleBulletList>", "<SimpleBulletList>", $text);
        $text = str_replace("<br /><SimpleNumberedList>", "<SimpleNumberedList>", $text);
        $text = str_replace("</SimpleBulletList><br />", "</SimpleBulletList>", $text);
        $text = str_replace("</SimpleNumberedList><br />", "</SimpleNumberedList>", $text);
        $text = str_replace("</SimpleListItem><br />", "</SimpleListItem>", $text);

        return $text;
    }

    /**
     * Update page object
     * (it would be better to have this centralized and to change the constructors
     * and pass the page object instead the dom object)
     * @return array|bool
     * @throws ilDateTimeException
     */
    public function updatePage(ilPageObject $a_page)
    {
        $a_page->beforePageContentUpdate($this);
        return $a_page->update();
    }

    public function autoLinkGlossaries(
        array $a_glos
    ): void {
        if (is_array($a_glos) && count($a_glos) > 0) {
            // check which terms occur in the text (we may
            // get some false positives due to the strip_tags, but
            // we do not want to find strong or list or other stuff
            // within the tags
            $text = strip_tags($this->getText());
            $found_terms = array();
            foreach ($a_glos as $glo) {
                if (ilObject::_lookupType($glo) == "glo") {
                    $ref_ids = ilObject::_getAllReferences($glo);
                    $glo_ref_id = current($ref_ids);
                    if ($glo_ref_id > 0) {
                        $terms = ilGlossaryTerm::getTermList([$glo_ref_id]);
                        foreach ($terms as $t) {
                            if (is_int(stripos($text, $t["term"]))) {
                                $found_terms[$t["id"]] = $t;
                            }
                        }
                    }
                }
            }
            // did we find anything? -> modify content
            if (count($found_terms) > 0) {
                self::linkTermsInDom($this->dom, $found_terms, $this->par_node);
            }
        }
    }

    /**
     * Link terms in a dom page object in bb style
     */
    protected static function linkTermsInDom(
        php4DOMDocument $a_dom,
        array $a_terms,
        php4DOMElement $a_par_node = null
    ): void {
        $par_node = null;
        // sort terms by their length (shortes first)
        // to prevent that nested tags are builded
        foreach ($a_terms as $k => $t) {
            $a_terms[$k]["termlength"] = strlen($t["term"]);
        }
        $a_terms = ilArrayUtil::sortArray($a_terms, "termlength", "asc", true);


        if ($a_dom instanceof php4DOMDocument) {
            $a_dom = $a_dom->myDOMDocument;
        }
        if ($a_par_node instanceof php4DOMElement) {
            $par_node = $a_par_node->myDOMNode;
        }

        $xpath = new DOMXPath($a_dom);

        if ($par_node == null) {
            $parnodes = $xpath->query("//Paragraph[@Characteristic != 'Code']");
        } else {
            $parnodes = $xpath->query(".//Paragraph[@Characteristic != 'Code']", $par_node->parentNode);
        }

        $strrPos = function (string $a_haystack, string $a_needle, ?int $a_offset = null): int {
            if (function_exists("mb_strpos")) {
                return mb_strrpos($a_haystack, $a_needle, $a_offset, "UTF-8");
            } else {
                return strrpos($a_haystack, $a_needle, $a_offset);
            }
        };

        foreach ($parnodes as $parnode) {
            $textnodes = $xpath->query('.//text()', $parnode);
            foreach ($textnodes as $node) {
                $p = $node->getNodePath();

                // we do not change text nodes inside of links
                if (!is_int(strpos($p, "/IntLink")) &&
                    !is_int(strpos($p, "/ExtLink"))) {
                    $node_val = $node->nodeValue;

                    // all terms
                    foreach ($a_terms as $t) {
                        $pos = ilStr::strIPos($node_val, $t["term"]);

                        // if term found
                        while (is_int($pos)) {
                            // check if we are in a tex tag, see #22261
                            $tex_bpos = $strrPos(ilStr::subStr($node_val, 0, $pos), "[tex]");
                            $tex_epos = ilStr::strPos($node_val, "[/tex]", $tex_bpos);
                            if ($tex_bpos > 0 && $tex_epos > 0 && $tex_bpos < $pos && $tex_epos > $pos) {
                                $pos += ilStr::strLen($t["term"]);
                            } else {

                                // check if the string is not included in another word
                                // note that []
                                $valid_limiters = array("", " ", "&nbsp;", ".", ",", ":", ";", "!", "?", "\"", "'", "(", ")");
                                $b = ($pos > 0)
                                    ? ilStr::subStr($node_val, $pos - 1, 1)
                                    : "";
                                $a = ilStr::subStr($node_val, $pos + ilStr::strLen($t["term"]), 1);
                                if ((in_array($b, $valid_limiters) || htmlentities($b, null, 'utf-8') == "&nbsp;") && in_array($a, $valid_limiters)) {
                                    $mid = '[iln term="' . $t["id"] . '"]' .
                                        ilStr::subStr($node_val, $pos, ilStr::strLen($t["term"])) .
                                        "[/iln]";

                                    $node_val = ilStr::subStr($node_val, 0, $pos) .
                                        $mid .
                                        ilStr::subStr($node_val, $pos + ilStr::strLen($t["term"]));

                                    $pos += ilStr::strLen($mid);
                                } else {
                                    $pos += ilStr::strLen($t["term"]);
                                }
                            }
                            $pos = ilStr::strIPos($node_val, $t["term"], $pos);
                        }

                        // insert [iln] tags
                    }

                    $node->nodeValue = $node_val;
                }

                //				var_dump($p);
//				var_dump($node->nodeValue);
            }


            // dump paragraph node
            $text = $a_dom->saveXML($parnode);
            $text = substr($text, 0, strlen($text) - strlen("</Paragraph>"));
            $text = substr($text, strpos($text, ">") + 1);

            // replace [iln] by tags with xml representation
            $text = self::intLinks2xml($text);

            // "set text"
            $temp_dom = domxml_open_mem(
                '<?xml version="1.0" encoding="UTF-8"?><Paragraph>' . $text . '</Paragraph>',
                DOMXML_LOAD_PARSING,
                $error
            );
            $temp_dom = $temp_dom->myDOMDocument;

            if (empty($error)) {
                // delete children of paragraph node
                $children = $parnode->childNodes;
                while ($parnode->hasChildNodes()) {
                    $parnode->removeChild($parnode->firstChild);
                }

                // copy new content children in paragraph node
                $xpath_temp = new DOMXPath($temp_dom);
                $temp_pars = $xpath_temp->query("//Paragraph");

                foreach ($temp_pars as $new_par_node) {
                    $new_childs = $new_par_node->childNodes;

                    foreach ($new_childs as $new_child) {
                        //$cloned_child = $new_child->cloneNode(true);
                        $cloned_child = $a_dom->importNode($new_child, true);
                        $parnode->appendChild($cloned_child);
                    }
                }
            }
        }
        //		exit;
    }


    /**
     * Auto link glossary of whole page
     * @throws ilDateTimeException
     */
    public static function autoLinkGlossariesPage(
        ilPageObject $a_page,
        array $a_terms
    ): void {
        $a_page->buildDom();
        $a_dom = $a_page->getDom();

        self::linkTermsInDom($a_dom, $a_terms);

        $a_page->update();
    }

    /**
     * After page has been updated (or created)
     */
    public static function afterPageUpdate(
        ilPageObject $a_page,
        DOMDocument $a_domdoc,
        string $a_xml,
        bool $a_creation
    ): void {
        // pc paragraph
        self::saveMetaKeywords($a_page, $a_domdoc);
        self::saveAnchors($a_page, $a_domdoc);
    }

    /**
     * Before page is being deleted
     */
    public static function beforePageDelete(
        ilPageObject $a_page
    ): void {
        // delete anchors
        self::_deleteAnchors($a_page->getParentType(), $a_page->getId(), $a_page->getLanguage());
    }

    /**
     * After page history entry has been created
     */
    public static function afterPageHistoryEntry(
        ilPageObject $a_page,
        DOMDocument $a_old_domdoc,
        string $a_old_xml,
        int $a_old_nr
    ): void {
    }

    /**
     * Save anchors
     */
    public static function saveAnchors(
        ilPageObject $a_page,
        DOMDocument $a_domdoc
    ): void {
        self::_deleteAnchors($a_page->getParentType(), $a_page->getId(), $a_page->getLanguage());

        // get all anchors
        $xpath = new DOMXPath($a_domdoc);
        $nodes = $xpath->query('//Anchor');
        $saved = array();
        foreach ($nodes as $node) {
            $name = $node->getAttribute("Name");
            if (trim($name) != "" && !in_array($name, $saved)) {
                self::_saveAnchor($a_page->getParentType(), $a_page->getId(), $a_page->getLanguage(), $name);
                $saved[] = $name;
            }
        }
    }

    /**
     * Delete anchors of a page
     */
    public static function _deleteAnchors(
        string $a_parent_type,
        int $a_page_id,
        string $a_page_lang
    ): void {
        global $DIC;

        $ilDB = $DIC->database();

        $ilDB->manipulate(
            "DELETE FROM page_anchor WHERE " .
            " page_parent_type = " . $ilDB->quote($a_parent_type, "text") .
            " AND page_id = " . $ilDB->quote($a_page_id, "integer") .
            " AND page_lang = " . $ilDB->quote($a_page_lang, "text")
        );
    }

    /**
     * Save an anchor
     */
    public static function _saveAnchor(
        string $a_parent_type,
        int $a_page_id,
        string $a_page_lang,
        string $a_anchor_name
    ): void {
        global $DIC;

        $ilDB = $DIC->database();

        $ilDB->manipulate("INSERT INTO page_anchor " .
            "(page_parent_type, page_id, page_lang, anchor_name) VALUES (" .
            $ilDB->quote($a_parent_type, "text") . "," .
            $ilDB->quote($a_page_id, "integer") . "," .
            $ilDB->quote($a_page_lang, "text") . "," .
            $ilDB->quote($a_anchor_name, "text") .
            ")");
    }

    /**
     * Read anchors of a page
     */
    public static function _readAnchors(
        string $a_parent_type,
        int $a_page_id,
        string $a_page_lang = "-"
    ): array {
        global $DIC;

        $ilDB = $DIC->database();

        $and_lang = ($a_page_lang != "")
            ? " AND page_lang = " . $ilDB->quote($a_page_lang, "text")
            : "";

        $set = $ilDB->query(
            "SELECT * FROM page_anchor " .
            " WHERE page_parent_type = " . $ilDB->quote($a_parent_type, "text") .
            " AND page_id = " . $ilDB->quote($a_page_id, "integer") .
            $and_lang
        );
        $anchors = array();
        while ($rec = $ilDB->fetchAssoc($set)) {
            $anchors[] = $rec["anchor_name"];
        }
        return $anchors;
    }

    /**
     * save all keywords
     */
    public static function saveMetaKeywords(
        ilPageObject $a_page,
        DOMDocument $a_domdoc
    ): void {
        // not nice, should be set by context per method
        if ($a_page->getParentType() == "gdf" ||
            $a_page->getParentType() == "lm") {
            // get existing keywords
            $keywords = array();

            // find all Keyw tags
            $xpath = new DOMXPath($a_domdoc);
            $nodes = $xpath->query('//Keyw');
            foreach ($nodes as $node) {
                $k = trim(strip_tags($node->nodeValue));
                if (!in_array($k, $keywords)) {
                    $keywords[] = $k;
                }
            }

            $meta_type = ($a_page->getParentType() == "gdf")
                ? "gdf"
                : "pg";
            $meta_rep_id = $a_page->getParentId();
            $meta_id = $a_page->getId();

            $md_obj = new ilMD($meta_rep_id, $meta_id, $meta_type);
            $mkeywords = array();
            $lang = "";
            if (is_object($md_section = $md_obj->getGeneral())) {
                foreach ($ids = $md_section->getKeywordIds() as $id) {
                    $md_key = $md_section->getKeyword($id);
                    $mkeywords[] = strtolower($md_key->getKeyword());
                    if ($lang == "") {
                        $lang = $md_key->getKeywordLanguageCode();
                    }
                }
                if ($lang == "") {
                    foreach ($ids = $md_section->getLanguageIds() as $id) {
                        $md_lang = $md_section->getLanguage($id);
                        if ($lang == "") {
                            $lang = $md_lang->getLanguageCode();
                        }
                    }
                }
                foreach ($keywords as $k) {
                    if (!in_array(strtolower($k), $mkeywords)) {
                        if (trim($k) != "" && $lang != "") {
                            $md_key = $md_section->addKeyword();
                            $md_key->setKeyword(ilUtil::stripSlashes($k));
                            $md_key->setKeywordLanguage(new ilMDLanguageItem($lang));
                            $md_key->save();
                        }
                        $mkeywords[] = strtolower($k);
                    }
                }
            }
        }
    }

    public function getJavascriptFiles(string $a_mode): array
    {
        $adve_settings = new ilSetting("adve");

        if ($a_mode != "edit" && $adve_settings->get("auto_url_linking")) {
            return ilLinkifyUtil::getLocalJsPaths();
        }

        return array();
    }

    public function getOnloadCode(string $a_mode): array
    {
        $adve_settings = new ilSetting("adve");

        if ($a_mode != "edit" && $adve_settings->get("auto_url_linking")) {
            return array("il.ExtLink.autolink('.ilc_Paragraph, .ilc_page_fn_Footnote','ilc_link_ExtLink');");
        }

        return array();
    }

    public function getModel(): ?stdClass
    {
        $model = new \stdClass();
        $s_text = $this->getText();
        $s_text = $this->xml2output($s_text, true, false);
        $s_text = ilPCParagraphGUI::xml2outputJS($s_text);
        $char = $this->getCharacteristic();
        if ($char == "") {
            $char = "Standard";
        }
        $model->characteristic = $char;
        $model->text = $s_text;

        return $model;
    }

    /**
     * Save input coming from ajax
     * @return array|bool|string
     * @throws ilCOPagePCEditException
     * @throws ilCOPageUnknownPCTypeException
     * @throws ilDateTimeException
     */
    public function insert(
        \ilPageObject $page,
        string $a_content,
        string $a_char,
        string $a_pc_id,
        string $a_insert_at = "",
        string $a_new_pc_id = ""
    ) {
        $ilUser = $this->user;

        $this->log->debug("step 1: " . substr($a_content, 0, 1000));

        try {
            $t = self::handleAjaxContent($a_content);
        } catch (Exception $ex) {
            return $ex->getMessage() . ": " . htmlentities($a_content);
        }

        $this->log->debug("step 2: " . substr($t["text"], 0, 1000));
        if ($t === false) {
            return false;
        }

        $pc_id = explode(":", $a_pc_id);
        $insert_at = explode(":", $a_insert_at);
        $t_id = explode(":", $t["id"]);

        // insert new paragraph
        if ($a_insert_at != "") {
            $par = new ilPCParagraph($this->getPage());
            $par->create($page, $insert_at[0], $insert_at[1]);
        } else {
            $par = $page->getContentObject($pc_id[0], $pc_id[1]);
        }

        if ($a_insert_at != "") {
            $pc_id = ($a_new_pc_id != "")
                ? $a_new_pc_id
                : $page->generatePcId();
            $par->writePCId($pc_id);
            $this->inserted_pc_id = $pc_id;
        } else {
            $this->inserted_pc_id = $pc_id[1];
        }

        $par->setLanguage($ilUser->getLanguage());
        $par->setCharacteristic($t["class"]);

        $t2 = $par->input2xml($t["text"], true, false);
        $this->log->debug("step 3: " . substr($t2, 0, 1000));

        $t2 = ilPCParagraph::handleAjaxContentPost($t2);
        $this->log->debug("step 4: " . substr($t2, 0, 1000));

        $updated = $par->setText($t2, true);

        if ($updated !== true) {
            echo $updated;
            exit;
        }
        $updated = $par->updatePage($page);
        return $updated;
    }
}
