<?php declare(strict_types=1);
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Abstract class wrapping the HTMLPurifier instance
 * @author Michael Jansen <mjansen@databay.de>
 */
abstract class ilHtmlPurifierAbstractLibWrapper implements ilHtmlPurifierInterface
{
    /** @var HTMLPurifier */
    protected $purifier;

    /**
     * ilHtmlPurifierAbstractLibWrapper constructor.
     */
    public function __construct()
    {
        $this->setPurifier(
            new HTMLPurifier($this->getPurifierConfigInstance())
        );
    }

    /**
     * @inheritDoc
     */
    final public function purify(string $html) : string
    {
        return $this->purifier->purify($html);
    }

    /**
     * @inheritDoc
     */
    final public function purifyArray(array $htmlCollection) : array
    {
        return $this->purifier->purifyArray($htmlCollection);
    }

    /**
     * @return HTMLPurifier_Config
     */
    abstract protected function getPurifierConfigInstance() : HTMLPurifier_Config;

    /**
     * @param HTMLPurifier $purifier
     * @return ilHtmlPurifierAbstractLibWrapper
     */
    protected function setPurifier(HTMLPurifier $purifier) : self
    {
        $this->purifier = $purifier;
        return $this;
    }

    /**
     * @return HTMLPurifier
     */
    protected function getPurifier() : HTMLPurifier
    {
        return $this->purifier;
    }

    /**
     * @return string
     */
    final static public function _getCacheDirectory() : string
    {
        if (!file_exists(ilUtil::getDataDir() . '/HTMLPurifier') ||
            !is_dir(ilUtil::getDataDir() . '/HTMLPurifier')) {
            ilUtil::makeDirParents(ilUtil::getDataDir() . '/HTMLPurifier');
        }

        return ilUtil::getDataDir() . '/HTMLPurifier';
    }

    /**
     * Removes all unsupported elements
     * @param string[] $elements
     * @return string[]
     */
    final protected function removeUnsupportedElements(array $elements) : array
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

        foreach ($elements as $element) {
            if (!in_array($element, $notSupportedTags)) {
                $supportedElements[] = $element;
            }
        }

        return $supportedElements;
    }

    /**
     * @param string[] $elements
     * @return string[]
     */
    protected function makeElementListTinyMceCompliant(array $elements) : array
    {
        // Bugfix #5945: Necessary because TinyMCE does not use the "u" 
        // html element but <span style="text-decoration: underline">E</span>

        if (in_array('u', $elements) && !in_array('span', $elements)) {
            $elements[] = 'span';
        }

        return $elements;
    }
}