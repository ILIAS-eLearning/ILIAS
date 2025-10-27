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

namespace ILIAS\TestQuestionPool;

use GuzzleHttp\Psr7\UploadedFile;
use ILIAS\FileUpload\DTO\UploadResult;
use ILIAS\Repository\BaseGUIRequest;
use ILIAS\HTTP\Services;
use ILIAS\Refinery\Factory;
use ILIAS\FileUpload\FileUpload;

class RequestDataCollector implements RequestDataCollectorInterface
{
    use BaseGUIRequest;

    public function __construct(
        Services $http,
        Factory $refinery,
        protected readonly FileUpload $upload
    ) {
        $this->initRequest($http, $refinery);
    }

    /**
     * @return array<UploadResult>
     */
    public function getProcessedUploads(): array
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
     * @param array<string> $http_names An array of keys used as structure for the HTTP name (e.g. ['terms', 'image'] for $_FILES['terms']['image'])
     * @param int $index
     * @return string|null
     */
    public function getUploadFilename(array $http_names, int $index): ?string
    {
        $uploaded_files = $this->http->request()->getUploadedFiles();

        while (($current_key = array_shift($http_names)) !== null) {
            if (!isset($uploaded_files[$current_key])) {
                return null;
            }

            $uploaded_files = $uploaded_files[$current_key];

            if (isset($uploaded_files[$index]) && $http_names === []) {
                /** @var UploadedFile $file */
                $file = $uploaded_files[$index];
                $c = \Closure::bind(static function (UploadedFile $file): ?string {
                    return $file->file ?? null;
                }, null, $file);

                return $c($file);
            }
        }

        return null;
    }

    public function upload(): FileUpload
    {
        return $this->upload;
    }

    /**
     * @return array<int>
     */
    public function getUnitIds(): array
    {
        return $this->intArray('unit_ids');
    }

    /**
     * @return array<int>
     */
    public function getUnitCategoryIds(): array
    {
        return $this->intArray('category_ids');
    }

    public function getMatchingPairs(): array
    {
        if (!$this->http->wrapper()->post()->has('matching')) {
            return [];
        }

        return $this->http->wrapper()->post()->retrieve(
            'matching',
            $this->refinery->byTrying([
                $this->refinery->container()->mapValues(
                    $this->refinery->custom()->transformation(
                        fn(string $v): array => $this->refinery->container()->mapValues(
                            $this->refinery->kindlyTo()->int()
                        )->transform(json_decode($v))
                    )
                ),
                $this->refinery->always([])
            ])
        );
    }

    public function getCmdIndex(string $key): int|string|null
    {
        $cmd = $this->rawArray('cmd');
        if (!isset($cmd[$key]) || !is_array($cmd[$key])) {
            return null;
        }
        return key($cmd[$key]);
    }
}
