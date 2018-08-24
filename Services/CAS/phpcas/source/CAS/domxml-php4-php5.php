<?php
/**
 * @file domxml-php4-php5.php
 * Require PHP5, uses built-in DOM extension.
 * To be used in PHP4 scripts using DOMXML extension.
 * Allows PHP4/DOMXML scripts to run on PHP5/DOM.
 * (Requires PHP5/XSL extension for domxml_xslt functions)
 *
 * Typical use:
 * <pre>
 * {
 *  if (version_compare(PHP_VERSION,'5','>='))
 *   require_once('domxml-php4-to-php5.php');
 * }
 * </pre>
 *
 * Version 1.5.5, 2005-01-18, http://alexandre.alapetite.net/doc-alex/domxml-php4-php5/
 *
 * ------------------------------------------------------------------<br>
 * Written by Alexandre Alapetite, http://alexandre.alapetite.net/cv/
 *
 * Copyright 2004, Licence: Creative Commons "Attribution-ShareAlike 2.0 France" BY-SA (FR),
 * http://creativecommons.org/licenses/by-sa/2.0/fr/
 * http://alexandre.alapetite.net/divers/apropos/#by-sa
 * - Attribution. You must give the original author credit
 * - Share Alike. If you alter, transform, or build upon this work,
 *   you may distribute the resulting work only under a license identical to this one
 * - The French law is authoritative
 * - Any of these conditions can be waived if you get permission from Alexandre Alapetite
 * - Please send to Alexandre Alapetite the modifications you make,
 *   in order to improve this file for the benefit of everybody
 *
 * If you want to distribute this code, please do it as a link to:
 * http://alexandre.alapetite.net/doc-alex/domxml-php4-php5/
 */

	// new
	function domxml_new_doc($version)
	{
echo "-1";
		return new php4DOMDocument('');
	}
	
	// same
	function domxml_open_file($filename)
	{
echo "-2";
		return new php4DOMDocument($filename);
	}
	
	// different
	function domxml_open_mem($str)
	{
echo "-3";
		 $dom=new php4DOMDocument('');
		 $dom->myDOMNode->loadXML($str);
		 return $dom;
	}
	
	// different -> adapted in ilias
	function xpath_eval($xpath_context,$eval_str,$contextnode=null)
	{
echo "-4";
		return $xpath_context->query($eval_str,$contextnode);
	}
	
	// same
	function xpath_new_context($dom_document)
	{
echo "-5";
		return new php4DOMXPath($dom_document);
	}

// same (nearly)
class php4DOMAttr extends php4DOMNode
{
	function php4DOMAttr($aDOMAttr) {
echo "-6";
		$this->myDOMNode=$aDOMAttr;
	}
	
	function Name() {
echo "-7";
		return $this->myDOMNode->name;
	}
	
	function Specified() {
echo "-8";
		return $this->myDOMNode->specified;
	}
	
	function Value() {
echo "-9";
		return $this->myDOMNode->value;
	}
}

// different
class php4DOMDocument extends php4DOMNode
{
	// different
	function php4DOMDocument($filename='')
	{
echo "-A";
		$this->myDOMNode=new DOMDocument();
		if ($filename!='') $this->myDOMNode->load($filename);
	}

	// different (a little)
	function create_attribute($name,$value)
	{
echo "-B";
		$myAttr=$this->myDOMNode->createAttribute($name);
		$myAttr->value=$value;
		return new php4DOMAttr($myAttr,$this);
	}
	
	// different (little)
	function create_cdata_section($content)
	{
echo "-C";
		return new php4DOMNode($this->myDOMNode->createCDATASection($content),$this);
	}
	
	// different (little)
	function create_comment($data)
	{
echo "-D";
		return new php4DOMNode($this->myDOMNode->createComment($data),$this);
	}
	
	// different (little)
	function create_element($name)
	{
echo "-E";
		return new php4DOMElement($this->myDOMNode->createElement($name),$this);
	}
	
	function create_text_node($content)
	{
echo "-F";
		return new php4DOMNode($this->myDOMNode->createTextNode($content),$this);
	}
	
	function document_element()
	{
echo "-G";
		return new php4DOMElement($this->myDOMNode->documentElement,$this);
	}
	
	function dump_file($filename,$compressionmode=false,$format=false)
	{
echo "-H";
		return $this->myDOMNode->save($filename);
	}
	
	function dump_mem($format=false,$encoding=false)
	{
echo "-I";
		return $this->myDOMNode->saveXML();
	}
	
	function get_element_by_id($id)
	{
echo "-J";
		return new php4DOMElement($this->myDOMNode->getElementById($id),$this);
	}
	
	function get_elements_by_tagname($name)
	{
echo "-K";
		$myDOMNodeList=$this->myDOMNode->getElementsByTagName($name);
		$nodeSet=array();
		$i=0;
		if (isset($myDOMNodeList))
		while ($node=$myDOMNodeList->item($i))
		{
			$nodeSet[]=new php4DOMElement($node,$this);
			$i++;
		}
		return $nodeSet;
	}
	
	function html_dump_mem()
	{
echo "-L";
		return $this->myDOMNode->saveHTML();
	}
	
	function root()
	{
echo "-M";
		return new php4DOMElement($this->myDOMNode->documentElement,$this);
	}
}

class php4DOMElement extends php4DOMNode
{
	function get_attribute($name)
	{
echo "-N";
		return $this->myDOMNode->getAttribute($name);
	}
	
	function get_elements_by_tagname($name)
	{
echo "-O";
		$myDOMNodeList=$this->myDOMNode->getElementsByTagName($name);
		$nodeSet=array();
		$i=0;
		if (isset($myDOMNodeList))
		while ($node=$myDOMNodeList->item($i))
		{
			$nodeSet[]=new php4DOMElement($node,$this->myOwnerDocument);
			$i++;
		}
		return $nodeSet;
	}
	
	 function has_attribute($name)
	 {
echo "-P";
	 	return $this->myDOMNode->hasAttribute($name);
	 }
	 
	 function remove_attribute($name)
	 {
echo "-Q";
	 	return $this->myDOMNode->removeAttribute($name);
	 }
	 
	 function set_attribute($name,$value)
	 {
echo "-R";
	 	return $this->myDOMNode->setAttribute($name,$value);
	 }
	 
	 function tagname()
	 {
echo "-S";
	 	return $this->myDOMNode->tagName;
	 }
}

class php4DOMNode
{
 var $myDOMNode;
 var $myOwnerDocument;
 
	 function php4DOMNode($aDomNode,$aOwnerDocument)
	 {
echo "-T";
		$this->myDOMNode=$aDomNode;
		$this->myOwnerDocument=$aOwnerDocument;
	 }
	 
	function __get($name)
	{
echo "-U";
		if ($name=='type') return $this->myDOMNode->nodeType;
		elseif ($name=='tagname') return $this->myDOMNode->tagName;
		elseif ($name=='content') return $this->myDOMNode->textContent;
		else
		{
		$myErrors=debug_backtrace();
		trigger_error('Undefined property: '.get_class($this).'::$'.$name.' ['.$myErrors[0]['file'].':'.$myErrors[0]['line'].']',E_USER_NOTICE);
		return false;
		}
	}

	function append_child($newnode)
	{
echo "-V";
		return new php4DOMElement($this->myDOMNode->appendChild($newnode->myDOMNode),$this->myOwnerDocument);
	}
	
	function append_sibling($newnode)
	{
echo "-W";
		return new php4DOMElement($this->myDOMNode->parentNode->appendChild($newnode->myDOMNode),$this->myOwnerDocument);
	}
	
	function attributes()
	{
echo "-X";
		$myDOMNodeList=$this->myDOMNode->attributes;
		$nodeSet=array();
		$i=0;
		if (isset($myDOMNodeList))
		while ($node=$myDOMNodeList->item($i))
		{
			$nodeSet[]=new php4DOMAttr($node,$this->myOwnerDocument);
			$i++;
		}
		return $nodeSet;
	}
	
	function child_nodes()
	{
echo "-Y";
		$myDOMNodeList=$this->myDOMNode->childNodes;
		$nodeSet=array();
		$i=0;
		if (isset($myDOMNodeList))
		while ($node=$myDOMNodeList->item($i))
		{
			$nodeSet[]=new php4DOMElement($node,$this->myOwnerDocument);
			$i++;
		}
		return $nodeSet;
	}
	
	function children() {
echo "-Z";
		return $this->child_nodes();
	}
	
	function clone_node($deep=false) {
echo "-a";
		return new php4DOMElement($this->myDOMNode->cloneNode($deep),$this->myOwnerDocument);
	}
	
	function first_child() {
echo "-b";
		return new php4DOMElement($this->myDOMNode->firstChild,$this->myOwnerDocument);
	}
	function get_content() {
echo "-c";
		return $this->myDOMNode->textContent;
	}
	
	function has_attributes() {
echo "-d";
		return $this->myDOMNode->hasAttributes();
	}
	function has_child_nodes()
	{
echo "-e";
		return $this->myDOMNode->hasChildNodes();
	}
	function insert_before($newnode,$refnode)
	{
echo "-f";
		return new php4DOMElement($this->myDOMNode->insertBefore($newnode->myDOMNode,$refnode->myDOMNode),$this->myOwnerDocument);
	}
	
	function is_blank_node()
	{
echo "-g";
		$myDOMNodeList=$this->myDOMNode->childNodes;
		$i=0;
		if (isset($myDOMNodeList))
		while ($node=$myDOMNodeList->item($i))
		{
		if (($node->nodeType==XML_ELEMENT_NODE)||
		(($node->nodeType==XML_TEXT_NODE)&&!ereg('^([[:cntrl:]]|[[:space:]])*$',$node->nodeValue)))
		return false;
		$i++;
		}
		return true;
	}
	
	function last_child() {
echo "-h";
		return new php4DOMElement($this->myDOMNode->lastChild,$this->myOwnerDocument);
	}
	
	function new_child($name,$content)
	{
echo "-i";
		$mySubNode=$this->myDOMNode->ownerDocument->createElement($name);
		$mySubNode->appendChild($this->myDOMNode->ownerDocument->createTextNode($content));
		$this->myDOMNode->appendChild($mySubNode);
		return new php4DOMElement($mySubNode,$this->myOwnerDocument);
	}
	
	function next_sibling() {
echo "-j";
		return new php4DOMElement($this->myDOMNode->nextSibling,$this->myOwnerDocument);
	}
	
	function node_name() {
echo "-k";
		return $this->myDOMNode->localName;
	}
	
	function node_type() {
echo "-l";
		return $this->myDOMNode->nodeType;
	}
	
	function node_value()
	{
echo "-m";
		return $this->myDOMNode->nodeValue;
	}
	
	function owner_document()
	{
echo "-n";
		return $this->myOwnerDocument;
	}
	
	function parent_node() {
echo "-o";
		return new php4DOMElement($this->myDOMNode->parentNode,$this->myOwnerDocument);
	}
	
	function prefix() {
echo "-p";
		return $this->myDOMNode->prefix;
	}
	
	function previous_sibling() {
echo "-q";
		return new php4DOMElement($this->myDOMNode->previousSibling,$this->myOwnerDocument);
	}
	
	function remove_child($oldchild)
	{
echo "-r";
		return new php4DOMElement($this->myDOMNode->removeChild($oldchild->myDOMNode),$this->myOwnerDocument);
	}
	
	function replace_child($oldnode,$newnode) {
echo "-s";
		return new php4DOMElement($this->myDOMNode->replaceChild($oldnode->myDOMNode,$newnode->myDOMNode),$this->myOwnerDocument);
	}
	
	function set_content($text)
	{
echo "-t";
		if (($this->myDOMNode->hasChildNodes())&&($this->myDOMNode->firstChild->nodeType==XML_TEXT_NODE))
		$this->myDOMNode->removeChild($this->myDOMNode->firstChild);
		return $this->myDOMNode->appendChild($this->myDOMNode->ownerDocument->createTextNode($text));
	}
}

class php4DOMNodelist
{
 var $myDOMNodelist;
 var $nodeset;
 
	function php4DOMNodelist($aDOMNodelist,$aOwnerDocument)
	{
echo "-u";
		$this->myDOMNodelist=$aDOMNodelist;
		$this->nodeset=array();
		$i=0;
		if (isset($this->myDOMNodelist))
		while ($node=$this->myDOMNodelist->item($i))
		{
		$this->nodeset[]=new php4DOMElement($node,$aOwnerDocument);
		$i++;
		}
	}
}

class php4DOMXPath
{
 var $myDOMXPath;
 var $myOwnerDocument;
	function php4DOMXPath($dom_document)
	{
echo "-v";
		$this->myOwnerDocument=$dom_document;
		$this->myDOMXPath=new DOMXPath($dom_document->myDOMNode);
	}
	
	function query($eval_str,$contextnode)
	{
echo "-w";
		if (isset($contextnode)) return new php4DOMNodelist($this->myDOMXPath->query($eval_str,$contextnode->myDOMNode),$this->myOwnerDocument);
		else return new php4DOMNodelist($this->myDOMXPath->query($eval_str),$this->myOwnerDocument);
	}
 
	function xpath_register_ns($prefix,$namespaceURI)
	{
echo "-x";
		return $this->myDOMXPath->registerNamespace($prefix,$namespaceURI);
	}
}

if (extension_loaded('xsl'))
{//See also: http://alexandre.alapetite.net/doc-alex/xslt-php4-php5/
	
	function domxml_xslt_stylesheet($xslstring)
	{
echo "-y";
		return new php4DomXsltStylesheet(DOMDocument::loadXML($xslstring));
	}
	
	function domxml_xslt_stylesheet_doc($dom_document) {
echo "-z";
		return new php4DomXsltStylesheet($dom_document);
	}
	
 function domxml_xslt_stylesheet_file($xslfile) {
echo "-Ä";
 	return new php4DomXsltStylesheet(DOMDocument::load($xslfile));
 }

 class php4DomXsltStylesheet
 {
  var $myxsltProcessor;
	function php4DomXsltStylesheet($dom_document)
	{
echo "-Ö";
		$this->myxsltProcessor=new xsltProcessor();
		$this->myxsltProcessor->importStyleSheet($dom_document);
	}
	
	function process($dom_document,$xslt_parameters=array(),$param_is_xpath=false)
	{
echo "-Ü";
		foreach ($xslt_parameters as $param=>$value)
		$this->myxsltProcessor->setParameter('',$param,$value);
		$myphp4DOMDocument=new php4DOMDocument();
		$myphp4DOMDocument->myDOMNode=$this->myxsltProcessor->transformToDoc($dom_document->myDOMNode);
		return $myphp4DOMDocument;
	}
	
	function result_dump_file($dom_document,$filename)
	{
echo "-ä";
		$html=$dom_document->myDOMNode->saveHTML();
		file_put_contents($filename,$html);
		return $html;
	}
	
	function result_dump_mem($dom_document) {
echo "-ö";
		return $dom_document->myDOMNode->saveHTML();
	}
 }
}

?>