<?php

namespace GetId3\Module\Misc;

use GetId3\Handler\BaseHandler;
use GetId3\Lib\Helper;

/////////////////////////////////////////////////////////////////
/// GetId3() by James Heinrich <info@getid3.org>               //
//  available at http://getid3.sourceforge.net                 //
//            or http://www.getid3.org                         //
/////////////////////////////////////////////////////////////////
// See readme.txt for more details                             //
/////////////////////////////////////////////////////////////////
//                                                             //
// module.archive.doc.php                                      //
// module for analyzing MS Office (.doc, .xls, etc) files      //
// dependencies: NONE                                          //
//                                                            ///
/////////////////////////////////////////////////////////////////

/**
 * module for analyzing MS Office (.doc, .xls, etc) files
 *
 * @author James Heinrich <info@getid3.org>
 * @link http://getid3.sourceforge.net
 * @link http://www.getid3.org
 */
class Msoffice extends BaseHandler
{

    /**
     *
     * @return boolean
     */
    public function analyze()
    {
        $info = &$this->getid3->info;

        fseek($this->getid3->fp, $info['avdataoffset'], SEEK_SET);
        $DOCFILEheader = fread($this->getid3->fp, 8);
        $magic = "\xD0\xCF\x11\xE0\xA1\xB1\x1A\xE1";
        if (substr($DOCFILEheader, 0, 8) != $magic) {
            $info['error'][] = 'Expecting "'.Helper::PrintHexBytes($magic).'" at '.$info['avdataoffset'].', found '.Helper::PrintHexBytes(substr($DOCFILEheader, 0, 8)).' instead.';

            return false;
        }
        $info['fileformat'] = 'msoffice';

$info['error'][] = 'MS Office (.doc, .xls, etc) parsing not enabled in this version of GetId3Core() ['.$this->getid3->version().']';
return false;

    }

}
