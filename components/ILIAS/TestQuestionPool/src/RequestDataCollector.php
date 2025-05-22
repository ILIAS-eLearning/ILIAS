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

use Closure;
use GuzzleHttp\Psr7\UploadedFile;
use ILIAS\Refinery\Transformation;
use ILIAS\Repository\BaseGUIRequest;
use ILIAS\Refinery\ConstraintViolationException;
use ILIAS\HTTP\Services;
use ILIAS\Refinery\Factory;
use ILIAS\FileUpload\FileUpload;

class RequestDataCollector
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
                $c = Closure::bind(static function (UploadedFile $file): ?string {
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

    public function isset(string $key): bool
    {
        return $this->raw($key) !== null;
    }

    public function hasRefId(): int
    {
        return $this->raw('ref_id') !== null;
    }

    public function getRefId(): int
    {
        return $this->int('ref_id');
    }

    public function hasQuestionId(): bool
    {
        return $this->raw('q_id') !== null;
    }

    public function getQuestionId(): int
    {
        return $this->int('q_id');
    }

    /**
     * @return array<string>
     */
    public function getIds(): array
    {
        return $this->strArray('id');
    }

    /**
     * @return mixed|null
     */
    public function raw(string $key): mixed
    {
        return $this->get($key, $this->refinery->identity());
    }

    public function float(string $key): float
    {
        try {
            return $this->get($key, $this->refinery->kindlyTo()->float()) ?? 0.0;
        } catch (ConstraintViolationException $e) {
            return 0.0;
        }
    }

    public function string(string $key): string
    {
        return $this->get($key, $this->refinery->kindlyTo()->string()) ?? '';
    }

    public function bool(string $key): ?bool
    {
        return $this->get($key, $this->refinery->kindlyTo()->bool());
    }

    public function getParsedBody(): object|array|null
    {
        return $this->http->request()->getParsedBody();
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

    public function getPostKeys(): array
    {
        return $this->http->wrapper()->post()->keys();
    }

    public function getCmdIndex(string $key): int|string|null
    {
        $cmd = $this->rawArray('cmd');
        if (!isset($cmd[$key]) || !is_array($cmd[$key])) {
            return null;
        }
        return key($cmd[$key]);
    }

    /**
     * @return array<string|array>
     */
    public function strArray(string $key, int $depth = 1): array
    {
        return $this->retrieveArray($key, $depth, $this->refinery->kindlyTo()->string());
    }

    /**
     * @return array<float>
     */
    public function floatArray(string $key): array
    {
        return $this->retrieveArray($key, 1, $this->refinery->kindlyTo()->float());
    }

    /**
     * @return array<int>
     */
    public function intArray(string $key): array
    {
        return $this->retrieveArray($key, 1, $this->refinery->kindlyTo()->int());
    }

    /**
     * @return array<mixed|array>
     */
    public function rawArray(string $key): array
    {
        return $this->retrieveArray($key, 1, $this->refinery->identity());
    }

    private function retrieveArray(string $key, int $depth, Transformation $transformation): array
    {
        $chain = $this->refinery->kindlyTo()->dictOf($transformation);
        for ($i = 1; $i < $depth; $i++) {
            $chain = $this->refinery->kindlyTo()->dictOf($chain);
        }

        return $this->http->wrapper()->post()->retrieve(
            $key,
            $this->refinery->byTrying([
                $chain,
                $this->refinery->always([])
            ])
        );
    }
}
