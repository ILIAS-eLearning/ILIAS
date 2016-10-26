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
// module.audio.nsv.php                                        //
// module for analyzing Nullsoft NSV files                     //
// dependencies: NONE                                          //
//                                                            ///
/////////////////////////////////////////////////////////////////

/**
 * module for analyzing Nullsoft NSV files
 *
 * @author James Heinrich <info@getid3.org>
 * @link http://getid3.sourceforge.net
 * @link http://www.getid3.org
 */
class Nsv extends BaseHandler
{

    /**
     *
     * @return boolean
     */
    public function analyze()
    {
        $info = &$this->getid3->info;

        fseek($this->getid3->fp, $info['avdataoffset'], SEEK_SET);
        $NSVheader = fread($this->getid3->fp, 4);

        switch ($NSVheader) {
            case 'NSVs':
                if ($this->getNSVsHeaderFilepointer(0)) {
                    $info['fileformat']          = 'nsv';
                    $info['audio']['dataformat'] = 'nsv';
                    $info['video']['dataformat'] = 'nsv';
                    $info['audio']['lossless']   = false;
                    $info['video']['lossless']   = false;
                }
                break;

            case 'NSVf':
                if ($this->getNSVfHeaderFilepointer(0)) {
                    $info['fileformat']          = 'nsv';
                    $info['audio']['dataformat'] = 'nsv';
                    $info['video']['dataformat'] = 'nsv';
                    $info['audio']['lossless']   = false;
                    $info['video']['lossless']   = false;
                    $this->getNSVsHeaderFilepointer($info['nsv']['NSVf']['header_length']);
                }
                break;

            default:
                $info['error'][] = 'Expecting "NSVs" or "NSVf" at offset '.$info['avdataoffset'].', found "'.Helper::PrintHexBytes($NSVheader).'"';

                return false;
                break;
        }

        if (!isset($info['nsv']['NSVf'])) {
            $info['warning'][] = 'NSVf header not present - cannot calculate playtime or bitrate';
        }

        return true;
    }

    /**
     *
     * @param  type    $fileoffset
     * @return boolean
     */
    public function getNSVsHeaderFilepointer($fileoffset)
    {
        $info = &$this->getid3->info;
        fseek($this->getid3->fp, $fileoffset, SEEK_SET);
        $NSVsheader = fread($this->getid3->fp, 28);
        $offset = 0;

        $info['nsv']['NSVs']['identifier']      =                  substr($NSVsheader, $offset, 4);
        $offset += 4;

        if ($info['nsv']['NSVs']['identifier'] != 'NSVs') {
            $info['error'][] = 'expected "NSVs" at offset ('.$fileoffset.'), found "'.$info['nsv']['NSVs']['identifier'].'" instead';
            unset($info['nsv']['NSVs']);

            return false;
        }

        $info['nsv']['NSVs']['offset']          = $fileoffset;

        $info['nsv']['NSVs']['video_codec']     =                              substr($NSVsheader, $offset, 4);
        $offset += 4;
        $info['nsv']['NSVs']['audio_codec']     =                              substr($NSVsheader, $offset, 4);
        $offset += 4;
        $info['nsv']['NSVs']['resolution_x']    = Helper::LittleEndian2Int(substr($NSVsheader, $offset, 2));
        $offset += 2;
        $info['nsv']['NSVs']['resolution_y']    = Helper::LittleEndian2Int(substr($NSVsheader, $offset, 2));
        $offset += 2;

        $info['nsv']['NSVs']['framerate_index'] = Helper::LittleEndian2Int(substr($NSVsheader, $offset, 1));
        $offset += 1;
        //$info['nsv']['NSVs']['unknown1b']       = GetId3_lib::LittleEndian2Int(substr($NSVsheader, $offset, 1));
        $offset += 1;
        //$info['nsv']['NSVs']['unknown1c']       = GetId3_lib::LittleEndian2Int(substr($NSVsheader, $offset, 1));
        $offset += 1;
        //$info['nsv']['NSVs']['unknown1d']       = GetId3_lib::LittleEndian2Int(substr($NSVsheader, $offset, 1));
        $offset += 1;
        //$info['nsv']['NSVs']['unknown2a']       = GetId3_lib::LittleEndian2Int(substr($NSVsheader, $offset, 1));
        $offset += 1;
        //$info['nsv']['NSVs']['unknown2b']       = GetId3_lib::LittleEndian2Int(substr($NSVsheader, $offset, 1));
        $offset += 1;
        //$info['nsv']['NSVs']['unknown2c']       = GetId3_lib::LittleEndian2Int(substr($NSVsheader, $offset, 1));
        $offset += 1;
        //$info['nsv']['NSVs']['unknown2d']       = GetId3_lib::LittleEndian2Int(substr($NSVsheader, $offset, 1));
        $offset += 1;

        switch ($info['nsv']['NSVs']['audio_codec']) {
            case 'PCM ':
                $info['nsv']['NSVs']['bits_channel'] = Helper::LittleEndian2Int(substr($NSVsheader, $offset, 1));
                $offset += 1;
                $info['nsv']['NSVs']['channels']     = Helper::LittleEndian2Int(substr($NSVsheader, $offset, 1));
                $offset += 1;
                $info['nsv']['NSVs']['sample_rate']  = Helper::LittleEndian2Int(substr($NSVsheader, $offset, 2));
                $offset += 2;

                $info['audio']['sample_rate']        = $info['nsv']['NSVs']['sample_rate'];
                break;

            case 'MP3 ':
            case 'NONE':
            default:
                //$info['nsv']['NSVs']['unknown3']     = GetId3_lib::LittleEndian2Int(substr($NSVsheader, $offset, 4));
                $offset += 4;
                break;
        }

        $info['video']['resolution_x']       = $info['nsv']['NSVs']['resolution_x'];
        $info['video']['resolution_y']       = $info['nsv']['NSVs']['resolution_y'];
        $info['nsv']['NSVs']['frame_rate']   = $this->NSVframerateLookup($info['nsv']['NSVs']['framerate_index']);
        $info['video']['frame_rate']         = $info['nsv']['NSVs']['frame_rate'];
        $info['video']['bits_per_sample']    = 24;
        $info['video']['pixel_aspect_ratio'] = (float) 1;

        return true;
    }

    /**
     *
     * @param  type    $fileoffset
     * @param  type    $getTOCoffsets
     * @return boolean
     */
    public function getNSVfHeaderFilepointer($fileoffset, $getTOCoffsets=false)
    {
        $info = &$this->getid3->info;
        fseek($this->getid3->fp, $fileoffset, SEEK_SET);
        $NSVfheader = fread($this->getid3->fp, 28);
        $offset = 0;

        $info['nsv']['NSVf']['identifier']    =                  substr($NSVfheader, $offset, 4);
        $offset += 4;

        if ($info['nsv']['NSVf']['identifier'] != 'NSVf') {
            $info['error'][] = 'expected "NSVf" at offset ('.$fileoffset.'), found "'.$info['nsv']['NSVf']['identifier'].'" instead';
            unset($info['nsv']['NSVf']);

            return false;
        }

        $info['nsv']['NSVs']['offset']        = $fileoffset;

        $info['nsv']['NSVf']['header_length'] = Helper::LittleEndian2Int(substr($NSVfheader, $offset, 4));
        $offset += 4;
        $info['nsv']['NSVf']['file_size']     = Helper::LittleEndian2Int(substr($NSVfheader, $offset, 4));
        $offset += 4;

        if ($info['nsv']['NSVf']['file_size'] > $info['avdataend']) {
            $info['warning'][] = 'truncated file - NSVf header indicates '.$info['nsv']['NSVf']['file_size'].' bytes, file actually '.$info['avdataend'].' bytes';
        }

        $info['nsv']['NSVf']['playtime_ms']   = Helper::LittleEndian2Int(substr($NSVfheader, $offset, 4));
        $offset += 4;
        $info['nsv']['NSVf']['meta_size']     = Helper::LittleEndian2Int(substr($NSVfheader, $offset, 4));
        $offset += 4;
        $info['nsv']['NSVf']['TOC_entries_1'] = Helper::LittleEndian2Int(substr($NSVfheader, $offset, 4));
        $offset += 4;
        $info['nsv']['NSVf']['TOC_entries_2'] = Helper::LittleEndian2Int(substr($NSVfheader, $offset, 4));
        $offset += 4;

        if ($info['nsv']['NSVf']['playtime_ms'] == 0) {
            $info['error'][] = 'Corrupt NSV file: NSVf.playtime_ms == zero';

            return false;
        }

        $NSVfheader .= fread($this->getid3->fp, $info['nsv']['NSVf']['meta_size'] + (4 * $info['nsv']['NSVf']['TOC_entries_1']) + (4 * $info['nsv']['NSVf']['TOC_entries_2']));
        $NSVfheaderlength = strlen($NSVfheader);
        $info['nsv']['NSVf']['metadata']      =                  substr($NSVfheader, $offset, $info['nsv']['NSVf']['meta_size']);
        $offset += $info['nsv']['NSVf']['meta_size'];

        if ($getTOCoffsets) {
            $TOCcounter = 0;
            while ($TOCcounter < $info['nsv']['NSVf']['TOC_entries_1']) {
                if ($TOCcounter < $info['nsv']['NSVf']['TOC_entries_1']) {
                    $info['nsv']['NSVf']['TOC_1'][$TOCcounter] = Helper::LittleEndian2Int(substr($NSVfheader, $offset, 4));
                    $offset += 4;
                    $TOCcounter++;
                }
            }
        }

        if (trim($info['nsv']['NSVf']['metadata']) != '') {
            $info['nsv']['NSVf']['metadata'] = str_replace('`', "\x01", $info['nsv']['NSVf']['metadata']);
            $CommentPairArray = explode("\x01".' ', $info['nsv']['NSVf']['metadata']);
            foreach ($CommentPairArray as $CommentPair) {
                if (strstr($CommentPair, '='."\x01")) {
                    list($key, $value) = explode('='."\x01", $CommentPair, 2);
                    $info['nsv']['comments'][strtolower($key)][] = trim(str_replace("\x01", '', $value));
                }
            }
        }

        $info['playtime_seconds'] = $info['nsv']['NSVf']['playtime_ms'] / 1000;
        $info['bitrate']          = ($info['nsv']['NSVf']['file_size'] * 8) / $info['playtime_seconds'];

        return true;
    }

    /**
     *
     * @staticvar array $NSVframerateLookup
     * @param  type $framerateindex
     * @return type
     */
    public static function NSVframerateLookup($framerateindex)
    {
        if ($framerateindex <= 127) {
            return (float) $framerateindex;
        }
        static $NSVframerateLookup = array();
        if (empty($NSVframerateLookup)) {
            $NSVframerateLookup[129] = (float) 29.970;
            $NSVframerateLookup[131] = (float) 23.976;
            $NSVframerateLookup[133] = (float) 14.985;
            $NSVframerateLookup[197] = (float) 59.940;
            $NSVframerateLookup[199] = (float) 47.952;
        }

        return (isset($NSVframerateLookup[$framerateindex]) ? $NSVframerateLookup[$framerateindex] : false);
    }
}
