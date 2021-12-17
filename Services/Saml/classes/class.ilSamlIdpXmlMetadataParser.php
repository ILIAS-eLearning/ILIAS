<?php declare(strict_types=1);
/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\Data\Factory as DataTypeFactory;
use ILIAS\Data\Result;

final class ilSamlIdpXmlMetadataParser
{
    private DataTypeFactory $dataFactory;
    private ilSamlIdpXmlMetadataErrorFormatter $errorFormatter;
    private Result $result;
    private bool $xmlErrorState = false;
    /** @var LibXMLError */
    private array $errorStack = [];

    public function __construct(DataTypeFactory $dataFactory, ilSamlIdpXmlMetadataErrorFormatter $errorFormatter)
    {
        $this->dataFactory = $dataFactory;
        $this->errorFormatter = $errorFormatter;
        $this->result = new Result\Error('No metadata parsed, yet');
    }

    private function beginLogging() : void
    {
        if (0 === count($this->errorStack)) {
            $this->xmlErrorState = libxml_use_internal_errors(true);
            libxml_clear_errors();
        } else {
            $this->addErrors();
        }

        $this->errorStack[] = [];
    }

    private function addErrors() : void
    {
        $currentErrors = libxml_get_errors();
        libxml_clear_errors();

        $level = count($this->errorStack) - 1;
        $this->errorStack[$level] = array_merge($this->errorStack[$level], $currentErrors);
    }

    /**
     * @return LibXMLError[] An array with the LibXMLErrors which has occurred since beginLogging() was called.
     */
    private function endLogging() : array
    {
        $this->addErrors();

        $errors = array_pop($this->errorStack);

        if (0 === count($this->errorStack)) {
            libxml_use_internal_errors($this->xmlErrorState);
        }

        return $errors;
    }

    public function parse(string $xmlString) : void
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

            if ($entityId) {
                $this->result = $this->dataFactory->ok($entityId);
                return;
            }

            $errors = $this->endLogging();

            $error = new LibXMLError();
            $error->level = LIBXML_ERR_FATAL;
            $error->code = 0;
            $error->message = 'No entityID found';
            $error->line = 1;
            $error->column = 0;

            $errors[] = $error;

            $this->result = $this->dataFactory->error($this->errorFormatter->formatErrors(...$errors));
        } catch (Exception $e) {
            $this->result = $this->dataFactory->error($this->errorFormatter->formatErrors(...$this->endLogging()));
        }
    }

    public function result() : Result
    {
        return $this->result;
    }
}
