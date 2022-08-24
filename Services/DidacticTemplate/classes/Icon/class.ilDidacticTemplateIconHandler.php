<?php

declare(strict_types=1);

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\Filesystem\Filesystem;
use ILIAS\Filesystem\Exception\IOException;
use ILIAS\FileUpload\FileUpload;
use ILIAS\Filesystem\Exception\IllegalStateException;
use ILIAS\FileUpload\DTO\UploadResult;
use ILIAS\FileUpload\DTO\ProcessingStatus;
use ILIAS\FileUpload\Location;

/**
 * Icon handler for didactic template custom icons
 * @author  Stefan Meyer <meyer@leifos.com>
 * @ingroup ServicesDidacticTemplate
 */
class ilDidacticTemplateIconHandler
{
    protected const WEBDIR_PREFIX = 'ilDidacticTemplateIcons';

    protected ilDidacticTemplateSetting $settings;
    protected ilLogger $logger;
    protected Filesystem $webDirectory;

    public function __construct(ilDidacticTemplateSetting $setting)
    {
        global $DIC;

        $this->settings = $setting;
        $this->webDirectory = $DIC->filesystem()->web();
        $this->logger = $DIC->logger()->otpl();
    }

    public function handleUpload(FileUpload $upload, string $tmpname): void
    {
        if ($upload->hasUploads() && !$upload->hasBeenProcessed()) {
            try {
                $upload->process();
            } catch (IllegalStateException $e) {
                $this->logger->warning('File upload already processed: ' . $e->getMessage());
            }
        }
        $this->initWebDir();
        $result = $upload->getResults()[$tmpname] ?? false;
        if ($result instanceof UploadResult && $result->isOK() && $result->getSize()) {
            $this->delete();
            $upload->moveOneFileTo(
                $result,
                self::WEBDIR_PREFIX,
                Location::WEB,
                $this->settings->getId() . '.svg'
            );

            $this->settings->setIconIdentifier((string) $this->settings->getId());
            $this->settings->update();
        }
    }

    public function writeSvg(string $svg): void
    {
        try {
            $this->webDirectory->write(
                self::WEBDIR_PREFIX . '/' . $this->settings->getId() . '.svg',
                trim($svg)
            );
            $this->settings->setIconIdentifier((string) $this->settings->getId());
            $this->settings->update();
        } catch (Exception $e) {
            $this->logger->warning('Error writing svg image from xml: ' . $e->getMessage());
        }
    }

    public function getAbsolutePath(): string
    {
        if ($this->webDirectory->has(self::WEBDIR_PREFIX . '/' . $this->settings->getIconIdentifier() . '.svg')) {
            return ilFileUtils::getWebspaceDir() . '/' . self::WEBDIR_PREFIX . '/' . $this->settings->getIconIdentifier() . '.svg';
        }

        return '';
    }

    public function copy(ilDidacticTemplateSetting $original): void
    {
        if ($original->getIconHandler()->getAbsolutePath()) {
            try {
                $this->webDirectory->copy(
                    self::WEBDIR_PREFIX . '/' . $original->getIconIdentifier() . '.svg',
                    self::WEBDIR_PREFIX . '/' . $this->settings->getId() . '.svg'
                );
            } catch (Exception $e) {
                $this->logger->warning('Copying icon failed with message: ' . $e->getMessage());
            }
            $this->settings->setIconIdentifier((string) $this->settings->getId());
        } else {
            $this->settings->setIconIdentifier("0");
        }
        $this->settings->update();
    }

    public function delete(): void
    {
        if ($this->webDirectory->has(self::WEBDIR_PREFIX . '/' . $this->settings->getIconIdentifier() . '.svg')) {
            try {
                $this->webDirectory->delete(self::WEBDIR_PREFIX . '/' . $this->settings->getIconIdentifier() . '.svg');
                $this->settings->setIconIdentifier('');
                $this->settings->update();
            } catch (Exception $e) {
                $this->logger->warning('Deleting icon dfailed with message: ' . $e->getMessage());
            }
        }
    }

    private function initWebDir(): void
    {
        if (!$this->webDirectory->has(self::WEBDIR_PREFIX)) {
            try {
                $this->webDirectory->createDir(self::WEBDIR_PREFIX);
            } catch (IOException $e) {
                $this->logger->error('Creating icon directory failed with message: ' . $e->getMessage());
            } catch (IllegalStateException $e) {
                $this->logger->warning('Creating icon directory failed with message: ' . $e->getMessage());
            }
        }
    }

    public function toXml(ilXmlWriter $writer): ilXmlWriter
    {
        if ($this->settings->getIconIdentifier()) {
            try {
                if ($this->webDirectory->has(self::WEBDIR_PREFIX . '/' . $this->settings->getIconIdentifier() . '.svg')) {
                    $writer->xmlElement('icon', [], $this->webDirectory->read(
                        self::WEBDIR_PREFIX . '/' . $this->settings->getIconIdentifier() . '.svg'
                    ));
                }
            } catch (Exception $e) {
                $this->logger->warning('Export xml failed with message: ' . $e->getMessage());
            }
        }
        return $writer;
    }
}
