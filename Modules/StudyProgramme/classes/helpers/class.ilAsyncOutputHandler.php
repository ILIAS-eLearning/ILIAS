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
 *********************************************************************/

/**
 * Class ilAsyncOutputHandler
 * Handles the output for async-requests. The class allows to generate the basic structure of a bootstrap-modal (for modal-content)
 *
 * @author Michael Herren <mh@studer-raimann.ch>
 * @version 1.0.0
 */
class ilAsyncOutputHandler
{
    private const OUTPUT_MODAL = "output_modal";
    private const OUTPUT_EMPTY = "output_empty";

    /**
     * @var mixed|null
     */
    protected $content;

    /**
     * @var mixed|null
     */
    protected $heading;

    protected array $window_properties;

    public function __construct($content = null, $heading = null, array $windows_properties = array())
    {
        $this->content = $content;
        $this->heading = $heading;
        $this->window_properties = $windows_properties;
    }

    /**
     * Output content in different ways
     * self::OUTPUT_MODAL: Output as bootstrap modal
     * self::OUTPUT_EMPTY: Only content without ILIAS-layout
     */
    public function terminate(string $type = self::OUTPUT_MODAL): void
    {
        if ($type === self::OUTPUT_MODAL) {
            $tpl = new ilTemplate('tpl.modal_content.html', false, false, 'Modules/StudyProgramme');
            $tpl->setVariable('HEADING', $this->getHeading());
            $tpl->setVariable('BODY', $this->getContent());

            //TODO: implement window properties
            /*foreach($this->window_properties as $key => $value) {
                if($value) {
                    $tpl->activeBlock($key);
                } else {
                    $tpl->removeBlockData($key);
                }
            }*/

            echo $tpl->get();
            exit();
        }

        if ($type === self::OUTPUT_EMPTY) {
            echo $this->getContent();// TODO PHP8-REVIEW I sugges to use the HTTP service instead of echo/exit
            exit();
        }
    }

    /**
     * Encode data as json for async output
     */
    public static function encodeAsyncResponse(array $data = array()): string
    {
        global $DIC;
        $ilCtrl = $DIC['ilCtrl'];

        $data['cmd'] = $ilCtrl->getCmd();

        return json_encode($data, JSON_THROW_ON_ERROR);
    }

    /**
     * Handles async output
     *
     * @return string|void
     */
    public static function handleAsyncOutput(
        string $normal_content,
        string $async_content = null,
        bool $apply_to_tpl = true
    ) {
        global $DIC;
        $ilCtrl = $DIC['ilCtrl'];
        $tpl = $DIC['tpl'];
        $http = $DIC['http'];

        $content = ($ilCtrl->isAsynch() && $async_content !== null) ? $async_content : $normal_content;

        if ($ilCtrl->isAsynch()) {
            $http->saveResponse(
                $http->response()
                    ->withHeader(ResponseHeader::CONTENT_TYPE, 'text/html')
                    ->withBody(Streams::ofString($content))
            );
            $http->sendResponse();
            $http->close();
            exit();
        } elseif ($apply_to_tpl) {
            $tpl->setContent($content);
        } else {
            return $content;
        }
    }

    /**
     * Returns the content of the modal output
     *
     * @return mixed
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Sets the content of the modal output
     *
     * @param mixed $content
     */
    public function setContent($content): void
    {
        $this->content = $content;
    }

    /**
     * Return the heading of a modal
     *
     * @return mixed
     */
    public function getHeading()
    {
        return $this->heading;
    }

    /**
     * Sets the heading of a modal-output
     *
     * @param mixed $heading
     */
    public function setHeading($heading): void
    {
        $this->heading = $heading;
    }

    /**
     * Return all window properties
     */
    public function getWindowProperties(): array
    {
        return $this->window_properties;
    }

    /**
     * Set windows properties
     */
    public function setWindowProperties(array $window_properties): void
    {
        $this->window_properties = $window_properties;
    }
}
