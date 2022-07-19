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

namespace ILIAS\TestQuestionPool;

use ILIAS\Repository\BaseGUIRequest;

class InternalRequestService
{
    use BaseGUIRequest;

    protected \ILIAS\HTTP\Services $http;
    protected \ILIAS\FileUpload\FileUpload $upload;

    public function __construct(
        \ILIAS\HTTP\Services $http,
        \ILIAS\Refinery\Factory $refinery,
        \ILIAS\FileUpload\FileUpload $upload
    ) {
        $this->initRequest(
            $http,
            $refinery
        );
        $this->upload = $upload;
    }

    /**
     * @return \ILIAS\FileUpload\DTO\UploadResult[]
     */
    public function getProcessedUploads() : array
    {
        $uploads = [];
        if ($this->upload->hasUploads()) {
            if (!$this->upload->hasBeenProcessed()) {
                $this->upload->process();
            }
            $uploads = $this->upload->getResults();
        }

        return $uploads;
    }

    /**
     * @param string[] $http_names An array of keys used as structure for the HTTP name (e.g. ['terms', 'image'] for $_FILES['terms']['image'])
     * @param int $index
     * @return string|null
     */
    public function getUploadFilename(array $http_names, int $index) : ?string
    {
        $uploaded_files = $this->http->request()->getUploadedFiles();

        while (($current_key = array_shift($http_names)) !== null) {
            if (!isset($uploaded_files[$current_key])) {
                return null;
            }

            $uploaded_files = $uploaded_files[$current_key];

            if (isset($uploaded_files[$index]) && $http_names === []) {
                /** @var \GuzzleHttp\Psr7\UploadedFile $file */
                $file = $uploaded_files[$index];
                $c = \Closure::bind(static function (\GuzzleHttp\Psr7\UploadedFile $file) : ?string {
                    return $file->file ?? null;
                }, null, $file);

                return $c($file);
            }
        }

        return null;
    }
    
    public function upload() : \ILIAS\FileUpload\FileUpload
    {
        return $this->upload;
    }

    public function isset(string $key) : bool
    {
        return $this->raw($key) !== null;
    }
    public function hasRefId() : int
    {
        return $this->raw('ref_id') !== null;
    }

    public function getRefId() : int
    {
        return $this->int("ref_id");
    }

    public function hasQuestionId() : bool
    {
        return $this->raw('q_id') !== null;
    }

    public function getQuestionId() : int
    {
        return $this->int('q_id');
    }

    /** @return string[] */
    public function getIds() : array
    {
        return $this->strArray("id");
    }

    /**
     * @return mixed|null
     */
    public function raw(string $key)
    {
        $no_transform = $this->refinery->identity();
        return $this->get($key, $no_transform);
    }

    public function getParsedBody()
    {
        return $this->http->request()->getParsedBody();
    }
}
