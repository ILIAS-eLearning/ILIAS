<?php

namespace GetId3\Module\Audio;

use GetId3\Handler\BaseHandler;
use GetId3\Lib\Helper;
use GetId3\GetId3Core;
use GetId3\Module\AudioVideo\Riff;

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
class Shorten extends BaseHandler
{

    /**
     *
     * @staticvar type $shorten_present
     * @return boolean
     */
    public function analyze()
    {
        $info = &$this->getid3->info;

        fseek($this->getid3->fp, $info['avdataoffset'], SEEK_SET);

        $ShortenHeader = fread($this->getid3->fp, 8);
        $magic = 'ajkg';
        if (substr($ShortenHeader, 0, 4) != $magic) {
            $info['error'][] = 'Expecting "'.Helper::PrintHexBytes($magic).'" at offset '.$info['avdataoffset'].', found "'.Helper::PrintHexBytes(substr($ShortenHeader, 0, 4)).'"';

            return false;
        }
        $info['fileformat']            = 'shn';
        $info['audio']['dataformat']   = 'shn';
        $info['audio']['lossless']     = true;
        $info['audio']['bitrate_mode'] = 'vbr';

        $info['shn']['version'] = Helper::LittleEndian2Int(substr($ShortenHeader, 4, 1));

        fseek($this->getid3->fp, $info['avdataend'] - 12, SEEK_SET);
        $SeekTableSignatureTest = fread($this->getid3->fp, 12);
        $info['shn']['seektable']['present'] = (bool) (substr($SeekTableSignatureTest, 4, 8) == 'SHNAMPSK');
        if ($info['shn']['seektable']['present']) {
            $info['shn']['seektable']['length'] = Helper::LittleEndian2Int(substr($SeekTableSignatureTest, 0, 4));
            $info['shn']['seektable']['offset'] = $info['avdataend'] - $info['shn']['seektable']['length'];
            fseek($this->getid3->fp, $info['shn']['seektable']['offset'], SEEK_SET);
            $SeekTableMagic = fread($this->getid3->fp, 4);
            $magic = 'SEEK';
            if ($SeekTableMagic != $magic) {

                $info['error'][] = 'Expecting "'.Helper::PrintHexBytes($magic).'" at offset '.$info['shn']['seektable']['offset'].', found "'.Helper::PrintHexBytes($SeekTableMagic).'"';

                return false;

            } else {

                // typedef struct tag_TSeekEntry
                // {
                //   unsigned long SampleNumber;
                //   unsigned long SHNFileByteOffset;
                //   unsigned long SHNLastBufferReadPosition;
                //   unsigned short SHNByteGet;
                //   unsigned short SHNBufferOffset;
                //   unsigned short SHNFileBitOffset;
                //   unsigned long SHNGBuffer;
                //   unsigned short SHNBitShift;
                //   long CBuf0[3];
                //   long CBuf1[3];
                //   long Offset0[4];
                //   long Offset1[4];
                // }TSeekEntry;

                $SeekTableData = fread($this->getid3->fp, $info['shn']['seektable']['length'] - 16);
                $info['shn']['seektable']['entry_count'] = floor(strlen($SeekTableData) / 80);
                //$info['shn']['seektable']['entries'] = array();
                //$SeekTableOffset = 0;
                //for ($i = 0; $i < $info['shn']['seektable']['entry_count']; $i++) {
                //	$SeekTableEntry['sample_number'] = GetId3_lib::LittleEndian2Int(substr($SeekTableData, $SeekTableOffset, 4));
                //	$SeekTableOffset += 4;
                //	$SeekTableEntry['shn_file_byte_offset'] = GetId3_lib::LittleEndian2Int(substr($SeekTableData, $SeekTableOffset, 4));
                //	$SeekTableOffset += 4;
                //	$SeekTableEntry['shn_last_buffer_read_position'] = GetId3_lib::LittleEndian2Int(substr($SeekTableData, $SeekTableOffset, 4));
                //	$SeekTableOffset += 4;
                //	$SeekTableEntry['shn_byte_get'] = GetId3_lib::LittleEndian2Int(substr($SeekTableData, $SeekTableOffset, 2));
                //	$SeekTableOffset += 2;
                //	$SeekTableEntry['shn_buffer_offset'] = GetId3_lib::LittleEndian2Int(substr($SeekTableData, $SeekTableOffset, 2));
                //	$SeekTableOffset += 2;
                //	$SeekTableEntry['shn_file_bit_offset'] = GetId3_lib::LittleEndian2Int(substr($SeekTableData, $SeekTableOffset, 2));
                //	$SeekTableOffset += 2;
                //	$SeekTableEntry['shn_gbuffer'] = GetId3_lib::LittleEndian2Int(substr($SeekTableData, $SeekTableOffset, 4));
                //	$SeekTableOffset += 4;
                //	$SeekTableEntry['shn_bit_shift'] = GetId3_lib::LittleEndian2Int(substr($SeekTableData, $SeekTableOffset, 2));
                //	$SeekTableOffset += 2;
                //	for ($j = 0; $j < 3; $j++) {
                //		$SeekTableEntry['cbuf0'][$j] = GetId3_lib::LittleEndian2Int(substr($SeekTableData, $SeekTableOffset, 4));
                //		$SeekTableOffset += 4;
                //	}
                //	for ($j = 0; $j < 3; $j++) {
                //		$SeekTableEntry['cbuf1'][$j] = GetId3_lib::LittleEndian2Int(substr($SeekTableData, $SeekTableOffset, 4));
                //		$SeekTableOffset += 4;
                //	}
                //	for ($j = 0; $j < 4; $j++) {
                //		$SeekTableEntry['offset0'][$j] = GetId3_lib::LittleEndian2Int(substr($SeekTableData, $SeekTableOffset, 4));
                //		$SeekTableOffset += 4;
                //	}
                //	for ($j = 0; $j < 4; $j++) {
                //		$SeekTableEntry['offset1'][$j] = GetId3_lib::LittleEndian2Int(substr($SeekTableData, $SeekTableOffset, 4));
                //		$SeekTableOffset += 4;
                //	}
                //
                //	$info['shn']['seektable']['entries'][] = $SeekTableEntry;
                //}

            }

        }

        if (preg_match('#(1|ON)#i', ini_get('safe_mode'))) {
            $info['error'][] = 'PHP running in Safe Mode - backtick operator not available, cannot run shntool to analyze Shorten files';

            return false;
        }

        if (GetId3Core::environmentIsWindows()) {

            $RequiredFiles = array('shorten.exe', 'cygwin1.dll', 'head.exe');
            foreach ($RequiredFiles as $required_file) {
                if (!is_readable(GetId3Core::getHelperAppsDir().$required_file)) {
                    $info['error'][] = GetId3Core::getHelperAppsDir().$required_file.' does not exist';

                    return false;
                }
            }
            $commandline = GetId3Core::getHelperAppsDir().'shorten.exe -x "'.$info['filenamepath'].'" - | '.GetId3Core::getHelperAppsDir().'head.exe -c 64';
            $commandline = str_replace('/', '\\', $commandline);

        } else {

            static $shorten_present;
            if (!isset($shorten_present)) {
                $shorten_present = file_exists('/usr/local/bin/shorten') || `which shorten`;
            }
            if (!$shorten_present) {
                $info['error'][] = 'shorten binary was not found in path or /usr/local/bin';

                return false;
            }
            $commandline = (file_exists('/usr/local/bin/shorten') ? '/usr/local/bin/' : '' ) . 'shorten -x '.escapeshellarg($info['filenamepath']).' - | head -c 64';

        }

        $output = `$commandline`;

        if (!empty($output) && (substr($output, 12, 4) == 'fmt ')) {

            $fmt_size = Helper::LittleEndian2Int(substr($output, 16, 4));
            $DecodedWAVFORMATEX = Riff::RIFFparseWAVEFORMATex(substr($output, 20, $fmt_size));
            $info['audio']['channels']        = $DecodedWAVFORMATEX['channels'];
            $info['audio']['bits_per_sample'] = $DecodedWAVFORMATEX['bits_per_sample'];
            $info['audio']['sample_rate']     = $DecodedWAVFORMATEX['sample_rate'];

            if (substr($output, 20 + $fmt_size, 4) == 'data') {

                $info['playtime_seconds'] = Helper::LittleEndian2Int(substr($output, 20 + 4 + $fmt_size, 4)) / $DecodedWAVFORMATEX['raw']['nAvgBytesPerSec'];

            } else {

                $info['error'][] = 'shorten failed to decode DATA chunk to expected location, cannot determine playtime';

                return false;

            }

            $info['audio']['bitrate'] = (($info['avdataend'] - $info['avdataoffset']) / $info['playtime_seconds']) * 8;

        } else {

            $info['error'][] = 'shorten failed to decode file to WAV for parsing';

            return false;

        }

        return true;
    }
}
