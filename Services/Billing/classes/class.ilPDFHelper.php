<?php

/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Billing/lib/fpdf/fpdf.php';

/**
 * @author  Maximilian Frings <mfrings@databay.de>
 * @author  Michael Jansen <mjansen@databay.de>
 * @version $Id$
 */
class ilPDFHelper extends FPDF
{

	/**
	 * @param    float  $x
	 * @param    float  $y
	 * @param    string $txt
	 */
	public function WriteText($x, $y, $txt)
	{
		$this->SetXY($x, $y);
		$this->Text($x, $y, $txt);
	}

	/**
	 * @param  float  $x
	 * @param  float  $y
	 * @param  string $txt
	 */
	public function WriteMultiCell($x, $y, $txt, $spacing)
	{
		$this->SetXY($x - 0.1, $y);
		$this->MultiCell(21 - $spacing, 1, $txt);
		return $this->GetY();
	}

	/**
	 * @param int    $w
	 * @param int    $h
	 * @param string $txt
	 * @param int    $border
	 * @param int    $ln
	 * @param string $align
	 * @param int    $fill
	 * @param string $link
	 */
	public function Cell($w, $h = 0, $txt = '', $border = 0, $ln = 0, $align = '', $fill = 0, $link = '')
	{
		$k = $this->k;
		if($this->y + $h > $this->PageBreakTrigger and !$this->InFooter and $this->AcceptPageBreak())
		{
			$x  = $this->x;
			$ws = $this->ws;
			if($ws > 0)
			{
				$this->ws = 0;
				$this->_out('0 Tw');
			}
			$this->AddPage($this->CurOrientation);
			$this->x = $x;
			if($ws > 0)
			{
				$this->ws = $ws;
				$this->_out(sprintf('%.3f Tw', $ws * $k));
			}
		}
		if($w == 0) $w = $this->w - $this->rMargin - $this->x;
		$s = '';
		if($fill == 1 or $border == 1)
		{
			if($fill == 1) $op = ($border == 1) ? 'B' : 'f';
			else $op = 'S';
			$s = sprintf('%.2f %.2f %.2f %.2f re %s ', $this->x * $k, ($this->h - $this->y) * $k, $w * $k, -$h * $k, $op);
		}
		if(is_string($border))
		{
			$x = $this->x;
			$y = $this->y;
			if(is_int(strpos($border, 'L'))) $s .= sprintf('%.2f %.2f m %.2f %.2f l S ', $x * $k, ($this->h - $y) * $k, $x * $k, ($this->h - ($y + $h)) * $k);
			if(is_int(strpos($border, 'T'))) $s .= sprintf('%.2f %.2f m %.2f %.2f l S ', $x * $k, ($this->h - $y) * $k, ($x + $w) * $k, ($this->h - $y) * $k);
			if(is_int(strpos($border, 'R'))) $s .= sprintf('%.2f %.2f m %.2f %.2f l S ', ($x + $w) * $k, ($this->h - $y) * $k, ($x + $w) * $k, ($this->h - ($y + $h)) * $k);
			if(is_int(strpos($border, 'B'))) $s .= sprintf('%.2f %.2f m %.2f %.2f l S ', $x * $k, ($this->h - ($y + $h)) * $k, ($x + $w) * $k, ($this->h - ($y + $h)) * $k);
		}
		if($txt != '')
		{
			if($align == 'R') $dx = $w - $this->cMargin - $this->GetStringWidth($txt);
			elseif($align == 'C') $dx = ($w - $this->GetStringWidth($txt)) / 2;
			elseif($align == 'FJ')
			{
				$wmax     = ($w - 2 * $this->cMargin);
				$this->ws = ($wmax - $this->GetStringWidth($txt)) / substr_count($txt, ' ');
				$this->_out(sprintf('%.3f Tw', $this->ws * $this->k));
				$dx = $this->cMargin;
			}
			else $dx = $this->cMargin;
			$txt = str_replace(')', '\\)', str_replace('(', '\\(', str_replace('\\', '\\\\', $txt)));
			if($this->ColorFlag) $s .= 'q ' . $this->TextColor . ' ';
			$s .= sprintf('BT %.2f %.2f Td (%s) Tj ET', ($this->x + $dx) * $k, ($this->h - ($this->y + .5 * $h + .3 * $this->FontSize)) * $k, $txt);
			if($this->underline) $s .= ' ' . $this->_dounderline($this->x + $dx, $this->y + .5 * $h + .3 * $this->FontSize, $txt);
			if($this->ColorFlag) $s .= ' Q';
			if($link)
			{
				if($align == 'FJ') $wlink = $wmax;
				else $wlink = $this->GetStringWidth($txt);
				$this->Link($this->x + $dx, $this->y + .5 * $h - .5 * $this->FontSize, $wlink, $this->FontSize, $link);
			}
		}
		if($s) $this->_out($s);
		if($align == 'FJ')
		{
			$this->_out('0 Tw');
			$this->ws = 0;
		}
		$this->lasth = $h;
		if($ln > 0)
		{
			$this->y += $h;
			if($ln == 1) $this->x = $this->lMargin;
		}
		else $this->x += $w;
	}

}
