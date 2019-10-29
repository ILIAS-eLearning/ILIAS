<?php

abstract class ilWebDAVBaseMountInstructions
{
    /** @var ilWebDAVMountInstructionsRepository */
    protected $repo;

    /** @var ilWebDAVUriBuilder */
    protected $uri_builder;

    /** @var ilSetting */
    protected $settings;

    /** @var string */
    protected $language;

    public function __construct(ilWebDAVMountInstructionsRepository $a_repo,
        ilWebDAVUriBuilder $a_uri_builder,
        ilSetting $a_settings)
    {
        $this->repo = $a_repo;
        $this->uri_builder = $a_uri_builder;
        $this->settings = $a_settings;
    }

    public static function buildMountInstructionsObjectFromURI(ilWebDAVMountInstructionsRepositoryImpl $a_repo, \Psr\Http\Message\RequestInterface $a_request)
    {
        $uri_builder = new ilWebDAVUriBuilder($a_request);
        $uri = $a_request->getUri()->getPath();

        $splitted_uri = explode('/', $uri);

        // Remove path elements before and until webdav script
        while($value = array_shift($splitted_uri) != 'webdav.php' && count($splitted_uri) > 0);

        $client_id = array_shift($splitted_uri);
        $path_value = array_shift($splitted_uri);

        if(strlen($path_value) == 2)
        {
            return new ilWebDAVObjectlessMountInstructions($a_repo,
                $uri_builder,
                new ilSetting('file_access'));
        }
        else if (substr($path_value, 0, 4) == 'ref_')
        {
            return new ilWebDAVObjectMountInstructions($a_repo,
                $uri_builder,
                new ilSetting('file_access'),
                (int)substr($path_value, 4));
        }
        else
        {
            throw new InvalidArgumentException("Invalid path given");
        }
    }

    public function getMountInstructionsAsArray(string $a_language) : array
    {
        $document = $this->repo->getMountInstructionsByLanguage($a_language);
        $processed = $document->getProcessedInstructions();
        $mount_instructions = json_decode($processed, true);

        $mount_instructions = $this->fillPlaceholdersForMountInstructions($mount_instructions);

        return $mount_instructions;
    }

    abstract protected function fillPlaceholdersForMountInstructions(array $mount_instructions) : array ;
}