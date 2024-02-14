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

class ilScormAiccImporter extends ilXmlImporter
{
    private ilScormAiccDataSet $dataset;
    private \ILIAS\Data\Result $result;
    private DataTypeFactory $df;
    /**
     * @var array<string, string|SimpleXMLElement>
     */
    private array $module_properties = [];

    public function __construct()
    {
        $this->dataset = new ilScormAiccDataSet();
        $this->df = new DataTypeFactory();
        $this->initResult();

        parent::__construct();
    }

    private function initResult(): void
    {
        $this->publishResult($this->df->error('No XML parsed, yet'));
        $this->module_properties = [];
    }

    private function publishResult(\ILIAS\Data\Result $result): \ILIAS\Data\Result
    {
        $this->result = $result;
        return $this->result;
    }

    /**
     * @return \ILIAS\Data\Result|\ILIAS\Data\Result\Ok<array<string, string|SimpleXMLElement>>
     */
    public function getResult(): \ILIAS\Data\Result
    {
        return $this->result;
    }

    public function init(): void
    {
    }

    /**
     * @throws ilDatabaseException
     * @throws ilFileUtilsException
     * @throws ilObjectNotFoundException
     */
    public function importXmlRepresentation(
        string $a_entity,
        string $a_id,
        string $a_xml,
        ?ilImportMapping $a_mapping
    ): void {
        global $DIC;

        $this->initResult();

        $xml_directory = $a_xml;
        $new_object = null;

        $this->publishResult(
            $this->df
                ->ok('Parsing started')
                ->then(
                    function (string $message) use (
                        &$new_object,
                        &$xml_directory,
                        $a_id,
                        $a_mapping
                    ): ?\ILIAS\Data\Result {
                        if ($a_id !== '' &&
                            $a_mapping !== null &&
                            ($new_id = $a_mapping->getMapping('Services/Container', 'objs', $a_id))) {
                            $new_object = ilObjectFactory::getInstanceByObjId((int) $new_id, false);
                            $xml_directory = $this->getImportDirectory();
                        }

                        return $this->df->ok($xml_directory);
                    }
                )
                ->then(function (string $xml_directory): ?\ILIAS\Data\Result {
                    if (!is_dir($xml_directory)) {
                        return $this->df->error(
                            sprintf('Directory lost while importing: %s', $xml_directory)
                        );
                    }

                    return null;
                })
                ->then(function (string $xml_directory): ?\ILIAS\Data\Result {
                    $manifest_file = $xml_directory . '/manifest.xml';
                    if (!file_exists($manifest_file)) {
                        return $this->df->error(
                            sprintf(
                                'No manifest file found in import directory "%s": %s',
                                $xml_directory,
                                $manifest_file
                            )
                        );
                    }

                    return $this->df->ok($manifest_file);
                })
                ->then(function (string $manifest_file): ?\ILIAS\Data\Result {
                    $manifest_file_content = file_get_contents($manifest_file);
                    if (!is_string($manifest_file_content) || $manifest_file_content === '') {
                        return $this->df->error(
                            sprintf(
                                'Could not read content from manifest file: %s',
                                $manifest_file
                            )
                        );
                    }

                    return $this->df->ok($manifest_file_content);
                })
                ->then(function (string $manifest_file_content) use ($xml_directory): ?\ILIAS\Data\Result {
                    $properties_file = $xml_directory . '/properties.xml';
                    $properties_file_content = file_get_contents($properties_file);
                    if (!is_string($properties_file_content) || $properties_file_content === '') {
                        return $this->df->error(
                            sprintf(
                                'Could not read file: %s',
                                $properties_file
                            )
                        );
                    }

                    return $this->df->ok($properties_file_content);
                })
                ->then(function (string $properties_file_content): ?\ILIAS\Data\Result {
                    return (new ilScormImportParser($this->df))->parse($properties_file_content);
                })
                ->then(
                    function (SimpleXMLElement $properties_xml_doc): ?\ILIAS\Data\Result {
                        try {
                            foreach ($this->dataset->properties as $key => $value) {
                                $this->module_properties[$key] = $properties_xml_doc->{$key};
                            }

                            $this->module_properties['Title'] = $properties_xml_doc->Title;
                            $this->module_properties['Description'] = $properties_xml_doc->Description;

                            foreach ($this->module_properties as $key => $property_node) {
                                $property_value = $property_node->__toString();
                                $filteredValue = preg_replace('%\s%', '', $property_value);
                                $this->module_properties[$key] = ilUtil::stripSlashes($filteredValue);
                            }

                            return $this->df->ok($this->module_properties);
                        } catch (Exception $exception) {
                            return $this->df->error($exception);
                        }
                    }
                )->then(function (array $module_properties) use (
                    $xml_directory,
                    $a_id,
                    $a_mapping,
                    $new_object
                ): ?\ILIAS\Data\Result {
                    if ($a_id !== '' &&
                        $a_mapping !== null &&
                        ($new_id = $a_mapping->getMapping(
                            'Services/Container',
                            'objs',
                            $a_id
                        ))) {
                        $this->dataset->writeData(
                            'sahs',
                            '5.1.0',
                            $new_object->getId(),
                            $this->module_properties
                        );

                        $new_object->createReference();

                        $scormFile = 'content.zip';
                        $scormFilePath = $xml_directory . '/' . $scormFile;
                        $targetPath = $new_object->getDataDirectory() . '/' . $scormFile;
                        $file_path = $targetPath;

                        ilFileUtils::rename($scormFilePath, $targetPath);
                        ilFileUtils::unzip($file_path);
                        unlink($file_path);
                        ilFileUtils::renameExecutables($new_object->getDataDirectory());

                        $new_ref_id = $new_object->getRefId();
                        $subType = $module_properties['SubType'];
                        if ($subType === 'scorm') {
                            $new_object = new ilObjSCORMLearningModule($new_ref_id);
                        } else {
                            $new_object = new ilObjSCORM2004LearningModule($new_ref_id);
                        }

                        $title = $new_object->readObject();
                        $new_object->setLearningProgressSettingsAtUpload();
                    }

                    return null;
                })
        );
    }

    public function writeData(string $a_entity, string $a_version, int $a_id): void
    {
        $this->dataset->writeData($a_entity, $a_version, $a_id, $this->module_properties);
    }
}
