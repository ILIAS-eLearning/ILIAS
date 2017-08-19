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
// module.misc.exe.php                                         //
// module for analyzing EXE files                              //
// dependencies: NONE                                          //
//                                                            ///
/////////////////////////////////////////////////////////////////

/**
 * module for analyzing EXE files
 *
 * @author James Heinrich <info@getid3.org>
 * @link http://getid3.sourceforge.net
 * @link http://www.getid3.org
 */
class Exe extends BaseHandler
{

    /**
     *
     * @return boolean
     */
    public function analyze()
    {
        $info = &$this->getid3->info;

        fseek($this->getid3->fp, $info['avdataoffset'], SEEK_SET);
        $EXEheader = fread($this->getid3->fp, 28);

        $magic = 'MZ';
        if (substr($EXEheader, 0, 2) != $magic) {
            $info['error'][] = 'Expecting "'.Helper::PrintHexBytes($magic).'" at offset '.$info['avdataoffset'].', found "'.Helper::PrintHexBytes(substr($EXEheader, 0, 2)).'"';

            return false;
        }

        $info['fileformat'] = 'exe';
        $info['exe']['mz']['magic'] = 'MZ';

        $info['exe']['mz']['raw']['last_page_size']          = Helper::LittleEndian2Int(substr($EXEheader,  2, 2));
        $info['exe']['mz']['raw']['page_count']              = Helper::LittleEndian2Int(substr($EXEheader,  4, 2));
        $info['exe']['mz']['raw']['relocation_count']        = Helper::LittleEndian2Int(substr($EXEheader,  6, 2));
        $info['exe']['mz']['raw']['header_paragraphs']       = Helper::LittleEndian2Int(substr($EXEheader,  8, 2));
        $info['exe']['mz']['raw']['min_memory_paragraphs']   = Helper::LittleEndian2Int(substr($EXEheader, 10, 2));
        $info['exe']['mz']['raw']['max_memory_paragraphs']   = Helper::LittleEndian2Int(substr($EXEheader, 12, 2));
        $info['exe']['mz']['raw']['initial_ss']              = Helper::LittleEndian2Int(substr($EXEheader, 14, 2));
        $info['exe']['mz']['raw']['initial_sp']              = Helper::LittleEndian2Int(substr($EXEheader, 16, 2));
        $info['exe']['mz']['raw']['checksum']                = Helper::LittleEndian2Int(substr($EXEheader, 18, 2));
        $info['exe']['mz']['raw']['cs_ip']                   = Helper::LittleEndian2Int(substr($EXEheader, 20, 4));
        $info['exe']['mz']['raw']['relocation_table_offset'] = Helper::LittleEndian2Int(substr($EXEheader, 24, 2));
        $info['exe']['mz']['raw']['overlay_number']          = Helper::LittleEndian2Int(substr($EXEheader, 26, 2));

        $info['exe']['mz']['byte_size']          = (($info['exe']['mz']['raw']['page_count'] - 1)) * 512 + $info['exe']['mz']['raw']['last_page_size'];
        $info['exe']['mz']['header_size']        = $info['exe']['mz']['raw']['header_paragraphs'] * 16;
        $info['exe']['mz']['memory_minimum']     = $info['exe']['mz']['raw']['min_memory_paragraphs'] * 16;
        $info['exe']['mz']['memory_recommended'] = $info['exe']['mz']['raw']['max_memory_paragraphs'] * 16;

        $info['error'][] = 'EXE parsing not enabled in this version of GetId3Core() ['.$this->getid3->version().']';

        return false;

    }

}
