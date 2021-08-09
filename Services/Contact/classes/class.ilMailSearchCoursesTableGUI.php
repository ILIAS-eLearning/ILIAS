<?php declare(strict_types=1);
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
*
* @author Jan Posselt <jposselt@databay.de>
* @version $Id$
*
*
* @ingroup ServicesMail
*/

use Psr\Http\Message\RequestInterface;

include_once('Services/Table/classes/class.ilTable2GUI.php');


class ilMailSearchCoursesTableGUI extends ilTable2GUI
{
    private RequestInterface $httpRequest;
    /**
     * @var ilLanguage
     */
    protected $lng;

    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * @var ilObjUser
     */
    protected $user;

    /**
     * @var ilRbacSystem
     */
    protected $rbacsystem;

    protected $parentObject;
    protected $mode;
    protected $mailing_allowed;
    
    /**
     * Constructor
     *
     * @access public
     * @param object	parent object
     * @param string	type; valid values are 'crs' for courses and
     *			'grp' for groups
     * @param string	context
     */
    public function __construct($a_parent_obj, $type = 'crs', $context = 'mail')
    {
        global $DIC;

        $this->lng = $DIC['lng'];
        $this->ctrl = $DIC['ilCtrl'];
        $this->user = $DIC['ilUser'];
        $this->rbacsystem = $DIC['rbacsystem'];
        $this->httpRequest = $DIC->http()->request();

        $this->lng->loadLanguageModule('crs');
        $this->lng->loadLanguageModule('buddysystem');

        if ($context === "mail") {
            // check if current user may send mails
            include_once "Services/Mail/classes/class.ilMail.php";
            $mail = new ilMail($this->user->getId());
            $this->mailing_allowed = $this->rbacsystem->checkAccess('internal_mail', $mail->getMailObjectReferenceId());
        }

        $mode = array();
        
        if ($type === "crs") {
            $mode["short"] = "crs";
            $mode["long"] = "course";
            $mode["checkbox"] = "search_crs";
            $mode["tableprefix"] = "crstable";
            $mode["lng_mail"] = $this->lng->txt("mail_my_courses");
            $mode["view"] = "mycourses";
            $this->setTitle($mode["lng_mail"]);
        } elseif ($type === "grp") {
            $mode["short"] = "grp";
            $mode["long"] = "group";
            $mode["checkbox"] = "search_grp";
            $mode["tableprefix"] = "grptable";
            $mode["lng_mail"] = $this->lng->txt("mail_my_groups");
            $mode["view"] = "mygroups";
            $this->setTitle($mode["lng_mail"]);
        }

        $this->setId('search_' . $mode["short"]);
        parent::__construct($a_parent_obj);

        //$this->courseIds = $crs_ids;
        $this->parentObject = $a_parent_obj;
        $this->mode = $mode;
        $this->context = $context;

        $this->ctrl->setParameter($a_parent_obj, 'view', $mode['view']);
        if (isset($this->httpRequest->getQueryParams()['ref']) && $this->httpRequest->getQueryParams()['ref'] !== '') {
            $this->ctrl->setParameter($a_parent_obj, 'ref', $this->httpRequest->getQueryParams()['ref']);
        }
        if (isset($this->httpRequest->getParsedBody()[$mode["checkbox"]]) && is_array($this->httpRequest->getParsedBody()[$mode["checkbox"]])) {
            $this->ctrl->setParameter($a_parent_obj, $mode["checkbox"], implode(',', $this->httpRequest->getParsedBody()[$mode["checkbox"]]));
        }

        $this->setFormAction($this->ctrl->getFormAction($a_parent_obj));
        $this->ctrl->clearParameters($a_parent_obj);
        
        $this->setSelectAllCheckbox($mode["checkbox"] . '[]');
        $this->setRowTemplate('tpl.mail_search_courses_row.html', 'Services/Contact');
        
        $this->setShowRowsSelector(true);

        // setup columns
        $this->addColumn('', '', '1px', true);
        $this->addColumn($mode["lng_mail"], 'CRS_NAME', '30%');
        $this->addColumn($this->lng->txt('path'), 'CRS_PATH', '30%');
        $this->addColumn($this->lng->txt('crs_count_members'), 'CRS_NO_MEMBERS', '20%');
        $this->addColumn($this->lng->txt('actions'), '', '19%');
        
        if ($context === "mail") {
            if ($this->mailing_allowed) {
                $this->addMultiCommand('mail', $this->lng->txt('mail_members'));
            }
        } elseif ($context === "wsp") {
            $this->lng->loadLanguageModule("wsp");
            $this->addMultiCommand('share', $this->lng->txt('wsp_share_with_members'));
        }
        $this->addMultiCommand('showMembers', $this->lng->txt('mail_list_members'));
        
        if (isset($this->httpRequest->getParsedBody()['ref']) && $this->httpRequest->getQueryParams()['ref'] === 'mail') {
            $this->addCommandButton('cancel', $this->lng->txt('cancel'));
        }
    }
    
    /**
     * Fill row
     *
     * @access public
     * @param
     *
     */
    protected function fillRow($a_set) : void
    {
        if ($a_set['hidden_members']) {
            $this->tpl->setCurrentBlock('caption_asterisk');
            $this->tpl->touchBlock('caption_asterisk');
            $this->tpl->parseCurrentBlock();
        }
        foreach ($a_set as $key => $value) {
            $this->tpl->setVariable(strtoupper($key), $value);
        }
        $this->tpl->setVariable('SHORT', $this->mode["short"]);
    }

    public function numericOrdering($column) : bool
    {
        return $column === 'CRS_NO_MEMBERS';
    }
}
