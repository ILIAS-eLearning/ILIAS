<?php

namespace GetId3\Module\Graphic;

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
// module.graphic.gif.php                                      //
// module for analyzing GIF Image files                        //
// dependencies: NONE                                          //
//                                                            ///
/////////////////////////////////////////////////////////////////

/**
 * module for analyzing GIF Image files
 *
 * @author James Heinrich <info@getid3.org>
 * @link http://getid3.sourceforge.net
 * @link http://www.getid3.org
 */
class Gif extends BaseHandler
{

    /**
     *
     * @return boolean
     */
    public function analyze()
    {
        $info = &$this->getid3->info;

        $info['fileformat']                  = 'gif';
        $info['video']['dataformat']         = 'gif';
        $info['video']['lossless']           = true;
        $info['video']['pixel_aspect_ratio'] = (float) 1;

        fseek($this->getid3->fp, $info['avdataoffset'], SEEK_SET);
        $GIFheader = fread($this->getid3->fp, 13);
        $offset = 0;

        $info['gif']['header']['raw']['identifier']            =                              substr($GIFheader, $offset, 3);
        $offset += 3;

        $magic = 'GIF';
        if ($info['gif']['header']['raw']['identifier'] != $magic) {
            $info['error'][] = 'Expecting "'.Helper::PrintHexBytes($magic).'" at offset '.$info['avdataoffset'].', found "'.Helper::PrintHexBytes($info['gif']['header']['raw']['identifier']).'"';
            unset($info['fileformat']);
            unset($info['gif']);

            return false;
        }

        $info['gif']['header']['raw']['version']               =                              substr($GIFheader, $offset, 3);
        $offset += 3;
        $info['gif']['header']['raw']['width']                 = Helper::LittleEndian2Int(substr($GIFheader, $offset, 2));
        $offset += 2;
        $info['gif']['header']['raw']['height']                = Helper::LittleEndian2Int(substr($GIFheader, $offset, 2));
        $offset += 2;
        $info['gif']['header']['raw']['flags']                 = Helper::LittleEndian2Int(substr($GIFheader, $offset, 1));
        $offset += 1;
        $info['gif']['header']['raw']['bg_color_index']        = Helper::LittleEndian2Int(substr($GIFheader, $offset, 1));
        $offset += 1;
        $info['gif']['header']['raw']['aspect_ratio']          = Helper::LittleEndian2Int(substr($GIFheader, $offset, 1));
        $offset += 1;

        $info['video']['resolution_x']                         = $info['gif']['header']['raw']['width'];
        $info['video']['resolution_y']                         = $info['gif']['header']['raw']['height'];
        $info['gif']['version']                                = $info['gif']['header']['raw']['version'];
        $info['gif']['header']['flags']['global_color_table']  = (bool) ($info['gif']['header']['raw']['flags'] & 0x80);
        if ($info['gif']['header']['raw']['flags'] & 0x80) {
            // Number of bits per primary color available to the original image, minus 1
            $info['gif']['header']['bits_per_pixel']  = 3 * ((($info['gif']['header']['raw']['flags'] & 0x70) >> 4) + 1);
        } else {
            $info['gif']['header']['bits_per_pixel']  = 0;
        }
        $info['gif']['header']['flags']['global_color_sorted'] = (bool) ($info['gif']['header']['raw']['flags'] & 0x40);
        if ($info['gif']['header']['flags']['global_color_table']) {
            // the number of bytes contained in the Global Color Table. To determine that
            // actual size of the color table, raise 2 to [the value of the field + 1]
            $info['gif']['header']['global_color_size'] = pow(2, ($info['gif']['header']['raw']['flags'] & 0x07) + 1);
            $info['video']['bits_per_sample']           = ($info['gif']['header']['raw']['flags'] & 0x07) + 1;
        } else {
            $info['gif']['header']['global_color_size'] = 0;
        }
        if ($info['gif']['header']['raw']['aspect_ratio'] != 0) {
            // Aspect Ratio = (Pixel Aspect Ratio + 15) / 64
            $info['gif']['header']['aspect_ratio'] = ($info['gif']['header']['raw']['aspect_ratio'] + 15) / 64;
        }

//		if ($info['gif']['header']['flags']['global_color_table']) {
//			$GIFcolorTable = fread($this->getid3->fp, 3 * $info['gif']['header']['global_color_size']);
//			$offset = 0;
//			for ($i = 0; $i < $info['gif']['header']['global_color_size']; $i++) {
//				$red   = GetId3_lib::LittleEndian2Int(substr($GIFcolorTable, $offset++, 1));
//				$green = GetId3_lib::LittleEndian2Int(substr($GIFcolorTable, $offset++, 1));
//				$blue  = GetId3_lib::LittleEndian2Int(substr($GIFcolorTable, $offset++, 1));
//				$info['gif']['global_color_table'][$i] = (($red << 16) | ($green << 8) | ($blue));
//			}
//		}
//
//		// Image Descriptor
//		while (!feof($this->getid3->fp)) {
//			$NextBlockTest = fread($this->getid3->fp, 1);
//			switch ($NextBlockTest) {
//
//				case ',': // ',' - Image separator character
//
//					$ImageDescriptorData = $NextBlockTest.fread($this->getid3->fp, 9);
//					$ImageDescriptor = array();
//					$ImageDescriptor['image_left']   = GetId3_lib::LittleEndian2Int(substr($ImageDescriptorData, 1, 2));
//					$ImageDescriptor['image_top']    = GetId3_lib::LittleEndian2Int(substr($ImageDescriptorData, 3, 2));
//					$ImageDescriptor['image_width']  = GetId3_lib::LittleEndian2Int(substr($ImageDescriptorData, 5, 2));
//					$ImageDescriptor['image_height'] = GetId3_lib::LittleEndian2Int(substr($ImageDescriptorData, 7, 2));
//					$ImageDescriptor['flags_raw']    = GetId3_lib::LittleEndian2Int(substr($ImageDescriptorData, 9, 1));
//					$ImageDescriptor['flags']['use_local_color_map'] = (bool) ($ImageDescriptor['flags_raw'] & 0x80);
//					$ImageDescriptor['flags']['image_interlaced']    = (bool) ($ImageDescriptor['flags_raw'] & 0x40);
//					$info['gif']['image_descriptor'][] = $ImageDescriptor;
//
//					if ($ImageDescriptor['flags']['use_local_color_map']) {
//
//						$info['warning'][] = 'This version of GetId3Core() cannot parse local color maps for GIFs';
//						return true;
//
//					}
//echo 'Start of raster data: '.ftell($this->getid3->fp).'<BR>';
//					$RasterData = array();
//					$RasterData['code_size']        = GetId3_lib::LittleEndian2Int(fread($this->getid3->fp, 1));
//					$RasterData['block_byte_count'] = GetId3_lib::LittleEndian2Int(fread($this->getid3->fp, 1));
//					$info['gif']['raster_data'][count($info['gif']['image_descriptor']) - 1] = $RasterData;
//
//					$CurrentCodeSize = $RasterData['code_size'] + 1;
//					for ($i = 0; $i < pow(2, $RasterData['code_size']); $i++) {
//						$DefaultDataLookupTable[$i] = chr($i);
//					}
//					$DefaultDataLookupTable[pow(2, $RasterData['code_size']) + 0] = ''; // Clear Code
//					$DefaultDataLookupTable[pow(2, $RasterData['code_size']) + 1] = ''; // End Of Image Code
//
//
//					$NextValue = $this->GetLSBits($CurrentCodeSize);
//					echo 'Clear Code: '.$NextValue.'<BR>';
//
//					$NextValue = $this->GetLSBits($CurrentCodeSize);
//					echo 'First Color: '.$NextValue.'<BR>';
//
//					$Prefix = $NextValue;
//$i = 0;
//					while ($i++ < 20) {
//						$NextValue = $this->GetLSBits($CurrentCodeSize);
//						echo $NextValue.'<BR>';
//					}
//return true;
//					break;
//
//				case '!':
//					// GIF Extension Block
//					$ExtensionBlockData = $NextBlockTest.fread($this->getid3->fp, 2);
//					$ExtensionBlock = array();
//					$ExtensionBlock['function_code']  = GetId3_lib::LittleEndian2Int(substr($ExtensionBlockData, 1, 1));
//					$ExtensionBlock['byte_length']    = GetId3_lib::LittleEndian2Int(substr($ExtensionBlockData, 2, 1));
//					$ExtensionBlock['data']           = fread($this->getid3->fp, $ExtensionBlock['byte_length']);
//					$info['gif']['extension_blocks'][] = $ExtensionBlock;
//					break;
//
//				case ';':
//					$info['gif']['terminator_offset'] = ftell($this->getid3->fp) - 1;
//					// GIF Terminator
//					break;
//
//				default:
//					break;
//
//
//			}
//		}
        return true;
    }

    /**
     *
     * @staticvar string $bitbuffer
     * @param  type $bits
     * @return type
     */
    public function GetLSBits($bits)
    {
        static $bitbuffer = '';
        while (strlen($bitbuffer) < $bits) {
            $bitbuffer = str_pad(decbin(ord(fread($this->getid3->fp, 1))), 8, '0', STR_PAD_LEFT).$bitbuffer;
        }
        $value = bindec(substr($bitbuffer, 0 - $bits));
        $bitbuffer = substr($bitbuffer, 0, 0 - $bits);

        return $value;
    }

}
