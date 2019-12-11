<?php

/**
 * Class ilWebDAVMountInstructionsfactory
 *
 * @author Stephan Winiker <stephan.winiker@hslu.ch>
 * $Id$
 */
class ilWebDAVMountInstructionsFactory
{
    private $repo;
    private $request;
    private $user;
    
    public function __construct(
        ilWebDAVMountInstructionsRepositoryImpl $a_repo,
        \Psr\Http\Message\RequestInterface $a_request,
        \ilObjUser $a_user
    ) {
        $this->repo = $a_repo;
        $this->request = $a_request;
        $this->user = $a_user;
    }
    
    public function getMountInstructionsObject() : ilWebDAVBaseMountInstructions
    {
        $uri_builder = new ilWebDAVUriBuilder($this->request);
        $uri = $this->request->getUri()->getPath();
        
        $splitted_uri = explode('/', $uri);
        
        // Remove path elements before and until webdav script
        while (array_shift($splitted_uri) != 'webdav.php' && count($splitted_uri) > 0);
        
        $path_value = $splitted_uri[1];
        
        if (strlen($path_value) == 2) {
            return new ilWebDAVObjectlessMountInstructions(
                $this->repo,
                $uri_builder,
                new ilSetting('file_access'),
                $path_value
            );
        } elseif (substr($path_value, 0, 4) == 'ref_') {
            return new ilWebDAVObjectMountInstructions(
                $this->repo,
                $uri_builder,
                new ilSetting('file_access'),
                $this->user->getLanguage(),
                (int) substr($path_value, 4)
            );
        } else {
            throw new InvalidArgumentException("Invalid path given");
        }
    }
}
