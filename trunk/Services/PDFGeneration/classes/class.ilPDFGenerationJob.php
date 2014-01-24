<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilPDFGenerationJob
 * 
 * Data-object blueprint that holds all PDF-generation related settings.
 * If you add to the methods, see to it that they follow the fluent interface, meaning
 * that all setters return $this for developer convenience.
 *
 * @author Maximilian Becker <mbecker@databay.de>
 * @version $Id$
 * 
 */
class ilPDFGenerationJob 
{
	#region Properties
	private $font;					/** @var $font array Font */
	private $author;				/** @var $author string Author */	
	private $creator;				/** @var $creator string Creator */
	private $footer;				/** @var $footer string Footer */
	private $footer_font;			/** @var $footer_font array Font*/
	private $header;				/** @var $header string Header */
	private $header_font;			/** @var $header_font array Font */
	private $title;					/** @var $title string Title */
	private $subject;				/** @var $subject string Subject */
	private $keywords;				/** @var $keywords string Keywords */
	private $margin_left;			/** @var $margin_left string Margin left */
	private $margin_right;			/** @var $margin_right string Margin right */
	private $margin_top;			/** @var $margin_top string Margin top */
	private $margin_bottom;			/** @var $margin_bottom string Margin bottom */
	private $auto_page_break;		/** @var $auto_page_break bool Auto page break */
	private $image_scale = 1;		/** @var $image_scale float Image scale */
	private $pages;					/** @var $pages string[] HTML pages */
	private $filename;				/** @var $filename string Filename */
	private $output_mode;			/** @var $output_mode string Output mode, one D, F or I */
	#endregion
	
	#region Methods
	
	/**
	 * @param boolean $auto_page_break
	 * @return $this
	 */
	public function setAutoPageBreak($auto_page_break)
	{
		$this->auto_page_break = $auto_page_break;
		return $this;
	}

	/**
	 * @return boolean
	 */
	public function getAutoPageBreak()
	{
		return $this->auto_page_break;
	}

	/**
	 * @param string $author
	 */
	public function setAuthor($author)
	{
		$this->author = $author;
	}

	/**
	 * @return string
	 */
	public function getAuthor()
	{
		return $this->author;
	}

	/**
	 * @param string $creator
	 * @return $this
	 */
	public function setCreator($creator)
	{
		$this->creator = $creator;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getCreator()
	{
		return $this->creator;
	}

	/**
	 * @param string $filename
	 * @return $this
	 */
	public function setFilename($filename)
	{
		$this->filename = $filename;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getFilename()
	{
		return $this->filename;
	}

	/**
	 * @param array $font
	 * @return $this
	 */
	public function setFont($font)
	{
		$this->font = $font;
		return $this;
	}

	/**
	 * @return array
	 */
	public function getFont()
	{
		return $this->font;
	}

	/**
	 * @param string $footer
	 * @return $this
	 */
	public function setFooter($footer)
	{
		$this->footer = $footer;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getFooter()
	{
		return $this->footer;
	}

	/**
	 * @param array $footer_font
	 * @return $this
	 */
	public function setFooterFont($footer_font)
	{
		$this->footer_font = $footer_font;
		return $this;
	}

	/**
	 * @return array
	 */
	public function getFooterFont()
	{
		return $this->footer_font;
	}

	/**
	 * @param string $header
	 * @return $this
	 */
	public function setHeader($header)
	{
		$this->header = $header;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getHeader()
	{
		return $this->header;
	}

	/**
	 * @param array $header_font
	 * @return $this
	 */
	public function setHeaderFont($header_font)
	{
		$this->header_font = $header_font;
		return $this;
	}

	/**
	 * @return array
	 */
	public function getHeaderFont()
	{
		return $this->header_font;
	}

	/**
	 * @param float $image_scale Image scale factor
	 * @return $this
	 */
	public function setImageScale($image_scale)
	{
		$this->image_scale = $image_scale;
		return $this;
	}

	/**
	 * @return float 
	 */
	public function getImageScale()
	{
		return $this->image_scale;
	}

	/**
	 * @param string $keywords
	 * @return $this
	 */
	public function setKeywords($keywords)
	{
		$this->keywords = $keywords;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getKeywords()
	{
		return $this->keywords;
	}

	/**
	 * @param string $margin_bottom
	 * @return $this
	 */
	public function setMarginBottom($margin_bottom)
	{
		$this->margin_bottom = $margin_bottom;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getMarginBottom()
	{
		return $this->margin_bottom;
	}

	/**
	 * @param string $margin_left
	 * @return $this
	 */
	public function setMarginLeft($margin_left)
	{
		$this->margin_left = $margin_left;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getMarginLeft()
	{
		return $this->margin_left;
	}

	/**
	 * @param string $margin_right
	 * @return $this
	 */
	public function setMarginRight($margin_right)
	{
		$this->margin_right = $margin_right;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getMarginRight()
	{
		return $this->margin_right;
	}

	/**
	 * @param string $margin_top
	 * @return $this
	 */
	public function setMarginTop($margin_top)
	{
		$this->margin_top = $margin_top;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getMarginTop()
	{
		return $this->margin_top;
	}

	/**
	 * @param $pages string[] Array of html-strings.
	 *
	 * @return $this
	 */
	public function setPages($pages)
	{
		$this->pages = $pages;
		return $this;
	}

	/**
	 * @return string[] Array of html-strings.
	 */
	public function getPages()
	{
		return $this->pages;
	}

	/**
	 * @param $page
	 * @return $this
	 */
	public function addPage($page)
	{
		$this->pages[] = $page;
		return $this;
	}

	/**
	 * @return $this
	 */
	public function flushPages()
	{
		$this->pages = array();
		return $this;
	}

	/**
	 * @param string $subject
	 * @return $this
	 */
	public function setSubject($subject)
	{
		$this->subject = $subject;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getSubject()
	{
		return $this->subject;
	}

	/**
	 * @param string $title
	 * @return $this
	 */
	public function setTitle($title)
	{
		$this->title = $title;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getTitle()
	{
		return $this->title;
	}

	/**
	 * @param string $output_mode
	 * @return $this
	 */
	public function setOutputMode($output_mode)
	{
		$this->output_mode = $output_mode;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getOutputMode()
	{
		return $this->output_mode;
	}
	
	#endregion
}