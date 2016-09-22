<?php

namespace GetId3\Module\Audio;

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
// module.audio.aa.php                                         //
// module for analyzing Audible Audiobook files                //
// dependencies: NONE                                          //
//                                                            ///
/////////////////////////////////////////////////////////////////

/**
 * module for analyzing Audible Audiobook files
 *
 * @author James Heinrich <info@getid3.org>
 * @link http://getid3.sourceforge.net
 * @link http://www.getid3.org
 */
class Aa extends BaseHandler
{

    /**
     *
     * @return boolean
     */
    public function analyze()
    {
        $info = &$this->getid3->info;

        fseek($this->getid3->fp, $info['avdataoffset'], SEEK_SET);
        $AAheader  = fread($this->getid3->fp, 8);

        $magic = "\x57\x90\x75\x36";
        if (substr($AAheader, 4, 4) != $magic) {
            $info['error'][] = 'Expecting "'.Helper::PrintHexBytes($magic).'" at offset '.$info['avdataoffset'].', found "'.Helper::PrintHexBytes(substr($AAheader, 4, 4)).'"';

            return false;
        }

        // shortcut
        $info['aa'] = array();
        $thisfile_au = &$info['aa'];

        $info['fileformat']            = 'aa';
        $info['audio']['dataformat']   = 'aa';
$info['error'][] = 'Audible Audiobook (.aa) parsing not enabled in this version of GetId3Core() ['.$this->getid3->version().']';
return false;
        $info['audio']['bitrate_mode'] = 'cbr'; // is it?
        $thisfile_au['encoding']       = 'ISO-8859-1';

        $thisfile_au['filesize'] = Helper::BigEndian2Int(substr($AUheader,  0, 4));
        if ($thisfile_au['filesize'] > ($info['avdataend'] - $info['avdataoffset'])) {
            $info['warning'][] = 'Possible truncated file - expecting "'.$thisfile_au['filesize'].'" bytes of data, only found '.($info['avdataend'] - $info['avdataoffset']).' bytes"';
        }

        $info['audio']['bits_per_sample'] = 16; // is it?
        $info['audio']['sample_rate'] = $thisfile_au['sample_rate'];
        $info['audio']['channels']    = $thisfile_au['channels'];

        //$info['playtime_seconds'] = 0;
        //$info['audio']['bitrate'] = 0;
        return true;
    }
}
