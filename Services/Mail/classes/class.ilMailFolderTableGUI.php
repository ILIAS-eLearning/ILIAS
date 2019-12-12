<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\UI\Factory;
use ILIAS\UI\Renderer;

/**
 * @author  Jan Posselt <jposselt@databay.de>
 * @author  Michael Jansen <mjansen@databay.de>
 * @ingroup ServicesMail
 */
class ilMailFolderTableGUI extends ilTable2GUI
{
    protected $_folderNode = [];
    protected $_parentObject = null;
    protected $_currentFolderId = 0;
    protected $_number_of_mails = 0;
    protected $_selectedItems = [];
    protected $_isTrashFolder = false;
    protected $_isDraftsFolder = false;
    protected $_isSentFolder = false;

    /** @var \ilObjUser */
    protected $user;

    /** @var array */
    protected $filter = [];

    /** @var array */
    protected $sub_filter = [];

    /** @var array */
    protected $visibleOptionalColumns = [];

    /** @var array */
    protected $optionalColumns = [];

    /** @var array */
    protected $optional_filter = [];

    /** @var Factory|null */
    private $uiFactory;

    /** @var Renderer|null */
    private $uiRenderer;

    /**
     * Constructor
     * @param                    $a_parent_obj           Pass an instance of ilObjectGUI
     * @param integer $a_current_folder_id Id of the current mail box folder
     * @param string $a_parent_cmd Command for the parent class
     * @param Factory|null $uiFactory
     * @param Renderer|null $uiRenderer
     */
    public function __construct(
        $a_parent_obj,
        $a_current_folder_id,
        $a_parent_cmd = '',
        Factory $uiFactory = null,
        Renderer $uiRenderer = null
    ) {
        global $DIC;

        $this->user = $DIC->user();
        
        if (null === $uiFactory) {
            $uiFactory = $DIC->ui()->factory();
        }
        if (null === $uiRenderer) {
            $uiRenderer = $DIC->ui()->renderer();
        }
        $this->uiFactory = $uiFactory;
        $this->uiRenderer = $uiRenderer;

        $this->_currentFolderId = $a_current_folder_id;
        $this->_parentObject = $a_parent_obj;
        if (null === $uiFactory) {
            $uiFactory = $DIC->ui()->factory();
        }
        if (null === $uiRenderer) {
            $uiRenderer = $DIC->ui()->renderer();
        }
        $this->uiFactory = $uiFactory;
        $this->uiRenderer = $uiRenderer;

        $this->setId('mail_folder_tbl_' . $a_current_folder_id);
        $this->setPrefix('mtable');

        $this->setExternalSorting(true);
        $this->setExternalSegmentation(true);
        $this->setDefaultOrderField('send_time');
        $this->setDefaultOrderDirection('desc');

        parent::__construct($a_parent_obj, $a_parent_cmd);

        $this->ctrl->setParameter($this->getParentObject(), 'mobj_id', $this->_currentFolderId);
        $this->setFormAction($this->ctrl->getFormAction($this->getParentObject(), 'showFolder'));
        $this->ctrl->clearParameters($this->getParentObject());

        $this->setEnableTitle(true);
        $this->setSelectAllCheckbox('mail_id[]');
        $this->setRowTemplate('tpl.mail_folder_row.html', 'Services/Mail');

        $this->setFilterCommand('applyFilter');
        $this->setResetCommand('resetFilter');
    }

    /**
     * @return array
     */
    public function getSelectableColumns()
    {
        $optionalColumns = array_filter($this->getColumnDefinition(), function ($column) {
            return isset($column['optional']) && $column['optional'];
        });

        $columns = array();
        foreach ($optionalColumns as $index => $column) {
            $columns[$column['field']] = $column;
        }

        return $columns;
    }

    /**
     * @param int $index
     * @return bool
     */
    protected function isColumnVisible(int $index) : bool
    {
        $columnDefinition = $this->getColumnDefinition();
        if (array_key_exists($index, $columnDefinition)) {
            $column = $columnDefinition[$index];
            if (isset($column['optional']) && !$column['optional']) {
                return true;
            }
            if (
                is_array($this->visibleOptionalColumns) &&
                array_key_exists($column['field'], $this->visibleOptionalColumns)
            ) {
                return true;
            }
        }

        return false;
    }

    /**@inheritdoc
     */
    final protected function fillRow($a_set)
    {
        foreach ($this->removeInvisibleFields($a_set) as $key => $value) {
            $this->tpl->setVariable(strtoupper($key), $value);
        }
    }

    /**
     * @param array $row
     * @return array
     */
    protected function removeInvisibleFields(array $row) : array
    {
        if (is_array($this->visibleOptionalColumns)) {
            if (!array_key_exists('attachments', $this->visibleOptionalColumns)) {
                unset($row['attachment_indicator']);
            }

            if (!array_key_exists('personal_picture', $this->visibleOptionalColumns)) {
                unset($row['img_sender']);
                unset($row['alt_sender']);
            }
        }

        return $row;
    }

    /**
     * @return array
     */
    protected function getColumnDefinition() : array
    {
        $i = 0;

        $columns = [];

        $columns[++$i] = [
            'field' => 'chb',
            'txt' => '',
            'default' => true,
            'optional' => false,
            'sortable' => false,
            'is_checkbox' => true,
            'width' => '1%'
        ];

        $columns[++$i] = [
            'field' => 'attachments',
            'txt' => $this->lng->txt('mail_tbl_head_attachments'),
            'default' => false,
            'optional' => true,
            'sortable' => false,
            'width' => '5%'
        ];

        if (!$this->isDraftFolder() && !$this->isSentFolder()) {
            $columns[++$i] = [
                'field' => 'personal_picture',
                'txt' => $this->lng->txt('personal_picture'),
                'default' => true,
                'optional' => true,
                'sortable' => false,
                'width' => '5%'
            ];
        }

        if ($this->isDraftFolder() || $this->isSentFolder()) {
            $columns[++$i] = [
                'field' => 'rcp_to',
                'txt' => $this->lng->txt('recipient'),
                'default' => true,
                'optional' => false,
                'sortable' => true,
                'width' => '35%'
            ];
        } else {
            $columns[++$i] = [
                'field' => 'from',
                'txt' => $this->lng->txt('sender'),
                'default' => true,
                'optional' => false,
                'sortable' => true,
                'width' => '20%'
            ];
        }

        if ($this->isLuceneSearchEnabled()) {
            $columns[++$i] = [
                'field' => 'search_content',
                'txt' => $this->lng->txt('search_content'),
                'default' => true,
                'optional' => false,
                'sortable' => false,
                'width' => '35%'
            ];
        } else {
            $columns[++$i] = [
                'field' => 'm_subject',
                'txt' => $this->lng->txt('subject'),
                'default' => true,
                'optional' => false,
                'sortable' => true,
                'width' => '35%'
            ];
        }

        $columns[++$i] = [
            'field' => 'send_time',
            'txt' => $this->lng->txt('date'),
            'default' => true,
            'optional' => false,
            'sortable' => true,
            'width' => '20%'
        ];

        $columns[++$i] = [
            'field' => 'actions',
            'txt' => $this->lng->txt('actions'),
            'default' => true,
            'optional' => false,
            'sortable' => false,
            'width' => '5%'
        ];

        return $columns;
    }

    /**
     * Call this before using getHTML()
     * @return ilMailFolderTableGUI
     * @throws Exception
     */
    final public function prepareHTML() : self
    {
        $columns = $this->getColumnDefinition();
        $this->optionalColumns = (array) $this->getSelectableColumns();
        $this->visibleOptionalColumns = (array) $this->getSelectedColumns();
        foreach ($columns as $index => $column) {
            if ($this->isColumnVisible($index)) {
                $this->addColumn(
                    $column['txt'],
                    isset($column['sortable']) && $column['sortable'] ? $column['field'] : '',
                    isset($column['width']) ? $column['width'] : '',
                    isset($column['is_checkbox']) ? (bool) $column['is_checkbox'] : false
                );
            }
        }

        $mtree = new \ilTree($this->user->getId());
        $mtree->setTableNames('mail_tree', 'mail_obj_data');
        $this->_folderNode = $mtree->getNodeData($this->_currentFolderId);

        $this->fetchTableData();

        $this->initCommandButtons();
        $this->initMultiCommands($this->_parentObject->mbox->getActions($this->_currentFolderId));

        return $this;
    }

    /**
     * Setter/Getter for folder status
     * @param  mixed $a_bool Boolean folder status or null
     * @return bool|ilMailFolderTableGUI    Either an object of type ilMailFolderTableGUI or the boolean folder status
     */
    public function isDraftFolder(bool $a_bool = null)
    {
        if (null === $a_bool) {
            return $this->_isDraftsFolder;
        }

        $this->_isDraftsFolder = $a_bool;

        return $this;
    }

    /**
     * Setter/Getter for folder status
     * @param  mixed $a_bool Boolean folder status or null
     * @return bool|ilMailFolderTableGUI    Either an object of type ilMailFolderTableGUI or the boolean folder status
     */
    public function isSentFolder(bool $a_bool = null)
    {
        if (null === $a_bool) {
            return $this->_isSentFolder;
        }

        $this->_isSentFolder = $a_bool;

        return $this;
    }

    /**
     * Setter/Getter for folder status
     * @param  mixed $a_bool Boolean folder status or null
     * @return bool|ilMailFolderTableGUI    Either an object of type ilMailFolderTableGUI or the boolean folder status
     */
    public function isTrashFolder(bool $a_bool = null)
    {
        if (null === $a_bool) {
            return $this->_isTrashFolder;
        }

        $this->_isTrashFolder = $a_bool;

        return $this;
    }

    /**
     * @return ilMailFolderTableGUI
     */
    private function initCommandButtons() : self
    {
        if ($this->_folderNode['m_type'] === 'trash' && $this->getNumberOfMails() > 0) {
            $this->addCommandButton('confirmEmptyTrash', $this->lng->txt('mail_empty_trash'));
        }

        return $this;
    }

    /**
     * @param $actions
     * @return ilMailFolderTableGUI
     */
    private function initMultiCommands(array $actions) : self
    {
        foreach ($actions as $key => $action) {
            if ($key === 'moveMails') {
                $folders = $this->_parentObject->mbox->getSubFolders();
                foreach ($folders as $folder) {
                    if ($folder['type'] !== 'trash' || !$this->isTrashFolder()) {
                        if ($folder['type'] !== 'user_folder') {
                            $label = $action . ' ' . $this->lng->txt('mail_' . $folder['title']) .
                                ($folder['type'] === 'trash' ? ' (' . $this->lng->txt('delete') . ')' : '');
                            $this->addMultiCommand($key . '_' . $folder['obj_id'], $label);
                        } else {
                            $this->addMultiCommand($key . '_' . $folder['obj_id'], $action . ' ' . $folder['title']);
                        }
                    }
                }
            } else {
                if ($key !== 'deleteMails' || $this->isTrashFolder()) {
                    $this->addMultiCommand($key, $action);
                }
            }
        }

        return $this;
    }

    /**
     * @param array $a_selected_items
     * @return ilMailFolderTableGUI
     */
    public function setSelectedItems(array $a_selected_items) : self
    {
        $this->_selectedItems = $a_selected_items;

        return $this;
    }

    /**
     * @return array
     */
    public function getSelectedItems() : array
    {
        return $this->_selectedItems;
    }

    /**
     * @return bool
     */
    protected function isLuceneSearchEnabled() : bool
    {
        if (ilSearchSettings::getInstance()->enabledLucene() && strlen($this->filter['mail_filter'])) {
            return true;
        }

        return false;
    }

    /**
     * @return $this
     * @throws Exception
     */
    protected function fetchTableData() : self
    {
        if ($this->_folderNode['m_type'] == 'user_folder') {
            $txt_folder = $this->_folderNode['title'];
            $img_folder = 'icon_user_folder.png';
        } else {
            $txt_folder = $this->lng->txt('mail_' . $this->_folderNode['title']);
            $img_folder = 'icon' . substr($this->_folderNode['title'], 1) . '.png';
        }

        try {
            if ($this->isLuceneSearchEnabled()) {
                $query_parser = new ilMailLuceneQueryParser($this->filter['mail_filter']);
                $query_parser->setFields(array(
                    'title' => (bool) $this->filter['mail_filter_subject'],
                    'content' => (bool) $this->filter['mail_filter_body'],
                    'mattachment' => (bool) $this->filter['mail_filter_attach'],
                    'msender' => (bool) $this->filter['mail_filter_sender'],
                    'mrcp' => (bool) $this->filter['mail_filter_recipients']
                ));
                $query_parser->parse();

                $result = new ilMailSearchResult();
                $searcher = new ilMailLuceneSearcher($query_parser, $result);
                $searcher->search($this->user->getId(), $this->_currentFolderId);

                if (!$result->getIds()) {
                    throw new ilException('mail_search_empty_result');
                }

                ilMailBoxQuery::$filtered_ids = $result->getIds();
                ilMailBoxQuery::$filter = [
                    'mail_filter_only_unread' => $this->filter['mail_filter_only_unread'],
                    'mail_filter_only_with_attachments' => $this->filter['mail_filter_only_with_attachments'],
                ];
            } else {
                ilMailBoxQuery::$filter = (array) $this->filter;
            }

            if ($this->isDraftFolder() || $this->isSentFolder() && isset(ilMailBoxQuery::$filter['mail_filter_only_unread'])) {
                unset(ilMailBoxQuery::$filter['mail_filter_only_unread']);
            }

            if ($this->isDraftFolder() && isset(ilMailBoxQuery::$filter['mail_filter_only_with_attachments'])) {
                unset(ilMailBoxQuery::$filter['mail_filter_only_with_attachments']);
            }

            $this->determineOffsetAndOrder();

            ilMailBoxQuery::$folderId = $this->_currentFolderId;
            ilMailBoxQuery::$userId = $this->user->getId();
            ilMailBoxQuery::$limit = $this->getLimit();
            ilMailBoxQuery::$offset = $this->getOffset();
            ilMailBoxQuery::$orderDirection = $this->getOrderDirection();
            ilMailBoxQuery::$orderColumn = $this->getOrderField();
            $data = ilMailBoxQuery::_getMailBoxListData();

            if (!count($data['set']) && $this->getOffset() > 0) {
                $this->resetOffset();

                ilMailBoxQuery::$limit = $this->getLimit();
                ilMailBoxQuery::$offset = $this->getOffset();
                $data = ilMailBoxQuery::_getMailBoxListData();
            }
        } catch (Exception $e) {
            if ('mail_search_empty_result' === $e->getMessage()) {
                $data['set'] = array();
                $data['cnt'] = 0;
                $data['cnt_unread'] = 0;
            } else {
                throw $e;
            }
        }

        if (!$this->isDraftFolder() && !$this->isSentFolder()) {
            $user_ids = array();
            foreach ($data['set'] as $mail) {
                if ($mail['sender_id'] && $mail['sender_id'] != ANONYMOUS_USER_ID) {
                    $user_ids[$mail['sender_id']] = $mail['sender_id'];
                }
            }

            ilMailUserCache::preloadUserObjects($user_ids);
        }

        $counter = 0;
        foreach ($data['set'] as $key => $mail) {
            ++$counter;

            if (is_array($this->getSelectedItems()) && in_array($mail['mail_id'], $this->getSelectedItems())) {
                $mail['checked'] = ' checked="checked" ';
            }

            if ($this->isDraftFolder() || $this->isSentFolder()) {
                $mail['rcp_to'] = $mail['mail_login'] = ilUtil::htmlencodePlainString(
                    $this->_parentObject->umail->formatNamesForOutput($mail['rcp_to']),
                    false
                );
            } else {
                if ($mail['sender_id'] == ANONYMOUS_USER_ID) {
                    $mail['img_sender'] = ilUtil::getImagePath('HeaderIconAvatar.svg');
                    $mail['from'] = $mail['mail_login'] = $mail['alt_sender'] = htmlspecialchars(ilMail::_getIliasMailerName());
                } else {
                    $user = ilMailUserCache::getUserObjectById($mail['sender_id']);
                    if ($user) {
                        $mail['img_sender'] = $user->getPersonalPicturePath('xxsmall');
                        $mail['from'] = $mail['mail_login'] = $mail['alt_sender'] = htmlspecialchars($user->getPublicName());
                    } else {
                        $mail['from'] = $mail['mail_login'] = $mail['import_name'] . ' (' . $this->lng->txt('user_deleted') . ')';
                    }
                }
            }

            if ($this->isDraftFolder()) {
                $this->ctrl->setParameterByClass('ilmailformgui', 'mail_id', $mail['mail_id']);
                $this->ctrl->setParameterByClass('ilmailformgui', 'mobj_id', $this->_currentFolderId);
                $this->ctrl->setParameterByClass('ilmailformgui', 'type', 'draft');
                $link_mark_as_read = $this->ctrl->getLinkTargetByClass('ilmailformgui');
                $this->ctrl->clearParametersByClass('ilmailformgui');
            } else {
                $this->ctrl->setParameter($this->getParentObject(), 'mail_id', $mail['mail_id']);
                $this->ctrl->setParameter($this->getParentObject(), 'mobj_id', $this->_currentFolderId);
                $link_mark_as_read = $this->ctrl->getLinkTarget($this->getParentObject(), 'showMail');
                $this->ctrl->clearParameters($this->getParentObject());
            }
            $css_class = $mail['m_status'] == 'read' ? 'mailread' : 'mailunread';

            if ($this->isLuceneSearchEnabled()) {
                $search_result = array();
                foreach ($result->getFields($mail['mail_id']) as $content) {
                    if ('title' == $content[0]) {
                        $mail['msr_subject_link_read'] = $link_mark_as_read;
                        $mail['msr_subject_mailclass'] = $css_class;
                        $mail['msr_subject'] = $content[1];
                    } else {
                        $search_result[] = $content[1];
                    }
                }
                $mail['msr_data'] = implode('', array_map(function ($value) {
                    return '<p>' . $value . '</p>';
                }, $search_result));

                if (!$mail['msr_subject']) {
                    $mail['msr_subject_link_read'] = $link_mark_as_read;
                    $mail['msr_subject_mailclass'] = $css_class;
                    $mail['msr_subject'] = htmlspecialchars($mail['m_subject']);
                }
            } else {
                $mail['mail_link_read'] = $link_mark_as_read;
                $mail['mailclass'] = $css_class;
                $mail['mail_subject'] = htmlspecialchars($mail['m_subject']);
            }

            $mail['mail_date'] = ilDatePresentation::formatDate(new ilDateTime($mail['send_time'], IL_CAL_DATETIME));

            $mail['attachment_indicator'] = '';
            if (is_array($mail['attachments']) && count($mail['attachments']) > 0) {
                $this->ctrl->setParameter($this->getParentObject(), 'mail_id', (int) $mail['mail_id']);
                if ($this->isDraftFolder()) {
                    $this->ctrl->setParameter($this->getParentObject(), 'type', 'draft');
                }
                $this->ctrl->setParameter($this->getParentObject(), 'mobj_id', $this->_currentFolderId);
                $mail['attachment_indicator'] = $this->uiRenderer->render(
                    $this->uiFactory->glyph()->attachment(
                        $this->ctrl->getLinkTarget($this->getParentObject(), 'deliverAttachments')
                    )
                );
                $this->ctrl->clearParameters($this->getParentObject());
            }

            $mail['actions'] = $this->formatActionsDropDown($mail);

            $data['set'][$key] = $mail;
        }

        $this->setData($data['set']);
        $this->setMaxCount((int) $data['cnt']);
        $this->setNumberOfMails((int) $data['cnt']);

        $this->setTitleData($txt_folder, (int) $data['cnt'], (int) $data['cnt_unread'], $img_folder);

        return $this;
    }

    /**
     * @param string $folderLabel
     * @param int $mailCount
     * @param int $unreadCount
     * @param string $imgFolder
     * @return ilMailFolderTableGUI
     * @throws ilTemplateException
     */
    protected function setTitleData(string $folderLabel, int $mailCount, int $unreadCount, string $imgFolder) : self
    {
        $titleTemplate = new ilTemplate('tpl.mail_folder_title.html', true, true, 'Services/Mail');
        $titleTemplate->setVariable('TXT_FOLDER', $folderLabel);
        $titleTemplate->setVariable('MAIL_COUNT', $mailCount);
        $titleTemplate->setVariable('TXT_MAIL_S', $this->lng->txt('mail_s'));
        $titleTemplate->setVariable('MAIL_COUNT_UNREAD', $unreadCount);
        $titleTemplate->setVariable('TXT_UNREAD', $this->lng->txt('unread'));

        parent::setTitle($titleTemplate->get(), $imgFolder);

        return $this;
    }

    /**
     * @param int $a_number_of_mails
     * @return $this
     */
    public function setNumberOfMails(int $a_number_of_mails) : self
    {
        $this->_number_of_mails = $a_number_of_mails;

        return $this;
    }

    /**
     * @return int
     */
    public function getNumberOfMails() : int
    {
        return $this->_number_of_mails;
    }

    /**
     *
     */
    public function initFilter()
    {
        $this->filter = [];

        $quickFilter = new ilMailQuickFilterInputGUI($this->lng->txt('mail_filter'), 'mail_filter');
        $quickFilter->setSubmitFormOnEnter(false);
        $this->addFilterItem($quickFilter);
        $quickFilter->readFromSession();
        $this->filter['mail_filter'] = $quickFilter->getValue();

        if ($this->isDraftFolder() || $this->isSentFolder()) {
            $this->sub_filter[] = $subFilterInRecipients = new ilCheckboxInputGUI(
                $this->lng->txt('mail_filter_recipients'),
                'mail_filter_recipients'
            );
            $subFilterInRecipients->setOptionTitle($this->lng->txt('mail_filter_recipients'));
            $subFilterInRecipients->setValue(1);
            $quickFilter->addSubItem($subFilterInRecipients);
            $subFilterInRecipients->setParent($this);
            $subFilterInRecipients->readFromSession();
            $this->filter['mail_filter_recipients'] = (int) $subFilterInRecipients->getChecked();
        } else {
            $this->sub_filter[] = $subFilterInSender = new ilCheckboxInputGUI(
                $this->lng->txt('mail_filter_sender'),
                'mail_filter_sender'
            );
            $subFilterInSender->setOptionTitle($this->lng->txt('mail_filter_sender'));
            $subFilterInSender->setValue(1);
            $quickFilter->addSubItem($subFilterInSender);
            $subFilterInSender->setParent($this);
            $subFilterInSender->readFromSession();
            $this->filter['mail_filter_sender'] = (int) $subFilterInSender->getChecked();
        }

        $this->sub_filter[] = $subFilterInSubject = new ilCheckboxInputGUI(
            $this->lng->txt('mail_filter_subject'),
            'mail_filter_subject'
        );
        $subFilterInSubject->setOptionTitle($this->lng->txt('mail_filter_subject'));
        $subFilterInSubject->setValue(1);
        $quickFilter->addSubItem($subFilterInSubject);
        $subFilterInSubject->setParent($this);
        $subFilterInSubject->readFromSession();
        $this->filter['mail_filter_subject'] = (int) $subFilterInSubject->getChecked();

        $this->sub_filter[] = $subFilterInBody = new ilCheckboxInputGUI(
            $this->lng->txt('mail_filter_body'),
            'mail_filter_body'
        );
        $subFilterInBody->setOptionTitle($this->lng->txt('mail_filter_body'));
        $subFilterInBody->setValue(1);
        $quickFilter->addSubItem($subFilterInBody);
        $subFilterInBody->setParent($this);
        $subFilterInBody->readFromSession();
        $this->filter['mail_filter_body'] = (int) $subFilterInBody->getChecked();

        $this->sub_filter[] = $subFilterInAttachments = new ilCheckboxInputGUI(
            $this->lng->txt('mail_filter_attach'),
            'mail_filter_attach'
        );
        $subFilterInAttachments->setOptionTitle($this->lng->txt('mail_filter_attach'));
        $subFilterInAttachments->setValue(1);
        $quickFilter->addSubItem($subFilterInAttachments);
        $subFilterInAttachments->setParent($this);
        $subFilterInAttachments->readFromSession();
        $this->filter['mail_filter_attach'] = (int) $subFilterInAttachments->getChecked();

        if (!$this->isDraftFolder() && !$this->isSentFolder()) {
            $onlyUnread = new ilCheckboxInputGUI(
                $this->lng->txt('mail_filter_only_unread'),
                'mail_filter_only_unread'
            );
            $onlyUnread->setValue(1);
            $this->addFilterItem($onlyUnread);
            $onlyUnread->readFromSession();
            $this->filter['mail_filter_only_unread'] = (int) $onlyUnread->getChecked();
        }

        if (!$this->isDraftFolder()) {
            $onlyWithAttachments = new ilCheckboxInputGUI(
                $this->lng->txt('mail_filter_only_with_attachments'),
                'mail_filter_only_with_attachments'
            );
            $onlyWithAttachments->setValue(1);
            $this->addFilterItem($onlyWithAttachments);
            $onlyWithAttachments->readFromSession();
            $this->filter['mail_filter_only_with_attachments'] = (int) $onlyWithAttachments->getChecked();
        }

        $duration = new \ilDateDurationInputGUI($this->lng->txt('mail_filter_period'), 'period');
        $duration->setAllowOpenIntervals(true);
        $duration->setStartText($this->lng->txt('mail_filter_period_from'));
        $duration->setEndText($this->lng->txt('mail_filter_period_until'));
        $duration->setStart(new ilDateTime(null, IL_CAL_UNIX));
        $duration->setEnd(new ilDateTime(null, IL_CAL_UNIX));
        $duration->setShowTime(false);
        $this->addFilterItem($duration);
        $duration->readFromSession();
        $this->filter['period'] = $duration->getValue();
    }

    /**
     * @inheritdoc
     */
    public function writeFilterToSession()
    {
        parent::writeFilterToSession();

        foreach ($this->sub_filter as $item) {
            if ($item->checkInput()) {
                $item->setValueByArray($_POST);
                $item->writeToSession();
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function resetFilter()
    {
        parent::resetFilter();

        foreach ($this->sub_filter as $item) {
            if ($item->checkInput()) {
                $item->setValueByArray($_POST);
                $item->clearFromSession();
            }
        }
    }

    /**
     * @param array $mail
     * @return string
     */
    protected function formatActionsDropDown(array $mail) : string
    {
        $buttons = [];

        $this->addViewRowAction($mail, $buttons);
        $this->addReplyRowAction($mail, $buttons);
        $this->addForwardRowAction($mail, $buttons);
        $this->addPrintRowAction($mail, $buttons);

        $dropDown = $this->uiFactory
            ->dropdown()
            ->standard($buttons)
            ->withLabel($this->lng->txt('actions'));

        return $this->uiRenderer->render([$dropDown]);
    }

    /**
     * @param array $mail
     * @param array $buttons
     */
    protected function addViewRowAction(array $mail, array &$buttons)
    {
        if ($this->isDraftFolder()) {
            $this->ctrl->setParameterByClass('ilmailformgui', 'mail_id', (int) $mail['mail_id']);
            $this->ctrl->setParameterByClass('ilmailformgui', 'mobj_id', $this->_currentFolderId);
            $this->ctrl->setParameterByClass('ilmailformgui', 'type', 'draft');
            $viewButton = $this->uiFactory
                ->button()
                ->shy(
                    $this->lng->txt('view'),
                    $this->ctrl->getLinkTargetByClass('ilmailformgui')
                );
            $this->ctrl->clearParametersByClass('ilmailformgui');
        } else {
            $this->ctrl->setParameter($this->getParentObject(), 'mail_id', (int) $mail['mail_id']);
            $this->ctrl->setParameter($this->getParentObject(), 'mobj_id', $this->_currentFolderId);
            $viewButton = $this->uiFactory
                ->button()
                ->shy(
                    $this->lng->txt('view'),
                    $this->ctrl->getLinkTarget($this->getParentObject(), 'showMail')
                );
            $this->ctrl->clearParameters($this->getParentObject());
        }

        $buttons[] = $viewButton;
    }

    /**
     * @param array $mail
     * @param array $buttons
     */
    protected function addReplyRowAction(array $mail, array &$buttons)
    {
        if (!$this->isDraftFolder()) {
            if (isset($mail['sender_id']) && $mail['sender_id'] > 0 && $mail['sender_id'] != ANONYMOUS_USER_ID) {
                $this->ctrl->setParameterByClass('ilmailformgui', 'mobj_id', $this->_currentFolderId);
                $this->ctrl->setParameterByClass('ilmailformgui', 'mail_id', (int) $mail['mail_id']);
                $this->ctrl->setParameterByClass('ilmailformgui', 'type', 'reply');
                $replyButton = $this->uiFactory
                    ->button()
                    ->shy(
                        $this->lng->txt('reply'),
                        $this->ctrl->getLinkTargetByClass('ilmailformgui')
                    );
                $this->ctrl->clearParametersByClass('ilmailformgui');

                $buttons[] = $replyButton;
            }
        }
    }

    /**
     * @param array $mail
     * @param array $buttons
     */
    protected function addForwardRowAction(array $mail, array &$buttons)
    {
        if (!$this->isDraftFolder()) {
            $this->ctrl->setParameterByClass('ilmailformgui', 'mobj_id', $this->_currentFolderId);
            $this->ctrl->setParameterByClass('ilmailformgui', 'mail_id', (int) $mail['mail_id']);
            $this->ctrl->setParameterByClass('ilmailformgui', 'type', 'forward');
            $forwardButton = $this->uiFactory
                ->button()
                ->shy(
                    $this->lng->txt('forward'),
                    $this->ctrl->getLinkTargetByClass('ilmailformgui')
                );
            $this->ctrl->clearParametersByClass('ilmailformgui');

            $buttons[] = $forwardButton;
        }
    }

    /**
     * @param array $mail
     * @param array $buttons
     */
    protected function addPrintRowAction(array $mail, array &$buttons)
    {
        if (!$this->isDraftFolder()) {
            $this->ctrl->setParameter($this->getParentObject(), 'mobj_id', $this->_currentFolderId);
            $this->ctrl->setParameter($this->getParentObject(), 'mail_id', (int) $mail['mail_id']);
            $printButton = $this->uiFactory
                ->button()
                ->shy(
                    $this->lng->txt('print'),
                    $this->ctrl->getLinkTarget($this->getParentObject(), 'printMail')
                );
            $this->ctrl->clearParameters($this->getParentObject());

            $buttons[] = $printButton;
        }
    }

    /**
     * @inheritDoc
     */
    public function getHTML()
    {
        $this->ctrl->setParameter($this->getParentObject(), 'mobj_id', $this->_currentFolderId);
        $html = parent::getHTML();
        $this->ctrl->clearParameters($this->getParentObject());

        return $html;
    }
}
