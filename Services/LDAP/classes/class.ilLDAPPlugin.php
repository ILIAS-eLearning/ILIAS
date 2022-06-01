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
* Plugin definition
*
* @author Stefan Meyer <meyer@leifos.com>
*/
abstract class ilLDAPPlugin extends ilPlugin
{
    /**
     * Check if user data matches a keyword value combination
     */
    protected function checkValue(array $a_user_data, string $a_keyword, string $a_value) : bool
    {
        if (!$a_user_data[$a_keyword]) {
            return false;
        }

        if (is_array($a_user_data[$a_keyword])) {
            foreach ($a_user_data[$a_keyword] as $values) {
                if (strcasecmp(trim($values), $a_value) === 0) {
                    return true;
                }
            }
            return false;
        }

        if (strcasecmp(trim($a_user_data[$a_keyword]), trim($a_value)) === 0) {
            return true;
        }

        return false;
    }
}
