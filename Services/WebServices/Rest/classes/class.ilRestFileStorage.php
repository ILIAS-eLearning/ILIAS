<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/FileSystem/classes/class.ilFileSystemStorage.php';

/**
 * File storage handling
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 * $Id$
 */
class ilRestFileStorage extends ilFileSystemStorage
{
    const AVAILABILITY_IN_DAYS = 1;

    /**
     * @var \ilLogger
     */
    private $logger = null;


    /**
     * Constructor
     */
    public function __construct()
    {
        global $DIC;

        $this->logger = $DIC->logger()->wsrv();
        $this->logger->logStack();

        parent::__construct(
            ilFileSystemStorage::STORAGE_DATA,
            false,
            0
        );
    }

    /**
     * @param \Slim\Http\Request $request
     * @param \Slim\Http\Response $response
     * @return \Slim\Http\Response | null $response
     */
    protected function checkWebserviceActivation(\Slim\Http\Request $request, \Slim\Http\Response $response)
    {
        global $DIC;

        $settings = $DIC->settings();
        if (!$settings->get('soap_user_administration', 0)) {
            $this->logger->warning('Webservices disabled in administration.');

            $response = $response
                ->withHeader('Content-Type', 'text/html')
                ->withStatus(\Slim\Http\StatusCode::HTTP_FORBIDDEN)
                ->write('Webservice not enabled.');
            return $response;
        }
        return null;
    }

    /**
     * Get path prefix
     */
    protected function getPathPrefix()
    {
        return 'ilRestFileStorage';
    }

    /**
     * Get path prefix
     */
    protected function getPathPostfix()
    {
        return 'files';
    }

    /**
     * init and create directory
     */
    protected function init()
    {
        parent::init();
        $this->create();
    }

    /**
     * @param \Slim\Http\Request $request
     * @param \Slim\Http\Response $response
     */
    public function getFile(\Slim\Http\Request $request, \Slim\Http\Response $response)
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

            $response = $response
                ->withStatus(\Slim\Http\StatusCode::HTTP_OK)
                ->withHeader('Content-Type', 'application/json')
                ->write($return);
            return $response;
        }
        $this->responeNotFound($response);
    }


    /**
     * Send 403
     * @param \Slim\Http\Response $response
     * @return \Slim\Http\Response $response
     */
    protected function responeNotFound(\Slim\Http\Response $response)
    {
        return $response
            ->withHeader('Content-Type', 'text/html')
            ->withStatus(\Slim\Http\StatusCode::HTTP_NOT_FOUND)
            ->write('File not found');
    }



    /**
     * Create new file from post
     *
     * @param \Slim\Http\Request $request
     * @param \Slim\Http\Response $response
     */
    public function createFile(\Slim\Http\Request $request, \Slim\Http\Response $response)
    {
        $failure = $this->checkWebserviceActivation($request, $response);
        if ($failure instanceof \Slim\Http\Response) {
            return $failure;
        }

        $request_body = $request->getParam('content');

        $tmpname = ilUtil::ilTempnam();
        $path = $this->getPath() . '/' . basename($tmpname);

        $this->writeToFile($request_body, $path);
        $return = basename($tmpname);

        $response = $response
            ->withHeader('ContentType', 'application/json')
            ->write($return);

        return $response;
    }

    public function storeFileForRest($content)
    {
        $tmpname = ilUtil::ilTempnam();
        $path = $this->getPath() . '/' . basename($tmpname);

        $this->writeToFile($content, $path);
        return basename($tmpname);
    }

    /**
     * @param $tmpname
     * @return string
     */
    public function getStoredFilePath($tmpname)
    {
        return $this->getPath() . '/' . $tmpname;
    }

    /**
     * Delete deprecated files
     */
    public function deleteDeprecated()
    {
        $max_age = time() - self::AVAILABILITY_IN_DAYS * 24 * 60 * 60;
        $ite = new DirectoryIterator($this->getPath());
        foreach ($ite as $file) {
            if ($file->getCTime() <= $max_age) {
                try {
                    @unlink($file->getPathname());
                } catch (Exception $e) {
                    $this->logger->warning($e->getMessage());
                }
            }
        }
    }
}
