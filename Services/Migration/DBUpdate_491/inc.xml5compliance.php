<?php
/*
    +-----------------------------------------------------------------------------+
    | Copyright (c) by Alexandre Alapetite,                                       |
    | http://alexandre.alapetite.net/cv/alexandre-alapetite.en.html               |
    | http://alexandre.alapetite.net/doc-alex/domxml-php4-php5/                   |
    | Modifications by Alex Killing, alex.killing@gmx.de  (search for ##)         |
    |-----------------------------------------------------------------------------|
    | Allows PHP4/DOMXML scripts to run on PHP5/DOM                               |
    |                                                                             |
    | Typical use:                                                                |
    | {                                                                           |
    | 	if (version_compare(PHP_VERSION,'5','>='))                                |
    | 		require_once('domxml-php4-to-php5.php');                              |
    | }                                                                           |
    |-----------------------------------------------------------------------------|
    | This code is published under Creative Commons                               |
    | Attribution-ShareAlike 2.0 "BY-SA" licence.                                 |
    | See http://creativecommons.org/licenses/by-sa/2.0/ for details.             |
    +-----------------------------------------------------------------------------+
*/


function domxml_open_file($filename)
{
    return new php4DOMDocument($filename);
}

/*
* ##added
*/
function domxml_open_mem($str)
{
    return new php4DOMDocument($str, false);
}

function xpath_eval($xpath_context, $eval_str)
{
    return $xpath_context->query($eval_str);
}

function xpath_new_context($dom_document)
{
    return new php4DOMXPath($dom_document);
}

class php4DOMAttr extends php4DOMNode
{
    public $myDOMAttr;

    public function php4DOMAttr($aDOMAttr)
    {
        $this->myDOMAttr=$aDOMAttr;
    }

    public function Name()
    {
        return $this->myDOMAttr->name;
    }

    public function Specified()
    {
        return $this->myDOMAttr->specified;
    }

    public function Value()
    {
        return $this->myDOMAttr->value;
    }
}

class php4DOMCDATASection extends php4DOMNode
{
    public $myDOMCDATASection;

    public function php4DOMCDATASection($aDOMCDATASection)
    {
        parent::php4DOMNode($aDOMCDATASection);						// #added
        $this->myDOMCDATASection=$aDOMCDATASection;
    }
}

class php4DOMDocument
{
    public $myDOMDocument;

    // ##altered
    public function php4DOMDocument($source, $file = true)
    {
        $this->myDOMDocument=new DOMDocument();
        if ($file) {
            $this->myDOMDocument->load($source);
        } else {
            $this->myDOMDocument->loadXML($source);
        }
    }

    // ##added
    public function xpath_init()
    {
    }

    public function free()
    {
        unset($this->myDOMDocument);
    }

    // ##added
    public function xpath_new_context()
    {
        return xpath_new_context($this);
    }

    // ##added
    public function dump_node($node)
    {
        $str = $this->myDOMDocument->saveXML($node->myDOMNode);
        return $str;
    }

    // ##added
    public function validate(&$error)
    {
        $ok = $this->myDOMDocument->validate();
        if (!$ok) {
            $error = array(array("0", "Unknown Error"));
        }
        return $error;
    }

    public function create_attribute($name, $value)
    {
        $myAttr=$this->myDOMDocument->createAttribute($name);
        $myAttr->value=$value;

        return new php4DOMAttr($myAttr);
    }

    public function create_cdata_section($content)
    {
        return new php4DOMCDATASection($this->myDOMDocument->createCDATASection($content));
    }

    public function create_comment($data)
    {
        return new php4DOMElement($this->myDOMDocument->createComment($data));
    }

    public function create_element($name)
    {
        return new php4DOMElement($this->myDOMDocument->createElement($name));
    }

    public function create_text_node($content)
    {
        return new php4DOMNode($this->myDOMDocument->createTextNode($content));
    }

    public function document_element()
    {
        return new php4DOMElement($this->myDOMDocument->documentElement);
    }

    public function dump_file($filename, $compressionmode=false, $format=false)
    {
        return $this->myDOMDocument->save($filename);
    }

    public function dump_mem($format=false, $encoding=false)
    {
        return $this->myDOMDocument->saveXML();
    }

    public function get_elements_by_tagname($name)
    {
        $myDOMNodeList=$this->myDOMDocument->getElementsByTagName($name);
        $nodeSet=array();
        $i=0;
        while ($node=$myDOMNodeList->item($i)) {
            $nodeSet[]=new php4DOMElement($node);
            $i++;
        }

        return $nodeSet;
    }

    public function html_dump_mem()
    {
        return $this->myDOMDocument->saveHTML();
    }
}

/**
* php4DomElement
*/
class php4DOMElement extends php4DOMNode
{
    public function get_attribute($name)
    {
        return $this->myDOMNode->getAttribute($name);
    }

    public function get_elements_by_tagname($name)
    {
        $myDOMNodeList=$this->myDOMNode->getElementsByTagName($name);
        $nodeSet=array();
        $i=0;
        while ($node=$myDOMNodeList->item($i)) {
            $nodeSet[]=new php4DOMElement($node);
            $i++;
        }

        return $nodeSet;
    }

    public function has_attribute($name)
    {
        return $this->myDOMNode->hasAttribute($name);
    }

    public function remove_attribute($name)
    {
        return $this->myDOMNode->removeAttribute($name);
    }

    public function set_attribute($name, $value)
    {
        return $this->myDOMNode->setAttribute($name, $value);
    }

    public function tagname()
    {
        return $this->myDOMNode->tagName;
    }

    // ##added
    public function set_content($text)
    {
        // the following replace has been added to conform with PHP4.
        // A set_content("&amp;") brought a get_content() = "&" there,
        // whereas PHP5 gives a get_content() = "&amp;"
        $text = str_replace("&lt;", "<", $text);
        $text = str_replace("&gt;", ">", $text);
        $text = str_replace("&amp;", "&", $text);
        
        $text_node = new DOMText();
        $text_node->appendData($text);
        if (is_object($this->myDOMNode->firstChild)) {
            $this->myDOMNode->replaceChild($text_node, $this->myDOMNode->firstChild);
        } else {
            $this->myDOMNode->appendChild($text_node);
        }
    }

    // ##added
    public function get_content()
    {
        $text_node =&$this->myDOMNode->firstChild;

        if (is_object($text_node)) {
            return $text_node->textContent;
        } else {
            return "";
        }
    }
    
    // ## added
    public function unlink($aDomNode)
    {
        parent::unlink_node($aDomNode);
    }
}

/**
* php4DOMNode
*/
class php4DOMNode
{
    public $myDOMNode;

    public function php4DOMNode($aDomNode)
    {
        $this->myDOMNode=$aDomNode;
    }

    public function append_child($newnode)
    {
        //echo "BH";
        //if (strtolower(get_class($newnode)) != "php4domcdatasection")
        //{
        $doc =&$this->myDOMNode->ownerDocument;
        //echo "<br>BH1:".get_class($newnode).":";
        $newnode->myDOMNode =&$doc->importNode($newnode->myDOMNode, true);
        //echo "BH2";
        return new php4DOMElement($this->myDOMNode->appendChild($newnode->myDOMNode));
        //}
        //else
        //{
        //}
    }

    public function replace_node($newnode)
    {
        return $this->set_content($newnode->myDOMNode->textContent);
    }
    
    public function append_sibling($newnode)
    {
        return new php4DOMElement($this->myDOMNode->parentNode->appendChild($newnode->myDOMNode));
    }

    public function attributes()
    {
        //echo "<br>node:".$this->myDOMNode->nodeName.":";
        $myDOMNodeList=$this->myDOMNode->attributes;
        $nodeSet=array();
        $i=0;
        if (is_object($myDOMNodeList)) {
            while ($node=$myDOMNodeList->item($i)) {
                $nodeSet[]=new php4DOMAttr($node);
                $i++;
            }
        }

        return $nodeSet;
    }

    public function child_nodes()
    {
        $myDOMNodeList=$this->myDOMNode->childNodes;
        $nodeSet=array();
        $i=0;
        while ($node=$myDOMNodeList->item($i)) {
            $nodeSet[]=new php4DOMElement($node);
            $i++;
        }
        return $nodeSet;
    }

    // ## added
    public function children()
    {
        //echo "<br>php4DomNode::children"; flush();
        return $this->child_nodes();
    }

    // ## added
    public function unlink_node($aDomNode = "")
    {
        // sometimes the node to unlink is passed
        if (!is_object($aDomNode)) {
            $aDomNode =&$this;
        }

        $parent =&$aDomNode->myDOMNode->parentNode;
        if (is_object($parent)) {
            $parent->removeChild($aDomNode->myDOMNode);
        }
    }

    public function clone_node($deep=false)
    {
        return new php4DOMElement($this->myDOMNode->cloneNode($deep));
    }

    public function first_child()
    {
        return new php4DOMElement($this->myDOMNode->firstChild);
    }

    public function get_content()
    {
        return $this->myDOMNode->textContent;
    }

    public function has_attributes()
    {
        return $this->myDOMNode->hasAttributes();
    }

    public function has_child_nodes()
    {
        return $this->myDOMNode->hasChildNodes();
    }

    // ## changed
    public function insert_before($newnode, $refnode)
    {
        //echo "BH";
        $doc =&$this->myDOMNode->ownerDocument;
        $newnode->myDOMNode =&$doc->importNode($newnode->myDOMNode, true);
        
        $mydomnode =&$this->myDOMNode;
        $mynewnode =&$newnode->myDOMNode;
        $myrefnode =&$refnode->myDOMNode;
        try {
            $domel =&$mydomnode->insertBefore($mynewnode, $myrefnode);
        } catch (DOMException $exception) {
            // php 4 accepted $this == $refnode -> switch to parent of $this
            $mydomnode =&$this->myDOMNode->parentNode;
            $domel =&$mydomnode->insertBefore($mynewnode, $myrefnode);
        }
        $el = new php4DOMElement($domel);
        return $el;
    }

    // ## changed
    public function last_child()
    {
        $last =&$this->myDOMNode->lastChild;

        if (is_object($last)) {
            return new php4DOMElement($last);
        } else {
            return false;
        }
    }

    // ## changed
    public function next_sibling()
    {
        $next =&$this->myDOMNode->nextSibling;

        if (is_object($next)) {
            return new php4DOMElement($next);
        } else {
            return false;
        }
    }

    public function node_name()
    {
        return $this->myDOMNode->nodeName;
    }

    public function node_type()
    {
        return $this->myDOMNode->nodeType;
    }

    public function node_value()
    {
        return $this->myDOMNode->nodeValue;
    }

    // ## changed
    public function parent_node()
    {
        $parent =&$this->myDOMNode->parentNode;

        if (is_object($parent)) {
            return new php4DOMElement($parent);
        } else {
            return false;
        }
    }

    // ## changed
    public function previous_sibling()
    {
        $prev =&$this->myDOMNode->previousSibling;

        if (is_object($prev)) {
            return new php4DOMElement($prev);
        } else {
            return false;
        }
    }

    public function remove_child($oldchild)
    {
        return new php4DOMElement($this->myDOMNode->removeChild($oldchild->myDOMNode));
    }

    public function replace_child($oldnode, $newnode)
    {
        return new php4DOMElement($this->myDOMNode->replaceChild($oldchild->myDOMNode, $newnode->myDOMNode));
    }

    public function set_content($text)
    {
        $this->myDOMNode->textContent = $text;
        return $this->myDOMNode->textContent;
    }
}

class php4DOMNodelist
{
    public $myDOMNodelist;
    public $nodeset;

    public function php4DOMNodelist($aDOMNodelist)
    {
        $this->myDOMNodelist=$aDOMNodelist;
        $this->nodeset=array();
        $i=0;
        while ($node=$this->myDOMNodelist->item($i)) {
            $this->nodeset[]=new php4DOMElement($node);
            $i++;
        }
    }
}

class php4DOMXPath
{
    public $myDOMXPath;

    // ## added
    public function xpath_eval($eval_str)
    {
        return xpath_eval($this, $eval_str);
    }

    public function php4DOMXPath($dom_document)
    {
        $this->myDOMXPath=new DOMXPath($dom_document->myDOMDocument);
    }

    public function query($eval_str)
    {
        return new php4DOMNodelist($this->myDOMXPath->query($eval_str));
    }

    public function xpath_register_ns($prefix, $namespaceURI)
    {
        return $this->myDOMXPath->registerNamespace($prefix, $namespaceURI);
    }
}
