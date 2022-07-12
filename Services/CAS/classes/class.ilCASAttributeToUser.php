<?php declare(strict_types=1);

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/

/**
 * CAS user creation helper
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
class ilCASAttributeToUser
{
    private ilLogger $logger;
    private ilXmlWriter $writer;
    private ilCASSettings $settings;

    public function __construct(\ilCASSettings $settings)
    {
        global $DIC;

        $this->logger = $DIC->logger()->auth();

        $this->writer = new ilXmlWriter();

        $this->settings = $settings;
    }

    public function create(string $a_username) : string
    {
        $this->writer->xmlStartTag('Users');

        $this->writer->xmlStartTag('User', array('Action' => 'Insert'));
        $new_name = ilAuthUtils::_generateLogin($a_username);
        $this->writer->xmlElement('Login', array(), $new_name);

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

        $importParser = new ilUserImportParser();
        $importParser->setXMLContent($this->writer->xmlDumpMem(false));
        $importParser->setRoleAssignment(
            array(
                $this->settings->getDefaultRole() => $this->settings->getDefaultRole()
            )
        );
        //TODO check if there is a constant
        $importParser->setFolderId(7);
        $importParser->startParsing();

        return $new_name;
    }
}
