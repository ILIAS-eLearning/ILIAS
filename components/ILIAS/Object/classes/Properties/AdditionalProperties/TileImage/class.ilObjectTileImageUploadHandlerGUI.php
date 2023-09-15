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

use ILIAS\FileUpload\Handler\AbstractCtrlAwareUploadHandler;
use ILIAS\FileUpload\Handler\BasicFileInfoResult;
use ILIAS\FileUpload\Handler\BasicHandlerResult;
use ILIAS\FileUpload\Handler\FileInfoResult;
use ILIAS\FileUpload\Handler\HandlerResult;
use ILIAS\FileUpload\DTO\UploadResult;
use ILIAS\FileUpload\MimeType;

/**
 *
 * @author Stephan Kergomard <webmaster@kergomard.ch>
 */
class ilObjectTileImageUploadHandlerGUI extends AbstractCtrlAwareUploadHandler implements ilCtrlBaseClassInterface
{
    use ilObjectPropertiesUploadSecurityFunctionsTrait;

    protected ilLanguage $language;
    protected bool $has_access = false;

    public function __construct(
        protected ?ilObjectTileImage $tile_image = null
    ) {
        /** @var ILIAS\DI\Container $DIC */
        global $DIC;
        $this->language = $DIC->language();

        $ref_id = null;
        if ($DIC->http()->wrapper()->query()->has('ref_id')) {
            $transformation = $DIC->refinery()->kindlyTo()->int();
            $ref_id = $DIC->http()->wrapper()->query()->retrieve('ref_id', $transformation);
        }

        $this->has_access = $this->getAccess(
            $ref_id,
            $DIC->access()
        );

        $DIC->ctrl()->setParameterByClass(self::class, 'ref_id', $ref_id);

        parent::__construct();
    }

    /**
     * @inheritDoc
     */
    protected function getUploadResult(): HandlerResult
    {
        $tempfile = '';
        if ($this->has_access === false) {
            return $this->getAccessFailureResult(
                $this->getFileIdentifierParameterName(),
                $tempfile,
                $this->language
            );
        }

        $this->upload->process();

        $result_array = $this->upload->getResults();
        $result = end($result_array);

        if ($result instanceof UploadResult
            && in_array($result->getMimeType(), ilObjectPropertyTileImage::SUPPORTED_MIME_TYPES)
            && $result->isOK()) {
            $status = HandlerResult::STATUS_OK;
            $message = 'Upload ok';
            $tempfile = $this->getTempFileWithExtension($result->getName());
            rename($result->getPath(), $tempfile);
        } else {
            $status = HandlerResult::STATUS_FAILED;
            $message = $result->getStatus()->getMessage();
        }

        return new BasicHandlerResult(
            $this->getFileIdentifierParameterName(),
            $status,
            basename($tempfile),
            $message
        );
    }

    protected function getRemoveResult(string $file_name): HandlerResult
    {
        if ($this->has_access === false) {
            return $this->getAccessFailureResult(
                $this->getFileIdentifierParameterName(),
                $file_name,
                $this->language
            );
        }

        return new BasicHandlerResult(
            $this->getFileIdentifierParameterName(),
            HandlerResult::STATUS_OK,
            $file_name,
            'There is nothing to do here.'
        );
    }

    public function getInfoResult(string $file_name): ?FileInfoResult
    {
        if ($this->has_access === false) {
            return null;
        }
        $file_path = $this->tile_image->getFullPath();

        $extension = '.' . strtolower($this->tile_image->getExtension());
        $mimetype_map = MimeType::getExt2MimeMap();

        return new BasicFileInfoResult(
            $this->getFileIdentifierParameterName(),
            'tile_image',
            $this->language->txt('obj_tile_image'),
            filesize($file_path),
            $mimetype_map[$extension]
        );
    }

    /**
     * @return \ILIAS\FileUpload\Handler\BasicFileInfoResult[]
     */
    public function getInfoForExistingFiles(array $file_names): array
    {
        return [$this->getInfoResult('tile_image')];
    }

    protected function getTempFileWithExtension($upload_file_name): string
    {
        $file_name_parts = explode('.', $upload_file_name);
        $extension = '.' . array_pop($file_name_parts);
        return ilFileUtils::ilTempnam() . strtolower($extension);
    }
}
