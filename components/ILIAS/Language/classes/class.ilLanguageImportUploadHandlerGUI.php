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

use ILIAS\Data\DataSize;
use ILIAS\FileUpload\DTO\UploadResult;
use ILIAS\FileUpload\Handler\AbstractCtrlAwareUploadHandler;
use ILIAS\FileUpload\Handler\BasicFileInfoResult;
use ILIAS\FileUpload\Handler\BasicHandlerResult;
use ILIAS\FileUpload\Handler\FileInfoResult;
use ILIAS\FileUpload\Handler\HandlerResult;
use ILIAS\Filesystem\Filesystem;

/**
 * Keeps an uploaded language file in the temporary filesystem until it is imported.
 * Only identifiers issued to this session are accepted, so a request can never point to another file.
 */
class ilLanguageImportUploadHandlerGUI extends AbstractCtrlAwareUploadHandler
{
    private const string SESSION_KEY = 'lang_ext_import_uploads';

    private readonly Filesystem $temp_system;

    public function __construct()
    {
        global $DIC;
        $this->temp_system = $DIC->filesystem()->temp();
        parent::__construct();
    }

    protected function getUploadResult(): HandlerResult
    {
        if (!$this->upload->hasBeenProcessed()) {
            $this->upload->process();
        }
        $results = $this->upload->getResults();
        $result = end($results);

        if (!$result instanceof UploadResult || !$result->isOK()) {
            return new BasicHandlerResult(
                $this->getFileIdentifierParameterName(),
                HandlerResult::STATUS_FAILED,
                '',
                $result instanceof UploadResult ? $result->getStatus()->getMessage() : 'no upload'
            );
        }

        $identifier = uniqid('lng', true);
        $this->temp_system->createDir($identifier);
        $this->temp_system->put(
            $identifier . '/' . ilFileUtils::getValidFilename($result->getName()),
            file_get_contents($result->getPath())
        );
        ilSession::set(self::SESSION_KEY, [...$this->getOwnIdentifiers(), $identifier]);

        return new BasicHandlerResult(
            $this->getFileIdentifierParameterName(),
            HandlerResult::STATUS_OK,
            $identifier,
            'Upload ok'
        );
    }

    protected function getRemoveResult(string $identifier): HandlerResult
    {
        $this->discard($identifier);

        return new BasicHandlerResult(
            $this->getFileIdentifierParameterName(),
            HandlerResult::STATUS_OK,
            $identifier,
            'File removed'
        );
    }

    public function getInfoResult(string $identifier): ?FileInfoResult
    {
        $path = $this->getPath($identifier);
        if ($path === null) {
            return null;
        }

        return new BasicFileInfoResult(
            $this->getFileIdentifierParameterName(),
            $identifier,
            basename($path),
            (int) $this->temp_system->getSize($path, DataSize::Byte)->inBytes(),
            $this->temp_system->getMimeType($path)
        );
    }

    public function getInfoForExistingFiles(array $file_ids): array
    {
        return array_values(array_filter(array_map(
            fn(string $identifier): ?FileInfoResult => $this->getInfoResult($identifier),
            $file_ids
        )));
    }

    public function getFileName(string $identifier): ?string
    {
        $path = $this->getPath($identifier);

        return $path === null ? null : basename($path);
    }

    /**
     * Copies the upload to a local file, since ilObjLanguage::importLanguageFile() needs a path.
     * The caller removes the returned file.
     */
    public function copyToLocalFile(string $identifier): ?string
    {
        $path = $this->getPath($identifier);
        if ($path === null) {
            return null;
        }
        $local_file = ilFileUtils::ilTempnam() . '.sec';
        file_put_contents($local_file, $this->temp_system->read($path));

        return $local_file;
    }

    public function discard(string $identifier): void
    {
        $own = $this->getOwnIdentifiers();
        if (!in_array($identifier, $own, true)) {
            return;
        }
        if ($this->temp_system->hasDir($identifier)) {
            $this->temp_system->deleteDir($identifier);
        }
        ilSession::set(self::SESSION_KEY, array_values(array_diff($own, [$identifier])));
    }

    /** @param list<string> $except identifiers to keep */
    public function discardAll(array $except = []): void
    {
        foreach (array_diff($this->getOwnIdentifiers(), $except) as $identifier) {
            $this->discard($identifier);
        }
    }

    private function getPath(string $identifier): ?string
    {
        if (!in_array($identifier, $this->getOwnIdentifiers(), true) || !$this->temp_system->hasDir($identifier)) {
            return null;
        }
        $files = $this->temp_system->listContents($identifier);

        return $files === [] ? null : $files[0]->getPath();
    }

    /** @return list<string> */
    private function getOwnIdentifiers(): array
    {
        return array_values(array_filter(
            (array) (ilSession::get(self::SESSION_KEY) ?? []),
            'is_string'
        ));
    }
}
