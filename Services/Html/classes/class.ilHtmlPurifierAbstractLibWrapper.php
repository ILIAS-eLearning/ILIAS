<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'libs/composer/vendor/autoload.php';
require_once 'Services/Html/interfaces/interface.ilHtmlPurifierInterface.php';

/**
* Abstract class wrapping the HTMLPurifier instance
*
* @author	Michael Jansen <mjansen@databay.de>
* @version	$Id$
* @abstract
*
*/
abstract class ilHtmlPurifierAbstractLibWrapper implements ilHtmlPurifierInterface
{
    
    /**
    * Instance of HTMLPurifier
    *
    * @var		HTMLPurifier
    * @type		HTMLPurifier
    * @access	protected
    *
    */
    protected $oPurifier = null;
    
    /**
    * Constructor
    *
    * @access	public
    *
    */
    public function __construct()
    {
        $this->setPurifier(
            new HTMLPurifier($this->getPurifierConfigInstance())
        );
    }
    
    /**
    * Filters an HTML snippet/document to be XSS-free and standards-compliant.
    *
    * @access	public
    * @param	string	$a_html HTML snippet/document
    * @return	string	purified html
    * @final
    *
    */
    final public function purify($a_html, $a_config = null)
    {
        return $this->oPurifier->purify($a_html, $a_config);
    }
    
    /**
    * Filters an array of HTML snippets/documents to be XSS-free and standards-compliant.
    *
    * @access	public
    * @param	array	$a_array_of_html	HTML snippet/document
    * @return	array	Array of HTML snippets/documents
    * @final
    *
    */
    final public function purifyArray(array $a_array_of_html, $a_config = null)
    {
        return $this->oPurifier->purifyArray($a_array_of_html, $a_config);
    }
    
    /**
    * Has to be implemented by subclasses to build the HTMLPurifier_Config instance with
    * object specific configurations
    *
    * @abstract
    * @access	protected
    *
    */
    abstract protected function getPurifierConfigInstance();
    
    /**
    *
    * Set the purifier by subclass
    *
    * @param	HTMLPurifier	$oPurifier	Instance of HTMLPurifier
    * @return	ilHtmlPurifier	This reference
    * @access	protected
    *
    */
    protected function setPurifier(HTMLPurifier $oPurifier)
    {
        $this->oPurifier = $oPurifier;
        return $this;
    }
    
    /**
    *
    * Get the purifier
    *
    * @return	HTMLPurifier instance of HTMLPurifier
    * @access	protected
    *
    */
    protected function getPurifier()
    {
        return $this->oPurifier;
    }
    
    /**
    *
    * Get the directory for HTMLPurifier cache files
    *
    * @return	string	Cache directory for HTMLPurifier
    * @access	public
    * @final
    * @statc
    *
    */
    final public static function _getCacheDirectory()
    {
        if (!file_exists(ilUtil::getDataDir() . '/HTMLPurifier') ||
           !is_dir(ilUtil::getDataDir() . '/HTMLPurifier')) {
            ilUtil::makeDirParents(ilUtil::getDataDir() . '/HTMLPurifier');
        }
        
        return ilUtil::getDataDir() . '/HTMLPurifier';
    }
    
    /**
    * Removes all unsupported elements
    *
    * @param	Array	$a_array array of all elements
    * @return	Array	array of supported elements
    * @access	protected
    * @final
    *
    */
    final protected function removeUnsupportedElements($a_array)
    {
        $supportedElements = array();
        
        $notSupportedTags = array(
            'rp',
            'rt',
            'rb',
            'rtc',
            'rbc',
            'ruby',
            'u',
            'strike',
            'param',
            'object'
        );
        
        foreach ($a_array as $element) {
            if (!in_array($element, $notSupportedTags)) {
                $supportedElements[] = $element;
            }
        }

        return $supportedElements;
    }
    
    protected function makeElementListTinyMceCompliant($elements)
    {
        // Bugfix #5945: Necessary because TinyMCE does not use the "u"
        // html element but <span style="text-decoration: underline">E</span>
        
        if (in_array('u', $elements) && !in_array('span', $elements)) {
            $elements[] = 'span';
        }
        
        return $elements;
    }
}
