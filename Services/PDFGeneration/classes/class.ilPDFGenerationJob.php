<?php
/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/
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
     * @return $this
     */
    public function setFilename(string $filename) : self
    {
        $this->filename = $filename;
        return $this;
    }

    public function getFilename() : ?string
    {
        return $this->filename;
    }

    /**
     * @param $pages string[] Array of html-strings.
     *
     * @return $this
     */
    public function setPages(array $pages) : self
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
    public function addPage($page) : self
    {
        $this->pages[] = $page;
        return $this;
    }

    /**
     * @return $this
     */
    public function flushPages() : self
    {
        $this->pages = array();
        return $this;
    }

    /**
                 * @return $this
                 */
    public function setOutputMode(string $output_mode) : self
    {
        $this->output_mode = $output_mode;
        return $this;
    }

    public function getOutputMode() : ?string
    {
        return $this->output_mode;
    }
}
