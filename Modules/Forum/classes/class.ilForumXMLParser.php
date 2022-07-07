<?php declare(strict_types=1);

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

class ilForumXMLParser extends ilSaxParser
{
    private ilObjForum $forum;
    private string $entity = '';
    private array $mapping = [
        'frm' => [],
        'thr' => [],
        'pos' => []
    ];
    private ilDBInterface $db;
    private ilObjUser $aobject;
    /** @var null|string|int  */
    private $import_install_id = null;
    private ?string $importDirectory = null;
    private ?string $schema_version = null;
    private string $cdata = '';

    private ?ilForumTopic $forumThread = null;
    private ?ilForumPost $forumPost = null;
    private ?int $forum_obj_id = null;
    private ?int $frm_last_mapped_top_usr_id = null;
    private ?int $lastHandledForumId = null;
    private ?int $lastHandledThreadId = null;
    private ?int $lastHandledPostId = null;
    private array $forumArray = [];
    private array $postArray = [];
    private array $threadArray = [];
    private array $contentArray = [
        'content' => ''
    ];
    private array $user_id_mapping = [];
    private array $mediaObjects = [];
    private ilImportMapping $importMapping;

    public function __construct(ilObjForum $forum, string $a_xml_data, ilImportMapping $importMapping)
    {
        global $DIC;

        $this->db = $DIC->database();
        $this->aobject = new ilObjUser(ANONYMOUS_USER_ID);

        $this->forum = $forum;
        $this->importMapping = $importMapping;

        parent::__construct();

        $this->setXMLContent('<?xml version="1.0" encoding="utf-8"?>' . $a_xml_data);
    }

    public function setImportDirectory(?string $a_val) : void
    {
        $this->importDirectory = $a_val;
    }

    public function getImportDirectory() : ?string
    {
        return $this->importDirectory;
    }

    public function getSchemaVersion() : ?string
    {
        return $this->schema_version;
    }

    public function setSchemaVersion(?string $schema_version) : void
    {
        $this->schema_version = $schema_version;
    }

    public function setHandlers($a_xml_parser) : void
    {
        xml_set_object($a_xml_parser, $this);
        xml_set_element_handler($a_xml_parser, [$this, 'handlerBeginTag'], [$this, 'handlerEndTag']);
        xml_set_character_data_handler($a_xml_parser, [$this, 'handlerCharacterData']);
    }

    /**
     * @param XMLParser|resource $a_xml_parser
     * @param string $a_name
     * @param array  $a_attribs
     * @return void
     */
    public function handlerBeginTag($a_xml_parser, string $a_name, array $a_attribs) : void
    {
        switch ($a_name) {
            case 'Forum':
                $this->entity = 'forum';
                $this->forumArray = [];
                break;

            case 'Thread':
                $this->entity = 'thread';
                $this->threadArray = [];
                break;

            case 'Post':
                $this->entity = 'post';
                $this->postArray = [];
                $this->mediaObjects = [];
                break;

            case 'Content':
                $this->entity = 'content';
                $this->contentArray = [
                    'content' => ''
                ];
                break;

            case 'MediaObject':
                $this->mediaObjects[] = $a_attribs;
                break;
        }
    }

    /**
     * @param XMLParser|resource $a_xml_parser
     * @param string $a_name
     * @return void
     */
    public function handlerEndTag($a_xml_parser, string $a_name) : void
    {
        $this->cdata = trim($this->cdata);
        $property = strtolower($this->entity) . 'Array';
        
        if (!property_exists($this, $property)) {
            return;
        }

        $propertyValue = &$this->{$property};

        switch ($a_name) {
            case 'Forum':
                $query_num_posts = "SELECT COUNT(pos_pk) cnt FROM frm_posts WHERE pos_top_fk = " . $this->db->quote(
                    $this->lastHandledForumId,
                    'integer'
                );
                $res_pos = $this->db->query($query_num_posts);
                $data_pos = $this->db->fetchAssoc($res_pos);
                $num_posts = (int) $data_pos['cnt'];

                $query_num_threads = "SELECT COUNT(thr_pk) cnt FROM frm_threads WHERE thr_top_fk = " . $this->db->quote(
                    $this->lastHandledForumId,
                    'integer'
                );
                $res_thr = $this->db->query($query_num_threads);
                $data_thr = $this->db->fetchAssoc($res_thr);
                $num_threads = (int) $data_thr['cnt'];

                $update_str = null;
                if ($this->lastHandledPostId !== 0) {
                    $update_str = implode('#', [
                        (string) $this->lastHandledForumId,
                        (string) $this->lastHandledThreadId,
                        (string) $this->lastHandledPostId
                    ]);
                }

                $this->db->manipulateF(
                    "UPDATE frm_data 
                        SET top_last_post = %s,
                            top_num_posts = %s,
                            top_num_threads = %s,
                            top_usr_id = %s
                    WHERE top_frm_fk = %s",
                    ['text', 'integer', 'integer', 'integer', 'integer'],
                    [$update_str, $num_posts, $num_threads, $this->frm_last_mapped_top_usr_id, $this->forum_obj_id]
                );

                ilLPStatusWrapper::_refreshStatus($this->forum->getId());
                break;

            case 'Id':
                $propertyValue['Id'] = $this->cdata;
                break;

            case 'StyleId':
                $x['StyleId'] = $this->cdata;
                break;

            case 'ObjId':
                $propertyValue['ObjId'] = $this->cdata;
                break;

            case 'Title':
                $propertyValue['Title'] = $this->cdata;
                break;

            case 'Description':
                $propertyValue['Description'] = $this->cdata;
                break;

            case 'DefaultView':
                $propertyValue['DefaultView'] = $this->cdata;
                break;

            case 'Pseudonyms':
                $propertyValue['Pseudonyms'] = $this->cdata;
                break;

            case 'Statistics':
                $propertyValue['Statistics'] = $this->cdata;
                break;

            case 'ThreadRatings':
                $propertyValue['ThreadRatings'] = $this->cdata;
                break;

            case 'PostingActivation':
                $propertyValue['PostingActivation'] = $this->cdata;
                break;

            case 'PresetSubject':
                $propertyValue['PresetSubject'] = $this->cdata;
                break;

            case 'PresetRe':
                $propertyValue['PresetRe'] = $this->cdata;
                break;

            case 'NotificationType':
                $propertyValue['NotificationType'] = $this->cdata;
                break;

            case 'ForceNotification':
                $propertyValue['ForceNotification'] = $this->cdata;
                break;

            case 'ToggleNotification':
                $propertyValue['ToggleNotification'] = $this->cdata;
                break;

            case 'LastPost':
                $propertyValue['LastPost'] = $this->cdata;
                break;

            case 'Moderator':
                $propertyValue['Moderator'] = $this->cdata;
                break;

            case 'CreateDate':
                $propertyValue['CreateDate'] = $this->cdata;
                break;

            case 'UpdateDate':
                $propertyValue['UpdateDate'] = $this->cdata;
                break;

            case 'FileUpload':
                $propertyValue['FileUpload'] = $this->cdata;
                break;

            case 'UpdateUserId':
                $propertyValue['UpdateUserId'] = $this->cdata;
                break;

            case 'AuthorId':
                $propertyValue['AuthorId'] = $this->cdata;
                break;
            case 'isAuthorModerator':
                $propertyValue['isAuthorModerator'] = $this->cdata;
                break;

            case 'UserId':
                $propertyValue['UserId'] = $this->cdata;
                if ($this->entity === 'forum' && $this->forumArray !== []) {
                    //// @todo: Maybe problems if the forum xml is imported as content of a course
                    // createSettings accesses superglobal $_GET  array, which can cause problems
                    // with public_notifications of block settings
                    $this->forum->createSettings();

                    $forum_array = $this->getUserIdAndAlias(
                        (int) ($this->forumArray['UserId'] ?? 0),
                        ''
                    );
                    $this->frm_last_mapped_top_usr_id = $forum_array['usr_id'];

                    $update_forum_array = $this->getUserIdAndAlias(
                        (int) ($this->forumArray['UpdateUserId'] ?? 0),
                        ''
                    );
                    // Store old user id
                    // Manipulate user object
                    // changed smeyer 28.7.16: the session id is not manipulated
                    // anymore. Instead the user is passwd ilObjForum::update()
                    $this->forum->setTitle(ilUtil::stripSlashes((string) ($this->forumArray["Title"] ?? '')));
                    $this->forum->setDescription(ilUtil::stripSlashes((string) ($this->forumArray["Description"] ?? '')));
                    $this->forum->update();
                    $this->forum->updateMoficationUserId($update_forum_array['usr_id']);

                    $newObjProp = ilForumProperties::getInstance($this->forum->getId());
                    $newObjProp->setDefaultView((int) ($this->forumArray['DefaultView'] ?? ilForumProperties::VIEW_TREE));
                    $newObjProp->setAnonymisation((bool) ($this->forumArray['Pseudonyms'] ?? false));
                    $newObjProp->setStatisticsStatus((bool) ($this->forumArray['Statistics'] ?? false));
                    $newObjProp->setIsThreadRatingEnabled((bool) ($this->forumArray['ThreadRatings'] ?? false));
                    $newObjProp->setPostActivation((bool) ($this->forumArray['PostingActivation'] ?? false));
                    $newObjProp->setPresetSubject((bool) ($this->forumArray['PresetSubject'] ?? false));
                    $newObjProp->setAddReSubject((bool) ($this->forumArray['PresetRe'] ?? false));
                    $newObjProp->setNotificationType((string) ($this->forumArray['NotificationType'] ?: 'all_users'));
                    $newObjProp->setAdminForceNoti((bool) ($this->forumArray['ForceNotification'] ?? false));
                    $newObjProp->setUserToggleNoti((bool) ($this->forumArray['ToggleNotification'] ?? false));
                    $newObjProp->setFileUploadAllowed((bool) ($this->forumArray['FileUpload'] ?? false));
                    $newObjProp->setThreadSorting((int) ($this->forumArray['Sorting'] ?? ilForumProperties::THREAD_SORTING_DEFAULT));
                    $newObjProp->setMarkModeratorPosts((bool) ($this->forumArray['MarkModeratorPosts'] ?? false));
                    $newObjProp->update();

                    $id = $this->getNewForumPk();
                    $this->forum_obj_id = $newObjProp->getObjId();
                    $this->mapping['frm'][$this->forumArray['Id']] = $id;
                    $this->lastHandledForumId = $id;

                    $this->importMapping->addMapping(
                        'Services/COPage',
                        'pg',
                        'frm:' . $this->forumArray['ObjId'],
                        'frm:' . $this->forum->getId()
                    );

                    $this->forumArray = [];
                }
                break;

            case 'Thread':
                $update_str = null;
                if ($this->lastHandledPostId !== 0) {
                    $update_str = implode('#', [
                        (string) $this->lastHandledForumId,
                        (string) $this->lastHandledThreadId,
                        (string) $this->lastHandledPostId
                    ]);
                }

                $this->db->manipulateF(
                    "UPDATE frm_threads SET thr_last_post = %s WHERE thr_pk = %s",
                    ['text', 'integer'],
                    [$update_str, $this->lastHandledThreadId]
                );
                break;

            case 'Subject':
                $propertyValue['Subject'] = $this->cdata;
                break;

            case 'Alias':
                $propertyValue['Alias'] = $this->cdata;
                break;

            case 'Sticky':
                $propertyValue['Sticky'] = $this->cdata;
                break;

            case 'Sorting':
                $propertyValue['Sorting'] = $this->cdata;
                break;

            case 'MarkModeratorPosts':
                $propertyValue['MarkModeratorPosts'] = $this->cdata;
                break;

            case 'Closed':
                $propertyValue['Closed'] = $this->cdata;

                if ($this->entity === 'thread' && $this->lastHandledForumId && $this->threadArray !== []) {
                    $this->forumThread = new ilForumTopic();
                    $this->forumThread->setId((int) ($this->threadArray['Id'] ?? 0));
                    $this->forumThread->setForumId($this->lastHandledForumId);
                    $this->forumThread->setSubject(ilUtil::stripSlashes((string) ($this->threadArray['Subject'] ?? '')));
                    $this->forumThread->setSticky((bool) ($this->threadArray['Sticky'] ?? false));
                    $this->forumThread->setClosed((bool) ($this->threadArray['Closed'] ?? false));

                    $this->forumThread->setImportName(
                        isset($this->threadArray['ImportName']) ?
                            ilUtil::stripSlashes($this->threadArray['ImportName']) :
                            null
                    );
                    $this->forumThread->setCreateDate($this->threadArray['CreateDate']);
                    $this->forumThread->setChangeDate($this->threadArray['UpdateDate']);

                    $usr_data = $this->getUserIdAndAlias(
                        (int) ($this->threadArray['UserId'] ?? 0),
                        ilUtil::stripSlashes((string) ($this->threadArray['Alias'] ?? ''))
                    );

                    $this->forumThread->setDisplayUserId($usr_data['usr_id']);
                    $this->forumThread->setUserAlias($usr_data['usr_alias']);

                    if (version_compare($this->getSchemaVersion(), '4.5.0', '<=')) {
                        $this->threadArray['AuthorId'] = $this->threadArray['UserId'];
                    }

                    $author_id_data = $this->getUserIdAndAlias(
                        (int) ($this->threadArray['AuthorId'] ?? 0)
                    );
                    $this->forumThread->setThrAuthorId($author_id_data['usr_id']);

                    $this->forumThread->insert();

                    $this->mapping['thr'][$this->threadArray['Id']] = $this->forumThread->getId();
                    $this->lastHandledThreadId = $this->forumThread->getId();
                    $this->threadArray = [];
                }
                break;

            case 'Post':
                break;

            case 'Censorship':
                $propertyValue['Censorship'] = $this->cdata;
                break;

            case 'CensorshipMessage':
                $propertyValue['CensorshipMessage'] = $this->cdata;
                break;

            case 'Notification':
                $propertyValue['Notification'] = $this->cdata;
                break;

            case 'ImportName':
                $propertyValue['ImportName'] = $this->cdata;
                break;

            case 'Status':
                $propertyValue['Status'] = $this->cdata;
                break;

            case 'Message':
                $propertyValue['Message'] = $this->cdata;
                break;

            case 'Lft':
                $propertyValue['Lft'] = $this->cdata;
                break;

            case 'Rgt':
                $propertyValue['Rgt'] = $this->cdata;
                break;

            case 'Depth':
                $propertyValue['Depth'] = $this->cdata;
                break;

            case 'ParentId':
                $propertyValue['ParentId'] = $this->cdata;

                if (
                    $this->entity === 'post' &&
                    $this->lastHandledForumId &&
                    $this->postArray !== [] &&
                    $this->forumThread &&
                    $this->lastHandledThreadId
                ) {
                    $this->forumPost = new ilForumPost();
                    $this->forumPost->setThread($this->forumThread);

                    $this->forumPost->setId((int) $this->postArray['Id']);
                    $this->forumPost->setCensorship((bool) ($this->postArray['Censorship'] ?? false));
                    $this->forumPost->setCensorshipComment(
                        ilUtil::stripSlashes((string) ($this->postArray['CensorshipMessage'] ?? ''))
                    );
                    $this->forumPost->setNotification((bool) ($this->postArray['Notification'] ?? false));
                    $this->forumPost->setStatus((bool) ($this->postArray['Status'] ?? false));
                    $this->forumPost->setMessage(ilUtil::stripSlashes((string) ($this->postArray['Message'] ?? '')));
                    $this->forumPost->setSubject(ilUtil::stripSlashes((string) ($this->postArray['Subject'] ?? '')));
                    $this->forumPost->setLft((int) $this->postArray['Lft']);
                    $this->forumPost->setRgt((int) $this->postArray['Rgt']);
                    $this->forumPost->setDepth((int) $this->postArray['Depth']);
                    $this->forumPost->setParentId((int) $this->postArray['ParentId']);
                    $this->forumPost->setThreadId($this->lastHandledThreadId);
                    $this->forumPost->setForumId($this->lastHandledForumId);

                    $this->forumPost->setImportName(
                        isset($this->postArray['ImportName']) ?
                            ilUtil::stripSlashes($this->postArray['ImportName']) :
                            null
                    );
                    $this->forumPost->setCreateDate($this->postArray['CreateDate']);
                    $this->forumPost->setChangeDate($this->postArray['UpdateDate']);

                    $usr_data = $this->getUserIdAndAlias(
                        (int) ($this->postArray['UserId'] ?? 0),
                        ilUtil::stripSlashes((string) ($this->postArray['Alias'] ?? ''))
                    );
                    $update_usr_data = $this->getUserIdAndAlias(
                        (int) ($this->postArray['UpdateUserId'] ?? 0)
                    );
                    $this->forumPost->setDisplayUserId($usr_data['usr_id']);
                    $this->forumPost->setUserAlias($usr_data['usr_alias']);
                    $this->forumPost->setUpdateUserId($update_usr_data['usr_id']);

                    if (version_compare($this->getSchemaVersion(), '4.5.0', '<=')) {
                        $this->postArray['AuthorId'] = $this->postArray['UserId'];
                    }
                    $author_id_data = $this->getUserIdAndAlias(
                        (int) ($this->postArray['AuthorId'] ?? 0)
                    );
                    $this->forumPost->setPosAuthorId((int) $author_id_data['usr_id']);

                    if (isset($this->postArray['isAuthorModerator']) && $this->postArray['isAuthorModerator'] === 'NULL') {
                        $this->forumPost->setIsAuthorModerator(false);
                    } else {
                        $this->forumPost->setIsAuthorModerator((bool) $this->postArray['isAuthorModerator']);
                    }

                    $this->forumPost->insert();

                    if (isset($this->postArray['ParentId'], $this->mapping['pos'][$this->postArray['ParentId']])) {
                        $parentId = (int) $this->mapping['pos'][$this->postArray['ParentId']];
                    } else {
                        $parentId = 0;
                    }

                    $postTreeNodeId = $this->db->nextId('frm_posts_tree');
                    $this->db->insert('frm_posts_tree', [
                        'fpt_pk' => ['integer', $postTreeNodeId],
                        'thr_fk' => ['integer', $this->lastHandledThreadId],
                        'pos_fk' => ['integer', $this->forumPost->getId()],
                        'parent_pos' => ['integer', $parentId],
                        'lft' => ['integer', $this->postArray['Lft']],
                        'rgt' => ['integer', $this->postArray['Rgt']],
                        'depth' => ['integer', $this->postArray['Depth']],
                        'fpt_date' => ['timestamp', date('Y-m-d H:i:s')]
                    ]);

                    $this->mapping['pos'][$this->postArray['Id']] = $this->forumPost->getId();
                    $this->lastHandledPostId = $this->forumPost->getId();

                    $media_objects_found = false;
                    foreach ($this->mediaObjects as $mob_attr) {
                        $importfile = $this->getImportDirectory() . '/' . $mob_attr['uri'];
                        if (is_file($importfile)) {
                            $mob = ilObjMediaObject::_saveTempFileAsMediaObject(
                                basename($importfile),
                                $importfile,
                                false
                            );
                            ilObjMediaObject::_saveUsage($mob->getId(), "frm:html", $this->forumPost->getId());

                            $this->forumPost->setMessage(
                                str_replace(
                                    [
                                        "src=\"" . $mob_attr["label"] . "\"",
                                        "src=\"" . preg_replace(
                                            "/(il)_[\d]+_(mob)_([\d]+)/",
                                            "$1_0_$2_$3",
                                            $mob_attr["label"]
                                        ) . "\""
                                    ],
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
                    $this->postArray = [];
                }

                break;

            case 'Content':
                $propertyValue['content'] = $this->cdata;
                break;

            case 'Attachment':
                $filedata = new ilFileDataForum($this->forum->getId(), $this->lastHandledPostId);

                $importPath = $this->contentArray['content'];

                if ($importPath !== '') {
                    $importPath = $this->getImportDirectory() . '/' . $importPath;

                    $newFilename = preg_replace(
                        "/^\d+_\d+(_.*)/ms",
                        $this->forum->getId() . "_" . $this->lastHandledPostId . "$1",
                        basename($importPath)
                    );
                    $path = $filedata->getForumPath();
                    $newPath = $path . '/' . $newFilename;
                    @copy($importPath, $newPath);
                }
                break;
        }

        $this->cdata = '';
    }

    /**
     * @param int $imp_usr_id
     * @param string $param
     * @return array|array{usr_id: int, usr_alias: string}
     */
    private function getIdAndAliasArray(int $imp_usr_id, string $param = 'import') : array
    {
        $where = '';
        $select = 'SELECT od.obj_id, ud.login FROM object_data od INNER JOIN usr_data ud ON od.obj_id = ud.usr_id';
        if ($param === 'import') {
            $where = ' WHERE od.import_id = ' . $this->db->quote(
                'il_' . $this->import_install_id . '_usr_' . $imp_usr_id,
                'text'
            );
        }

        if ($param === 'user') {
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
            return [
                'usr_id' => (int) $res['obj_id'],
                'usr_alias' => (string) $res['login']
            ];
        }

        return [];
    }

    /**
     * @return array{usr_id: int, usr_alias: string}
     */
    private function getAnonymousArray() : array
    {
        return [
            'usr_id' => $this->aobject->getId(),
            'usr_alias' => $this->aobject->getLogin()
        ];
    }

    /**
     * @param int $imp_usr_id
     * @param string $imp_usr_alias
     * @return array{usr_id: int, usr_alias: string}
     */
    private function getUserIdAndAlias(int $imp_usr_id, string $imp_usr_alias = '') : array
    {
        if (!($imp_usr_id > 0)) {
            return [
                'usr_id' => $imp_usr_id,
                'usr_alias' => $imp_usr_alias
            ];
        }

        if ($this->import_install_id != IL_INST_ID && IL_INST_ID > 0) {
            // Different installations
            if (isset($this->user_id_mapping[$imp_usr_id])) {
                return $this->user_id_mapping[$imp_usr_id];
            }

            $res = $this->getIdAndAliasArray($imp_usr_id, 'import');
            if ($res !== []) {
                $this->user_id_mapping[$imp_usr_id] = $res;

                return $res;
            }

            $return_value = $this->getAnonymousArray();
            $this->user_id_mapping[$imp_usr_id] = $return_value;

            return $return_value;
        }

        if ($this->import_install_id == IL_INST_ID && IL_INST_ID == 0) {
            // Eventually different installations. We cannot determine it.
            if (isset($this->user_id_mapping[$imp_usr_id])) {
                return $this->user_id_mapping[$imp_usr_id];
            }

            $res = $this->getIdAndAliasArray($imp_usr_id, 'import');
            if ($res !== []) {
                $this->user_id_mapping[$imp_usr_id] = $res;

                return $res;
            }

            if (isset($this->user_id_mapping[$imp_usr_id])) {
                return $this->user_id_mapping[$imp_usr_id];
            }

            $res = $this->getIdAndAliasArray($imp_usr_id, 'user');
            if ($res !== []) {
                $this->user_id_mapping[$imp_usr_id] = $res;

                return $res;
            }

            $return_value = $this->getAnonymousArray();
            $this->user_id_mapping[$imp_usr_id] = $return_value;

            return $return_value;
        }

        if (isset($this->user_id_mapping[$imp_usr_id])) {
            return $this->user_id_mapping[$imp_usr_id];
        }

        $res = $this->getIdAndAliasArray($imp_usr_id, 'user');
        if ($res !== []) {
            $this->user_id_mapping[$imp_usr_id] = $res;

            return $res;
        }

        $return_value = $this->getAnonymousArray();
        $this->user_id_mapping[$imp_usr_id] = $return_value;

        return $return_value;
    }

    public function setImportInstallId($id) : void
    {
        $this->import_install_id = $id;
    }

    private function getNewForumPk() : int
    {
        $query = "SELECT top_pk FROM frm_data WHERE top_frm_fk = " . $this->db->quote(
            $this->forum->getId(),
            'integer'
        );
        $res = $this->db->query($query);
        $data = $this->db->fetchAssoc($res);

        return (int) $data['top_pk'];
    }

    /**
     * handler for character data
     * @param XMLParser|resource $a_xml_parser xml parser
     * @param string $a_data character data
     */
    public function handlerCharacterData($a_xml_parser, string $a_data) : void
    {
        if ($a_data !== "\n") {
            // Replace multiple tabs with one space
            $a_data = preg_replace("/\t+/", " ", $a_data);

            $this->cdata .= $a_data;
        }
    }
}
