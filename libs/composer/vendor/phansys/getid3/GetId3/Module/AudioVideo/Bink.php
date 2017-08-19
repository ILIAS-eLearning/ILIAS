<?php

namespace GetId3\Module\AudioVideo;

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
// module.audio.bink.php                                       //
// module for analyzing Bink or Smacker audio-video files      //
// dependencies: NONE                                          //
//                                                            ///
/////////////////////////////////////////////////////////////////

/**
 * module for analyzing Bink or Smacker audio-video files
 *
 * @author James Heinrich <info@getid3.org>
 * @link http://getid3.sourceforge.net
 * @link http://www.getid3.org
 */
class Bink extends BaseHandler
{

    /**
     *
     * @return boolean
     */
    public function analyze()
    {
        $info = &$this->getid3->info;

        $info['error'][] = 'Bink / Smacker files not properly processed by this version of GetId3Core() [' . $this->getid3->version() . ']';

        fseek($this->getid3->fp, $info['avdataoffset'], SEEK_SET);
        $fileTypeID = fread($this->getid3->fp, 3);
        switch ($fileTypeID) {
            case 'BIK':
                return $this->ParseBink();
                break;

            case 'SMK':
                return $this->ParseSmacker();
                break;

            default:
                $info['error'][] = 'Expecting "BIK" or "SMK" at offset ' . $info['avdataoffset'] . ', found "' . Helper::PrintHexBytes($fileTypeID) . '"';

                return false;
                break;
        }

        return true;
    }

    /**
     *
     * @return boolean
     */
    public function ParseBink()
    {
        $info = &$this->getid3->info;
        $info['fileformat'] = 'bink';
        $info['video']['dataformat'] = 'bink';

        $fileData = 'BIK' . fread($this->getid3->fp, 13);

        $info['bink']['data_size'] = Helper::LittleEndian2Int(substr($fileData,
                                                                                4,
                                                                                4));
        $info['bink']['frame_count'] = Helper::LittleEndian2Int(substr($fileData,
                                                                                  8,
                                                                                  2));

        if (($info['avdataend'] - $info['avdataoffset']) != ($info['bink']['data_size'] + 8)) {
            $info['error'][] = 'Probably truncated file: expecting ' . $info['bink']['data_size'] . ' bytes, found ' . ($info['avdataend'] - $info['avdataoffset']);
        }

        return true;
    }

    /**
     *
     * @return boolean
     */
    public function ParseSmacker()
    {
        $info = &$this->getid3->info;
        $info['fileformat'] = 'smacker';
        $info['video']['dataformat'] = 'smacker';

        return true;
    }
}
