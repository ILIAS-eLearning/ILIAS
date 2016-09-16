<?php
namespace CaT\Plugins\TalentAssessment\Observations;
require_once 'Services/Utilities/classes/class.ilUtil.php';

class SVGConverter {
	public function convertAndReturnPath($img_markup_string, $format = 'png24') {
		$svg_file_h = tmpfile();
		$png_file_h = tmpfile();
		fwrite($svg_file_h, $img_markup_string);
		$svg_filename = stream_get_meta_data($svg_file_h)['uri'];
		rename($svg_filename, $svg_filename.'.svg');
		$png_filename = stream_get_meta_data($png_file_h)['uri'];
		\ilUtil::convertImage($svg_filename.'.svg', $png_filename, $format);
		rename($svg_filename.'.svg', $svg_filename);
		rename($png_filename, $png_filename.'.png');
		$png_filename .= '.png';

		return $png_filename;
	}
}