<?php

declare(strict_types=1);

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/

use ILIAS\Data\Result;
use ILIAS\Data\Factory as DataTypeFactory;

final class ilXMLChecker
{
    private Result $result;
    private bool $xmlErrorState = false;
    /** @var array<int, LibXMLError[]> */
    private array $errorStack = [];

    public function __construct(private DataTypeFactory $dataFactory)
    {
        $this->result = new Result\Error('No XML parsed, yet');
    }

    private function beginLogging(): void
    {
        if ([] === $this->errorStack) {
            $this->xmlErrorState = libxml_use_internal_errors(true);
            libxml_clear_errors();
        } else {
            $this->addErrors();
        }

        $this->errorStack[] = [];
    }

    private function addErrors(): void
    {
        $currentErrors = libxml_get_errors();
        libxml_clear_errors();

        $level = count($this->errorStack) - 1;
        $this->errorStack[$level] = array_merge($this->errorStack[$level], $currentErrors);
    }

    /**
     * @return LibXMLError[] An array with the LibXMLErrors which has occurred since beginLogging() was called.
     */
    private function endLogging(): array
    {
        $this->addErrors();

        $errors = array_pop($this->errorStack);

        if ([] === $this->errorStack) {
            libxml_use_internal_errors($this->xmlErrorState);
        }

        return $errors;
    }

    public function parse(string $xmlString): void
    {
        try {
            $this->beginLogging();

            /** @noinspection PhpExpressionResultUnusedInspection */
            new SimpleXMLElement($xmlString);

            $this->result = $this->dataFactory->ok($xmlString);
            $this->endLogging();
        } catch (Exception) {
            $this->result = $this->dataFactory->error(implode(
                "\n",
                array_map(static function (LibXMLError $error): string {
                    return implode(',', [
                        'level=' . $error->level,
                        'code=' . $error->code,
                        'line=' . $error->line,
                        'col=' . $error->column,
                        'msg=' . trim($error->message)
                    ]);
                }, $this->endLogging())
            ));
        }
    }

    public function result(): Result
    {
        return $this->result;
    }
}
