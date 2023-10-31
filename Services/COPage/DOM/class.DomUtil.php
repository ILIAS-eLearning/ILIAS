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

namespace ILIAS\COPage\Dom;

/**
 * DOM 2 util
 * @author Alexander Killing <killing@leifos.de>
 */
class DomUtil
{
    protected static function xmlError(int $errno, string $errstr, ?string $errfile = null, ?int $errline = null, ?array $errcontext = null, bool $ret = false)
    {
        static $errs = array();

        $tag = 'DOMDocument::validate(): ';
        $errs[] = str_replace($tag, '', $errstr);

        if ($ret === true) {
            return $errs;
        }
    }

    public function docFromString(string $xml, ?string &$error_str): ?\DOMDocument
    {
        $doc = new \DOMDocument();
        set_error_handler('ILIAS\COPage\Dom\DomUtil::xmlError');
        $old = ini_set('html_errors', false);
        $success = $doc->loadXML($xml);
        // Restore error handling
        ini_set('html_errors', $old);
        restore_error_handler();
        $error_str = "";
        if (!$success) {
            $error_arr = self::xmlError(0, "", "", 0, null, true);
            foreach ($error_arr as $error) {
                $error = str_replace("DOMDocument::loadXML():", "", $error);
                $error_str .= $error . "<br />";
            }
            return null;
        }
        return $doc;
    }

    public function validate(\DOMDocument $doc, ?string &$error): void
    {
        $ok = $doc->validate();

        if (!$ok) {
            $error = array(array("0", "Unknown Error"));

            if (function_exists("libxml_get_last_error")) {
                $err = libxml_get_last_error();

                if (is_object($err)) {
                    $error = array(array($err->code, $err->message));
                }
            }
        }
    }

    public function path(\DOMDocument $doc, string $path): \DOMNodeList
    {
        $xpath = new \DOMXPath($doc);
        return $xpath->query($path);
    }

    // change node name
    public function changeName(
        \DOMNode $node,
        string $name,
        bool $keep_attributes = true
    ): \DOMNode {
        $newnode = $node->ownerDocument->createElement($name);

        foreach ($node->childNodes as $child) {
            $child2 = $child->cloneNode(true);
            $newnode->appendChild($child2);
        }
        if ($keep_attributes) {
            foreach ($node->attributes as $attrName => $attrNode) {
                $newnode->setAttribute($attrName, $attrNode);
            }
        }
        $node->parentNode->replaceChild($newnode, $node);

        return $newnode;
    }

    // Add parent
    public function addParent(
        \DOMNode $node,
        string $name
    ): \DOMNode {
        $newnode = $node->ownerDocument->createElement($name);
        $par = $node->parentNode;
        if ($next_sib = $node->nextSibling) {
            $newnode = $par->insertBefore($newnode, $next_sib);
        } else {
            $newnode = $par->appendChild($newnode);
        }

        $node = $par->removeChild($node);
        $newnode->appendChild($node);
        return $newnode;
    }

    // Replace a node by its child
    public function replaceByChilds(\DOMNode $node): void
    {
        foreach ($node->childNodes as $child) {
            $child2 = $child->cloneNode(true);
            $node->parentNode->insertBefore($child2, $node);
        }
        $node->parentNode->removeChild($node);
    }

    // delete all childs of a node by names in $node_names
    public function deleteAllChildsByName(
        \DOMNode $parent,
        array $node_names
    ): void {
        foreach ($parent->childNodes as $child) {
            if (in_array($child->nodeName, $node_names)) {
                $parent->removeChild($child);
            }
        }
    }

    public function deleteAllChilds(
        \DOMNode $parent
    ): void {
        while ($parent->hasChildNodes()) {
            $parent->removeChild($parent->firstChild);
        }
    }

    /**
     * set attributes of a node
     */
    public function setAttributes(
        ?\DOMNode $node,
        array $attributes
    ): void {
        foreach ($attributes as $attribute => $value) {
            $this->setAttribute($node, $attribute, $value);
        }
    }

    public function setAttribute(
        ?\DOMNode $node,
        string $attribute,
        ?string $value
    ): void {
        if (!is_null($node)) {
            if (!is_null($value) && $value !== "") {
                $node->setAttribute($attribute, $value);
            } elseif ($node->hasAttribute($attribute)) {
                $node->removeAttribute($attribute);
            }
        }
    }

    /**
     * searches for an element $node_name within the childs of $parent_node
     * if no node is found, a new is created before the childs with names of
     * $successors. the content of the node is set to $content and the
     * attributes to $attributes
     */
    public function setFirstOptionalElement(
        \DOMNode $parent_node,
        string $node_name,
        array $successors,
        string $content,
        array $attributes,
        bool $remove_childs = true
    ): void {
        $doc = $parent_node->ownerDocument;
        $search = $successors;
        $search[] = $node_name;
        $child_name = "";
        $child = null;

        $found = false;
        foreach ($parent_node->childNodes as $child) {
            $child_name = $child->nodeName;
            if (in_array($child_name, $search)) {
                $found = true;
                break;
            }
        }
        // didn't find element
        if (!$found) {
            $new_node = $doc->createElement($node_name);
            $new_node = $parent_node->appendChild($new_node);
            if ($content != "") {
                $this->setContent($new_node, $content);
            }
            $this->setAttributes($new_node, $attributes);
        } else {
            if ($child_name == $node_name) {
                if ($remove_childs) {
                    foreach ($child->childNodes as $child2) {
                        $child->removeChild($child2);
                    }
                }
                if ($content != "") {
                    $this->setContent($child, $content);
                }
                $this->setAttributes($child, $attributes);
            } else {
                $new_node = $doc->createElement($node_name);
                $new_node = $child->parentNode->insertBefore($new_node, $child);
                if ($content != "") {
                    $this->setContent($new_node, $content);
                }
                $this->setAttributes($new_node, $attributes);
            }
        }
    }

    public function dump(\DOMNode $node): string
    {
        return $node->ownerDocument->saveXML($node);
    }

    public function setContent(\DOMNode $node, string $text): void
    {
        // the following replace has been added to conform with PHP4.
        // A set_content("&amp;") brought a get_content() = "&" there,
        // whereas PHP5 gives a get_content() = "&amp;"
        $text = str_replace("&lt;", "<", $text);
        $text = str_replace("&gt;", ">", $text);
        $text = str_replace("&amp;", "&", $text);

        $text_node = new \DOMText();
        $text_node->appendData($text);
        if (is_object($node->firstChild)) {
            $node->replaceChild($text_node, $node->firstChild);
        } else {
            $node->appendChild($text_node);
        }
    }

    public function getContent(\DOMNode $node): string
    {
        $text_node = $node->firstChild;

        if (is_object($text_node)) {
            return $text_node->textContent;
        } else {
            return "";
        }
    }

    /**
     * Places a new node $a_node_name directly before nodes with names of
     * $a_successors. The content of the node is set to $a_content and the
     * attributes to $a_attributes
     */
    public function addElementToList(
        \DOMNode $parent_node,
        string $a_node_name,
        array $a_successors,
        string $a_content,
        array $a_attributes
    ): \DOMNode {
        $doc = $parent_node->ownerDocument;
        $search = $a_successors;
        $child = null;
        $childs = $parent_node->childNodes;
        $found = false;
        foreach ($childs as $child) {
            $child_name = $child->nodeName;
            if (in_array($child_name, $search)) {
                $found = true;
                break;
            }
        }
        // didn't successors -> append at the end
        $new_node = $doc->createElement($a_node_name);
        if (!$found) {
            $new_node = $parent_node->appendChild($new_node);
        } else {
            $new_node = $parent_node->insertBefore($new_node, $child);
        }
        if ($a_content != "") {
            $this->setContent($new_node, $a_content);
        }
        $this->setAttributes($new_node, $a_attributes);

        return $new_node;
    }
}
