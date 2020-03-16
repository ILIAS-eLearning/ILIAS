<?php
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

class ilForumXMLParser extends ilSaxParser
{
    /**
     *
     * An instance of ilObjForum
     *
     * @var ilObjForum
     */
    private $forum;
    private $entity;
    private $mapping = array(
        'frm' => array(),
        'thr' => array(),
        'pos' => array()
    );
    private $import_install_id = null;
    private $user_id_mapping = array();
    protected $mediaObjects = array();

    /**
     * @var null|string
     */
    protected $schema_version = null;

    private $db;
    /**
     * Constructor
     *
     * @param	ilObjForum	$forum	 existing forum object
     * @param	string		$a_xml_file	xml data
     * @access	public
     */
    public function __construct($forum, $a_xml_data)
    {
        global $DIC;
        
        parent::__construct();
        $this->forum = $forum;
        $this->setXMLContent('<?xml version="1.0" encoding="utf-8"?>' . $a_xml_data);
        $this->aobject = new ilObjUser(ANONYMOUS_USER_ID);
        $this->db = $DIC->database();
    }

    /**
     * Set import directory
     *
     * @param	string	import directory
     */
    public function setImportDirectory($a_val)
    {
        $this->importDirectory = $a_val;
    }

    /**
     * Get import directory
     *
     * @return	string	import directory
     */
    public function getImportDirectory()
    {
        return $this->importDirectory;
    }

    /**
     * @return null|string
     */
    public function getSchemaVersion()
    {
        return $this->schema_version;
    }

    /**
     * @param null|string $schema_version
     */
    public function setSchemaVersion($schema_version)
    {
        $this->schema_version = $schema_version;
    }

    /**
    * set event handlers
    *
    * @param	resource	reference to the xml parser
    * @access	private
    */
    public function setHandlers($a_xml_parser)
    {
        xml_set_object($a_xml_parser, $this);
        xml_set_element_handler($a_xml_parser, 'handlerBeginTag', 'handlerEndTag');
        xml_set_character_data_handler($a_xml_parser, 'handlerCharacterData');
    }

    /**
    * handler for begin of element
    *
    * @param	resource	$a_xml_parser		xml parser
    * @param	string		$a_name				element name
    * @param	array		$a_attribs			element attributes array
    */
    public function handlerBeginTag($a_xml_parser, $a_name, $a_attribs)
    {
        switch ($a_name) {
            case 'Forum':
                $this->entity = 'forum';
                $this->forumArray = array();
                break;

            case 'Thread':
                $this->entity = 'thread';
                $this->threadArray = array();
                break;

            case 'Post':
                $this->mediaObjects = array();
                $this->entity = 'post';
                $this->postArray = array();
                break;

            case 'Content':
                $this->entity = 'content';
                $this->contentArray = array();
                break;

            case 'MediaObject':
                $this->mediaObjects[] = $a_attribs;
                break;
        }
    }

    /**
     * handler for end of element
     *
     * @param	resource	$a_xml_parser		xml parser
     * @param	string		$a_name				element name
     */
    public function handlerEndTag($a_xml_parser, $a_name)
    {
        $this->cdata = trim($this->cdata);
        $arrayname = strtolower($this->entity) . 'Array';
        $x = &$this->$arrayname;

        switch ($a_name) {
            case 'Forum':
                $query_num_posts = "SELECT COUNT(pos_pk) cnt
										FROM frm_posts
									WHERE pos_top_fk = " . $this->db->quote(
                    $this->lastHandledForumId,
                    'integer'
                );

                $res_pos = $this->db->query($query_num_posts);
                $data_pos = $this->db->fetchAssoc($res_pos);
                $num_posts = $data_pos['cnt'];

                $query_num_threads = "SELECT COUNT(thr_pk) cnt
										FROM frm_threads
									  WHERE thr_top_fk = " . $this->db->quote(
                    $this->lastHandledForumId,
                    'integer'
                );

                $res_thr = $this->db->query($query_num_threads);
                $data_thr = $this->db->fetchAssoc($res_thr);
                $num_threads = $data_thr['cnt'];

                $update_str = "$this->lastHandledForumId#$this->lastHandledThreadId#$this->lastHandledPostId";
                $this->db->manipulateF(
                    "UPDATE frm_data 
						SET top_last_post = %s,
							top_num_posts = %s,
							top_num_threads = %s,
							top_usr_id = %s
					WHERE top_frm_fk = %s",
                    array('text', 'integer', 'integer', 'integer', 'integer'),
                    array($update_str, $num_posts, $num_threads, $this->frm_last_mapped_top_usr_id, $this->forum_obj_id)
                );
                break;

            case 'Id':
                $x['Id'] = $this->cdata;
                break;
            
            case 'ObjId':
                $x['ObjId'] = $this->cdata;
                break;
            
            case 'Title':
                $x['Title'] = $this->cdata;
                break;
            
            case 'Description':
                $x['Description'] = $this->cdata;
                break;
            
            case 'DefaultView':
                $x['DefaultView'] = $this->cdata;
                break;
            
            case 'Pseudonyms':
                $x['Pseudonyms'] = $this->cdata;
                break;
            
            case 'Statistics':
                $x['Statistics'] = $this->cdata;
                break;
            
            case 'ThreadRatings':
                $x['ThreadRatings'] = $this->cdata;
                break;
            
            case 'PostingActivation':
                $x['PostingActivation'] = $this->cdata;
                break;
            
            case 'PresetSubject':
                $x['PresetSubject'] = $this->cdata;
                break;
            
            case 'PresetRe':
                $x['PresetRe'] = $this->cdata;
                break;
            
            case 'NotificationType':
                $x['NotificationType'] = $this->cdata;
                break;
            
            case 'ForceNotification':
                $x['ForceNotification'] = $this->cdata;
                break;
            
            case 'ToggleNotification':
                $x['ToggleNotification'] = $this->cdata;
                break;
            
            case 'LastPost':
                $x['LastPost'] = $this->cdata;
                break;
            
            case 'Moderator':
                $x['Moderator'] = $this->cdata;
                break;
            
            case 'CreateDate':
                $x['CreateDate'] = $this->cdata;
                break;
            
            case 'UpdateDate':
                $x['UpdateDate'] = $this->cdata;
                break;

            case 'FileUpload':
                $x['FileUpload'] = $this->cdata;
                break;

            case 'UpdateUserId':
                $x['UpdateUserId'] = $this->cdata;
                break;

            case 'AuthorId':
                $x['AuthorId'] = $this->cdata;
                break;
            case 'isAuthorModerator':
                $x['isAuthorModerator'] = $this->cdata;
                break;
            
            case 'UserId':
                $x['UserId'] = $this->cdata;
                if ($this->entity == 'forum' && $this->forumArray) {
                    //// @todo: Maybe problems if the forum xml is imported as content of a course
                    // createSettings accesses superglobal $_GET  array, which can cause problems
                    // with public_notifications of block settings
                    $this->forum->createSettings();

                    $forum_array = $this->getUserIdAndAlias(
                        $this->forumArray['UserId'],
                        ''
                    );

                    $this->frm_last_mapped_top_usr_id = $forum_array['usr_id'];

                    $update_forum_array = $this->getUserIdAndAlias(
                        $this->forumArray['UpdateUserId'],
                        ''
                    );
                    // Store old user id
                    // Manipulate user object
                    // changed smeyer 28.7.16: the session id is not manipulated
                    // anymore. Instead the user is passwd ilObjForum::update()
                    $this->forum->setTitle($this->forumArray["Title"]);
                    $this->forum->setDescription($this->forumArray["Description"]);
                    $this->forum->update($update_forum_array['usr_id']);

                    // create frm_settings
                    $newObjProp = ilForumProperties::getInstance($this->forum->getId());
                    $newObjProp->setDefaultView((int) $this->forumArray['DefaultView']);
                    $newObjProp->setAnonymisation((int) $this->forumArray['Pseudonyms']);
                    $newObjProp->setStatisticsStatus((int) $this->forumArray['Statistics']);
                    $newObjProp->setIsThreadRatingEnabled((int) $this->forumArray['ThreadRatings']);
                    $newObjProp->setPostActivation((int) $this->forumArray['PostingActivation']);
                    $newObjProp->setPresetSubject((int) $this->forumArray['PresetSubject']);
                    $newObjProp->setAddReSubject((int) $this->forumArray['PresetRe']);
                    $newObjProp->setNotificationType($this->forumArray['NotificationType'] ? $this->forumArray['NotificationType'] : 'all_users');
                    $newObjProp->setAdminForceNoti((int) $this->forumArray['ForceNotification']);
                    $newObjProp->setUserToggleNoti((int) $this->forumArray['ToggleNotification']);
                    $newObjProp->setFileUploadAllowed((int) $this->forumArray['FileUpload']);
                    $newObjProp->setThreadSorting($this->forumArray['Sorting']);
                    $newObjProp->setMarkModeratorPosts($this->forumArray['MarkModeratorPosts']);
                    $newObjProp->update();

                    $id = $this->getNewForumPk();
                    $this->forum_obj_id = $newObjProp->getObjId();
                    $this->mapping['frm'][$this->forumArray['Id']] = $id;
                    $this->lastHandledForumId = $id;

                    unset($this->forumArray);
                }
                
                break;

            case 'Thread':
                $update_str = "$this->lastHandledForumId#$this->lastHandledThreadId#$this->lastHandledPostId";
                $this->db->manipulateF(
                    "UPDATE frm_threads
						SET thr_last_post = %s
					WHERE thr_pk = %s",
                    array('text', 'integer'),
                    array($update_str, $this->lastHandledThreadId)
                );
                break;
            
            case 'Subject':
                $x['Subject'] = $this->cdata;
                break;

            case 'Alias':
                $x['Alias'] = $this->cdata;
                break;

            case 'Sticky':
                $x['Sticky'] = $this->cdata;
                break;

            case 'Sorting':
                $x['Sorting'] = $this->cdata;
                break;

            case 'MarkModeratorPosts':
                $x['MarkModeratorPosts'] = $this->cdata;
                break;

            case 'Closed':
                $x['Closed'] = $this->cdata;

                if ($this->entity == 'thread' && $this->threadArray) {
                    $this->forumThread = new ilForumTopic();
                    $this->forumThread->setId($this->threadArray['Id']);
                    $this->forumThread->setForumId($this->lastHandledForumId);
                    $this->forumThread->setSubject($this->threadArray['Subject']);
                    $this->forumThread->setSticky($this->threadArray['Sticky']);
                    $this->forumThread->setClosed($this->threadArray['Closed']);
                    $this->forumThread->setCreateDate($this->threadArray['CreateDate']);
                    $this->forumThread->setChangeDate($this->threadArray['UpdateDate']);
                    $this->forumThread->setImportName($this->threadArray['ImportName']);
                    
                    $usr_data = $this->getUserIdAndAlias(
                        $this->threadArray['UserId'],
                        $this->threadArray['Alias']
                    );

                    $this->forumThread->setDisplayUserId($usr_data['usr_id']);
                    $this->forumThread->setUserAlias($usr_data['usr_alias']);

                    if (version_compare($this->getSchemaVersion(), '4.5.0', '<=')) {
                        $this->threadArray['AuthorId'] = $this->threadArray['UserId'];
                    }

                    $author_id_data = $this->getUserIdAndAlias(
                        $this->threadArray['AuthorId']
                    );
                    $this->forumThread->setThrAuthorId((int) $author_id_data['usr_id']);
                
                    $this->forumThread->insert();

                    $this->mapping['thr'][$this->threadArray['Id']] = $this->forumThread->getId();
                    $this->lastHandledThreadId = $this->forumThread->getId();

                    unset($this->threadArray);
                }
                
                break;

            case 'Post':
                break;

            case 'Censorship':
                $x['Censorship'] = $this->cdata;
                break;

            case 'CensorshipMessage':
                $x['CensorshipMessage'] = $this->cdata;
                break;

            case 'Notification':
                $x['Notification'] = $this->cdata;
                break;

            case 'ImportName':
                $x['ImportName'] = $this->cdata;
                break;

            case 'Status':
                $x['Status'] = $this->cdata;
                break;

            case 'Message':
                $x['Message'] = $this->cdata;
                break;

            case 'Lft':
                $x['Lft'] = $this->cdata;
                break;

            case 'Rgt':
                $x['Rgt'] = $this->cdata;
                break;

            case 'Depth':
                $x['Depth'] = $this->cdata;
                break;

            case 'ParentId':
                $x['ParentId'] = $this->cdata;

                if ($this->entity == 'post' && $this->postArray) {
                    $this->forumPost = new ilForumPost();
                    $this->forumPost->setId($this->postArray['Id']);
                    $this->forumPost->setCensorship($this->postArray['Censorship']);
                    $this->forumPost->setCensorshipComment($this->postArray['CensorshipMessage']);
                    $this->forumPost->setNotification($this->postArray['Notification']);
                    $this->forumPost->setImportName($this->postArray['ImportName']);
                    $this->forumPost->setStatus($this->postArray['Status']);
                    $this->forumPost->setMessage($this->postArray['Message']);
                    $this->forumPost->setSubject($this->postArray['Subject']);
                    $this->forumPost->setLft($this->postArray['Lft']);
                    $this->forumPost->setRgt($this->postArray['Rgt']);
                    $this->forumPost->setDepth($this->postArray['Depth']);
                    $this->forumPost->setParentId($this->postArray['ParentId']);
                    $this->forumPost->setThread($this->forumThread);
                    $this->forumPost->setThreadId($this->lastHandledThreadId);
                    $this->forumPost->setForumId($this->lastHandledForumId);
                    $this->forumPost->setCreateDate($this->postArray['CreateDate']);
                    $this->forumPost->setChangeDate($this->postArray['UpdateDate']);

                    $usr_data = $this->getUserIdAndAlias(
                        $this->postArray['UserId'],
                        $this->postArray['Alias']
                    );
                    $update_usr_data = $this->getUserIdAndAlias(
                        $this->postArray['UpdateUserId']
                    );
                    $this->forumPost->setDisplayUserId($usr_data['usr_id']);
                    $this->forumPost->setUserAlias($usr_data['usr_alias']);
                    $this->forumPost->setUpdateUserId($update_usr_data['usr_id']);

                    if (version_compare($this->getSchemaVersion(), '4.5.0', '<=')) {
                        $this->postArray['AuthorId'] = $this->postArray['UserId'];
                    }
                    $author_id_data = $this->getUserIdAndAlias(
                        $this->postArray['AuthorId']
                    );
                    $this->forumPost->setPosAuthorId((int) $author_id_data['usr_id']);
                    
                    if ($this->postArray['isAuthorModerator'] === 'NULL') {
                        $this->forumPost->setIsAuthorModerator(null);
                    } else {
                        $this->forumPost->setIsAuthorModerator((int) $this->postArray['isAuthorModerator']);
                    }
                    
                    $this->forumPost->insert();

                    if ($this->mapping['pos'][$this->postArray['ParentId']]) {
                        $parentId = $this->mapping['pos'][$this->postArray['ParentId']];
                    } else {
                        $parentId = 0;
                    }

                    $postTreeNodeId = $this->db->nextId('frm_posts_tree');
                    $this->db->insert('frm_posts_tree', array(
                        'fpt_pk' => array('integer', $postTreeNodeId),
                        'thr_fk' => array('integer', $this->lastHandledThreadId),
                        'pos_fk' => array('integer', $this->forumPost->getId()),
                        'parent_pos' => array('integer', $parentId),
                        'lft' => array('integer', $this->postArray['Lft']),
                        'rgt' => array('integer', $this->postArray['Rgt']),
                        'depth' => array('integer', $this->postArray['Depth']),
                        'fpt_date' => array('timestamp', date('Y-m-d H:i:s'))
                    ));

                    $this->mapping['pos'][$this->postArray['Id']] = $this->forumPost->getId();
                    $this->lastHandledPostId = $this->forumPost->getId();

                    $media_objects_found = false;
                    foreach ($this->mediaObjects as $mob_attr) {
                        $importfile = $this->getImportDirectory() . '/' . $mob_attr['uri'];
                        if (file_exists($importfile)) {
                            $mob = ilObjMediaObject::_saveTempFileAsMediaObject(basename($importfile), $importfile, false);
                            ilObjMediaObject::_saveUsage($mob->getId(), "frm:html", $this->forumPost->getId());

                            $this->forumPost->setMessage(
                                str_replace(
                                    array(
                                        "src=\"" . $mob_attr["label"] . "\"",
                                        "src=\"" . preg_replace("/(il)_[\d]+_(mob)_([\d]+)/", "$1_0_$2_$3", $mob_attr["label"]) . "\""
                                    ),
                                    "src=\"" . "il_" . IL_INST_ID . "_mob_" . $mob->getId() . "\"",
                                    $this->forumPost->getMessage()
                                )
                            );
                            $media_objects_found = true;
                        }
                    }
                    
                    if ($media_objects_found) {
                        $this->forumPost->update();
                    }

                    unset($this->postArray);
                }

                break;

                case 'Content':
                    $x['content'] = $this->cdata;
                    break;

                case 'Attachment':
                    $filedata = new ilFileDataForum($this->forum->getId(), $this->lastHandledPostId);

                    $importPath = $this->contentArray['content'];

                    if (strlen($importPath)) {
                        $importPath = $this->getImportDirectory() . '/' . $importPath;

                        $newFilename = preg_replace("/^\d+_\d+(_.*)/ims", $this->forum->getId() . "_" . $this->lastHandledPostId . "$1", basename($importPath));
                        $path = $filedata->getForumPath();
                        $newPath = $path . '/' . $newFilename;
                        @copy($importPath, $newPath);
                    }
                    break;
        }

        $this->cdata = '';

        return;
    }

    private function getIdAndAliasArray($imp_usr_id, $param = 'import')
    {
        $select = 'SELECT od.obj_id, ud.login
					FROM object_data od
						INNER JOIN usr_data ud
							ON od.obj_id = ud.usr_id';

        if ($param == 'import') {
            $where = ' WHERE od.import_id = ' . $this->db->quote(
                'il_' . $this->import_install_id . '_usr_' . $imp_usr_id,
                'text'
            );
        }

        if ($param == 'user') {
            $where = ' WHERE ud.usr_id = ' . $this->db->quote(
                $imp_usr_id,
                'integer'
            );
        }

        $query = $this->db->query($select . $where);

        while ($res = $this->db->fetchAssoc($query)) {
            break;
        }

        if ($res) {
            return array(
                'usr_id' => $res['obj_id'],
                'usr_alias' => $res['login']
            );
        } else {
            return false;
        }
    }

    private function getAnonymousArray()
    {
        return array(
            'usr_id' => $this->aobject->getId(),
            'usr_alias' => $this->aobject->getLogin()
        );
    }


    private function getUserIdAndAlias($imp_usr_id, $imp_usr_alias = '')
    {
        if ((int) $imp_usr_id > 0) {
            $newUsrId = -1;
            
            if ($this->import_install_id != IL_INST_ID && IL_INST_ID > 0) {
                // Different installations
                if ($this->user_id_mapping[$imp_usr_id]) {
                    return $this->user_id_mapping[$imp_usr_id];
                } else {
                    $res = $this->getIdAndAliasArray($imp_usr_id, 'import');
                    
                    if ($res) {
                        $this->user_id_mapping[$imp_usr_id] = $res;

                        return $res;
                    } else {
                        $return_value = $this->getAnonymousArray();
                        $this->user_id_mapping[$imp_usr_id] = $return_value;

                        return $return_value;
                    }
                }
            } elseif ($this->import_install_id == IL_INST_ID && IL_INST_ID == 0) {
                // Eventually different installations. We cannot determine it.
                if ($this->user_id_mapping[$imp_usr_id]) {
                    return $this->user_id_mapping[$imp_usr_id];
                } else {
                    $res = $this->getIdAndAliasArray($imp_usr_id, 'import');

                    if ($res) {
                        $this->user_id_mapping[$imp_usr_id] = $res;

                        return $res;
                    } else {
                        // Same installation
                        if ($this->user_id_mapping[$imp_usr_id]) {
                            return $this->user_id_mapping[$imp_usr_id];
                        } else {
                            $res = $this->getIdAndAliasArray($imp_usr_id, 'user');

                            if ($res) {
                                $this->user_id_mapping[$imp_usr_id] = $res;

                                return $res;
                            } else {
                                $return_value = $this->getAnonymousArray();
                                $this->user_id_mapping[$imp_usr_id] = $return_value;

                                return $return_value;
                            }
                        }
                    }
                }
            } else {
                // Same installation
                if ($this->user_id_mapping[$imp_usr_id]) {
                    return $this->user_id_mapping[$imp_usr_id];
                } else {
                    $res = $this->getIdAndAliasArray($imp_usr_id, 'user');

                    if ($res) {
                        $this->user_id_mapping[$imp_usr_id] = $res;

                        return $res;
                    } else {
                        $return_value = $this->getAnonymousArray();
                        $this->user_id_mapping[$imp_usr_id] = $return_value;

                        return $return_value;
                    }
                }
            }
        } else {
            return array(
                'usr_id' => $imp_usr_id,
                'usr_alias' => $imp_usr_alias
            );
        }
    }

    public function setImportInstallId($id)
    {
        $this->import_install_id = $id;
    }

    private function getNewForumPk()
    {
        $query = "SELECT top_pk FROM frm_data
					WHERE top_frm_fk = " . $this->db->quote(
            $this->forum->getId(),
            'integer'
        );
        $res = $this->db->query($query);
        $data = $this->db->fetchAssoc($res);

        return $data['top_pk'];
    }

    /**
    * handler for character data
    *
    * @param	resource	$a_xml_parser		xml parser
    * @param	string		$a_data				character data
    */
    public function handlerCharacterData($a_xml_parser, $a_data)
    {
        if ($a_data != "\n") {
            // Replace multiple tabs with one space
            $a_data = preg_replace("/\t+/", " ", $a_data);

            $this->cdata .= $a_data;
        }
    }
    
    /**
     * starts parsing an changes object by side effect.
     *
     * @return boolean true, if no errors happend.
     *
     */
    public function start()
    {
        $this->startParsing();
        return $this->result > 0;
    }
}
