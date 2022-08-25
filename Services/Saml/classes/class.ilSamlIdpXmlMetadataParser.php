<?php

declare(strict_types=1);

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

use ILIAS\Data\Factory as DataTypeFactory;
use ILIAS\Data\Result;

final class ilSamlIdpXmlMetadataParser
{
    private Result $result;
    private bool $xmlErrorState = false;
    /** @var array<int, LibXMLError[]> */
    private array $errorStack = [];

    public function __construct(private DataTypeFactory $dataFactory, private ilSamlIdpXmlMetadataErrorFormatter $errorFormatter)
    {
        $this->result = new Result\Error('No metadata parsed, yet');
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

            $xml = new SimpleXMLElement($xmlString);

            $xml->registerXPathNamespace('md', 'urn:oasis:names:tc:SAML:2.0:metadata');
            $xml->registerXPathNamespace('mdui', 'urn:oasis:names:tc:SAML:metadata:ui');

            $idps = $xml->xpath('//md:EntityDescriptor[//md:IDPSSODescriptor]');
            $entityId = null;
            if ($idps && is_array($idps) && isset($idps[0]) && $idps[0] instanceof SimpleXMLElement) {
                $attributes = $idps[0]->attributes('', true);
                if ($attributes && isset($attributes['entityID'])) {
                    $entityId = (string) ($attributes->entityID[0] ?? '');
                }
            }

            $errors = $this->endLogging();

            if ($entityId) {
                $this->result = $this->dataFactory->ok($entityId);
                return;
            }

            $error = new LibXMLError();
            $error->level = LIBXML_ERR_FATAL;
            $error->code = 0;
            $error->message = 'No entityID found';
            $error->line = 1;
            $error->column = 0;

            $errors[] = $error;

            $this->result = $this->dataFactory->error($this->errorFormatter->formatErrors(...$errors));
        } catch (Exception) {
            $this->result = $this->dataFactory->error($this->errorFormatter->formatErrors(...$this->endLogging()));
        }
    }

    public function result(): Result
    {
        return $this->result;
    }
}
