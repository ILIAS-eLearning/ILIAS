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
// module.audio.shorten.php                                    //
// module for analyzing Shorten Audio files                    //
// dependencies: NONE                                          //
//                                                            ///
/////////////////////////////////////////////////////////////////

/**
 * module for analyzing Shorten Audio files
 *
 * @author James Heinrich <info@getid3.org>
 * @link http://getid3.sourceforge.net
 * @link http://www.getid3.org
 */
class Rkau extends BaseHandler
{

    /**
     *
     * @return boolean
     */
    public function analyze()
    {
        $info = &$this->getid3->info;

        fseek($this->getid3->fp, $info['avdataoffset'], SEEK_SET);
        $RKAUHeader = fread($this->getid3->fp, 20);
        $magic = 'RKA';
        if (substr($RKAUHeader, 0, 3) != $magic) {
            $info['error'][] = 'Expecting "'.Helper::PrintHexBytes($magic).'" at offset '.$info['avdataoffset'].', found "'.Helper::PrintHexBytes(substr($RKAUHeader, 0, 3)).'"';

            return false;
        }

        $info['fileformat']            = 'rkau';
        $info['audio']['dataformat']   = 'rkau';
        $info['audio']['bitrate_mode'] = 'vbr';

        $info['rkau']['raw']['version']   = Helper::LittleEndian2Int(substr($RKAUHeader, 3, 1));
        $info['rkau']['version']          = '1.'.str_pad($info['rkau']['raw']['version'] & 0x0F, 2, '0', STR_PAD_LEFT);
        if (($info['rkau']['version'] > 1.07) || ($info['rkau']['version'] < 1.06)) {
            $info['error'][] = 'This version of GetId3Core() ['.$this->getid3->version().'] can only parse RKAU files v1.06 and 1.07 (this file is v'.$info['rkau']['version'].')';
            unset($info['rkau']);

            return false;
        }

        $info['rkau']['source_bytes']     = Helper::LittleEndian2Int(substr($RKAUHeader,  4, 4));
        $info['rkau']['sample_rate']      = Helper::LittleEndian2Int(substr($RKAUHeader,  8, 4));
        $info['rkau']['channels']         = Helper::LittleEndian2Int(substr($RKAUHeader, 12, 1));
        $info['rkau']['bits_per_sample']  = Helper::LittleEndian2Int(substr($RKAUHeader, 13, 1));

        $info['rkau']['raw']['quality']   = Helper::LittleEndian2Int(substr($RKAUHeader, 14, 1));
        $this->RKAUqualityLookup($info['rkau']);

        $info['rkau']['raw']['flags']            = Helper::LittleEndian2Int(substr($RKAUHeader, 15, 1));
        $info['rkau']['flags']['joint_stereo']   = (bool) (!($info['rkau']['raw']['flags'] & 0x01));
        $info['rkau']['flags']['streaming']      =  (bool) ($info['rkau']['raw']['flags'] & 0x02);
        $info['rkau']['flags']['vrq_lossy_mode'] =  (bool) ($info['rkau']['raw']['flags'] & 0x04);

        if ($info['rkau']['flags']['streaming']) {
            $info['avdataoffset'] += 20;
            $info['rkau']['compressed_bytes']  = Helper::LittleEndian2Int(substr($RKAUHeader, 16, 4));
        } else {
            $info['avdataoffset'] += 16;
            $info['rkau']['compressed_bytes'] = $info['avdataend'] - $info['avdataoffset'] - 1;
        }
        // Note: compressed_bytes does not always equal what appears to be the actual number of compressed bytes,
        // sometimes it's more, sometimes less. No idea why(?)

        $info['audio']['lossless']        = $info['rkau']['lossless'];
        $info['audio']['channels']        = $info['rkau']['channels'];
        $info['audio']['bits_per_sample'] = $info['rkau']['bits_per_sample'];
        $info['audio']['sample_rate']     = $info['rkau']['sample_rate'];

        $info['playtime_seconds']         = $info['rkau']['source_bytes'] / ($info['rkau']['sample_rate'] * $info['rkau']['channels'] * ($info['rkau']['bits_per_sample'] / 8));
        $info['audio']['bitrate']         = ($info['rkau']['compressed_bytes'] * 8) / $info['playtime_seconds'];

        return true;

    }

    /**
     *
     * @param  type    $RKAUdata
     * @return boolean
     */
    public function RKAUqualityLookup(&$RKAUdata)
    {
        $level   = ($RKAUdata['raw']['quality'] & 0xF0) >> 4;
        $quality =  $RKAUdata['raw']['quality'] & 0x0F;

        $RKAUdata['lossless']          = (($quality == 0) ? true : false);
        $RKAUdata['compression_level'] = $level + 1;
        if (!$RKAUdata['lossless']) {
            $RKAUdata['quality_setting'] = $quality;
        }

        return true;
    }

}
