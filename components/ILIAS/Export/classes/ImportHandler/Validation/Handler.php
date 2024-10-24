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

namespace ILIAS\Export\ImportHandler\Validation;

use DOMDocument;
use Exception;
use ILIAS\Export\ImportHandler\I\File\HandlerInterface as FileHandlerInterface;
use ILIAS\Export\ImportHandler\I\File\XML\HandlerInterface as XMLFileHandlerInterface;
use ILIAS\Export\ImportHandler\I\File\XSD\HandlerInterface as XSDFileHandlerInterface;
use ILIAS\Export\ImportHandler\I\Parser\FactoryInterface as ParserFactoryInterface;
use ILIAS\Export\ImportHandler\I\Parser\NodeInfo\CollectionInterface as XMLFileNodeInfoCollection;
use ILIAS\Export\ImportHandler\I\Path\FactoryInterface as FilePathFactoryInterface;
use ILIAS\Export\ImportHandler\I\Path\HandlerInterface as FilePathHandlerInterface;
use ILIAS\Export\ImportHandler\I\Validation\HandlerInterface as FileValidationHandlerInterface;
use ILIAS\Export\ImportHandler\I\Validation\Set\CollectionInterface as FileValidationSetCollectionInterface;
use ILIAS\Export\ImportStatus\Exception\ilException as ImportStatusException;
use ILIAS\Export\ImportStatus\I\ilCollectionInterface as ImportStatusHandlerCollectionInterface;
use ILIAS\Export\ImportStatus\I\ilFactoryInterface as ImportStatusFactoryInterface;
use ILIAS\Export\ImportStatus\I\ilHandlerInterface as ImportStatusHandlerInterface;
use ILIAS\Export\ImportStatus\StatusType;
use ilLogger;
use LibXMLError;

class Handler implements FileValidationHandlerInterface
{
    public const TMP_DIR_NAME = 'temp';
    public const XML_DIR_NAME = 'xml';

    protected ilLogger $logger;
    protected ImportStatusFactoryInterface $import_status;
    protected ParserFactoryInterface $parser;
    protected FilePathFactoryInterface $path;
    protected ImportStatusHandlerInterface $success_status;

    public function __construct(
        ilLogger $logger,
        ParserFactoryInterface $parser,
        ImportStatusFactoryInterface $import_status,
        FilePathFactoryInterface $path,
    ) {
        $this->logger = $logger;
        $this->import_status = $import_status;
        $this->parser = $parser;
        $this->path = $path;
        $this->success_status = $import_status->handler()
            ->withType(StatusType::SUCCESS)
            ->withContent($import_status->content()->builder()->string()->withString('Validation SUCCESS'));
    }

    /**
     * @param FileHandlerInterface $file_handlers
     */
    protected function checkIfFilesExist(array $file_handlers): ImportStatusHandlerCollectionInterface
    {
        $status_collection = $this->import_status->collection()->withNumberingEnabled(true);
        foreach ($file_handlers as $file_handler) {
            if ($file_handler->fileExists()) {
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
        ?XMLFileHandlerInterface $xml_file_handler = null,
        ?XSDFileHandlerInterface $xsd_file_handler = null,
        array $errors = []
    ): ImportStatusHandlerCollectionInterface {
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
        ?XMLFileHandlerInterface $xml_file_handler = null,
        ?XSDFileHandlerInterface $xsd_file_handler = null
    ): ImportStatusHandlerCollectionInterface {
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
        XMLFileHandlerInterface $xml_file_handler,
        XSDFileHandlerInterface $xsd_file_handler,
        XMLFileNodeInfoCollection $nodes
    ): ImportStatusHandlerCollectionInterface {
        // Check if files exist
        $status_collection = $this->checkIfFilesExist([$xsd_file_handler]);
        if ($status_collection->hasStatusType(StatusType::FAILED)) {
            return $status_collection;
        }
        if (count($nodes) === 0) {
            return $this->validateEmptyXML($xml_file_handler, $xsd_file_handler);
        }
        $old_value = libxml_use_internal_errors(true);
        $status_collection = $this->import_status->collection()->withNumberingEnabled(true);
        foreach ($nodes as $node) {
            $doc = new DOMDocument();
            $doc->loadXML($node->getXML(), LIBXML_NOBLANKS);
            $doc->normalizeDocument();
            foreach ($xml_file_handler->getNamespaces() as $namespace) {
                $doc->createAttributeNS($namespace->getNamespace(), $namespace->getPrefix());
            }
            try {
                if ($doc->schemaValidate($xsd_file_handler->getFilePath())) {
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
        XMLFileHandlerInterface $xml_file_handler,
        XSDFileHandlerInterface $xsd_file_handler
    ): ImportStatusHandlerCollectionInterface {
        /*$old_value = libxml_use_internal_errors(true);
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
        ));*/
        return $this->import_status->collection()->withNumberingEnabled(true);
    }

    public function validateXMLFile(
        XMLFileHandlerInterface $xml_file_handler,
        XSDFileHandlerInterface $xsd_file_handler
    ): ImportStatusHandlerCollectionInterface {
        return $this->validateXMLAtPath(
            $xml_file_handler,
            $xsd_file_handler,
            $this->path->handler()->withNode($this->path->node()->anyElement())->withStartAtRoot(true)
        );
    }

    /**
     * @throws ImportStatusException
     */
    public function validateXMLAtPath(
        XMLFileHandlerInterface $xml_file_handler,
        XSDFileHandlerInterface $xsd_file_handler,
        FilePathHandlerInterface $path_handler
    ): ImportStatusHandlerCollectionInterface {
        return $this->validateXMLAtNodes(
            $xml_file_handler,
            $xsd_file_handler,
            $this->parser->DOM()->handler()
                ->withFileHandler($xml_file_handler)
                ->getNodeInfoAt($path_handler)
        );
    }

    /**
     * @throws ImportStatusException
     */
    public function validateSets(
        FileValidationSetCollectionInterface $sets
    ): ImportStatusHandlerCollectionInterface {
        $status_collection = $this->import_status->collection();
        foreach ($sets as $set) {
            $status_collection = $status_collection->getMergedCollectionWith($this->validateXMLAtPath(
                $set->getXMLFileHandler(),
                $set->getXSDFileHandler(),
                $set->getFilePathHandler()
            ));
        }
        return $status_collection;
    }
}
