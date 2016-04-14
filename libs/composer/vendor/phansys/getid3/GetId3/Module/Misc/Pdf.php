<?php
/////////////////////////////////////////////////////////////////
/// GetId3() by James Heinrich <info@getid3.org>               //
//  available at http://getid3.sourceforge.net                 //
//            or http://www.getid3.org                         //
/////////////////////////////////////////////////////////////////
// See readme.txt for more details                             //
/////////////////////////////////////////////////////////////////
//                                                             //
// module.misc.pdf.php                                         //
// module for analyzing PDF files                              //
// dependencies: NONE                                          //
//                                                            ///
/////////////////////////////////////////////////////////////////

/**
 * module for analyzing PDF files
 *
 * @author James Heinrich <info@getid3.org>
 * @link http://getid3.sourceforge.net
 * @link http://www.getid3.org
 */
class GetId3_Module_Misc_Pdf extends GetId3_Handler_BaseHandler
{

    /**
     *
     * @return boolean
     */
	public function Analyze() {
		$info = &$this->getid3->info;

		$info['fileformat'] = 'pdf';

		$info['error'][] = 'PDF parsing not enabled in this version of GetId3() ['.$this->getid3->version().']';
		return false;

	}

}
