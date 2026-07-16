<?php

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

class ilCSVWriter
{
    private string $csv = '';
    private string $separator = ',';
    private string $delimiter = '"';
    private string $new_line = "\n";
    private bool $first_entry = true;

    public function setSeparator(string $a_sep): void
    {
        $this->separator = $a_sep;
    }

    public function setDelimiter(string $a_del): void
    {
        $this->delimiter = $a_del;
    }

    public function setNewline(string $a_new): void
    {
        $this->new_line = $a_new;
    }

    public function withSeparator(string $a_sep): ilCSVWriter
    {
        $this->setSeparator($a_sep);
        return $this;
    }

    public function withDelimiter(string $a_del): ilCSVWriter
    {
        $this->setDelimiter($a_del);
        return $this;
    }

    public function withNewline(string $a_new): ilCSVWriter
    {
        $this->setNewline($a_new);
        return $this;
    }

    public function addRow(): void
    {
        $this->csv .= $this->new_line;
        $this->first_entry = true;
    }

    public function addColumn(string $a_col): void
    {
        if (!$this->first_entry) {
            $this->csv .= $this->separator;
        }
        $this->csv .= $this->delimiter;
        $this->csv .= $this->quote($a_col);
        $this->csv .= $this->delimiter;
        $this->first_entry = false;
    }

    public function getCSVString(): string
    {
        return $this->csv;
    }

    private function quote(string $a_str): string
    {
        return str_replace(
            $this->delimiter,
            $this->delimiter . $this->delimiter,
            $a_str
        );
    }
}
