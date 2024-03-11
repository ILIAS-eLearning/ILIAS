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
use ILIAS\Data\Result;

final class ilSamlIdpXmlMetadataParser
{
    private bool $xmlErrorState = false;
    /** @var array<int, LibXMLError[]> */
    private array $errorStack = [];

    public function __construct(
        private readonly DataTypeFactory $dataFactory,
        private readonly ilSamlIdpXmlMetadataErrorFormatter $errorFormatter
    ) {
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

    /**
     * @return Result<non-empty-string>
     */
    public function parse(string $xmlString): Result
    {
        try {
            $this->beginLogging();

            $xml = new SimpleXMLElement($xmlString);

            $xml->registerXPathNamespace('md', 'urn:oasis:names:tc:SAML:2.0:metadata');
            $xml->registerXPathNamespace('mdui', 'urn:oasis:names:tc:SAML:metadata:ui');

            $idps = $xml->xpath('//md:EntityDescriptor[//md:IDPSSODescriptor]');

            $entity_id = ($idps[0] ?? null)?->attributes('', true)['entityID'][0] ?? '';
            $entity_id = (string) $entity_id;

            if ($entity_id !== '') {
                return $this->ok($entity_id);
            }

            $error = new LibXMLError();
            $error->level = LIBXML_ERR_FATAL;
            $error->code = 0;
            $error->message = 'No entityID found';
            $error->line = 1;
            $error->column = 0;

            return $this->error([$error]);
        } catch (Exception) {
            return $this->error();
        }
    }

    private function ok(string $entity_id): Result
    {
        $this->endLogging();

        return $this->dataFactory->ok($entity_id);
    }

    /**
     * @param list<LibXMLError> $additional_errors
     */
    private function error($additional_errors = []): Result
    {
        return $this->dataFactory->error($this->errorFormatter->formatErrors(...$this->endLogging(), ...$additional_errors));
    }
}
