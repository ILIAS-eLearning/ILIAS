<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * CAS user creation helper
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 *
 */
class ilCASAttributeToUser
{
    /**
     * @var \ilLogger
     */
    private $logger = null;

    /**
     * @var ilXmlWriter|null
     */
    private $writer = null;

    /**
     * @var \ilCASSettings|null
     */
    private $settings = null;


    /**
     * Constructor
     *
     * @access public
     *
     */
    public function __construct(\ilCASSettings $settings)
    {
        global $DIC;

        $this->logger = $DIC->logger()->auth();

        include_once('./Services/Xml/classes/class.ilXmlWriter.php');
        $this->writer = new ilXmlWriter();

        $this->settings = $settings;
    }

    /**
     * Create new ILIAS account
     *
     * @access public
     *
     * @param string external username
     */
    public function create($a_username)
    {
        $this->writer->xmlStartTag('Users');

        // Single users
        // Required fields
        // Create user
        $this->writer->xmlStartTag('User', array('Action' => 'Insert'));
        $this->writer->xmlElement('Login', array(), $new_name = ilAuthUtils::_generateLogin($a_username));

        // Assign to role only for new users
        $this->writer->xmlElement(
            'Role',
            array(
                'Id' => $this->settings->getDefaultRole(),
                'Type' => 'Global',
                'Action' => 'Assign'),
            ''
        );

        $this->writer->xmlElement('Active', array(), "true");
        $this->writer->xmlElement('TimeLimitOwner', array(), 7);
        $this->writer->xmlElement('TimeLimitUnlimited', array(), 1);
        $this->writer->xmlElement('TimeLimitFrom', array(), time());
        $this->writer->xmlElement('TimeLimitUntil', array(), time());
        $this->writer->xmlElement('AuthMode', array('type' => 'cas'), 'cas');
        $this->writer->xmlElement('ExternalAccount', array(), $a_username);

        $this->writer->xmlEndTag('User');
        $this->writer->xmlEndTag('Users');

        $this->logger->info('CAS: Startet creation of user: ' . $new_name);

        include_once './Services/User/classes/class.ilUserImportParser.php';
        $importParser = new ilUserImportParser();
        $importParser->setXMLContent($this->writer->xmlDumpMem(false));
        $importParser->setRoleAssignment(
            array(
                $this->settings->getDefaultRole() => $this->settings->getDefaultRole()
            )
        );
        $importParser->setFolderId(7);
        $importParser->startParsing();

        return $new_name;
    }
}
