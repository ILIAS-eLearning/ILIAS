<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Html/interfaces/interface.ilHtmlPurifierInterface.php';

/** 
* Composite for nesting multiple purifiers
* 
* @author	Michael Jansen <mjansen@databay.de>
* @version	$Id$
* 
*/
class ilHtmlPurifierComposite implements ilHtmlPurifierInterface
{
	/** 
	* Array of ilHtmlPurifierInterface instances
	* 
	* @var		Array
	* @type		Array
	* @access	protected
	* 
	*/
	protected $purifiers = array();
	
   /** 
	* Adds a node to composite
	* 
	* @access	public
	* @param	ilHtmlPurifierInterface	$a_purifier	Instance of ilHtmlPurifierInterface
	* @return	bool					True if instance could be added, otherwise false
	* 
	*/
	public function addPurifier(ilHtmlPurifierInterface $a_purifier)
	{
		$key = array_search($a_purifier, $this->purifiers);
		if(false === $key)
		{
			$this->purifiers[] = $a_purifier;
			return true;
		}		
		
		return false;
	}
	
   /** 
	* Removes a node from composite
	* 
	* @access	public
	* @param	ilHtmlPurifierInterface	$a_purifier	Instance of ilHtmlPurifierInterface
	* @return	bool					True if instance could be removed, otherwise false
	* 
	*/
	public function removePurifier(ilHtmlPurifierInterface $a_purifier)
	{
		$key = array_search($a_purifier, $this->purifiers);
		if(false === $key)
		{
			return false;
		}
		unset($this->purifiers[$key]);		
		
		return true;
	}
	
	/** 
	* Filters an HTML snippet/document to be XSS-free and standards-compliant.
	* 
	* @access	public
	* @param	string	$a_html HTML snippet/document
	* @return	string	purified html
	* 
	*/
	public function purify($a_html)
	{
		foreach($this->purifiers as $oPurifier)
		{
			$a_html = $oPurifier->purify($a_html);
		}
		
		return $a_html;
	}
	
  	/** 
	* Filters an array of HTML snippets/documents to be XSS-free and standards-compliant.
	* 
	* @access	public
	* @param	array	$a_array_of_html	HTML snippet/document
	* @return	array	Array of HTML snippets/documents
	* 
	*/
	public function purifyArray(Array $a_array_of_html)
	{		
		foreach($a_array_of_html as $key => $html)
		{		
			foreach($this->purifiers as $oPurifier)
			{
				$html = $oPurifier->purify($html);
			}
			
			$a_array_of_html[$key] = $html;
		}
		
		return $a_array_of_html;
	}
}
?>