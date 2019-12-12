<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilPDFGenerationJob
 *
 * Data-object blueprint that holds all PDF-generation related settings.
 * If you add to the methods, see to it that they follow the fluent interface, meaning
 * that all setters return $this for developer convenience.
 *
 * @author Maximilian Becker <mbecker@databay.de>
 * @version $Id$
 *
 */
class ilPDFGenerationJob
{
    private $pages;					/** @var $pages string[] HTML pages */
    private $filename;				/** @var $filename string Filename */
    private $output_mode;			/** @var $output_mode string Output mode, one D, F or I */

    /**
     * @param string $filename
     * @return $this
     */
    public function setFilename($filename)
    {
        $this->filename = $filename;
        return $this;
    }

    /**
     * @return string
     */
    public function getFilename()
    {
        return $this->filename;
    }

    /**
     * @param $pages string[] Array of html-strings.
     *
     * @return $this
     */
    public function setPages($pages)
    {
        $this->pages = $pages;
        return $this;
    }

    /**
     * @return string[] Array of html-strings.
     */
    public function getPages()
    {
        return $this->pages;
    }

    /**
     * @param $page
     * @return $this
     */
    public function addPage($page)
    {
        $this->pages[] = $page;
        return $this;
    }

    /**
     * @return $this
     */
    public function flushPages()
    {
        $this->pages = array();
        return $this;
    }

    /**
     * @param string $output_mode
     * @return $this
     */
    public function setOutputMode($output_mode)
    {
        $this->output_mode = $output_mode;
        return $this;
    }

    /**
     * @return string
     */
    public function getOutputMode()
    {
        return $this->output_mode;
    }
}
