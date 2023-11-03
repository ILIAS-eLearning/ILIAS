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

namespace ImportHandler\File\Validation;

use DOMDocument;
use Exception;
use ilLogger;
use ImportHandler\I\File\XML\Node\Info\ilCollectionInterface as ilXMLFileNodeInfoCollection;
use ImportHandler\I\File\ilHandlerInterface as ilFileHandlerInterface;
use ImportHandler\I\File\Path\ilFactoryInterface as ilFilePathFactoryInterface;
use ImportHandler\I\File\Path\ilHandlerInterface as ilFilePathHandlerInterface;
use ImportHandler\I\File\Validation\ilHandlerInterface as ilFileValidationHandlerInterface;
use ImportHandler\I\File\XML\ilHandlerInterface as ilXMLFileHandlerInterface;
use ImportHandler\I\File\XSD\ilHandlerInterface as ilXSDFileHandlerInterface;
use ImportHandler\I\Parser\ilHandlerInterface as ilParserHandlerInterface;
use ImportStatus\Exception\ilException as ilImportStatusException;
use ImportStatus\I\ilFactoryInterface as ilImportStatusFactoryInterface;
use ImportStatus\I\ilCollectionInterface as ilImportStatusHandlerCollectionInterface;
use ImportStatus\I\ilHandlerInterface as ilImportStatusHandlerInterface;
use ImportStatus\StatusType;
use LibXMLError;

class ilHandler implements ilFileValidationHandlerInterface
{
    protected const TMP_DIR_NAME = 'temp';
    protected const XML_DIR_NAME = 'xml';

    protected ilLogger $logger;
    protected ilImportStatusFactoryInterface $import_status;
    protected ilParserHandlerInterface $parser_handler;
    protected ilFilePathFactoryInterface $path;
    protected ilImportStatusHandlerInterface $success_status;

    public function __construct(
        ilLogger $logger,
        ilParserHandlerInterface $parser_handler,
        ilImportStatusFactoryInterface $import_status,
        ilFilePathFactoryInterface $path,
    ) {
        $this->logger = $logger;
        $this->import_status = $import_status;
        $this->parser_handler = $parser_handler;
        $this->path = $path;
        $this->success_status = $import_status->handler()
            ->withType(StatusType::SUCCESS)
            ->withContent($import_status->content()->builder()->string()->withString('Validation SUCCESS'));
    }

    /**
     * @param ilFileHandlerInterface $file_handlers
     */
    protected function checkIfFilesExist(array $file_handlers): ilImportStatusHandlerCollectionInterface
    {
        $status_collection = $this->import_status->collection()->withNumberingEnabled(true);
        foreach ($file_handlers as $file_handler) {
            if($file_handler->fileExists()) {
                continue;
            }
            $status_collection->withAddedStatus($this->import_status->handler()
                ->withType(StatusType::FAILED)
                ->withContent($this->import_status->content()->builder()->string()
                    ->withString('File does not exist: ' . $file_handler->getFilePath())));
        }
        return $status_collection;
    }

    /**
     * @param LibXMLError[] $errors
     */
    protected function collectErrors(
        ?ilXMLFileHandlerInterface $xml_file_handler = null,
        ?ilXSDFileHandlerInterface $xsd_file_handler = null,
        array $errors = []
    ): ilImportStatusHandlerCollectionInterface {
        $status_collection = $this->import_status->collection();
        foreach ($errors as $error) {
            $status_collection = $status_collection->getMergedCollectionWith(
                $this->createErrorMessage($error->message, $xml_file_handler, $xsd_file_handler)
            );
        }
        return $status_collection;
    }

    protected function createErrorMessage(
        string $msg,
        ?ilXMLFileHandlerInterface $xml_file_handler = null,
        ?ilXSDFileHandlerInterface $xsd_file_handler = null
    ): ilImportStatusHandlerCollectionInterface {
        $status_collection = $this->import_status->collection();
        $xml_str = is_null($xml_file_handler)
            ? ''
            : "<br>XML-File: " . $xml_file_handler->getSubPathToDirBeginningAtPathEnd(self::TMP_DIR_NAME)->getFilePath();
        $xsd_str = is_null($xsd_file_handler)
            ? ''
            : "<br>XSD-File: " . $xsd_file_handler->getSubPathToDirBeginningAtPathEnd(self::XML_DIR_NAME)->getFilePath();
        $content = $this->import_status->content()->builder()->string()->withString(
            "Validation FAILED"
            . $xml_str
            . $xsd_str
            . "<br>ERROR Message: " . $msg
        );
        $status_collection = $status_collection->withAddedStatus(
            $this->import_status->handler()->withType(StatusType::FAILED)->withContent($content)
        );
        return $status_collection;
    }


    protected function validateXMLAtNodes(
        ilXMLFileHandlerInterface $xml_file_handler,
        ilXSDFileHandlerInterface $xsd_file_handler,
        ilXMLFileNodeInfoCollection $nodes
    ): ilImportStatusHandlerCollectionInterface {
        $this->logger->debug(
            "\n\nValidating:"
            . "\nXML: " . $xml_file_handler->getFilePath()
            . "\nXSD: " . $xsd_file_handler->getFilePath() . "\n"
        );
        // Check if files exist
        $status_collection = $this->checkIfFilesExist([$xsd_file_handler]);
        if($status_collection->hasStatusType(StatusType::FAILED)) {
            return $status_collection;
        }
        if(count($nodes) === 0) {
            return $this->validateEmptyXML($xml_file_handler, $xsd_file_handler);
        }
        $old_value = libxml_use_internal_errors(true);
        $status_collection = $this->import_status->collection()->withNumberingEnabled(true);
        foreach ($nodes as $node) {
            $doc = new DOMDocument();
            $doc->loadXML($node->getXML(), LIBXML_NOBLANKS);
            $doc->normalizeDocument();
            try {
                if($doc->schemaValidate($xsd_file_handler->getFilePath())) {
                    continue;
                }
            } catch (Exception $e) {
                // Catches xsd file related exceptions to allow for manual error handling
            }
            $errors = libxml_get_errors();
            libxml_clear_errors();
            $status_collection = $status_collection->getMergedCollectionWith($this->collectErrors(
                $xml_file_handler,
                $xsd_file_handler,
                $errors
            ));
        }
        libxml_use_internal_errors($old_value);
        return $status_collection->hasStatusType(StatusType::FAILED)
            ? $status_collection
            : $this->import_status->collection()->withAddedStatus($this->success_status);
    }

    protected function validateEmptyXML(
        ilXMLFileHandlerInterface $xml_file_handler,
        ilXSDFileHandlerInterface $xsd_file_handler
    ): ilImportStatusHandlerCollectionInterface {
        $old_value = libxml_use_internal_errors(true);
        $status_collection = $this->import_status->collection()->withNumberingEnabled(true);
        $doc = new DOMDocument();
        $doc->schemaValidate($xsd_file_handler->getFilePath());
        $errors = libxml_get_errors();
        libxml_clear_errors();
        libxml_use_internal_errors($old_value);
        return $status_collection->getMergedCollectionWith($this->collectErrors(
            $xml_file_handler,
            $xsd_file_handler,
            $errors
        ));
    }

    public function validateXMLFile(
        ilXMLFileHandlerInterface $xml_file_handler,
        ilXSDFileHandlerInterface $xsd_file_handler
    ): ilImportStatusHandlerCollectionInterface {
        return $this->validateXMLAtPath(
            $xml_file_handler,
            $xsd_file_handler,
            $this->path->handler()->withNode($this->path->node()->anyElement())->withStartAtRoot(true)
        );
    }

    /**
     * @throws ilImportStatusException
     */
    public function validateXMLAtPath(
        ilXMLFileHandlerInterface $xml_file_handler,
        ilXSDFileHandlerInterface $xsd_file_handler,
        ilFilePathHandlerInterface $path_handler
    ): ilImportStatusHandlerCollectionInterface {
        return $this->validateXMLAtNodes(
            $xml_file_handler,
            $xsd_file_handler,
            $this->parser_handler->withFileHandler($xml_file_handler)->getNodeInfoAt($path_handler)
        );
    }
}
