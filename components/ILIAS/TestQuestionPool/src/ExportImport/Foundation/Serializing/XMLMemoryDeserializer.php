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
 * Simple XML deserializer that reads an XML string in chunks and invokes registered handlers per group.
 */
class XMLMemoryDeserializer implements Deserializer
{
    use XMLDeserializerTrait;

    private string $xml = '';

    /**
     * @inheritDoc
     */
    public function open(string $path): static
    {
        $clone = clone $this;
        $clone->xml = $path;
        return $clone;
    }

    /**
     * @inheritDoc
     */
    public function process(): void
    {
        $reader = new \XMLReader();
        $xml = $this->prepareXmlInput($this->xml);

        if (!$reader->XML($xml, null, LIBXML_NONET)) {
            throw new \RuntimeException('Unable to read XML input.');
        }

        $this->processReader($reader);
    }

    private function prepareXmlInput(string $xml): string
    {
        $xml = trim($xml);
        $xml = preg_replace('/^\s*<\?xml[^>]*\?>\s*/i', '', $xml) ?? $xml;
        return "<deserializer-root>{$xml}</deserializer-root>";
    }
}
