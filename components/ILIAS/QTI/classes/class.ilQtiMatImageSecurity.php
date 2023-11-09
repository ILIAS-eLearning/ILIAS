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
 ********************************************************************
 */

/**
 * @author        Björn Heyser <bheyser@databay.de>
 * @version        $Id$
 *
 * @package components\ILIAS/Test(QuestionPool)
 */
class ilQtiMatImageSecurity
{
    private \ILIAS\TestQuestionPool\QuestionFilesService $questionFilesService;
    protected ilQTIMatimage $imageMaterial;
    protected string $detectedMimeType = "";

    public function __construct(ilQTIMatimage $imageMaterial, \ILIAS\TestQuestionPool\QuestionFilesService $questionFilesService)
    {
        $this->questionFilesService = $questionFilesService;

        $this->setImageMaterial($imageMaterial);

        if (!strlen($this->getImageMaterial()->getRawContent())) {
            throw new ilQtiException('cannot import image without content');
        }

        $this->setDetectedMimeType(
            $this->determineMimeType($this->getImageMaterial()->getRawContent())
        );
    }

    public function getImageMaterial(): ilQTIMatimage
    {
        return $this->imageMaterial;
    }

    public function setImageMaterial(ilQTIMatimage $imageMaterial): void
    {
        $this->imageMaterial = $imageMaterial;
    }

    protected function getDetectedMimeType(): string
    {
        return $this->detectedMimeType;
    }

    protected function setDetectedMimeType(string $detectedMimeType): void
    {
        $this->detectedMimeType = $detectedMimeType;
    }

    public function validate(): bool
    {
        if (!$this->validateLabel()) {
            return false;
        }

        if (!$this->validateContent()) {
            return false;
        }

        return true;
    }

    protected function validateContent(): bool
    {
        if ($this->getImageMaterial()->getImagetype() && !$this->questionFilesService->isAllowedImageMimeType($this->getImageMaterial()->getImagetype())) {
            return false;
        }

        if (!$this->questionFilesService->isAllowedImageMimeType($this->getDetectedMimeType())) {
            return false;
        }

        if ($this->getImageMaterial()->getImagetype()) {
            $declaredMimeType = current(explode(';', $this->getImageMaterial()->getImagetype()));
            $detectedMimeType = current(explode(';', $this->getDetectedMimeType()));

            if ($declaredMimeType != $detectedMimeType) {
                // since ilias exports jpeg declared pngs itself, we skip this validation ^^
                // return false;

                /* @var ilComponentLogger $log */
                $log = $GLOBALS['DIC'] ? $GLOBALS['DIC']['ilLog'] : $GLOBALS['ilLog'];
                $log->log(
                    'QPL: imported image with declared mime (' . $declaredMimeType . ') '
                    . 'and detected mime (' . $detectedMimeType . ')'
                );
            }
        }

        return true;
    }

    protected function validateLabel(): bool
    {
        if ($this->getImageMaterial()->getUri()) {
            if (!$this->hasFileExtension($this->getImageMaterial()->getUri())) {
                return true;
            }

            $extension = $this->determineFileExtension($this->getImageMaterial()->getUri());
        } else {
            $extension = $this->determineFileExtension($this->getImageMaterial()->getLabel());
        }

        return $this->questionFilesService->isAllowedImageFileExtension($this->getDetectedMimeType(), $extension);
    }

    public function sanitizeLabel(): void
    {
        $label = $this->getImageMaterial()->getLabel();

        $label = basename($label);
        $label = ilUtil::stripSlashes($label);
        $label = ilFileUtils::getASCIIFilename($label);

        $this->getImageMaterial()->setLabel($label);
    }

    protected function determineMimeType(?string $content): string
    {
        $finfo = new finfo(FILEINFO_MIME);

        return $finfo->buffer($content);
    }

    protected function determineFileExtension(string $label): ?string
    {
        $pathInfo = pathinfo($label);

        if (isset($pathInfo['extension'])) {
            return $pathInfo['extension'];
        }

        return null;
    }

    protected function hasFileExtension(string $label): bool
    {
        $pathInfo = pathinfo($label);

        return array_key_exists('extension', $pathInfo);
    }
}
