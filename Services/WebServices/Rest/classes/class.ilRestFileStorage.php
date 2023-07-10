<?php

declare(strict_types=1);

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

use Slim\Http\Request;
use Slim\Http\Response;

/**
 * File storage handling
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
class ilRestFileStorage extends ilFileSystemAbstractionStorage
{
    private const AVAILABILITY_IN_DAYS = 1;

    private $logger;

    protected ilSetting $settings;

    /**
     * Constructor
     */
    public function __construct()
    {
        global $DIC;

        $this->settings = $DIC->settings();
        $this->logger = $DIC->logger()->wsrv();
        parent::__construct(
            ilFileSystemAbstractionStorage::STORAGE_DATA,
            false,
            0
        );
    }

    protected function checkWebserviceActivation(Request $request, Response $response): ?Response
    {
        if (!$this->settings->get('soap_user_administration', '0')) {
            $this->logger->warning('Webservices disabled in administration.');

            return $response
                ->withHeader('Content-Type', 'text/html')
                ->withStatus(\Slim\Http\StatusCode::HTTP_FORBIDDEN)
                ->write('Webservice not enabled.');
        }
        return null;
    }

    protected function getPathPrefix(): string
    {
        return 'ilRestFileStorage';
    }

    protected function getPathPostfix(): string
    {
        return 'files';
    }

    /**
     * init and create directory
     */
    protected function init(): bool
    {
        parent::init();
        $this->create();
        return true;
    }

    public function getFile(Request $request, Response $response): Response
    {
        $failure = $this->checkWebserviceActivation($request, $response);
        if ($failure instanceof \Slim\Http\Response) {
            return $failure;
        }

        $file_id = $request->getParam('name');

        $this->logger->debug('Original file name: ' . $file_id);

        $real_path = realpath($this->getPath() . '/' . $file_id);
        if (!$real_path) {
            $this->logger->warning('No realpath found for ' . $this->getPath() . '/' . $file_id);
            return $this->responeNotFound($response);
        }
        $file_name = basename($real_path);
        $this->logger->debug('Translated name: ' . $this->getPath() . '/' . $file_name);
        if (
            $file_name &&
            is_file($this->getPath() . '/' . $file_name) &&
            file_exists($this->getPath() . '/' . $file_name)
        ) {
            $this->logger->info('Delivering file: ' . $this->getPath() . '/' . $file_name);
            $return = file_get_contents($this->getPath() . '/' . $file_name);

            $this->logger->dump($return);

            return $response
                ->withStatus(\Slim\Http\StatusCode::HTTP_OK)
                ->withHeader('Content-Type', 'application/json')
                ->write($return);
        }
        return $this->responeNotFound($response);
    }


    /**
     * Send 403
     * @param \Slim\Http\Response $response
     * @return \Slim\Http\Response $response
     */
    protected function responeNotFound(Response $response): Response
    {
        return $response
            ->withHeader('Content-Type', 'text/html')
            ->withStatus(\Slim\Http\StatusCode::HTTP_NOT_FOUND)
            ->write('File not found');
    }


    /**
     * Create new file from post
     */
    public function createFile(Request $request, Response $response): Response
    {
        $failure = $this->checkWebserviceActivation($request, $response);
        if ($failure instanceof \Slim\Http\Response) {
            return $failure;
        }

        $request_body = $request->getParam('content');

        $tmpname = ilFileUtils::ilTempnam();
        $path = $this->getPath() . '/' . basename($tmpname);

        $this->writeToFile($request_body, $path);
        $return = basename($tmpname);

        return $response
            ->withHeader('ContentType', 'application/json')
            ->write($return);
    }

    public function storeFileForRest(string $content): string
    {
        $tmpname = ilFileUtils::ilTempnam();
        $path = $this->getPath() . '/' . basename($tmpname);

        $this->writeToFile($content, $path);
        return basename($tmpname);
    }

    public function getStoredFilePath(string $tmpname): string
    {
        return $this->getPath() . '/' . $tmpname;
    }

    /**
     * Delete deprecated files
     */
    public function deleteDeprecated(): void
    {
        $max_age = time() - self::AVAILABILITY_IN_DAYS * 24 * 60 * 60;
        $ite = new DirectoryIterator($this->getPath());
        foreach ($ite as $file) {
            if ($file->getCTime() <= $max_age) {
                try {
                    unlink($file->getPathname());
                } catch (Exception $e) {
                    $this->logger->warning($e->getMessage());
                }
            }
        }
    }

    public function writeToFile($a_data, $a_absolute_path): bool
    {
        if (!$fp = fopen($a_absolute_path, 'wb+')) {
            return false;
        }
        if (fwrite($fp, $a_data) === false) {
            fclose($fp);
            return false;
        }
        fclose($fp);
        return true;
    }
}
