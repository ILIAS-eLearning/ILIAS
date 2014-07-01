<?php
/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/DeskDisplays/lib/fpdf/fpdf.php';

/**
 * @author Maximilian Frings <mfrings@databay.de>
 * @version $Id$
 */
class ilDeskDisplayPDF extends FPDF
{
	/**
	 * @var int
	 */
	protected $angle = 0;

	/**
	 * @param $angle
	 * @param $x
	 * @param $y
	 */
	public function Rotate($angle, $x = -1, $y = -1)
	{
		if($x == -1)
		{
			$x = $this->x;
		}
		if($y == -1)
		{
			$y = $this->y;
		}
		if($this->angle != 0)
		{
			$this->_out('Q');
		}
		$this->angle = $angle;
		if($angle != 0)
		{
			$angle *= M_PI / 180;
			$c  = cos($angle);
			$s  = sin($angle);
			$cx = $x * $this->k;
			$cy = ($this->h - $y) * $this->k;
			$this->_out(sprintf('q %.5f %.5f %.5f %.5f %.2f %.2f cm 1 0 0 1 %.2f %.2f cm', $c, $s, -$s, $c, $cx, $cy, -$cx, -$cy));
		}
	}

	/**
	 *
	 */
	public function _endpage()
	{
		if($this->angle != 0)
		{
			$this->angle = 0;
			$this->_out('Q');
		}
		parent::_endpage();
	}

	/**
	 * @param     $x
	 * @param     $y
	 * @param     $txt
	 * @param     $angle
	 * @param int $r
	 * @param int $g
	 * @param int $b
	 */
	public function RotatedText($x, $y, $txt, $angle, $r = 0, $g = 0, $b = 0)
	{
		$this->Rotate($angle, $x, $y);
		$this->SetTextColor($r, $g, $b);
		$this->Text($x, $y, $txt);

		$this->Rotate(0);
	}

	/**
	 * @param $file
	 * @param $x
	 * @param $y
	 * @param $w
	 * @param $h
	 * @param $angle
	 */
	public function RotatedImage($file, $x, $y, $w, $h, $angle)
	{
		$this->Rotate($angle, $x, $y);
		$this->Image($file, $x, $y, $w, $h);
		$this->Rotate(0);
	}
} 