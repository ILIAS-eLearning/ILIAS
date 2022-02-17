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
class ilPDFGenerationJob
{
    private array $pages;
    private string $filename;
    private string $output_mode;

    public function setFilename(string $filename) : self
    {
        $this->filename = $filename;
        return $this;
    }

    public function getFilename() : ?string
    {
        return $this->filename;
    }

    public function setPages(array $pages) : self
    {
        $this->pages = $pages;
        return $this;
    }

    public function getPages() : array
    {
        return $this->pages;
    }

    public function addPage(array $page) : self
    {
        $this->pages[] = $page;
        return $this;
    }

    public function flushPages() : self
    {
        $this->pages = array();
        return $this;
    }

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
