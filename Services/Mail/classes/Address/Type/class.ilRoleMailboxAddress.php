<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilRoleMailboxAddress
 * @author Werner Randelshofer <wrandels@hsw.fhz.ch>
 * @author Stefan Meyer <meyer@leifos.com>
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilRoleMailboxAddress
{
    /** @var int */
    protected $roleId;

    /** @var bool */
    protected $localize = true;

    /** @var \ilMailRfc822AddressParserFactory */
    protected $parserFactory;

    /** @var \ilDBInterface */
    protected $db;

    /** @var \ilLanguage */
    protected $lng;

    /**
     * ilRoleMailboxAddress constructor.
     * @param int                                    $roleId
     * @param bool                                   $localize A boolean flag whether mailbox addresses should be localized
     * @param \ilMailRfc822AddressParserFactory|null $parserFactory
     * @param \ilDBInterface|null                    $db
     * @param \ilLanguage|null                       $lng
     */
    public function __construct(
        int $roleId,
        bool $localize = true,
        \ilMailRfc822AddressParserFactory $parserFactory = null,
        \ilDBInterface $db = null,
        \ilLanguage $lng = null
    ) {
        global $DIC;

        $this->roleId = $roleId;
        $this->localize = $localize;

        if (null === $db) {
            $db = $DIC->database();
        }
        $this->db = $db;

        if (null === $lng) {
            $lng = $DIC->language();
        }
        $this->lng = $lng;

        if (null === $parserFactory) {
            $parserFactory = new \ilMailRfc822AddressParserFactory();
        }
        $this->parserFactory = $parserFactory;
    }

    /**
     * Returns the mailbox address of a role.
     *
     * Example 1: Mailbox address for an ILIAS reserved role name
     * ----------------------------------------------------------
     * The il_crs_member_345 role of the course object "English Course 1" is
     * returned as one of the following mailbox addresses:
     *
     * a)   Course Member <#member@[English Course 1]>
     * b)   Course Member <#il_crs_member_345@[English Course 1]>
     * c)   Course Member <#il_crs_member_345>
     *
     * Address a) is returned, if the title of the object is unique, and
     * if there is only one local role with the substring "member" defined for
     * the object.
     *
     * Address b) is returned, if the title of the object is unique, but
     * there is more than one local role with the substring "member" in its title.
     *
     * Address c) is returned, if the title of the course object is not unique.
     *
     *
     * Example 2: Mailbox address for a manually defined role name
     * -----------------------------------------------------------
     * The "Admin" role of the category object "Courses" is
     * returned as one of the following mailbox addresses:
     *
     * a)   Course Administrator <#Admin@Courses>
     * b)   Course Administrator <#Admin>
     * c)   Course Adminstrator <#il_role_34211>
     *
     * Address a) is returned, if the title of the object is unique, and
     * if there is only one local role with the substring "Admin" defined for
     * the course object.
     *
     * Address b) is returned, if the title of the object is not unique, but
     * the role title is unique.
     *
     * Address c) is returned, if neither the role title nor the title of the
     * course object is unique.
     *
     *
     * Example 3: Mailbox address for a manually defined role title that can
     *            contains special characters in the local-part of a
     *            mailbox address
     * --------------------------------------------------------------------
     * The "Author Courses" role of the category object "Courses" is
     * returned as one of the following mailbox addresses:
     *
     * a)   "#Author Courses"@Courses
     * b)   Author Courses <#il_role_34234>
     *
     * Address a) is returned, if the title of the role is unique.
     *
     * Address b) is returned, if neither the role title nor the title of the
     * course object is unique, or if the role title contains a quote or a
     * backslash.
     */
    public function value() : string
    {
        // Retrieve the role title and the object title.
        $query = "SELECT rdat.title role_title,odat.title object_title, " .
            " oref.ref_id object_ref " .
            "FROM object_data rdat " .
            "JOIN rbac_fa fa ON fa.rol_id = rdat.obj_id " .
            "JOIN tree rtree ON rtree.child = fa.parent " .
            "JOIN object_reference oref ON oref.ref_id = rtree.child " .
            "JOIN object_data odat ON odat.obj_id = oref.obj_id " .
            "WHERE rdat.obj_id = " . $this->db->quote($this->roleId, 'integer') . " " .
            "AND fa.assign = 'y' ";
        $res = $this->db->query($query);
        if (!$row = $this->db->fetchObject($res)) {
            return '';
        }

        $object_title = $row->object_title;
        $object_ref = $row->object_ref;
        $role_title = $row->role_title;

        // In a perfect world, we could use the object_title in the
        // domain part of the mailbox address, and the role title
        // with prefix '#' in the local part of the mailbox address.
        $domain = $object_title;
        $local_part = $role_title;

        // Determine if the object title is unique
        $q = "SELECT COUNT(DISTINCT dat.obj_id) count " .
            "FROM object_data dat " .
            "JOIN object_reference ref ON ref.obj_id = dat.obj_id " .
            "JOIN tree ON tree.child = ref.ref_id " .
            "WHERE title = " . $this->db->quote($object_title, 'text') . " " .
            "AND tree.tree = 1 ";
        $res = $this->db->query($q);
        $row = $this->db->fetchObject($res);

        // If the object title is not unique, we get rid of the domain.
        if ($row->count > 1) {
            $domain = null;
        }

        // If the domain contains illegal characters, we get rid of it.
        //if (domain != null && preg_match('/[\[\]\\]|[\x00-\x1f]/',$domain))
        // Fix for Mantis Bug: 7429 sending mail fails because of brakets
        // Fix for Mantis Bug: 9978 sending mail fails because of semicolon
        if ($domain != null && preg_match('/[\[\]\\]|[\x00-\x1f]|[\x28-\x29]|[;]/', $domain)) {
            $domain = null;
        }

        // If the domain contains special characters, we put square
        //   brackets around it.
        if ($domain != null &&
            (preg_match('/[()<>@,;:\\".\[\]]/', $domain) ||
                preg_match('/[^\x21-\x8f]/', $domain))
        ) {
            $domain = '[' . $domain . ']';
        }

        // If the role title is one of the ILIAS reserved role titles,
        //     we can use a shorthand version of it for the local part
        //     of the mailbox address.
        if (strpos($role_title, 'il_') === 0 && $domain != null) {
            $unambiguous_role_title = $role_title;

            $pos = strpos($role_title, '_', 3) + 1;
            $local_part = substr(
                $role_title,
                $pos,
                strrpos($role_title, '_') - $pos
            );
        } else {
            $unambiguous_role_title = 'il_role_' . $this->roleId;
        }

        // Determine if the local part is unique. If we don't have a
        // domain, the local part must be unique within the whole repositry.
        // If we do have a domain, the local part must be unique for that
        // domain.
        if ($domain == null) {
            $q = "SELECT COUNT(DISTINCT dat.obj_id) count " .
                "FROM object_data dat " .
                "JOIN object_reference ref ON ref.obj_id = dat.obj_id " .
                "JOIN tree ON tree.child = ref.ref_id " .
                "WHERE title = " . $this->db->quote($local_part, 'text') . " " .
                "AND tree.tree = 1 ";
        } else {
            $q = "SELECT COUNT(rd.obj_id) count " .
                "FROM object_data rd " .
                "JOIN rbac_fa fa ON rd.obj_id = fa.rol_id " .
                "JOIN tree t ON t.child = fa.parent " .
                "WHERE fa.assign = 'y' " .
                "AND t.child = " . $this->db->quote($object_ref, 'integer') . " " .
                "AND rd.title LIKE " . $this->db->quote(
                    '%' . preg_replace('/([_%])/', '\\\\$1', $local_part) . '%',
                    'text'
                ) . " ";
        }

        $res = $this->db->query($q);
        $row = $this->db->fetchObject($res);

        // if the local_part is not unique, we use the unambiguous role title
        //   instead for the local part of the mailbox address
        if ($row->count > 1) {
            $local_part = $unambiguous_role_title;
        }

        $use_phrase = true;

        // If the local part contains illegal characters, we use
        //     the unambiguous role title instead.
        if (preg_match('/[\\"\x00-\x1f]/', $local_part)) {
            $local_part = $unambiguous_role_title;
        } else {
            if (!preg_match('/^[\\x00-\\x7E]+$/i', $local_part)) {
                // 2013-12-05: According to #12283, we do not accept umlauts in the local part
                $local_part = $unambiguous_role_title;
                $use_phrase = false;
            }
        }

        // Add a "#" prefix to the local part
        $local_part = '#' . $local_part;

        // Put quotes around the role title, if needed
        if (preg_match('/[()<>@,;:.\[\]\x20]/', $local_part)) {
            $local_part = '"' . $local_part . '"';
        }

        $mailbox = ($domain == null) ?
            $local_part :
            $local_part . '@' . $domain;

        if ($this->localize) {
            if (substr($role_title, 0, 3) == 'il_') {
                $phrase = $this->lng->txt(substr($role_title, 0, strrpos($role_title, '_')));
            } else {
                $phrase = $role_title;
            }

            if ($use_phrase) {
                // make phrase RFC 822 conformant:
                // - strip excessive whitespace
                // - strip special characters
                $phrase = preg_replace('/\s\s+/', ' ', $phrase);
                $phrase = preg_replace('/[()<>@,;:\\".\[\]]/', '', $phrase);

                $mailbox = $phrase . ' <' . $mailbox . '>';
            }
        }

        try {
            $parser = $this->parserFactory->getParser((string) $mailbox);
            $parser->parse();

            return $mailbox;
        } catch (\ilException $e) {
            $res = $this->db->query("SELECT title FROM object_data WHERE obj_id = " . $this->db->quote($this->roleId, 'integer'));
            if ($row = $this->db->fetchObject($res)) {
                return '#' . $row->title;
            } else {
                return '';
            }
        }
    }
}
