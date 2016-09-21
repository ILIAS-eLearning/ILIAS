<?php

namespace GetId3\Module\Audio;

use GetId3\Handler\BaseHandler;
use GetId3\Lib\Helper;
use GetId3\GetId3Core;

/////////////////////////////////////////////////////////////////
/// GetId3() by James Heinrich <info@getid3.org>               //
//  available at http://getid3.sourceforge.net                 //
//            or http://www.getid3.org                         //
/////////////////////////////////////////////////////////////////
// See readme.txt for more details                             //
/////////////////////////////////////////////////////////////////
//                                                             //
// module.audio.flac.php                                       //
// module for analyzing FLAC and OggFLAC audio files           //
// dependencies: module.audio.ogg.php                          //
//                                                            ///
/////////////////////////////////////////////////////////////////

/**
 * module for analyzing FLAC and OggFLAC audio files
 *
 * @author James Heinrich <info@getid3.org>
 * @link http://getid3.sourceforge.net
 * @link http://www.getid3.org
 */
class Flac extends BaseHandler
{

    /**
     *
     * @return type
     */
    public function analyze()
    {
        $info = &$this->getid3->info;

        // http://flac.sourceforge.net/format.html

        $this->fseek($info['avdataoffset']);
        $StreamMarker = $this->fread(4);
        $magic = 'fLaC';
        if ($StreamMarker != $magic) {
            return $this->error('Expecting "'.Helper::PrintHexBytes($magic).'" at offset '.$info['avdataoffset'].', found "'.Helper::PrintHexBytes($StreamMarker).'"');
        }
        $info['fileformat']            = 'flac';
        $info['audio']['dataformat']   = 'flac';
        $info['audio']['bitrate_mode'] = 'vbr';
        $info['audio']['lossless']     = true;

        return $this->parseMETAdata();
    }

    /**
     *
     * @return boolean
     */
    public function parseMETAdata()
    {
        $info = &$this->getid3->info;
        do {
            $BlockOffset   = $this->ftell();
            $BlockHeader   = $this->fread(4);
            $LastBlockFlag = (bool) (Helper::BigEndian2Int(substr($BlockHeader, 0, 1)) & 0x80);
            $BlockType     = Helper::BigEndian2Int(substr($BlockHeader, 0, 1)) & 0x7F;
            $BlockLength   = Helper::BigEndian2Int(substr($BlockHeader, 1, 3));
            $BlockTypeText = self::metaBlockTypeLookup($BlockType);

            if (($BlockOffset + 4 + $BlockLength) > $info['avdataend']) {
                $this->error('METADATA_BLOCK_HEADER.BLOCK_TYPE ('.$BlockType.') at offset '.$BlockOffset.' extends beyond end of file');
                break;
            }
            if ($BlockLength < 1) {
                $this->error('METADATA_BLOCK_HEADER.BLOCK_LENGTH ('.$BlockLength.') at offset '.$BlockOffset.' is invalid');
                break;
            }

            $info['flac'][$BlockTypeText]['raw'] = array();
            $BlockTypeText_raw = &$info['flac'][$BlockTypeText]['raw'];

            $BlockTypeText_raw['offset']          = $BlockOffset;
            $BlockTypeText_raw['last_meta_block'] = $LastBlockFlag;
            $BlockTypeText_raw['block_type']      = $BlockType;
            $BlockTypeText_raw['block_type_text'] = $BlockTypeText;
            $BlockTypeText_raw['block_length']    = $BlockLength;
            if ($BlockTypeText_raw['block_type'] != 0x06) { // do not read attachment data automatically
                $BlockTypeText_raw['block_data']  = $this->fread($BlockLength);
            }

            switch ($BlockTypeText) {
                case 'STREAMINFO':     // 0x00
                    if (!$this->parseSTREAMINFO($BlockTypeText_raw['block_data'])) {
                        return false;
                    }
                    break;

                case 'PADDING':        // 0x01
                    // ignore
                    break;

                case 'APPLICATION':    // 0x02
                    if (!$this->parseAPPLICATION($BlockTypeText_raw['block_data'])) {
                        return false;
                    }
                    break;

                case 'SEEKTABLE':      // 0x03
                    if (!$this->parseSEEKTABLE($BlockTypeText_raw['block_data'])) {
                        return false;
                    }
                    break;

                case 'VORBIS_COMMENT': // 0x04
                    if (!$this->parseVORBIS_COMMENT($BlockTypeText_raw['block_data'])) {
                        return false;
                    }
                    break;

                case 'CUESHEET':       // 0x05
                    if (!$this->parseCUESHEET($BlockTypeText_raw['block_data'])) {
                        return false;
                    }
                    break;

                case 'PICTURE':        // 0x06
                    if (!$this->parsePICTURE($BlockTypeText_raw)) {
                        return false;
                    }
                    break;

                default:
                    $this->warning('Unhandled METADATA_BLOCK_HEADER.BLOCK_TYPE ('.$BlockType.') at offset '.$BlockOffset);
            }

            unset($info['flac'][$BlockTypeText]['raw']);
            $info['avdataoffset'] = $this->ftell();
        } while ($LastBlockFlag === false);

        // handle tags
        if (!empty($info['flac']['VORBIS_COMMENT']['comments'])) {
            $info['flac']['comments'] = $info['flac']['VORBIS_COMMENT']['comments'];
        }
        if (!empty($info['flac']['VORBIS_COMMENT']['vendor'])) {
            $info['audio']['encoder'] = str_replace('reference ', '', $info['flac']['VORBIS_COMMENT']['vendor']);
        }

        // copy attachments to 'comments' array if nesesary
        if (isset($info['flac']['PICTURE']) && $this->getid3->option_save_attachments === GetId3Core::ATTACHMENTS_INLINE) {
            foreach ($info['flac']['PICTURE'] as $key => $valuearray) {
                if (!empty($valuearray['image_mime']) && !empty($valuearray['data'])) {
                    $info['flac']['comments']['picture'][] = array('image_mime' => $valuearray['image_mime'], 'data' => $valuearray['data']);
                    unset($info['flac']['PICTURE'][$key]);
                }
            }
        }

        if (isset($info['flac']['STREAMINFO'])) {
            if (!$this->isDependencyFor('matroska')) {
                $info['flac']['compressed_audio_bytes'] = $info['avdataend'] - $info['avdataoffset'];
            }
            $info['flac']['uncompressed_audio_bytes'] = $info['flac']['STREAMINFO']['samples_stream'] * $info['flac']['STREAMINFO']['channels'] * ($info['flac']['STREAMINFO']['bits_per_sample'] / 8);
            if ($info['flac']['uncompressed_audio_bytes'] == 0) {
                return $this->error('Corrupt FLAC file: uncompressed_audio_bytes == zero');
            }
            if (!$this->isDependencyFor('matroska')) {
                $info['flac']['compression_ratio'] = $info['flac']['compressed_audio_bytes'] / $info['flac']['uncompressed_audio_bytes'];
            }
        }

        // set md5_data_source - built into flac 0.5+
        if (isset($info['flac']['STREAMINFO']['audio_signature'])) {

            if ($info['flac']['STREAMINFO']['audio_signature'] === str_repeat("\x00", 16)) {
                $this->warning('FLAC STREAMINFO.audio_signature is null (known issue with libOggFLAC)');
            } else {
                $info['md5_data_source'] = '';
                $md5 = $info['flac']['STREAMINFO']['audio_signature'];
                for ($i = 0; $i < strlen($md5); $i++) {
                    $info['md5_data_source'] .= str_pad(dechex(ord($md5[$i])), 2, '00', STR_PAD_LEFT);
                }
                if (!preg_match('/^[0-9a-f]{32}$/', $info['md5_data_source'])) {
                    unset($info['md5_data_source']);
                }
            }
        }

        if (isset($info['flac']['STREAMINFO']['bits_per_sample'])) {
            $info['audio']['bits_per_sample'] = $info['flac']['STREAMINFO']['bits_per_sample'];
            if ($info['audio']['bits_per_sample'] == 8) {
                // special case
                // must invert sign bit on all data bytes before MD5'ing to match FLAC's calculated value
                // MD5sum calculates on unsigned bytes, but FLAC calculated MD5 on 8-bit audio data as signed
                $this->warning('FLAC calculates MD5 data strangely on 8-bit audio, so the stored md5_data_source value will not match the decoded WAV file');
            }
        }

        return true;
    }

    /**
     *
     * @param  type    $BlockData
     * @return boolean
     */
    private function parseSTREAMINFO($BlockData)
    {
        $info = &$this->getid3->info;

        $info['flac']['STREAMINFO'] = array();
        $streaminfo = &$info['flac']['STREAMINFO'];

        $streaminfo['min_block_size']  = Helper::BigEndian2Int(substr($BlockData, 0, 2));
        $streaminfo['max_block_size']  = Helper::BigEndian2Int(substr($BlockData, 2, 2));
        $streaminfo['min_frame_size']  = Helper::BigEndian2Int(substr($BlockData, 4, 3));
        $streaminfo['max_frame_size']  = Helper::BigEndian2Int(substr($BlockData, 7, 3));

        $SRCSBSS                       = Helper::BigEndian2Bin(substr($BlockData, 10, 8));
        $streaminfo['sample_rate']     = Helper::Bin2Dec(substr($SRCSBSS,  0, 20));
        $streaminfo['channels']        = Helper::Bin2Dec(substr($SRCSBSS, 20,  3)) + 1;
        $streaminfo['bits_per_sample'] = Helper::Bin2Dec(substr($SRCSBSS, 23,  5)) + 1;
        $streaminfo['samples_stream']  = Helper::Bin2Dec(substr($SRCSBSS, 28, 36));

        $streaminfo['audio_signature'] = substr($BlockData, 18, 16);

        if (!empty($streaminfo['sample_rate'])) {

            $info['audio']['bitrate_mode']    = 'vbr';
            $info['audio']['sample_rate']     = $streaminfo['sample_rate'];
            $info['audio']['channels']        = $streaminfo['channels'];
            $info['audio']['bits_per_sample'] = $streaminfo['bits_per_sample'];
            $info['playtime_seconds']         = $streaminfo['samples_stream'] / $streaminfo['sample_rate'];
            if ($info['playtime_seconds'] > 0) {
                if (!$this->isDependencyFor('matroska')) {
                    $info['audio']['bitrate'] = (($info['avdataend'] - $info['avdataoffset']) * 8) / $info['playtime_seconds'];
                } else {
                    $this->warning('Cannot determine audio bitrate because total stream size is unknown');
                }
            }

        } else {
            return $this->error('Corrupt METAdata block: STREAMINFO');
        }

        return true;
    }

    /**
     *
     * @param  type    $BlockData
     * @return boolean
     */
    private function parseAPPLICATION($BlockData)
    {
        $info = &$this->getid3->info;

        $ApplicationID = Helper::BigEndian2Int(substr($BlockData, 0, 4));
        $info['flac']['APPLICATION'][$ApplicationID]['name'] = self::applicationIDLookup($ApplicationID);
        $info['flac']['APPLICATION'][$ApplicationID]['data'] = substr($BlockData, 4);

        return true;
    }

    /**
     *
     * @param  type    $BlockData
     * @return boolean
     */
    private function parseSEEKTABLE($BlockData)
    {
        $info = &$this->getid3->info;

        $offset = 0;
        $BlockLength = strlen($BlockData);
        $placeholderpattern = str_repeat("\xFF", 8);
        while ($offset < $BlockLength) {
            $SampleNumberString = substr($BlockData, $offset, 8);
            $offset += 8;
            if ($SampleNumberString == $placeholderpattern) {

                // placeholder point
                Helper::safe_inc($info['flac']['SEEKTABLE']['placeholders'], 1);
                $offset += 10;

            } else {

                $SampleNumber                                        = Helper::BigEndian2Int($SampleNumberString);
                $info['flac']['SEEKTABLE'][$SampleNumber]['offset']  = Helper::BigEndian2Int(substr($BlockData, $offset, 8));
                $offset += 8;
                $info['flac']['SEEKTABLE'][$SampleNumber]['samples'] = Helper::BigEndian2Int(substr($BlockData, $offset, 2));
                $offset += 2;

            }
        }

        return true;
    }

    /**
     *
     * @param  type    $BlockData
     * @return boolean
     */
    private function parseVORBIS_COMMENT($BlockData)
    {
        $info = &$this->getid3->info;

        $getid3_ogg = new Ogg($this->getid3);
        if ($this->isDependencyFor('matroska')) {
            $getid3_ogg->data_string_flag = true;
            $getid3_ogg->data_string = $this->data_string;
        }
        $getid3_ogg->ParseVorbisComments();
        if (isset($info['ogg'])) {
            unset($info['ogg']['comments_raw']);
            $info['flac']['VORBIS_COMMENT'] = $info['ogg'];
            unset($info['ogg']);
        }

        unset($getid3_ogg);

        return true;
    }

    /**
     *
     * @param  type    $BlockData
     * @return boolean
     */
    private function parseCUESHEET($BlockData)
    {
        $info = &$this->getid3->info;
        $offset = 0;
        $info['flac']['CUESHEET']['media_catalog_number'] =                              trim(substr($BlockData, $offset, 128), "\0");
        $offset += 128;
        $info['flac']['CUESHEET']['lead_in_samples']      =         Helper::BigEndian2Int(substr($BlockData, $offset, 8));
        $offset += 8;
        $info['flac']['CUESHEET']['flags']['is_cd']       = (bool) (Helper::BigEndian2Int(substr($BlockData, $offset, 1)) & 0x80);
        $offset += 1;

        $offset += 258; // reserved

        $info['flac']['CUESHEET']['number_tracks']        =         Helper::BigEndian2Int(substr($BlockData, $offset, 1));
        $offset += 1;

        for ($track = 0; $track < $info['flac']['CUESHEET']['number_tracks']; $track++) {
            $TrackSampleOffset = Helper::BigEndian2Int(substr($BlockData, $offset, 8));
            $offset += 8;
            $TrackNumber       = Helper::BigEndian2Int(substr($BlockData, $offset, 1));
            $offset += 1;

            $info['flac']['CUESHEET']['tracks'][$TrackNumber]['sample_offset']         = $TrackSampleOffset;

            $info['flac']['CUESHEET']['tracks'][$TrackNumber]['isrc']                  =                           substr($BlockData, $offset, 12);
            $offset += 12;

            $TrackFlagsRaw                                                             = Helper::BigEndian2Int(substr($BlockData, $offset, 1));
            $offset += 1;
            $info['flac']['CUESHEET']['tracks'][$TrackNumber]['flags']['is_audio']     = (bool) ($TrackFlagsRaw & 0x80);
            $info['flac']['CUESHEET']['tracks'][$TrackNumber]['flags']['pre_emphasis'] = (bool) ($TrackFlagsRaw & 0x40);

            $offset += 13; // reserved

            $info['flac']['CUESHEET']['tracks'][$TrackNumber]['index_points']          = Helper::BigEndian2Int(substr($BlockData, $offset, 1));
            $offset += 1;

            for ($index = 0; $index < $info['flac']['CUESHEET']['tracks'][$TrackNumber]['index_points']; $index++) {
                $IndexSampleOffset = Helper::BigEndian2Int(substr($BlockData, $offset, 8));
                $offset += 8;
                $IndexNumber       = Helper::BigEndian2Int(substr($BlockData, $offset, 1));
                $offset += 1;

                $offset += 3; // reserved

                $info['flac']['CUESHEET']['tracks'][$TrackNumber]['indexes'][$IndexNumber] = $IndexSampleOffset;
            }
        }

        return true;
    }

    /**
     *
     * @param  type    $Block
     * @return boolean
     */
    public function parsePICTURE($Block='')
    {
        $info = &$this->getid3->info;

        $picture['typeid']         = Helper::BigEndian2Int($this->fread(4));
        $picture['type']           = self::pictureTypeLookup($picture['typeid']);
        $picture['image_mime']     = $this->fread(Helper::BigEndian2Int($this->fread(4)));
        $descr_length              = Helper::BigEndian2Int($this->fread(4));
        if ($descr_length) {
            $picture['description'] = $this->fread($descr_length);
        }
        $picture['width']          = Helper::BigEndian2Int($this->fread(4));
        $picture['height']         = Helper::BigEndian2Int($this->fread(4));
        $picture['color_depth']    = Helper::BigEndian2Int($this->fread(4));
        $picture['colors_indexed'] = Helper::BigEndian2Int($this->fread(4));
        $data_length               = Helper::BigEndian2Int($this->fread(4));

        if ($picture['image_mime'] == '-->') {
            $picture['data'] = $this->fread($data_length);
        } else {
            $this->saveAttachment(
                $picture['data'],
                $picture['type'].'_'.$this->ftell().'.'.substr($picture['image_mime'], 6),
                $this->ftell(), $data_length);
        }

        $info['flac']['PICTURE'][] = $picture;

        return true;
    }

    /**
     *
     * @staticvar array $metaBlockTypeLookup
     * @param  type $blocktype
     * @return type
     */
    public static function metaBlockTypeLookup($blocktype)
    {
        static $metaBlockTypeLookup = array();
        if (empty($metaBlockTypeLookup)) {
            $metaBlockTypeLookup[0] = 'STREAMINFO';
            $metaBlockTypeLookup[1] = 'PADDING';
            $metaBlockTypeLookup[2] = 'APPLICATION';
            $metaBlockTypeLookup[3] = 'SEEKTABLE';
            $metaBlockTypeLookup[4] = 'VORBIS_COMMENT';
            $metaBlockTypeLookup[5] = 'CUESHEET';
            $metaBlockTypeLookup[6] = 'PICTURE';
        }

        return (isset($metaBlockTypeLookup[$blocktype]) ? $metaBlockTypeLookup[$blocktype] : 'reserved');
    }

    /**
     *
     * @staticvar array $applicationIDLookup
     * @param  type $applicationid
     * @return type
     */
    public static function applicationIDLookup($applicationid)
    {
        static $applicationIDLookup = array();
        if (empty($applicationIDLookup)) {
            // http://flac.sourceforge.net/id.html
            $applicationIDLookup[0x46746F6C] = 'flac-tools';      // 'Ftol'
            $applicationIDLookup[0x46746F6C] = 'Sound Font FLAC'; // 'SFFL'
        }

        return (isset($applicationIDLookup[$applicationid]) ? $applicationIDLookup[$applicationid] : 'reserved');
    }

    /**
     *
     * @staticvar array $lookup
     * @param  type $type_id
     * @return type
     */
    public static function pictureTypeLookup($type_id)
    {
        static $lookup = array (
             0 => 'Other',
             1 => '32x32 pixels \'file icon\' (PNG only)',
             2 => 'Other file icon',
             3 => 'Cover (front)',
             4 => 'Cover (back)',
             5 => 'Leaflet page',
             6 => 'Media (e.g. label side of CD)',
             7 => 'Lead artist/lead performer/soloist',
             8 => 'Artist/performer',
             9 => 'Conductor',
            10 => 'Band/Orchestra',
            11 => 'Composer',
            12 => 'Lyricist/text writer',
            13 => 'Recording Location',
            14 => 'During recording',
            15 => 'During performance',
            16 => 'Movie/video screen capture',
            17 => 'A bright coloured fish',
            18 => 'Illustration',
            19 => 'Band/artist logotype',
            20 => 'Publisher/Studio logotype',
        );

        return (isset($lookup[$type_id]) ? $lookup[$type_id] : 'reserved');
    }
}
