<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Factory for creating purifier instances
*
* @author	Michael Jansen <mjansen@databay.de>
* @version	$Id$
*
*/
class ilHtmlPurifierFactory
{
    /**
    * Factory method for creating purifier instances
    *
    * @access	public
    * @param	string	$a_type	type for the concrete purifier instance
    * @return	ilHtmlPurifierInterface	A purifier instance
    * @static
    * @throws	ilHtmlPurifierNotFoundException
    *
    */
    public static function _getInstanceByType($a_type)
    {
        global $DIC;

        switch ($a_type) {
            case 'frm_post':
                require_once 'Services/Html/classes/class.ilHtmlForumPostPurifier.php';
                return new ilHtmlForumPostPurifier();
                break;

            case 'qpl_usersolution':
                require_once 'Modules/TestQuestionPool/classes/class.ilAssHtmlUserSolutionPurifier.php';
                return new ilAssHtmlUserSolutionPurifier();
                break;
        }
        
        require_once 'Services/Html/exceptions/class.ilHtmlPurifierNotFoundException.php';
        throw new ilHtmlPurifierNotFoundException(sprintf($DIC->language()->txt('frm_purifier_not_implemented_for_type_x'), $a_type));
    }
}
