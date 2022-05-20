<?php declare(strict_types = 1);

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
 
use Pimple\Container;
use ILIAS\DI\Container as ILIASContainer;
use Sabre\DAV\Auth\Plugin as AuthPlugin;
use Sabre\DAV\Locks\Plugin as LocksPlugin;
use Sabre\DAV\Auth\Backend\BasicCallBack;

class ilWebDAVDIC extends Container
{
    public function initWithoutDIC() : void
    {
        global $DIC;
        $this->init($DIC);
    }
    
    public function init(ILIASContainer $DIC) : void
    {
        $this['dav_settings'] = fn ($c) : ilSetting => new ilSetting('webdav');
        
        $this['locks.repository'] = fn ($c) : ilWebDAVLocksRepository => new ilWebDAVLocksRepository($DIC->database());
        
        $this['repository.helper'] = fn ($c) : ilWebDAVRepositoryHelper => new ilWebDAVRepositoryHelper(
            $DIC->access(),
            $DIC->repositoryTree(),
            new ilRepUtil(),
            $c['locks.repository']
        );
        
        $this['uriresolver'] = fn ($c) : ilWebDAVLockUriPathResolver => new ilWebDAVLockUriPathResolver($c['repository.helper']);
        
        $this['davobj.factory'] = fn ($c) : ilWebDAVObjFactory => new ilWebDAVObjFactory(
            $c['repository.helper'],
            $DIC->user(),
            $DIC->resourceStorage(),
            $DIC->http()->request(),
            $DIC->language(),
            $DIC['ilias']->getClientId(),
            (bool) $c['dav_settings']->get('webdav_versioning_enabled', 'true')
        );
        
        $this['locks.backend'] = fn ($c) : ilWebDAVLocksBackend => new ilWebDAVLocksBackend(
            $c['locks.repository'],
            $c['repository.helper'],
            $c['davobj.factory'],
            $c['uriresolver'],
            $DIC->user()
        );
        
        $this['mountinstructions.repository'] = fn ($c) : ilWebDAVMountInstructionsRepository => new ilWebDAVMountInstructionsRepositoryImpl($DIC->database());
        
        $this['mountinstructions.facory'] = fn ($c) : ilWebDAVMountInstructionsFactory => new ilWebDAVMountInstructionsFactory(
            $c['mountinstructions.repository'],
            $DIC->http()->request(),
            $DIC->user()
        );
        
        $this['mountinstructions.gui'] = fn ($c) : ilWebDAVMountInstructionsGUI => new ilWebDAVMountInstructionsGUI(
            $c['mountinstructions.facory']->getMountInstructionsObject(),
            $DIC->language(),
            $DIC->ui(),
            $DIC->http()
        );
        
        $this['mountinstructions.uploadgui'] = fn ($c) : ilWebDAVMountInstructionsUploadGUI => new ilWebDAVMountInstructionsUploadGUI(
            $DIC->ui()->mainTemplate(),
            $DIC->user(),
            $DIC->ctrl(),
            $DIC->language(),
            $DIC->rbac()->system(),
            $DIC["ilErr"],
            $DIC->logger()->root(),
            $DIC->toolbar(),
            $DIC->http(),
            $DIC->refinery(),
            $DIC->ui(),
            $DIC->filesystem(),
            $DIC->upload(),
            $c['mountinstructions.repository']
        );
        
        $this['sabre.authplugin'] = function ($c) use ($DIC) : AuthPlugin {
            $webdav_auth = new ilWebDAVAuthentication($DIC->user(), $DIC['ilAuthSession']);
            $auth_callback_class = new BasicCallBack(array($webdav_auth, 'authenticate'));
            return new AuthPlugin($auth_callback_class);
        };
        
        $this['sabre.locksplugin'] = fn ($c) : LocksPlugin => new LocksPlugin($c['locks.backend']);
        
        $this['sabre.browserplugin'] = fn ($c) : ilWebDAVSabreBrowserPlugin => new ilWebDAVSabreBrowserPlugin($DIC->ctrl(), $DIC->http()->request()->getUri());
    }
    
    public function dav_settings() : ilSetting
    {
        return $this['dav_settings'];
    }
    
    public function dav_factory() : ilWebDAVObjFactory
    {
        return $this['davobj.factory'];
    }
    
    public function mountinstructions() : ilWebDAVMountInstructionsGUI
    {
        return $this['mountinstructions.gui'];
    }
    
    public function mountinstructions_upload() : ilWebDAVMountInstructionsUploadGUI
    {
        return $this['mountinstructions.uploadgui'];
    }
    
    public function authplugin() : AuthPlugin
    {
        return $this['sabre.authplugin'];
    }
    
    public function locksbackend() : ilWebDAVLocksBackend
    {
        return $this['locks.backend'];
    }
       
    public function locksplugin() : LocksPlugin
    {
        return $this['sabre.locksplugin'];
    }
    
    public function browserplugin() : LocksPlugin
    {
        return $this['sabre.locksplugin'];
    }
}
