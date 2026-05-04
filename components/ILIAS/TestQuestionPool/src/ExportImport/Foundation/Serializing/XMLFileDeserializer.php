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

declare(strict_types=1);

namespace ILIAS\TestQuestionPool\ExportImport\Foundation\Serializing;

use ILIAS\TestQuestionPool\ExportImport\Foundation\Contracts\Deserializer;

/**
 * XML deserializer that reads an XML file incrementally via XMLReader::open(), avoiding the need to load the entire
 * document into memory at once.
 */
class XMLFileDeserializer implements Deserializer
{
    use XMLDeserializerTrait;

    private string $file_path = '';

    /**
     * @inheritDoc
     */
    public function open(string $path): static
    {
        if (!is_file($path) || !is_readable($path)) {
            throw new \InvalidArgumentException(
                "The file '{$path}' does not exist or is not readable."
            );
        }

        $clone = clone $this;
        $clone->file_path = $path;
        return $clone;
    }

    /**
     * @inheritDoc
     */
    public function process(): void
    {
        if ($this->file_path === '') {
            throw new \RuntimeException(
                'No file has been opened. Call open() before process().'
            );
        }

        $reader = new \XMLReader();

        if (!$reader->open($this->file_path, null, LIBXML_NONET)) {
            throw new \RuntimeException(
                "Unable to open XML file '{$this->file_path}'."
            );
        }

        $this->processReader($reader);
    }
}
