<?php

namespace ILIAS\UI\Implementation\Component\Dropzone\File;

use ILIAS\UI\Component\Component;
use ILIAS\UI\Implementation\Component\Signal;
use ILIAS\UI\Implementation\Component\TriggeredSignal;
use ILIAS\UI\Implementation\DefaultRenderer;
use ILIAS\UI\Implementation\Render\AbstractComponentRenderer;
use ILIAS\UI\Implementation\Render\ResourceRegistry;

/**
 * Class Renderer
 *
 * Renderer implementation for file dropzones.
 *
 * @author  nmaerchy <nm@studer-raimann.ch>
 * @author  Stefan Wanzenried <sw@studer-raimann.ch>
 *
 * @package ILIAS\UI\Implementation\Component\Dropzone\File
 */
class Renderer extends AbstractComponentRenderer
{

    /**
     * @var $renderer DefaultRenderer
     */
    private $renderer;


    /**
     * @inheritdoc
     */
    protected function getComponentInterfaceName()
    {
        return array(
            \ILIAS\UI\Component\Dropzone\File\Standard::class,
            \ILIAS\UI\Component\Dropzone\File\Wrapper::class,
        );
    }


    /**
     * @inheritdoc
     */
    public function render(Component $component, \ILIAS\UI\Renderer $default_renderer)
    {
        $this->checkComponent($component);
        $this->renderer = $default_renderer;
        if ($component instanceof \ILIAS\UI\Component\Dropzone\File\Wrapper) {
            return $this->renderWrapper($component);
        }
        if ($component instanceof \ILIAS\UI\Component\Dropzone\File\Standard) {
            return $this->renderStandard($component);
        }
    }


    /**
     * @inheritDoc
     */
    public function registerResources(ResourceRegistry $registry)
    {
        parent::registerResources($registry);
        $registry->register("./libs/bower/bower_components/jquery-dragster/jquery.dragster.js");
        $registry->register("./libs/bower/bower_components/fine-uploader/dist/fine-uploader.core.min.js");
        $registry->register("./src/UI/templates/js/Dropzone/File/uploader.js");
        $registry->register("./src/UI/templates/js/Dropzone/File/dropzone.js");
    }


    /**
     * @param \ILIAS\UI\Component\Dropzone\File\Standard $dropzone
     *
     * @return string
     */
    private function renderStandard(\ILIAS\UI\Component\Dropzone\File\Standard $dropzone)
    {
        /**
         * @var $dropzone \ILIAS\UI\Component\Dropzone\File\Standard
         */
        $dropzone = $this->registerSignals($dropzone);
        $dropzoneId = $this->bindJavaScript($dropzone);
        $f = $this->getUIFactory();
        $r = $this->renderer;

        $tpl = $this->getTemplate("tpl.standard-dropzone.html", true, true);
        $tpl->setVariable("ID", $dropzoneId);
        // Set default message if empty
        $message = ($dropzone->getMessage()) ? $dropzone->getMessage() : $this->txt('drag_files_here');
        $tpl->setVariable("MESSAGE", $message);
        $button = $dropzone->getUploadButton();

        // Select-Button
        $select_button = $f->link()->standard($this->txt('select_files_from_computer'), '#');
        $tpl->setVariable('SHY_BUTTON', $r->render($select_button));

        // Upload-Button
        if ($button) {
            $button = $button->withUnavailableAction()->withAdditionalOnLoadCode(function ($id) use ($dropzoneId) {
                return "$ (function() {il.UI.uploader.bindUploadButton('{$dropzoneId}', $('#{$id}'));});";
            });
            $tpl->setCurrentBlock('with_upload_button');
            $tpl->setVariable('BUTTON', $r->render($button));
            $tpl->parseCurrentBlock();
        }
        $tplUploadFileList = $this->getFileListTemplate($dropzone);
        $tpl->setVariable('FILELIST', $tplUploadFileList->get());

        return $tpl->get();
    }


    /**
     * @param \ILIAS\UI\Component\Dropzone\File\Wrapper $dropzone
     *
     * @return string
     */
    private function renderWrapper(\ILIAS\UI\Component\Dropzone\File\Wrapper $dropzone)
    {
        // Create the roundtrip modal which displays the uploaded files
        $tplUploadFileList = $this->getFileListTemplate($dropzone);
        $uploadButton = $this->getUIFactory()->button()->primary($this->txt('upload'), '')->withUnavailableAction();
        $title = $dropzone->getTitle();
        if (!$title) {
            $title = $this->txt('upload');
        }
        $modal = $this->getUIFactory()->modal()->roundtrip($title, $this->getUIFactory()->legacy($tplUploadFileList->get()))->withActionButtons([ $uploadButton ]);

        // Register JS
        $dropzone = $dropzone->withAdditionalDrop($modal->getShowSignal());
        $dropzone = $this->registerSignals($dropzone);
        $dropzoneId = $this->bindJavaScript($dropzone);

        // Render the Wrapper-Dropzone
        $tpl = $this->getTemplate("tpl.wrapper-dropzone.html", true, true);
        $tpl->setVariable('ID', $dropzoneId);
        $tpl->setVariable('CONTENT', $this->renderer->render($dropzone->getContent()));
        $tpl->setVariable('MODAL', $this->renderer->render($modal));

        return $tpl->get();
    }


    /**
     * @param \ILIAS\UI\Component\Dropzone\File\File $dropzone
     *
     * @return \ILIAS\UI\Component\JavaScriptBindable
     */
    protected function registerSignals(\ILIAS\UI\Component\Dropzone\File\File $dropzone)
    {
        $signals = array_map(function ($triggeredSignal) {
            /** @var $triggeredSignal TriggeredSignal */
            return array(
                'id'      => $triggeredSignal->getSignal()->getId(),
                'options' => $triggeredSignal->getSignal()->getOptions(),
            );
        }, $dropzone->getTriggeredSignals());

        return $dropzone->withAdditionalOnLoadCode(function ($id) use ($dropzone, $signals) {
            $options = json_encode(
                [
                    'id'                => $id,
                    'registeredSignals' => $signals,
                    'uploadUrl'         => $dropzone->getUploadUrl(),
                    'allowedFileTypes'  => $dropzone->getAllowedFileTypes(),
                    'fileSizeLimit'     => $dropzone->getFileSizeLimit() ? $dropzone->getFileSizeLimit()->getSize()
                        * $dropzone->getFileSizeLimit()->getUnit() : 0,
                    'maxFiles'          => $dropzone->getMaxFiles(),
                    'identifier'        => $dropzone->getParametername(),
                    'typeError'         => $this->txt('msg_wrong_filetypes') . " " . implode(", ", $dropzone->getAllowedFileTypes()),
                ]
            );
            $reflect = new \ReflectionClass($dropzone);
            $type = $reflect->getShortName();

            return "il.UI.dropzone.initializeDropzone('{$type}', JSON.parse('{$options}'));";
        });
    }


    /**
     * @param string                                 $uploadId
     * @param \ILIAS\UI\Component\Dropzone\File\File $dropzone
     *
     * @return \ILIAS\UI\Implementation\Render\Template
     */
    private function getFileListTemplate(\ILIAS\UI\Component\Dropzone\File\File $dropzone)
    {
        $f = $this->getUIFactory();
        $r = $this->renderer;
        $tplUploadFileList = $this->getTemplate('tpl.upload-file-list.html', true, true);

        // Actions
        $items = array(
            $f->button()->shy($this->txt("remove"), "")->withAriaLabel("delete_file"),
        );

        $tplUploadFileList->setVariable("REMOVE", $r->render([$f->button()->close()]));

        if ($this->renderMetaData($dropzone)) {
            $tplUploadFileList->setVariable("TOGGLE", $r->render([$f->glyph()->collapse(), $f->glyph()->expand()]));
            $tplUploadFileList->setCurrentBlock("with_metadata");
            $items[] = $f->button()->shy($this->txt("edit_metadata"), "")->withAriaLabel("edit_metadata");
            if ($dropzone->allowsUserDefinedFileNames()) {
                $tplUploadFileList->setVariable("LABEL_FILENAME", $this->txt("filename"));
            }
            if ($dropzone->allowsUserDefinedFileDescriptions()) {
                $tplUploadFileList->setVariable("LABEL_DESCRIPTION", $this->txt("description"));
            }
            $tplUploadFileList->parseCurrentBlock();
        }
        $action = $f->dropdown()->standard($items);
        $tplUploadFileList->setVariable("DROPDOWN", $r->render($action));

        return $tplUploadFileList;
    }


    /**
     * @param \ILIAS\UI\Component\Dropzone\File\File $dropzone
     *
     * @return bool
     */
    private function renderMetaData(\ILIAS\UI\Component\Dropzone\File\File $dropzone)
    {
        return ($dropzone->allowsUserDefinedFileNames()
                || $dropzone->allowsUserDefinedFileDescriptions());
    }
}
