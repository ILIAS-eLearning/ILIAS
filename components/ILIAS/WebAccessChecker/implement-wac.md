# Web Access Checker

## What's new in ILIAS 5.1

- Web Access Checker is enabled by default
- Now all Files in the ./data directory will be checked
- New Token-Based File delivery (much faster than before)

The new WebAccessChecker allows fast and secure delivery of files in the /data directory (see [Feature-Wiki](https://docu.ilias.de/goto_docu_wiki_wpage_3394_1357.html) for performance comparison). As definied in the Data Directory Guideline, directories within the /sec-Folder are supported. Since there are folders outside the /sec-Folder which have to be secured, the new WebAccessChecker also secures those directories (e.g. lm_data, usr_images, mobs).

All requests to /data are now redirected to the WAC-Script per default, the .htaccess-File has a new entry:

`RewriteRule ^data/.*/.*/.*$ wac.php [L]`

The WAC delivers the file after the following decisions:

![Web Access Checker decisions scheme](https://files.ilias.de/images/web_access_checker.png)

The most performant ways to deliver files are the file- or the folder-based tokens. These requests are already signed and and access-checked during the rendering of a page (e.g. display of poll-image, the user accesses a course and ILIAS renders a poll-image to the page. At this moment the access is already checked). 

If there is no token or the token has become invalid, e previosly registred checking-instance has to decide whether the file can be delivered or not. if theres no checking instance, only files outside the /secore-Folder will bedelivered. if there's a checking instance and the instance declines delivery, access to the file is also denied.

## Implementation

### Signing Files

Developers can sign files and folders using the ilWACSignedPath-Class:

```php
// Example in Poll:
$img = $a_poll->getImageFullPath();
$this->tpl->setVariable("URL_IMAGE", ilWACSignedPath::signFile($img));

// Example in SCORM-Module
ilWACSignedPath::signFolderOfStartFile($this->slm->getDataDirectory().'/manifest.xml');
```

## Register Checking instance

When registering a checking instance, developers have to add a secured path to their service.xml or module.xml:

```php
<web_access_checker>
<secure_path path="ilPoll" checking-class="ilObjPollAccess" in-sec-folder='1'/>
</web_access_checker>
```

This entry will be registred with the next structure reload (add one if you want to register a new secured path). It's allowed to have multiple checking-instances per module/service but they must have unique paths. After the reload all requests in ./data/my_client/sec/ilPoll/* will be checked by the class ilObjPollAccess. The method canBeDelivered() which is defined by the ilWACCheckingClass-interface receives the ilWACPath Object which proviedes several information about the requested file. Most Modules will look for the obj_id and check using ilAccess.

```php
class ilObjPollAccess extends ilObjectAccess implements ilWACCheckingClass
{	
	// Other methods and checking functions of ilObjPollAccess
 
	/**
	 * @param ilWACPath $ilWACPath
	 *
	 * @return bool
	 */
	public function canBeDelivered(ilWACPath $ilWACPath) {
		global $ilAccess;
		preg_match("/\\/poll_([\\d]*)\\//uism", $ilWACPath->getPath(), $results);
 
		foreach (ilObject2::_getAllReferences($results[1]) as $ref_id) {
			if ($ilAccess->checkAccess('read', '', $ref_id)) {
				return true;
			}
		}
 
		return false;
	}
}
 
?>
```

## Error Handling

Per default the Web Access Checker delivers on images and videos a error-placeholder when the user has no permission to access the file.

## File Delivery

Delivering files is implemented using the ilFileDelivery-Service introduced in ILIAS 5. ilFileDelivery supports X-SendFile and introduces a updated ilMimeTypeUtil. Straming videos is now done chunked and allows (as previously in ilUtil) ranges. Methods in ilUtil are marked as deprecated.

```php
// File-Delivery example
$ilFileDelivery = new ilFileDelivery('./components/ILIAS/WebAccessChecker/templates/images/access_denied.png', 'file_name.png');
$ilFileDelivery->setDisposition(ilFileDelivery::DISP_INLINE);
$ilFileDelivery->deliver();
```

If you want to use ilFileDeliver with X-SendFile please install and activate

```php
sudo apt-get install libapache2-mod-xsendfile
sudo a2enmod xsendfile
```

In your Apache-Config or VHOST the "iliasdata"- and the "data"- directories must be unlocked, e.g.:

```php
XSendFilePath /var/www
XSendFilePath /var/iliasdata
```

Additionally in the .htaccess the following rule activated the X-SendFile Module:

```php

    XSendFile On

```
