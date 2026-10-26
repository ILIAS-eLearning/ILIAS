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
use ILIAS\Filesystem\Exception\IOException;
use ILIAS\Filesystem\Filesystem;

/**
 * Keeps an uploaded language file in the temporary filesystem until it is imported.
 * Only identifiers issued to this session are accepted, so a request can never point to another file.
 */
class ilLanguageImportUploadHandlerGUI extends AbstractCtrlAwareUploadHandler
{
    private const string SESSION_KEY = 'lang_ext_import_uploads';
    private const string FALLBACK_FILE_NAME = 'language.lang';
    /** about five times the largest distributed language file */
    public const int MAX_FILE_SIZE = 10 * 1024 * 1024;

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
        // the size limit of the File field is only checked in the browser
        if ($result->getSize() > self::MAX_FILE_SIZE) {
            return new BasicHandlerResult(
                $this->getFileIdentifierParameterName(),
                HandlerResult::STATUS_FAILED,
                '',
                'file too large'
            );
        }

        $identifier = uniqid('lng', true);
        $this->temp_system->createDir($identifier);
        $file_name = ilFileUtils::getValidFilename($result->getName());
        $this->temp_system->put(
            $identifier . '/' . ($file_name !== '' ? $file_name : self::FALLBACK_FILE_NAME),
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

        try {
            $mime_type = $this->temp_system->getMimeType($path);
        } catch (IOException) {
            // the detector gives up on plain text with the unknown extension .lang
            $mime_type = 'text/plain';
        }

        return new BasicFileInfoResult(
            $this->getFileIdentifierParameterName(),
            $identifier,
            // the KS renderer outputs the file name unescaped
            htmlspecialchars(basename($path), ENT_QUOTES, 'UTF-8'),
            (int) $this->temp_system->getSize($path, DataSize::Byte)->inBytes(),
            $mime_type
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
        if (file_put_contents($local_file, $this->temp_system->read($path)) === false) {
            if (is_file($local_file)) {
                unlink($local_file);
            }
            return null;
        }

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
