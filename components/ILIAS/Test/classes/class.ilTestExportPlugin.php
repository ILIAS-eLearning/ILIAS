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

use ILIAS\Test\ExportImport\Exporter;
use ILIAS\Test\ExportImport\ExportFilename;

/**
 * Abstract parent class for all test export plugin classes.
 * @author  Michael Jansen <mjansen@databay.de>
 */
abstract class ilTestExportPlugin extends ilPlugin implements Exporter
{
    protected ?ilObjTest $test = null;
    protected int $timestmap = -1;

    protected static $reserved_formats = [
        'xml',
        'csv'
    ];

    public function __construct(
        \ilDBInterface $db,
        \ilComponentRepositoryWrite $component_repository,
        string $id
    ) {
        parent::__construct($db, $component_repository, $id);
    }

    final public function setTest(ilObjTest $test): void
    {
        $this->test = $test;
    }

    final protected function getTest(): ?ilObjTest
    {
        return $this->test;
    }

    public function setTimestmap(int $timestmap): void
    {
        $this->timestmap = $timestmap;
    }

    public function getTimestmap(): int
    {
        return $this->timestmap;
    }

    /**
     * @throws ilException
     */
    final public function getFormat(): string
    {
        $format_id = $this->getFormatIdentifier();

        if (!is_string($format_id)) {
            throw new ilException('The format must be of type string.');
        }

        if (!strlen($format_id)) {
            throw new ilException('The format is empty.');
        }

        if (strtolower($format_id) != $format_id) {
            throw new ilException('Please use a lowercase format.');
        }

        if (in_array($format_id, self::$reserved_formats)) {
            throw new ilException('The format must not be one of: ' . implode(', ', self::$reserved_formats));
        }

        return $format_id;
    }

    /**
     * @throws ilException
     */
    final public function deliver(): void
    {
        /** @var ILIAS\DI\Container $DIC */
        global $DIC;
        $main_tpl = $DIC['tpl'];
        $file_delivery = $DIC['file_delivery'];

        if (($file_name = $this->createExportFile($main_tpl)) === null) {
            return;
        }

        $this->file_delivery->legacyDelivery()->attached(
            $file_name,
            null,
            null,
            true
        );
        $file_delivery->deliver();
    }

    final public function write(): ?string
    {
        return $this->createExportFile();
    }

    private function createExportFile(ilGlobalTemplateInterface $main_tpl): ?string
    {
        if ($this->getTest() === null) {
            throw new ilException('Incomplete object configuration. Please pass an instance of ilObjTest before calling the export!');
        }

        try {
            $export_filename = new ExportFilename($this->getTest()->getId());
            $this->buildExportFile($export_filename);
            return $export_filename->getPathname();
        } catch (ilException $e) {
            if ($this->txt($e->getMessage()) == '-' . $e->getMessage() . '-') {
                $main_tpl->setOnScreenMessage('failure', $e->getMessage(), true);
            } else {
                $main_tpl->setOnScreenMessage('failure', $this->txt($e->getMessage()), true);
            }
            return null;
        }
    }

    /**
     * This method is called if the user wants to export a test of YOUR export type
     * If you throw an exception of type ilException with a respective language variable, ILIAS presents a translated failure message.
     * @throws ilException
     * @param ExportFilename $export_path The path to store the export file
     */
    abstract protected function buildExportFile(ExportFilename $export_path): void;

    /**
     * A unique identifier which describes your export type, e.g. imsm
     * There is currently no mapping implemented concerning the filename.
     * Feel free to create csv, xml, zip files ....
     *
     * @return string
     */
    abstract protected function getFormatIdentifier(): string;

    /**
     * This method should return a human readable label for your export
     * @return string
     */
    abstract public function getFormatLabel(): string;
}
