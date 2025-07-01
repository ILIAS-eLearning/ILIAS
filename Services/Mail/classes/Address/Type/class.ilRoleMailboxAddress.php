<?php

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

declare(strict_types=1);

/**
 * Class ilRoleMailboxAddress
 * @author Werner Randelshofer <wrandels@hsw.fhz.ch>
 * @author Stefan Meyer <meyer@leifos.com>
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilRoleMailboxAddress
{
    protected ilMailRfc822AddressParserFactory $parserFactory;
    protected ilDBInterface $db;
    protected ilLanguage $lng;

    public function __construct(
        protected int $roleId,
        protected bool $localize = true,
        ilMailRfc822AddressParserFactory $parserFactory = null,
        ilDBInterface $db = null,
        ilLanguage $lng = null
    ) {
        global $DIC;

        if (null === $db) {
            $db = $DIC->database();
        }
        $this->db = $db;

        if (null === $lng) {
            $lng = $DIC->language();
        }
        $this->lng = $lng;

        if (null === $parserFactory) {
            $parserFactory = new ilMailRfc822AddressParserFactory();
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
    public function value(): string
    {
        // Retrieve the role title and the object title.
        $query = 'SELECT rdat.title role_title, odat.title object_title, ' .
            ' oref.ref_id object_ref ' .
            'FROM object_data rdat ' .
            'INNER JOIN role_data roledat ON roledat.role_id = rdat.obj_id ' .
            'INNER JOIN rbac_fa fa ON fa.rol_id = rdat.obj_id AND fa.assign = ' . $this->db->quote('y', ilDBConstants::T_TEXT) . ' ' .
            'INNER JOIN tree rtree ON rtree.child = fa.parent ' .
            'INNER JOIN object_reference oref ON oref.ref_id = rtree.child ' .
            'INNER JOIN object_data odat ON odat.obj_id = oref.obj_id ' .
            'WHERE rdat.obj_id = ' . $this->db->quote($this->roleId, ilDBConstants::T_INTEGER);
        $res = $this->db->query($query);
        if (($row = $this->db->fetchObject($res)) === null) {
            return '';
        }

        $object_title = $row->object_title;
        $object_ref = (int) $row->object_ref;
        $role_title = $row->role_title;

        // In a perfect world, we could use the object_title in the
        // domain part of the mailbox address, and the role title
        // with prefix '#' in the local part of the mailbox address.
        $domain = $object_title;
        $local_part = $role_title;

        // Determine if the object title is unique (we exclude trashed obects)
        $q = 'SELECT COUNT(DISTINCT dat.obj_id) AS count ' .
            'FROM object_data dat ' .
            'INNER JOIN object_reference ref ON ref.obj_id = dat.obj_id AND ref.deleted IS NULL ' .
            'INNER JOIN tree ON tree.child = ref.ref_id AND tree.tree = ' . $this->db->quote(1, ilDBConstants::T_INTEGER) . ' ' .
            'WHERE dat.title = ' . $this->db->quote($object_title, ilDBConstants::T_TEXT);
        $res = $this->db->query($q);
        $row = $this->db->fetchObject($res);

        // If the object title is not unique/does not exists, we get rid of the domain.
        if ($row->count !== 1) {
            $domain = null;
        }

        // If the domain contains illegal characters, we get rid of it.
        //if (domain != null && preg_match('/[\[\]\\]|[\x00-\x1f]/',$domain))
        // Fix for Mantis Bug: 7429 sending mail fails because of brakets
        // Fix for Mantis Bug: 9978 sending mail fails because of semicolon
        if ($domain !== null && preg_match('/[\[\]\\]|[\x00-\x1f]|[\x28-\x29]|[;]/', (string) $domain)) {
            $domain = null;
        }

        // If the domain contains special characters, we put square
        //   brackets around it.
        if ($domain !== null &&
            (preg_match('/[()<>@,;:\\".\[\]]/', (string) $domain) ||
                preg_match('/[^\x21-\x8f]/', (string) $domain))
        ) {
            $domain = '[' . $domain . ']';
        }

        // If the role title is one of the ILIAS reserved role titles,
        //     we can use a shorthand version of it for the local part
        //     of the mailbox address.
        if ($domain !== null && str_starts_with($role_title, 'il_')) {
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
        if ($domain === null) {
            // https://mantis.ilias.de/view.php?id=45319
            $q = 'SELECT COUNT(DISTINCT rdat.role_id) AS count ' .
                'FROM object_data dat ' .
                'INNER JOIN role_data rdat ON rdat.role_id = dat.obj_id ' .
                'INNER JOIN rbac_fa fa ON fa.rol_id = rdat.role_id ' .
                'INNER JOIN tree t ON t.child = fa.parent ' .
                'INNER JOIN object_reference oref ON oref.ref_id = t.child ' .
                'WHERE dat.title = ' . $this->db->quote($local_part, ilDBConstants::T_TEXT);
        } else {
            $q = 'SELECT COUNT(rd.obj_id) AS count ' .
                'FROM object_data rd ' .
                'INNER JOIN rbac_fa fa ON fa.rol_id = rd.obj_id AND fa.assign = ' . $this->db->quote('y', ilDBConstants::T_TEXT) . ' ' .
                'INNER JOIN tree t ON t.child = fa.parent AND t.child = ' . $this->db->quote($object_ref, ilDBConstants::T_INTEGER) . ' ' .
                'WHERE rd.title LIKE ' . $this->db->quote(
                    '%' . preg_replace('/([_%])/', '\\\\$1', $local_part) . '%',
                    ilDBConstants::T_TEXT
                ) . ' ';
        }

        $res = $this->db->query($q);
        $row = $this->db->fetchObject($res);

        // if the local_part is not unique, we use the unambiguous role title
        //   instead for the local part of the mailbox address
        if ($row->count !== 1) {
            $local_part = $unambiguous_role_title;
        }

        $use_phrase = true;

        // If the local part contains illegal characters, we use
        //     the unambiguous role title instead.
        if (preg_match('/[\\"\x00-\x1f]/', (string) $local_part)) {
            $local_part = $unambiguous_role_title;
        } elseif (!preg_match('/^[\\x00-\\x7E]+$/i', (string) $local_part)) {
            // 2013-12-05: According to #12283, we do not accept umlauts in the local part
            $local_part = $unambiguous_role_title;
            $use_phrase = false;
        }

        // Add a "#" prefix to the local part
        $local_part = '#' . $local_part;

        // Put quotes around the role title, if needed
        if (preg_match('/[()<>@,;:.\[\]\x20]/', $local_part)) {
            $local_part = '"' . $local_part . '"';
        }

        $mailbox = ($domain === null) ?
            $local_part :
            $local_part . '@' . $domain;

        if ($this->localize) {
            if (str_starts_with($role_title, 'il_')) {
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
            $parser = $this->parserFactory->getParser($mailbox);
            $parser->parse();

            return $mailbox;
        } catch (ilMailException) {
            $res = $this->db->query(
                'SELECT od.title
                 FROM object_data od
                 INNER JOIN role_data rd ON rd.role_id = od.obj_id
                 WHERE od.obj_id = ' . $this->db->quote($this->roleId, ilDBConstants::T_INTEGER) . '
                   AND NOT EXISTS (
                       SELECT 1
                       FROM object_data maybe_same_role_od
                       INNER JOIN role_data maybe_same_role_rd ON maybe_same_role_rd.role_id = maybe_same_role_od.obj_id
                       WHERE maybe_same_role_od.title = od.title
                         AND maybe_same_role_od.obj_id != od.obj_id
                   )'
            );
            if (($row = $this->db->fetchObject($res)) !== null) {
                return '#' . $row->title;
            }

            return '#il_role_' . $this->roleId;
        }
    }
}
