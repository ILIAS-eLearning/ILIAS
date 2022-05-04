<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 */

use ILIAS\FileUpload\Handler\AbstractCtrlAwareUploadHandler;
use ILIAS\FileUpload\Handler\FileInfoResult;
use ILIAS\FileUpload\Handler\HandlerResult;
use ILIAS\UI\Component\Dropzone\File\Wrapper;
use ILIAS\FileUpload\Location;
use ILIAS\FileUpload\Handler\BasicFileInfoResult;
use ILIAS\FileUpload\Handler\BasicHandlerResult;
use ILIAS\UI\Component\Input\Field\Group;

/**
 * @author            Fabian Schmid <fabian@sr.solutions>
 *
 * @ilCtrl_IsCalledBy ilMediaPoolBulkUploadGUI: ilObjMediaPoolGUI
 */
class ilMediaPoolBulkUploadGUI extends AbstractCtrlAwareUploadHandler
{
    const MAX_FILE_AMOUNT = 20;
    const MEP_ID = 'mep_id';
    private \ILIAS\UI\Factory $ui_factory;
    private ilObjMediaPool $media_pool;
    private ilLanguage $lng;
    
    private string $post_url;
    private int $mep_item_id;
    private int $max_upload_size = 2048;
    private string $upload_hash;
    
    public function __construct(
        ilObjMediaPool $media_ppol,
        int $mep_item_id,
        string $post_url,
        string $upload_hash
    ) {
        global $DIC;
        parent::__construct();
        $this->post_url = $post_url;
        $this->upload_hash = $upload_hash;
        $this->mep_item_id = $mep_item_id;
        $this->media_pool = $media_ppol;
        $this->ui_factory = $DIC->ui()->factory();
        $this->lng = $DIC->language();
        $this->max_upload_size = (int) ilFileUtils::getUploadSizeLimitBytes();
    }
    
    protected function getMetadataInputs() : Group
    {
        return $this->ui_factory->input()->field()->group([
            $this->ui_factory->input()->field()->text(
                $this->lng->txt('title')
            ),
            $this->ui_factory->input()->field()->textarea(
                $this->lng->txt('description')
            )
        ]);
    }
    
    public function getAsForm() : ILIAS\UI\Component\Input\Container\Form\Standard
    {
        $metadata_input = null; // $this->getMetadataInputs(); the additional fields could be used directly in the upload form
        
        $inputs = [
            $this->ui_factory->input()->field()->group(
                [
                    $this->ui_factory->input()->field()->file(
                        $this,
                        $this->lng->txt('mep_media_files'),
                        null,
                        $metadata_input
                    )->withMaxFiles(self::MAX_FILE_AMOUNT)
                                     ->withMaxFileSize($this->max_upload_size)
                ],
                $this->lng->txt('mep_bulk_upload')
            )
        
        ];
        return $this->ui_factory->input()->container()->form()->standard($this->post_url, $inputs);
    }
    
    // TODO check if you want this to keep: it would be possible to have a nice (TBD) dropzone to make the uploads
    public function getAsDropZone() : Wrapper
    {
        $metadata_input = null; // $this->getMetadataInputs(); the additional fields could be used directly in the upload form
        
        return $this->ui_factory->dropzone()
                                ->file()
                                ->wrapper(
                                    $this,
                                    $this->post_url,
                                    [
                                        $this->ui_factory->legacy(
                                            "<div style='border: solid 1px red; padding: 30px'>DROP YOUR FILES HERE</div>"
                                        )
                                    ],
                                    $metadata_input
                                )
                                ->withTitle($this->lng->txt('mep_bulk_upload'))
                                ->withMaxFileSize($this->max_upload_size)
                                ->withMaxFiles(self::MAX_FILE_AMOUNT);
    }
    
    protected function getUploadResult() : HandlerResult
    {
        $log = ilLoggerFactory::getLogger("mep"); // TODO check if you want this logging
        $log->debug("checking for uploads...");
        if ($this->upload->hasUploads()) {
            $log->debug("has upload...");
            try {
                $this->upload->process();
                $log->debug("nr of results: " . count($this->upload->getResults()));
                foreach ($this->upload->getResults(
                ) as $result) { // in this version, there will only be one upload at the time
                    $title = $result->getName();
                    
                    $mob = new ilObjMediaObject();
                    $mob->setTitle($title);
                    $mob->setDescription("");
                    $mob->create();
                    
                    $mob->createDirectory();
                    $media_item = new ilMediaItem();
                    $mob->addMediaItem($media_item);
                    $media_item->setPurpose("Standard");
                    
                    $mob_dir = ilObjMediaObject::_getRelativeDirectory($mob->getId());
                    $file_name = ilObjMediaObject::fixFilename($title);
                    $file = $mob_dir . "/" . $file_name;
                    
                    $this->upload->moveOneFileTo(
                        $result,
                        $mob_dir,
                        Location::WEB,
                        $file_name,
                        true
                    );
                    
                    $mep_item = new ilMediaPoolItem();
                    $mep_item->setTitle($title);
                    $mep_item->setType("mob");
                    $mep_item->setForeignId($mob->getId());
                    $mep_item->create();
                    
                    $tree = $this->media_pool->getTree();
                    $parent = $this->mep_item_id;
                    $tree->insertNode($mep_item->getId(), $parent);
                    
                    // get mime type
                    $format = ilObjMediaObject::getMimeType($file);
                    $location = $file_name;
                    
                    // set real meta and object data
                    $media_item->setFormat($format);
                    $media_item->setLocation($location);
                    $media_item->setLocationType("LocalFile");
                    $media_item->setUploadHash($this->upload_hash); // TODO CHECK
                    $mob->update();
                    
                    $item_ids[] = $mob->getId();
                    
                    $mob = new ilObjMediaObject($mob->getId());
                    $mob->generatePreviewPic(320, 240);
                    
                    // duration
                    $med_item = $mob->getMediaItem("Standard");
                    $med_item->determineDuration();
                    $med_item->update();
                    
                    $result = new BasicHandlerResult(
                        $this->getFileIdentifierParameterName(),
                        BasicHandlerResult::STATUS_OK,
                        $med_item->getId(),
                        ''
                    );
                }
            } catch (Exception $e) {
                $result = new BasicHandlerResult(
                    $this->getFileIdentifierParameterName(),
                    BasicHandlerResult::STATUS_FAILED,
                    '',
                    $e->getMessage()
                );
            }
            $log->debug("end of 'has_uploads'");
        } else {
            $log->debug("has no upload...");
        }
        
        return $result;
    }
    
    protected function getRemoveResult(string $identifier) : HandlerResult
    {
        return new BasicHandlerResult(
            $this->getFileIdentifierParameterName(),
            HandlerResult::STATUS_OK,
            $identifier,
            ''
        );
    }
    
    public function getInfoResult(string $identifier) : ?FileInfoResult
    {
        return null;
    }
    
    public function getInfoForExistingFiles(array $file_ids) : array
    {
        return [];
    }
    
    public function getFileIdentifierParameterName() : string
    {
        return self::MEP_ID;
    }
    
}
