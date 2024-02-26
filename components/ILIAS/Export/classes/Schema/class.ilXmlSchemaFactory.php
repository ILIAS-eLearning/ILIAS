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

namespace ILIAS\Export\Schema;

use SplFileObject;
use SplFileInfo;
use ILIAS\Data\Version;

class ilXmlSchemaFactory
{
    private const SCHEMA_DEFINITION_LOCATION = '../components/ILIAS/Export/xml/SchemaValidation';

    private ilXmlSchemaInfoCollection $collection;

    private \ilLogger $logger;

    public function __construct()
    {
        global $DIC;

        $this->logger = $DIC->logger()->exp();
        $this->collection = new ilXmlSchemaInfoCollection();
        $this->readSchemaFiles();
    }

    public function getLatest(string $type, string $sub_type = ''): ?SplFileInfo
    {
        $collection = $this->getByType($type, $sub_type);
        if ($collection->count() === 0) {
            return null;
        }
        $latest = $collection->offsetGet($collection->count() - 1);
        return $latest->getFile();
    }

    public function getByVersion(Version $version, string $type, string $sub_type = ''): ?SplFileInfo
    {
        $collection = $this->getByType($type, $sub_type);
        foreach ($collection as $schema_info) {
            if ($schema_info->getVersion()->equals($version)) {
                return $schema_info->getFile();
            }
        }
        return null;
    }

    public function getByVersionOrLatest(Version $version, string $type, string $sub_type = ''): ?SplFileInfo
    {
        $collection = $this->getByType($type, $sub_type);
        foreach ($collection as $schema_info) {
            if ($schema_info->getVersion()->equals($version)) {
                return $schema_info->getFile();
            }
        }
        return $this->getLatest($type, $sub_type);
    }

    protected function getByType(string $component, string $sub_type = ''): ilXmlSchemaInfoCollection
    {
        $collection = new ilXmlSchemaInfoCollection();
        foreach ($this->collection as $schema_info) {
            if ($schema_info->getComponent() === $component && $schema_info->getSubtype() === $sub_type) {
                $collection[] = $schema_info;
                $this->logger->info('Found version: ' . $schema_info->getFile()->getFilename());
            }
        }
        return $this->sortByVersion($collection);
    }

    protected function sortByVersion(ilXmlSchemaInfoCollection $collection): ilXmlSchemaInfoCollection
    {
        $collection->uasort(function (ilXmlSchemaInfo $a, ilXmlSchemaInfo $b): int {
            if ($a->getVersion()->equals($b->getVersion())) {
                return 0;
            }
            if ($a->getVersion()->isGreaterThan($b->getVersion())) {
                return 1;
            }
            return -1;
        });
        $sorted = new ilXmlSchemaInfoCollection();
        foreach ($collection as $schema_info) {
            $sorted[] = $schema_info;
        }
        return $sorted;
    }

    private function readSchemaFiles(): void
    {
        foreach (new \DirectoryIterator(self::SCHEMA_DEFINITION_LOCATION) as $file) {
            if ($file->isDot()) {
                $this->logger->debug('Ignoring file (dot file): ' . $file->getFilename());
                continue;
            }
            if ($file->getExtension() !== 'xsd') {
                $this->logger->debug('Ignoring file (!xsd): ' . $file->getFilename());
                continue;
            }
            $parts = explode('_', $file->getFilename());
            if (!count($parts)) {
                $this->logger->debug('Ignoring file (!_separated): ' . $file->getFilename());
                continue;
            }
            if ($parts[0] !== 'ilias') {
                $this->logger->debug('Ignoring file (!ilias): ' . $file->getFilename() . ' ' . $parts[0]);
                continue;
            }
            $matches = [];
            if (preg_match('/ilias_([a-zA-Z]+)(_([a-zA-Z]+))?_([3-9]|([1-9][0-9]+))_?([0-9]+)?.xsd/', $file->getFilename(), $matches) !== 1) {
                $this->logger->debug('Ignoring file (match): ' . $file->getFilename());
                $this->logger->dump($matches, \ilLogLevel::DEBUG);
                continue;
            }
            $this->collection[] = new ilXmlSchemaInfo(
                new SplFileInfo($file->getPathname()),
                (string) $matches[1],
                (string) $matches[3],
                new Version((string) $matches[4] . (($matches[6] ?? '') ? '.' . $matches[6] : ''))
            );
            $this->logger->debug($file->getFilename() . ' matches');
        }
    }
}
