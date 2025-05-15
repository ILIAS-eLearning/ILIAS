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

use ILIAS\Object\Properties\CoreProperties\TileImage\ilObjectTileImage;
use ILIAS\Object\Properties\CoreProperties\TileImage\ilObjectTileImageStakeholder;
use ILIAS\Object\Properties\CoreProperties\TileImage\ilObjectTileImageFlavourDefinition;
use ILIAS\ResourceStorage\Services as ResourceStorageServices;
use ILIAS\ResourceStorage\Identification\ResourceIdentification;
use ILIAS\FileUpload\Handler\AbstractCtrlAwareUploadHandler;
use ILIAS\FileUpload\Handler\BasicFileInfoResult;
use ILIAS\FileUpload\Handler\BasicHandlerResult;
use ILIAS\FileUpload\Handler\FileInfoResult;
use ILIAS\FileUpload\Handler\HandlerResult;
use ILIAS\FileUpload\DTO\UploadResult;

/**
 *
 * @author Stephan Kergomard <webmaster@kergomard.ch>
 */
class ilObjectTileImageUploadHandlerGUI extends AbstractCtrlAwareUploadHandler implements \ilCtrlBaseClassInterface
{
    use \ilObjectPropertiesUploadSecurityFunctionsTrait;

    protected \ilLanguage $language;
    protected ResourceStorageServices $storage;
    protected ilObjectTileImageStakeholder $stakeholder;
    protected ilObjectTileImageFlavourDefinition $flavour;

    protected ?ResourceIdentification $rid = null;
    protected bool $has_access = false;

    public function __construct(
        protected ?ilObjectTileImage $tile_image = null
    ) {
        /** @var \ILIAS\DI\Container $DIC */
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

        if ($DIC->http()->wrapper()->post()->has('rid')) {
            $id = $DIC->http()->wrapper()->post()->retrieve(
                'rid',
                $DIC->refinery()->to()->string()
            );
            $this->rid = $DIC->resourceStorage()->manage()->find($id);
        }

        $DIC->ctrl()->setParameterByClass(self::class, 'ref_id', $ref_id);

        $this->storage = $DIC->resourceStorage();
        $this->stakeholder = new ilObjectTileImageStakeholder();
        $this->flavour = new ilObjectTileImageFlavourDefinition();

        parent::__construct();
    }

    /**
     * @inheritDoc
     */
    protected function getUploadResult(): HandlerResult
    {
        $this->upload->process();

        $result_array = $this->upload->getResults();
        $result = end($result_array);

        if (!($result instanceof UploadResult) || !$result->isOK()) {
            return new BasicHandlerResult(
                $this->getFileIdentifierParameterName(),
                HandlerResult::STATUS_FAILED,
                '',
                $result->getStatus()->getMessage()
            );
        }

        $status = HandlerResult::STATUS_OK;
        $message = "file upload OK";
        if ($this->rid === null) {
            $i = $this->storage->manage()->upload($result, $this->stakeholder);
        } else {
            $i = $this->rid;
            $this->storage->manage()->replaceWithUpload(
                $i,
                $result,
                $this->stakeholder
            );
        }

        $this->storage->flavours()->ensure($i, $this->flavour);

        return new BasicHandlerResult(
            $this->getFileIdentifierParameterName(),
            $status,
            $i->serialize(),
            $message
        );
    }

    public function getInfoResult(string $identifier): ?FileInfoResult
    {
        if (null !== ($id = $this->storage->manage()->find($identifier))) {
            $revision = $this->storage->manage()->getCurrentRevision($id)->getInformation();
            $title = $revision->getTitle();
            $size = $revision->getSize();
            $mime = $revision->getMimeType();
        } else {
            $title = $mime = 'unknown';
            $size = 0;
        }

        return new BasicFileInfoResult(
            $this->getFileIdentifierParameterName(),
            $identifier,
            $title,
            $size,
            $mime
        );
    }

    /**
     * @return \ILIAS\FileUpload\Handler\BasicFileInfoResult[]
     */
    public function getInfoForExistingFiles(array $file_ids): array
    {
        $info_results = [];
        foreach ($file_ids as $identifier) {
            $info_results[] = $this->getInfoResult($identifier);
        }

        return $info_results;
    }
}
