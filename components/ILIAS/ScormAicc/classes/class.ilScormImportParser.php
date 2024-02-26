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

use ILIAS\Data\Factory as DataTypeFactory;

class ilScormImportParser
{
    private bool $xml_error_state = false;
    /** @var array<int, LibXMLError[]> */
    private array $error_stack = [];
    private DataTypeFactory $df;

    public function __construct(DataTypeFactory $data_factory)
    {
        $this->df = $data_factory;
    }

    private function formatError(LibXMLError $error): string
    {
        return implode(',', [
            'level=' . $error->level,
            'code=' . $error->code,
            'line=' . $error->line,
            'col=' . $error->column,
            'msg=' . trim($error->message)
        ]);
    }

    private function formatErrors(LibXMLError ...$errors): string
    {
        $text = '';
        foreach ($errors as $error) {
            $text .= $this->formatError($error) . "\n";
        }

        return $text;
    }

    private function beginLogging(): void
    {
        if ([] === $this->error_stack) {
            $this->xml_error_state = libxml_use_internal_errors(true);
            libxml_clear_errors();
        } else {
            $this->addErrors();
        }

        $this->error_stack[] = [];
    }

    private function addErrors(): void
    {
        $currentErrors = libxml_get_errors();
        libxml_clear_errors();

        $level = count($this->error_stack) - 1;
        $this->error_stack[$level] = array_merge($this->error_stack[$level], $currentErrors);
    }

    /**
     * @return LibXMLError[] An array with the LibXMLErrors which has occurred since beginLogging() was called.
     */
    private function endLogging(): array
    {
        $this->addErrors();

        $errors = array_pop($this->error_stack);

        if ([] === $this->error_stack) {
            libxml_use_internal_errors($this->xml_error_state);
        }

        return $errors;
    }

    public function parse(string $xmlString): \ILIAS\Data\Result
    {
        try {
            $this->beginLogging();

            $xml = new SimpleXMLElement($xmlString);

            $errors = $this->endLogging();

            if ($xml->xpath('//SubType')) {
                return $this->df->ok($xml);
            }

            $error = new LibXMLError();
            $error->level = LIBXML_ERR_FATAL;
            $error->code = 0;
            $error->message = 'No "SubType" element found';
            $error->line = 1;
            $error->column = 0;

            $errors[] = $error;

            return $this->df->error($this->formatErrors(...$errors));
        } catch (Throwable $e) {
            return $this->df->error($this->formatErrors(...$this->endLogging()));
        }
    }
}
