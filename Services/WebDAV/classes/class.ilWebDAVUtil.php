<?php

/**
 * This class contains some functions from the old ilDAVServer.
 * Sadly I wasn't able to refactor all of it. Some functions are still used in other classes. Will be refactored
 *
 * @author Raphael Heer <raphael.heer@hslu.ch>
 * $Id$
 *
 * TODO: Check for refactoring potential
 */
class ilWebDAVUtil
{
    protected static $clientBrowser = "firefox";
    protected static $clientOS = "windows";
    protected static $clientFlavor = "nichtxp";
    
    /**
     * Static getter. Returns true, if WebDAV actions are visible for repository items.
     *
     * @return	boolean	value
     */
    public static function _isActionsVisible()
    {
        global $DIC;
        return $DIC->clientIni()->readVariable('file_access', 'webdav_actions_visible') == '1';
    }
    
    /**
     * TODO: Check if needed and refactor
     * Mount instructions method handler for directories
     *
     * @param  ilObjectDAV  dav object handler
     * @return This function does not return. It exits PHP.
     */
    public function showMountInstructions(&$objDAV, &$options)
    {
        global $DIC;
        $lng = $DIC->language();
        $ilUser = $DIC->user();

        $path = $this->davDeslashify($options['path']);
        
        // The $path variable may contain a full or a shortened DAV path.
        // We convert it into an object path, which we can then use to
        // construct a new full DAV path.
        $objectPath = $this->toObjectPath($path);
        
        // Construct a (possibly) full DAV path from the object path.
        $fullPath = '';
        foreach ($objectPath as $object) {
            if ($object->getRefId() == 1 && $this->isFileHidden($object)) {
                // If the repository root object is hidden, we can not
                // create a full path, because nothing would appear in the
                // webfolder. We resort to a shortened path instead.
                $fullPath .= '/ref_1';
            } else {
                $fullPath .= '/' . $this->davUrlEncode($object->getResourceName());
            }
        }
        
        // Construct a shortened DAV path from the object path.
        $shortenedPath = '/ref_' .
            $objectPath[count($objectPath) - 1]->getRefId();
            
        if ($objDAV->isCollection()) {
            $shortenedPath .= '/';
            $fullPath .= '/';
        }
            
        // Prepend client id to path
        $shortenedPath = '/' . CLIENT_ID . $shortenedPath;
        $fullPath = '/' . CLIENT_ID . $fullPath;
            
        // Construct webfolder URI's. The URI's are used for mounting the
        // webfolder. Since mounting using URI's is not standardized, we have
        // to create different URI's for different browsers.
        $webfolderURI = $this->base_uri . $shortenedPath;
        $webfolderURI_Konqueror = ($this->isWebDAVoverHTTPS() ? "webdavs" : "webdav") .
            substr($this->base_uri, strrpos($this->base_uri, ':')) .
            $shortenedPath;
        ;
        $webfolderURI_Nautilus = ($this->isWebDAVoverHTTPS() ? "davs" : "dav") .
            substr($this->base_uri, strrpos($this->base_uri, ':')) .
            $shortenedPath
            ;
        $webfolderURI_IE = $this->base_uri . $shortenedPath;
            
        $webfolderTitle = $objectPath[count($objectPath) - 1]->getResourceName();
            
        header('Content-Type: text/html; charset=UTF-8');
        echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
        echo "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.1 plus MathML 2.0 plus SVG 1.1//EN\"\n";
        echo "	\"http://www.w3.org/2002/04/xhtml-math-svg/xhtml-math-svg.dtd\">\n";
        echo "<html xmlns=\"http://www.w3.org/1999/xhtml\">\n";
        echo "  <head>\n";
        echo "  <title>" . sprintf($lng->txt('webfolder_instructions_titletext'), $webfolderTitle) . "</title>\n";
        echo "  </head>\n";
        echo "  <body>\n";
            
        echo ilDAVServer::_getWebfolderInstructionsFor(
                $webfolderTitle,
                $webfolderURI,
                $webfolderURI_IE,
                $webfolderURI_Konqueror,
                $webfolderURI_Nautilus,
                $this->clientOS,
                $this->clientOSFlavor
            );
            
        echo "  </body>\n";
        echo "</html>\n";
            
        // Logout anonymous user to force authentication after calling mount uri
        if ($ilUser->getId() == ANONYMOUS_USER_ID) {
            $DIC['ilAuthSession']->logout();
        }
            
        exit;
    }
    
    /**
     * TODO: Check if needed and refactor
     * checkLock() helper
     *
     * @param  string resource path to check for locks
     * @return array with the following entries: {
     *    type => "write"
     *    scope => "exclusive" | "shared"
     *    depth => 0 | -1
     *    owner => string
     *    token => string
     *    expires => timestamp
     */
    protected function checkLock($path)
    {
        global $DIC;

        $this->writelog('checkLock(' . $path . ')');
        $result = null;
        
        // get dav object for path
        //$objDAV = $this->getObject($path);
        
        // convert DAV path into ilObjectDAV path
        $objPath = $this->toObjectPath($path);
        if (!is_null($objPath)) {
            $objDAV = $objPath[count($objPath) - 1];
            $locks = $this->locks->getLocksOnPathDAV($objPath);
            foreach ($locks as $lock) {
                $isLastPathComponent = $lock['obj_id'] == $objDAV->getObjectId()
                && $lock['node_id'] == $objDAV->getNodeId();
                
                // Check all locks on last object in path,
                // but only locks with depth infinity on parent objects.
                if ($isLastPathComponent || $lock['depth'] == 'infinity') {
                    // DAV Clients expects to see their own owner name in
                    // the locks. Since these names are not unique (they may
                    // just be the name of the local user running the DAV client)
                    // we return the ILIAS user name in all other cases.
                    if ($lock['ilias_owner'] == $DIC->user()->getId()) {
                        $owner = $lock['dav_owner'];
                    } else {
                        $owner = $this->getLogin($lock['ilias_owner']);
                    }
                    
                    // FIXME - Shouldn't we collect all locks instead of
                    //         using an arbitrary one?
                    $result = array(
                        "type"    => "write",
                        "obj_id"   => $lock['obj_id'],
                        "node_id"   => $lock['node_id'],
                        "scope"   => $lock['scope'],
                        "depth"   => $lock['depth'],
                        "owner"   => $owner,
                        "token"   => $lock['token'],
                        "expires" => $lock['expires']
                    );
                    if ($lock['scope'] == 'exclusive') {
                        // If there is an exclusive lock in the path, it
                        // takes precedence over all non-exclusive locks in
                        // parent nodes. Therefore we can can finish collecting
                        // locks.
                        break;
                    }
                }
            }
        }
        $this->writelog('checkLock(' . $path . '):' . var_export($result, true));
        
        return $result;
    }
    
    /**
     * TODO: Check if needed and refactor
     * Returns the login for the specified user id, or null if
     * the user does not exist.
     */
    protected function getLogin($userId)
    {
        $login = ilObjUser::_lookupLogin($userId);
        $this->writelog('getLogin(' . $userId . '):' . var_export($login, true));
        return $login;
    }
    
    
    /**
     * TODO: Check if needed and refactor
     * Gets a DAV object for the specified path.
     *
     * @param  String davPath A DAV path expression.
     * @return ilObjectDAV object or null, if the path does not denote an object.
     */
    private function getObject($davPath)
    {
        global $DIC;
        $tree = $DIC->repositoryTree();


        // If the second path elements starts with 'file_', the following
        // characters of the path element directly identify the ref_id of
        // a file object.
        $davPathComponents = explode('/', substr($davPath, 1));
        
        if (count($davPathComponents) > 1 &&
            substr($davPathComponents[1], 0, 5) == 'file_') {
            $ref_id = substr($davPathComponents[1], 5);
            $nodePath = $tree->getNodePath($ref_id, $tree->root_id);
            
            // Poor IE needs this, in order to successfully display
            // PDF documents
            header('Pragma: private');
        } else {
            $nodePath = $this->toNodePath($davPath);
            if ($nodePath == null && count($davPathComponents) == 1) {
                return ilObjectDAV::createObject(-1, 'mountPoint');
            }
        }
        if (is_null($nodePath)) {
            return null;
        } else {
            $top = $nodePath[count($nodePath)  - 1];
            return ilObjectDAV::createObject($top['child'], $top['type']);
        }
    }
    /**
     * TODO: Check if needed and refactor
     * Converts a DAV path into an array of DAV objects.
     *
     * @param  String davPath A DAV path expression.
     * @return array<ilObjectDAV> object or null, if the path does not denote an object.
     */
    private function toObjectPath($davPath)
    {
        $this->writelog('toObjectPath(' . $davPath);

        $nodePath = $this->toNodePath($davPath);
        
        if (is_null($nodePath)) {
            return null;
        } else {
            $objectPath = array();
            foreach ($nodePath as $node) {
                $pathElement = ilObjectDAV::createObject($node['child'], $node['type']);
                if (is_null($pathElement)) {
                    break;
                }
                $objectPath[] = $pathElement;
            }
            return $objectPath;
        }
    }
    
    /**
     * TODO: Check if needed and refactor
     * Converts a DAV path into a node path.
     * The returned array is granted to represent an absolute path.
     *
     * The first component of a DAV Path is the ILIAS client id. The following
     * component either denote an absolute path, or a relative path starting at
     * a ref_id.
     *
     * @param  String davPath A DAV path expression.
     * @return Array<String> An Array of path titles.
     */
    public function toNodePath($davPath)
    {
        global $DIC;
        $tree = $DIC->repositoryTree();

        $this->writelog('toNodePath(' . $davPath . ')...');
        
        // Split the davPath into path titles
        $titlePath = explode('/', substr($davPath, 1));
        
        // Remove the client id from the beginning of the title path
        if (count($titlePath) > 0) {
            array_shift($titlePath);
        }
        
        // If the last path title is empty, remove it
        if (count($titlePath) > 0 && $titlePath[count($titlePath) - 1] == '') {
            array_pop($titlePath);
        }
        
        // If the path is empty, return null
        if (count($titlePath) == 0) {
            $this->writelog('toNodePath(' . $davPath . '):null, because path is empty.');
            return null;
        }
        
        // If the path is an absolute path, ref_id is null.
        $ref_id = null;
        
        // If the path is a relative folder path, convert it into an absolute path
        if (count($titlePath) > 0 && substr($titlePath[0], 0, 4) == 'ref_') {
            $ref_id = substr($titlePath[0], 4);
            array_shift($titlePath);
        }
        
        $nodePath = $tree->getNodePathForTitlePath($titlePath, $ref_id);
        
        $this->writelog('toNodePath():' . var_export($nodePath, true));
        return $nodePath;
    }
    
    /**
     * TODO: Check if needed and refactor
     * davDeslashify - make sure path does not end in a slash
     *
     * @param   string directory path
     * @returns string directory path without trailing slash
     */
    private function davDeslashify($path)
    {
        $path = UtfNormal::toNFC($path);
        
        if ($path[strlen($path)-1] == '/') {
            $path = substr($path, 0, strlen($path) - 1);
        }
        return $path;
    }
    
    /**
     * TODO: Check if needed and refactor
     * Private implementation of PHP basename() function.
     * The PHP basename() function does not work properly with filenames that contain
     * international characters.
     * e.g. basename('/x/ö') returns 'x' instead of 'ö'
     */
    private function davBasename($path)
    {
        $components = explode('/', $path);
        return count($components) == 0 ? '' : $components[count($components) - 1];
    }
    
    /**
     * TODO: Check if needed and refactor
     * Returns an URI for mounting the repository object as a webfolder using Internet Explorer
     * and Firefox with the "openwebfolder" plugin.
     * The FolderURI is only in effect on Windows. Therefore we don't need to deal with other
     * pecularities.
     *
     * The URI can be used as the value of a "folder" attribute
     * inside of an HTML anchor tag "<a>".
     *
     * @param refId of the repository object.
     * @param nodeId of a childnode of the repository object.
     * @param ressourceName ressource name (if known), to reduce SQL queries
     * @param parentRefId refId of parent object (if known), to reduce SQL queries
     */
    public static function getFolderURI($refId, $nodeId = 0, $ressourceName = null, $parentRefId = null)
    {
        if (self::$clientOS == 'windows') {
            $baseUri = "https:";
            $query = null;
        } elseif (self::$clientBrowser == 'konqueror') {
            $baseUri = "webdavs:";
            $query = null;
        } elseif (self::$clientBrowser == 'nautilus') {
            $baseUri = "davs:";
            $query = null;
        } else {
            $baseUri = "https:";
            $query = null;
        }
        $baseUri.= "//$_SERVER[HTTP_HOST]$_SERVER[SCRIPT_NAME]";
        $baseUri = substr($baseUri, 0, strrpos($baseUri, '/')) . '/webdav.php/' . CLIENT_ID;
        
        $uri = $baseUri . '/ref_' . $refId . '/';
        if ($query != null) {
            $uri .= '?' . $query;
        }
        
        return $uri;
    }
    

    
    /**
     * TODO: Check if needed and refactor
     * Returns an URI for mounting the repository object as a webfolder.
     * The URI can be used as the value of a "href" attribute attribute
     * inside of an HTML anchor tag "<a>".
     *
     * @param refId of the repository object.
     * @param nodeId of a childnode of the repository object.
     * @param ressourceName ressource name (if known), to reduce SQL queries
     * @param parentRefId refId of parent object (if known), to reduce SQL queries
     * @param genericURI boolean Returns a generic mount URI, which works on
     * all platforms which support WebDAV as in the IETF specification.
     */
    public static function getMountURI($refId, $nodeId = 0, $ressourceName = null, $parentRefId = null, $genericURI = false)
    {
        if ($genericURI) {
            $baseUri = "https:";
            $query = null;
        } elseif (self::$clientOS == 'windows') {
            $baseUri = "http:";
            $query = 'mount-instructions';
        } elseif (self::$clientBrowser == 'konqueror') {
            $baseUri = "webdavs:";
            $query = null;
        } elseif (self::$clientBrowser == 'nautilus') {
            $baseUri = "davs:";
            $query = null;
        } else {
            $baseUri = "https:";
            $query = 'mount-instructions';
        }
        $baseUri.= "//$_SERVER[HTTP_HOST]$_SERVER[SCRIPT_NAME]";
        $baseUri = substr($baseUri, 0, strrpos($baseUri, '/')) . '/webdav.php/' . CLIENT_ID;
        
        $uri = $baseUri . '/ref_' . $refId . '/';
        if ($query != null) {
            $uri .= '?' . $query;
        }
        
        return $uri;
    }
    
    /**
     * TODO: Check if needed and refactor
     * Returns an URI for getting a object using WebDAV by its name.
     *
     * WebDAV clients can use this URI to access the object from ILIAS.
     *
     * @param refId of the object.
     * @param ressourceName object title (if known), to reduce SQL queries
     * @param parentRefId refId of parent object (if known), to reduce SQL queries
     *
     * @return Returns the URI or null if the URI can not be constructed.
     */
    public function getObjectURI($refId, $ressourceName = null, $parentRefId = null)
    {
        global $DIC;
        $nodeId = 0;
        $baseUri = ($this->isWebDAVoverHTTPS() ? "https:" : "http:") .
        "//$_SERVER[HTTP_HOST]$_SERVER[SCRIPT_NAME]";
        $baseUri = substr($baseUri, 0, strrpos($baseUri, '/')) . '/webdav.php/' . CLIENT_ID;
        
        if (!is_null($ressourceName) && !is_null($parentRefId)) {
            // Quickly create URI from the known data without needing SQL queries
            $uri = $baseUri . '/ref_' . $parentRefId . '/' . $this->davUrlEncode($ressourceName);
        } else {
            // Create URI and use some SQL queries to get the missing data
            $nodePath = $DIC->repositoryTree()->getNodePath($refId);
            
            if (is_null($nodePath) || count($nodePath) < 2) {
                // No object path? Return null - file is not in repository.
                $uri = null;
            } else {
                $uri = $baseUri . '/ref_' . $nodePath[count($nodePath) - 2]['child'] . '/' .
                    $this->davUrlEncode($nodePath[count($nodePath) - 1]['title']);
            }
        }
        return $uri;
    }
    
    /**
     * TODO: Check if needed and refactor
     * Returns an URI for getting a file object using WebDAV.
     *
     * Browsers can use this URI to download a file from ILIAS.
     *
     * Note: This could be the same URI that is returned by getObjectURI.
     * But we use a different URI, because we want to use the regular
     * ILIAS authentication method, if no session exists, and we
     * want to be able to download a file from the repository, even if
     * the name of the file object is not unique.
     *
     * @param refId of the file object.
     * @param ressourceName title of the file object (if known), to reduce SQL queries
     * @param parentRefId refId of parent object (if known), to reduce SQL queries
     *
     * @return Returns the URI or null if the URI can not be constructed.
     */
    public function getFileURI($refId, $ressourceName = null, $parentRefId = null)
    {
        global $DIC;
        $nodeId = 0;
        $baseUri = ($this->isWebDAVoverHTTPS() ? "https:" : "http:") .
        "//$_SERVER[HTTP_HOST]$_SERVER[SCRIPT_NAME]";
        $baseUri = substr($baseUri, 0, strrpos($baseUri, '/')) . '/webdav.php/' . CLIENT_ID;

        if (!is_null($ressourceName) && !is_null($parentRefId)) {
            // Quickly create URI from the known data without needing SQL queries
            $uri = $baseUri . '/file_' . $refId . '/' . $this->davUrlEncode($ressourceName);
        } else {
            // Create URI and use some SQL queries to get the missing data
            $nodePath = $DIC->repositoryTree()->getNodePath($refId);
            
            if (is_null($nodePath) || count($nodePath) < 2) {
                // No object path? Return null - file is not in repository.
                $uri = null;
            } else {
                $uri = $baseUri . '/file_' . $nodePath[count($nodePath) - 1]['child'] . '/' .
                    $this->davUrlEncode($nodePath[count($nodePath) - 1]['title']);
            }
        }
        return $uri;
    }
    
    /**
     * TODO: Check if needed and refactor
     * Returns true, if the WebDAV server transfers data over HTTPS.
     *
     * @return boolean Returns true if HTTPS is active.
     */
    public function isWebDAVoverHTTPS()
    {
        if ($this->isHTTPS == null) {
            global $DIC;
            $ilSetting = $DIC->settings();
            require_once './Services/Http/classes/class.ilHTTPS.php';
            $https = new ilHTTPS();
            $this->isHTTPS = $https->isDetected() || $ilSetting->get('https');
        }
        return $this->isHTTPS;
    }
    
    /**
     * TODO: Check if needed and refactor
     * Static getter. Returns true, if the WebDAV server is active.
     *
     * THe WebDAV Server is active, if the variable file_access::webdav_enabled
     * is set in the client ini file. (Removed wit 08.2016: , and if PEAR Auth_HTTP is installed).
     *
     * @return	boolean	value
     */
    public static function _isActive()
    {
        global $DIC;
        return $DIC->clientIni()->readVariable('file_access', 'webdav_enabled') == '1';
    }

    /**
     * TODO: Check if needed and refactor
     * Gets the maximum permitted upload filesize from php.ini in bytes.
     *
     * @return int Upload Max Filesize in bytes.
     */
    private function getUploadMaxFilesize()
    {
        $val = ini_get('upload_max_filesize');

        $val = trim($val);
        $last = strtolower($val[strlen($val)-1]);
        switch ($last) {
            // The 'G' modifier is available since PHP 5.1.0
            case 'g':
                $val *= 1024;
                // no break
            case 'm':
                $val *= 1024;
                // no break
            case 'k':
                $val *= 1024;
        }

        return $val;
    }

    private static $instance = null;

    private $pwd_instruction = null;

    /**
     * Singleton constructor
     * @return
     */
    private function __construct()
    {
    }

    /**
     * Get singleton instance
     * @return object ilDAVUtils
     */
    public static function getInstance()
    {
        if (self::$instance) {
            return self::$instance;
        }
        return self::$instance = new ilWebDAVUtil();
    }

    /**
     *
     * @return
     */
    public function isLocalPasswordInstructionRequired()
    {
        global $DIC;
        $ilUser = $DIC->user();

        if ($this->pwd_instruction !== null) {
            return $this->pwd_instruction;
        }
        include_once './Services/Authentication/classes/class.ilAuthUtils.php';
        $status = ilAuthUtils::supportsLocalPasswordValidation($ilUser->getAuthMode(true));
        if ($status != ilAuthUtils::LOCAL_PWV_USER) {
            return $this->pwd_instruction = false;
        }
        // Check if user has local password
        return $this->pwd_instruction = (bool) !strlen($ilUser->getPasswd());
    }
}
