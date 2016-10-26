<?php

namespace GetId3\Module\Misc;

use GetId3\Handler\BaseHandler;

/////////////////////////////////////////////////////////////////
/// GetId3() by James Heinrich <info@getid3.org>               //
//  available at http://getid3.sourceforge.net                 //
//            or http://www.getid3.org                         //
/////////////////////////////////////////////////////////////////
// See readme.txt for more details                             //
/////////////////////////////////////////////////////////////////
//                                                             //
// module.misc.pdf.php                                         //
// module for analyzing PDF files                              //
// dependencies: NONE                                          //
//                                                            ///
/////////////////////////////////////////////////////////////////

/**
 * module for analyzing PDF files
 *
 * @author James Heinrich <info@getid3.org>
 * @link http://getid3.sourceforge.net
 * @link http://www.getid3.org
 */
class Pdf extends BaseHandler
{

    /**
     *
     * @return boolean
     */
    public function analyze()
    {
        $info = &$this->getid3->info;

        $info['fileformat'] = 'pdf';

        $info['error'][] = 'PDF parsing not enabled in this version of GetId3Core() ['.$this->getid3->version().']';

        return false;

    }

}
