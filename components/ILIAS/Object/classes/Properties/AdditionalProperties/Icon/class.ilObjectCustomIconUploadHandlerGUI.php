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
class ilObjectCustomIconUploadHandlerGUI extends AbstractCtrlAwareUploadHandler implements ilCtrlBaseClassInterface
{
    use ilObjectPropertiesUploadSecurityFunctionsTrait;

    protected ilLanguage $language;
    protected bool $has_access = false;

    public function __construct(
        protected ?ilObjectCustomIcon $custom_icon = null
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
                $file_name,
                $this->language
            );
        }

        $this->upload->process();

        $result_array = $this->upload->getResults();
        $result = end($result_array);

        if ($result instanceof UploadResult
            && in_array($result->getMimeType(), ilObjectPropertyIcon::SUPPORTED_MIME_TYPES)
            && $result->isOK()) {
            $status = HandlerResult::STATUS_OK;
            $message = 'Upload ok';
            $tempfile = ilFileUtils::ilTempnam();
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

        $file_path = $this->custom_icon->getFullPath();

        return new BasicFileInfoResult(
            $this->getFileIdentifierParameterName(),
            'custom_icon',
            $this->language->txt('custom_icon'),
            filesize($file_path),
            MimeType::IMAGE__SVG_XML
        );
    }

    /**
     * @return \ILIAS\FileUpload\Handler\BasicFileInfoResult[]
     */
    public function getInfoForExistingFiles(array $file_names): array
    {
        return [$this->getInfoResult('custom_icon')];
    }
}
