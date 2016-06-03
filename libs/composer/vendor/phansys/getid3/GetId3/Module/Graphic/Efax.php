<?php
/////////////////////////////////////////////////////////////////
/// GetId3() by James Heinrich <info@getid3.org>               //
//  available at http://getid3.sourceforge.net                 //
//            or http://www.getid3.org                         //
/////////////////////////////////////////////////////////////////
// See readme.txt for more details                             //
/////////////////////////////////////////////////////////////////
//                                                             //
// module.archive.efax.php                                     //
// module for analyzing eFax files                             //
// dependencies: NONE                                          //
//                                                            ///
/////////////////////////////////////////////////////////////////

/**
 * module for analyzing eFax files
 *
 * @author James Heinrich <info@getid3.org>
 * @link http://getid3.sourceforge.net
 * @link http://www.getid3.org
 */
class GetId3_Module_Graphic_Efax extends GetId3_Handler_BaseHandler
{

    /**
     *
     * @return boolean
     */
	public function Analyze() {
		$info = &$this->getid3->info;

		fseek($this->getid3->fp, $info['avdataoffset'], SEEK_SET);
		$efaxheader = fread($this->getid3->fp, 1024);

		$info['efax']['header']['magic'] = substr($efaxheader, 0, 2);
		if ($info['efax']['header']['magic'] != "\xDC\xFE") {
			$info['error'][] = 'Invalid eFax byte order identifier (expecting DC FE, found '.GetId3_Lib_Helper::PrintHexBytes($info['efax']['header']['magic']).') at offset '.$info['avdataoffset'];
			return false;
		}
		$info['fileformat'] = 'efax';

		$info['efax']['header']['filesize'] = GetId3_Lib_Helper::LittleEndian2Int(substr($efaxheader, 2, 4));
		if ($info['efax']['header']['filesize'] != $info['filesize']) {
			$info['error'][] = 'Probable '.(($info['efax']['header']['filesize'] > $info['filesize']) ? 'truncated' : 'corrupt').' file, expecting '.$info['efax']['header']['filesize'].' bytes, found '.$info['filesize'].' bytes';
		}
		$info['efax']['header']['software1'] =                        rtrim(substr($efaxheader,  26, 32), "\x00");
		$info['efax']['header']['software2'] =                        rtrim(substr($efaxheader,  58, 32), "\x00");
		$info['efax']['header']['software3'] =                        rtrim(substr($efaxheader,  90, 32), "\x00");

		$info['efax']['header']['pages']      = GetId3_Lib_Helper::LittleEndian2Int(substr($efaxheader, 198, 2));
		$info['efax']['header']['data_bytes'] = GetId3_Lib_Helper::LittleEndian2Int(substr($efaxheader, 202, 4));

$info['error'][] = 'eFax parsing not enabled in this version of GetId3() ['.$this->getid3->version().']';
return false;

		return true;
	}

}
