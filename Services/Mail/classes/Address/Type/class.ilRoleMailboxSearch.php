<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilRoleMailboxSearch
 * @author Werner Randelshofer <wrandels@hsw.fhz.ch>
 * @author Stefan Meyer <meyer@leifos.com>
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilRoleMailboxSearch
{
    /** @var \ilMailRfc822AddressParserFactory */
    protected $parserFactory;

    /** @var \ilDBInterface */
    protected $db;

    /**
     * ilRoleMailboxSearch constructor.
     * @param \ilMailRfc822AddressParserFactory $parserFactory
     * @param \ilDBInterface|null               $db
     */
    public function __construct(
        \ilMailRfc822AddressParserFactory $parserFactory,
        \ilDBInterface $db = null
    ) {
        global $DIC;

        $this->parserFactory = $parserFactory;

        if (null === $db) {
            $db = $DIC->database();
        }
        $this->db = $db;
    }

    /**
     * Finds all role ids that match the specified user friendly role mailbox address list.
     *
     * The role mailbox name address list is an e-mail address list according to IETF RFC 822:
     *
     * address list  = role mailbox, {"," role mailbox } ;
     * role mailbox  = "#", local part, ["@" domain] ;
     *
     * Examples: The following role mailbox names are all resolved to the role il_crs_member_123:
     *
     *    #Course.A
     *    #member@Course.A
     *    #il_crs_member_123@Course.A
     *    #il_crs_member_123
     *    #il_crs_member_123@ilias
     *
     * Examples: The following role mailbox names are all resolved to the role il_crs_member_345:
     *
     *    #member@[English Course]
     *    #il_crs_member_345@[English Course]
     *    #il_crs_member_345
     *    #il_crs_member_345@ilias
     *
     * If only the local part is specified, or if domain is equal to "ilias", ILIAS compares
     * the title of role objects with local part. Only roles that are not in a trash folder
     * are considered for the comparison.
     *
     * If a domain is specified, and if the domain is not equal to "ilias", ILIAS compares
     * the title of objects with the domain. Only objects that are not in a trash folder are
     * considered for the comparison. Then ILIAS searches for local roles which contain
     * the local part in their title. This allows for abbreviated role names, e.g. instead of
     * having to specify #il_grp_member_345@MyGroup, it is sufficient to specify #member@MyGroup.
     *
     * The address list may contain addresses thate are not role mailboxes. These addresses
     * are ignored.
     *
     * If a role mailbox address is ambiguous, this function returns the ID's of all role
     * objects that are possible recipients for the role mailbox address.
     *
     * If Pear Mail is not installed, then the mailbox address
     * @param string $a_address_list
     * @return int[] Array with role ids that were found
     */
    public function searchRoleIdsByAddressString(string $a_address_list) : array
    {
        $parser = $this->parserFactory->getParser($a_address_list);
        $parsedList = $parser->parse();

        $role_ids = array();
        foreach ($parsedList as $address) {
            $local_part = $address->getMailbox();
            if (strpos($local_part, '#') !== 0 && !($local_part{0} == '"' && $local_part{1} == "#")) {
                // A local-part which doesn't start with a '#' doesn't denote a role.
                // Therefore we can skip it.
                continue;
            }

            $local_part = substr($local_part, 1);

            /* If role contains spaces, eg. 'foo role', double quotes are added which have to be removed here.*/
            if ($local_part{0} == '#' && $local_part{strlen($local_part) - 1} == '"') {
                $local_part = substr($local_part, 1);
                $local_part = substr($local_part, 0, strlen($local_part) - 1);
            }

            if (substr($local_part, 0, 8) == 'il_role_') {
                $role_id = substr($local_part, 8);
                $query = "SELECT t.tree " .
                    "FROM rbac_fa fa " .
                    "JOIN tree t ON t.child = fa.parent " .
                    "WHERE fa.rol_id = " . $this->db->quote($role_id, 'integer') . " " .
                    "AND fa.assign = 'y' " .
                    "AND t.tree = 1";
                $res = $this->db->query($query);
                if ($this->db->numRows($res) > 0) {
                    $role_ids[] = $role_id;
                }
                continue;
            }

            $domain = $address->getHost();
            if (strpos($domain, '[') == 0 && strrpos($domain, ']')) {
                $domain = substr($domain, 1, strlen($domain) - 2);
            }
            if (strlen($local_part) == 0) {
                $local_part = $domain;
                $address->setHost(\ilMail::ILIAS_HOST);
                $domain = \ilMail::ILIAS_HOST;
            }

            if (strtolower($address->getHost()) == \ilMail::ILIAS_HOST) {
                // Search for roles = local-part in the whole repository
                $query = "SELECT dat.obj_id " .
                    "FROM object_data dat " .
                    "JOIN rbac_fa fa ON fa.rol_id = dat.obj_id " .
                    "JOIN tree t ON t.child = fa.parent " .
                    "WHERE dat.title =" . $this->db->quote($local_part, 'text') . " " .
                    "AND dat.type = 'role' " .
                    "AND fa.assign = 'y' " .
                    "AND t.tree = 1";
            } else {
                // Search for roles like local-part in objects = host
                $query = "SELECT rdat.obj_id " .
                    "FROM object_data odat " .
                    "JOIN object_reference oref ON oref.obj_id = odat.obj_id " .
                    "JOIN tree otree ON otree.child = oref.ref_id " .
                    "JOIN rbac_fa rfa ON rfa.parent = otree.child " .
                    "JOIN object_data rdat ON rdat.obj_id = rfa.rol_id " .
                    "WHERE odat.title = " . $this->db->quote($domain, 'text') . " " .
                    "AND otree.tree = 1 " .
                    "AND rfa.assign = 'y' " .
                    "AND rdat.title LIKE " .
                    $this->db->quote('%' . preg_replace('/([_%])/', '\\\\$1', $local_part) . '%', 'text');
            }
            $res = $this->db->query($query);

            $count = 0;
            while ($row = $this->db->fetchAssoc($res)) {
                $role_ids[] = $row['obj_id'];

                $count++;
            }

            // Nothing found?
            // In this case, we search for roles = host.
            if ($count == 0 && strtolower($address->getHost()) == \ilMail::ILIAS_HOST) {
                $q = "SELECT dat.obj_id " .
                    "FROM object_data dat " .
                    "JOIN object_reference ref ON ref.obj_id = dat.obj_id " .
                    "JOIN tree t ON t.child = ref.ref_id " .
                    "WHERE dat.title = " . $this->db->quote($domain, 'text') . " " .
                    "AND dat.type = 'role' " .
                    "AND t.tree = 1 ";
                $res = $this->db->query($q);

                while ($row = $this->db->fetchAssoc($res)) {
                    $role_ids[] = $row['obj_id'];
                }
            }
        }

        return $role_ids;
    }
}
