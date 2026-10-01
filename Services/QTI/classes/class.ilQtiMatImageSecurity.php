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
 ********************************************************************
 */

declare(strict_types=1);

/**
 * @author        Björn Heyser <bheyser@databay.de>
 * @version        $Id$
 *
 * @package        Modules/Test(QuestionPool)
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
        if (!$this->validateEncoding()) {
            return false;
        }

        if (!$this->validateLabel()) {
            return false;
        }

        if (!$this->validateContent()) {
            return false;
        }

        return true;
    }

    /**
     * Question importers always base64-decode the content before writing it,
     * so only base64-embedded content is validated as it will be stored.
     */
    protected function validateEncoding(): bool
    {
        return $this->isMediaObjectLabel($this->getImageMaterial()->getLabel())
            || $this->getImageMaterial()->getEmbedded() === ilQTIMatimage::EMBEDDED_BASE64;
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
        $label = $this->getImageMaterial()->getLabel();
        if ($this->isMediaObjectLabel($label)) {
            return $this->validateMediaObjectUri();
        }

        $extension = $this->determineFileExtension($label);

        return $extension !== null
            && $this->questionFilesService->isAllowedImageFileExtension($this->getDetectedMimeType(), $extension);
    }

    /**
     * Media-object labels are not stored as question image files. The import
     * copies the referenced file into the web directory, so the uri has to
     * stay inside the import archive and carry an image extension that
     * matches the detected content.
     */
    protected function validateMediaObjectUri(): bool
    {
        $uri = $this->getImageMaterial()->getUri();
        if ($this->isUnsafeMediaObjectUri($uri)) {
            return false;
        }

        $extension = $this->determineFileExtension(basename($uri));

        return $extension !== null
            && $this->questionFilesService->isAllowedImageFileExtension($this->getDetectedMimeType(), $extension);
    }

    protected function isUnsafeMediaObjectUri(string $uri): bool
    {
        if ($uri === '' || str_contains($uri, "\0") || str_contains($uri, '\\') || str_starts_with($uri, '/')) {
            return true;
        }

        return in_array('..', explode('/', $uri), true);
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

    protected function isMediaObjectLabel(string $label): bool
    {
        return (bool) preg_match('/^il_[0-9]+_mob_[0-9]+\z/', $label);
    }
}
