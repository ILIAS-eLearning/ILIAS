<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Interface for html sanitizing functionality
*
* @author	Michael Jansen <mjansen@databay.de>
* @version	$Id$
*
*/
interface ilHtmlPurifierInterface
{
    /**
    * Filters an HTML snippet/document to be XSS-free and standards-compliant.
    *
    * @access	public
    * @param	string	$a_html HTML snippet/document
    *
    */
    public function purify($a_html);
    
    /**
    * Filters an array of HTML snippets/documents to be XSS-free and standards-compliant.
    *
    * @access	public
    * @param	array	$a_array_of_html HTML snippet/document
    *
    */
    public function purifyArray(array $a_array_of_html);
}
