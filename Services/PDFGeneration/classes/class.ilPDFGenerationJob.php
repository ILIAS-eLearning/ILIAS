<?php declare(strict_types=1);

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

class ilPDFGenerationJob
{
    /** @var string[] */
    private array $pages = [];
    private ?string $filename;
    private ?string $output_mode;

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
     * @param string[] $pages
     * @return $this
     */
    public function setPages(array $pages) : self
    {
        $this->pages = $pages;
        return $this;
    }

    /**
     * @return string[]
     */
    public function getPages() : array
    {
        return $this->pages;
    }

    public function addPage(string $page) : self
    {
        $this->pages[] = $page;
        return $this;
    }

    public function flushPages() : self
    {
        $this->pages = [];
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
