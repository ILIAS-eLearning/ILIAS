<?php

/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Billing/classes/class.ilPDFHelper.php';
require_once 'Services/Utilities/classes/class.ilUtil.php';
require_once 'Services/Calendar/classes/class.ilDatePresentation.php';

/**
 * @author  Maximilian Frings <mfrings@databay.de>
 * @author  Michael Jansen <mjansen@databay.de>
 * @version $Id$
 */
class ilPDFBill
{
	const DEFAULT_FONT_NAME              = 'Arial';
	const DEFAULT_FONT_SIZE              = 11;
	const DEFAULT_IS_BOLD                = false;
	const DEFAULT_IS_ITALIC              = false;
	const PAGENUMBER_DEFAULT_FONT_NAME   = 'Arial';
	const PAGENUMBER_DEFAULT_FONT_SIZE   = 8;
	const PAGENUMBER_DEFAULT_IS_BOLD     = false;
	const PAGENUMBER_DEFAULT_IS_ITALIC   = false;
	const DEFAULT_RGB_VALUE_RED          = 255;
	const DEFAULT_RGB_VALUE_GREEN        = 255;
	const DEFAULT_RGB_VALUE_BLUE         = 255;
	const LINE_2_DEFAULT_RGB_VALUE_RED   = 255;
	const LINE_2_DEFAULT_RGB_VALUE_GREEN = 255;
	const LINE_2_DEFAULT_RGB_VALUE_BLUE  = 255;

	/**
	 * @var array
	 */
	protected static $supported_fonts = array(
		'Courier',
		'Helvetica',
		'Symbol',
		'Times-Roman',
		'ZapfDingbats',
		'Arial',
		'Times'
	);

	/**
	 * @var float
	 */
	private $spaceLeft = 1.0;

	/**
	 * @var float
	 */
	private $spaceBottom = 1.0;

	/**
	 * @var float
	 */
	private $spaceRight = 1.0;

	/**
	 * @var float
	 */
	private $spaceAddress = 3.0;

	/**
	 * @var float
	 */
	private $spaceAbout = 7.0;

	/**
	 * @var float
	 */
	private $spaceBillnumber = 8.0;

	/**
	 * @var float
	 */
	private $spaceTitle = 10.0;

	/**
	 * @var float
	 */
	private $spaceText = 11.0;

	/**
	 * @var string
	 */
	private $format = 'A4';

	/**
	 * @var string
	 */
	private $unit = 'cm';

	/**
	 * @var string
	 */
	private $address = 'cm';

	/**
	 * @var string
	 */
	private $mode = 'P';

	/**
	 * @var string
	 */
	private $plGreetings = '';

	/**
	 * @var string
	 */
	private $plPosttext = '';

	/**
	 * @var string
	 */
	private $plPretext = '';

	/**
	 * @var string
	 */
	private $plSalutation = '';

	/**
	 * @var string
	 */
	private $plTitle = '';

	/**
	 * @var string
	 */
	private $plAbout = '';

	/**
	 * @var string
	 */
	private $plSideInfoForCurrentAfterTaxes = '(brutto)';

	/**
	 * @var string
	 */
	private $plSideInfoForCurrentPreTaxes = '(netto)';

	/**
	 * @var string
	 */
	private $plCalculationTotalAmount = 'Rechnungsbetrag';

	/**
	 * @var string
	 */
	private $plBillNumberLabel = '';

	/**
	 * @var string
	 */
	private $plCalculationTaxAmount = 'Umsatzsteuer';

	/**
	 * @var ilBill
	 */
	private $bill;

	/**
	 * @var string
	 */
	private $background = '';

	/**
	 * @var string
	 */
	private $AddressFontName = self::DEFAULT_FONT_NAME;

	/**
	 * @var int
	 */
	private $AddressFontSize = self::DEFAULT_FONT_SIZE;

	/**
	 * @var bool
	 */
	private $AddressFontBold = self::DEFAULT_IS_BOLD;

	/**
	 * @var bool
	 */
	private $AddressFontItalic = self::DEFAULT_IS_ITALIC;

	/**
	 * @var string
	 */
	private $DateFontName = self::DEFAULT_FONT_NAME;

	/**
	 * @var int
	 */
	private $DateFontSize = self::DEFAULT_FONT_SIZE;

	/**
	 * @var bool
	 */
	private $DateFontBold = self::DEFAULT_IS_BOLD;

	/**
	 * @var bool
	 */
	private $DateFontItalic = self::DEFAULT_IS_ITALIC;

	/**
	 * @var string
	 */
	private $AboutFontName = self::DEFAULT_FONT_NAME;

	/**
	 * @var int
	 */
	private $AboutFontSize = self::DEFAULT_FONT_SIZE;

	/**
	 * @var bool
	 */
	private $AboutFontBold = self::DEFAULT_IS_BOLD;

	/**
	 * @var bool
	 */
	private $AboutFontItalic = self::DEFAULT_IS_ITALIC;

	/**
	 * @var string
	 */
	private $BillNumberFontName = self::DEFAULT_FONT_NAME;

	/**
	 * @var int
	 */
	private $BillNumberFontSize = self::DEFAULT_FONT_SIZE;

	/**
	 * @var bool
	 */
	private $BillNumberFontBold = self::DEFAULT_IS_BOLD;

	/**
	 * @var bool
	 */
	private $BillNumberFontItalic = self::DEFAULT_IS_ITALIC;

	/**
	 * @var string
	 */
	private $TitleFontName = self::DEFAULT_FONT_NAME;

	/**
	 * @var int
	 */
	private $TitleFontSize = self::DEFAULT_FONT_SIZE;

	/**
	 * @var bool
	 */
	private $TitleFontBold = self::DEFAULT_IS_BOLD;

	/**
	 * @var bool
	 */
	private $TitleFontItalic = self::DEFAULT_IS_ITALIC;

	/**
	 * @var string
	 */
	private $CalculationFontName = self::DEFAULT_FONT_NAME;

	/**
	 * @var int
	 */
	private $CalculationFontSize = self::DEFAULT_FONT_SIZE;

	/**
	 * @var bool
	 */
	private $CalculationFontBold = self::DEFAULT_IS_BOLD;

	/**
	 * @var bool
	 */
	private $CalculationFontItalic = self::DEFAULT_IS_ITALIC;

	/**
	 * @var string
	 */
	private $PageNumberFontName = self::PAGENUMBER_DEFAULT_FONT_NAME;

	/**
	 * @var int
	 */
	private $PageNumberFontSize = self::PAGENUMBER_DEFAULT_FONT_SIZE;

	/**
	 * @var bool
	 */
	private $PageNumberFontBold = self::PAGENUMBER_DEFAULT_IS_BOLD;

	/**
	 * @var bool
	 */
	private $PageNumberFontItalic = self::PAGENUMBER_DEFAULT_IS_ITALIC;

	/**
	 * @var string
	 */
	private $TextFontName = self::DEFAULT_FONT_NAME;

	/**
	 * @var int
	 */
	private $TextFontSize = self::DEFAULT_FONT_SIZE;

	/**
	 * @var bool
	 */
	private $TextFontBold = self::DEFAULT_IS_BOLD;

	/**
	 * @var bool
	 */
	private $TextFontItalic = self::DEFAULT_IS_ITALIC;

	/**
	 * @var float
	 */
	private $distanceindex = 1.5;

	/**
	 * @var int
	 */
	private $sumPreTax = 0;

	/**
	 * @var int
	 */
	private $sumVAT = 0;

	/**
	 * @var int
	 */
	private $sumAfterTax = 0;

	/**
	 * @var int
	 */
	private $addidistX = 0;

	/**
	 * @var int
	 */
	private $additionaldistanceline = 0;

	/**
	 * @var int
	 */
	private $addidistY = 0;

	/**
	 * @var int
	 */
	private $forwardingY = 0;

	/**
	 * Constructor
	 */
	public function __construct()
	{
		$this->db     = $GLOBALS['ilDB'];
		$this->logger = $GLOBALS['ilLog'];
		$this->pdf    = new ilPDFHelper($this->mode, $this->unit, $this->format);

		$GLOBALS['lng']->loadLanguageModule('billing');

		$this->plSideInfoForCurrentAfterTaxes   = $GLOBALS['lng']->txt("billing_bru");
		$this->plSideInfoForCurrentPreTaxes = $GLOBALS['lng']->txt("billing_net");
		$this->plCalculationTotalAmount       = $GLOBALS['lng']->txt("billing_val");
		$this->plCalculationTaxAmount         = $GLOBALS['lng']->txt("billing_vat");
		$this->plBillNumberLabel              = $GLOBALS['lng']->txt("billing_numberlabel");
	}

	/**
	 * @param ilLog $logger
	 */
	public function setLogger(ilLog $logger)
	{
		$this->logger = $logger;
	}

	/**
	 * @return ilLog
	 */
	public function getLogger()
	{
		return $this->logger;
	}

	/**
	 * @return array
	 */
	private static function getSupportedFonts()
	{
		return self::$supported_fonts;
	}

	/**
	 * @return string
	 */
	public function getFormat()
	{
		return $this->format;
	}

	/**
	 * @return string
	 */
	public function getUnit()
	{
		return $this->unit;
	}

	/**
	 * @return string
	 */
	public function getMode()
	{
		return $this->mode;
	}

	/**
	 * @return float
	 */
	public function getSpaceBottom()
	{
		return $this->spaceBottom;
	}

	/**
	 * @param float $a_space_bottom Bottom space to label1 on PDF page
	 * @throws ilException
	 */
	public function setSpaceBottom($a_space_bottom)
	{
		if($a_space_bottom > 29.7 || $a_space_bottom <= 0)
		{
			throw new ilException("Space bottom dimensions exceed the pdf dimensions");
		}
		$this->spaceBottom = $a_space_bottom;
	}

	/**
	 * @return float
	 */
	public function getSpaceLeft()
	{
		return $this->spaceLeft;
	}

	/**
	 * @param float $a_space_left Left space on PDF page
	 * @throws ilException
	 */
	public function setSpaceLeft($a_space_left)
	{
		if($a_space_left > 21.0 || $a_space_left <= 0)
		{
			throw new ilException("Space left dimensions exceed the pdf dimensions");
		}
		$this->spaceLeft = $a_space_left;
	}

	/**
	 * @return float
	 */
	public function getSpaceRight()
	{
		return $this->spaceRight;
	}

	/**
	 * @param float $a_space_right space on PDF page
	 * @throws ilException
	 */
	public function setSpaceRight($a_space_right)
	{
		if($a_space_right > 21.0 || $a_space_right <= 0)
		{
			throw new ilException("Space right dimensions exceed the pdf dimensions");
		}
		$this->spaceRight = $a_space_right;
	}

	/**
	 * @return float
	 */
	public function getSpaceAddress()
	{
		return $this->spaceAddress;
	}

	/**
	 * @param float $a_space_address
	 * @throws ilException
	 */
	public function setSpaceAddress($a_space_address)
	{
		if($a_space_address > 29.7 || $a_space_address <= 0)
		{
			throw new ilException("Space address dimensions exceed the pdf dimensions");
		}
		$this->spaceAddress = $a_space_address;
	}

	/**
	 * @return float
	 */
	public function getSpaceAbout()
	{
		return $this->spaceAbout;
	}

	/**
	 * @param float $a_space_about
	 * @throws ilException
	 */
	public function setSpaceAbout($a_space_about)
	{
		if($a_space_about > 29.7 || $a_space_about <= 0)
		{
			throw new ilException("Space about dimensions exceed the pdf dimensions");
		}
		$this->spaceAbout = $a_space_about;
	}

	/**
	 * @return float
	 */
	public function getSpaceBillnumber()
	{
		return $this->spaceBillnumber;
	}

	/**
	 * @param float $a_space_bill_number
	 * @throws ilException
	 */
	public function setSpaceBillnumber($a_space_bill_number)
	{
		if($a_space_bill_number > 29.7 || $a_space_bill_number <= 0)
		{
			throw new ilException("Space billnumber dimensions exceed the pdf dimensions");
		}
		$this->spaceBillnumber = $a_space_bill_number;
	}

	/**
	 * @return float
	 */
	public function getSpaceTitle()
	{
		return $this->spaceTitle;
	}

	/**
	 * @param float $a_space_title
	 * @throws ilException
	 */
	public function setSpaceTitle($a_space_title)
	{
		if($a_space_title > 29.7 || $a_space_title <= 0)
		{
			throw new ilException("Space title dimensions exceed the pdf dimensions");
		}


		$this->spaceTitle = $a_space_title;
	}

	/**
	 * @return float
	 */
	public function getSpaceText()
	{
		return $this->spaceText;
	}

	/**
	 * @param float $a_space_text
	 * @throws ilException
	 */
	public function setSpaceText($a_space_text)
	{
		if($a_space_text > 29.7 || $a_space_text <= 0)
		{
			throw new ilException("Space text dimensions exceed the pdf dimensions");
		}


		$this->spaceText = $a_space_text;
	}

	/**
	 * @param string $a_greeting
	 */
	public function setGreetings($a_greeting)
	{

		$this->plGreetings = $a_greeting;
	}

	/**
	 * @param string $a_billnumber_label
	 */
	public function setBillnumberLabel($a_billnumber_label)
	{
		$this->plBillNumberLabel = $a_billnumber_label;
	}

	/**
	 * @param string $a_posttext
	 */
	public function setPosttext($a_posttext)
	{
		$this->plPosttext = $a_posttext;
	}

	/**
	 * @param string $a_pretext
	 */
	public function setPretext($a_pretext)
	{
		$this->plPretext = $a_pretext;
	}

	/**
	 * @param string $a_salutation
	 */
	public function setSalutation($a_salutation)
	{
		$this->plSalutation = $a_salutation;
	}

	/**
	 * @param string $a_sideinfo_pre
	 */
	public function setSideInfoForCurrentAfterTaxes($a_sideinfo_pre = "(netto)")
	{
		$this->plSideInfoForCurrentAfterTaxes = $a_sideinfo_pre;
	}

	/**
	 * @param string $a_sideinfo_after
	 */
	public function setSideInfoForCurrentPreTaxes($a_sideinfo_after = "(brutto)")
	{
		$this->plSideInfoForCurrentPreTaxes = $a_sideinfo_after;
	}

	/**
	 * @param string $a_info_total
	 */
	public function setTableInfoTotalAmount($a_info_total = "Rechnungsbetrag")
	{
		$this->plCalculationTotalAmount = $a_info_total;
	}

	/**
	 * @param string $a_info_tax
	 */
	public function setTableInfoTaxAmount($a_info_tax = "Umsatzsteuer")
	{
		$this->plCalculationTaxAmount = $a_info_tax;
	}

	/**
	 * @param string $a_title
	 */
	public function setTitle($a_title)
	{
		$this->plTitle = $a_title;
	}

	/**
	 * @param string $a_about
	 */
	public function setAbout($a_about)
	{
		$this->plAbout = $a_about;
	}

	/**
	 * @param ilBill $bill
	 */
	public function setBill(ilBill $bill)
	{
		$this->bill = $bill;
	}

	/**
	 * @param string $path
	 * @throws ilException
	 */
	public function setBackground($path)
	{

		if(strlen($path))
		{
			if(!file_exists($path))
			{
				throw new ilException('The passed path does not point to an existing file: ' . $path);
			}
			if(!is_file($path))
			{
				throw new ilException('The passed path does not point to an existing file: ' . $path);
			}
			if(!is_readable($path))
			{
				throw new ilException('The passed path is not readable by the server: ' . $path);
			}
			$this->background = $path;
		}


	}

	/**
	 * @param string $a_name    Address Font
	 * @param int    $a_size    Address Font Size
	 * @param bool   $a_bold    Address Bold
	 * @param bool   $a_italics Address Italics
	 */
	public function setAddressFont($a_name = self::DEFAULT_FONT_NAME, $a_size = self::DEFAULT_FONT_SIZE, $a_bold = self::DEFAULT_IS_BOLD, $a_italics = self::DEFAULT_IS_ITALIC)
	{
		$this->setFonts($a_name, $a_size, $a_bold, $a_italics, "address");
		
	}

	/**
	 * @param string $a_name    Address Font
	 * @param int    $a_size    Address Font Size
	 * @param bool   $a_bold    Address Bold
	 * @param bool   $a_italics Address Italics
	 */
	private function setFonts($a_name, $a_size, $a_bold, $a_italics, $type)
	{

		if(!is_string($a_name))
		{
			$a_name = self::DEFAULT_FONT_NAME;
		}
		if(!is_int($a_size))
		{
			$a_size = self::DEFAULT_FONT_SIZE;
		}
		if(!is_bool($a_bold))
		{
			$a_bold = self::DEFAULT_IS_BOLD;
		}
		if(!is_bool($a_italics))
		{
			$a_italics = self::DEFAULT_IS_ITALIC;
		}
		if(in_array(strtolower($a_name), array_map('strtolower', self::getSupportedFonts())))
		{
			switch($type)
			{
				case "address":
					$this->AddressFontName = $a_name;
					break;
				case "date":
					$this->DateFontName = $a_name;
					break;
				case "about":
					$this->AboutFontName = $a_name;
					break;
				case "billnumber":
					$this->BillNumberFontName = $a_name;
					break;
				case "title":
					$this->TitleFontName = $a_name;
					break;
				case "calculation":
					$this->CalculationFontName = $a_name;
					break;
				case "text":
					$this->TextFontName = $a_name;
					break;
				case "pagenumber":
					$this->PageNumberFontName = $a_name;
					break;
			}
		}
		else
		{
			switch($type)
			{
				case "address":
					$this->AddressFontName = self::DEFAULT_FONT_NAME;
					break;
				case "date":
					$this->DateFontName = self::DEFAULT_FONT_NAME;
					break;
				case "about":
					$this->AboutFontName = self::DEFAULT_FONT_NAME;
					break;
				case "billnumber":
					$this->BillNumberFontName = self::DEFAULT_FONT_NAME;
					break;
				case "title":
					$this->TitleFontName = self::DEFAULT_FONT_NAME;
					break;
				case "calculation":
					$this->CalculationFontName = self::DEFAULT_FONT_NAME;
					break;
				case "text":
					$this->TextFontName = self::DEFAULT_FONT_NAME;
					break;
				case "pagenumber":
					$this->PageNumberFontName = self::DEFAULT_FONT_NAME;
					break;
			}

			$this->log("Passed font '" . $a_name . "' is not supported, fell back to Arial");
		}
		if($a_size <= 0)
		{
			if($type == "pagenumber")
			{
				$a_size = self::PAGENUMBER_DEFAULT_FONT_SIZE;
			}
			else
			{
				$a_size = self::DEFAULT_FONT_SIZE;
			}
		}
		if($a_size > 140)
		{
			if($type == "pagenumber")
			{
				$a_size = self::PAGENUMBER_DEFAULT_FONT_SIZE;
			}
			else
			{
				$a_size = self::DEFAULT_FONT_SIZE;
			}
		}

		switch($type)
		{
			case "address":
				$this->AddressFontBold   = $a_bold;
				$this->AddressFontItalic = $a_italics;
				$this->AddressFontSize   = $a_size;
				break;
			case "date":
				$this->DateFontBold   = $a_bold;
				$this->DateFontItalic = $a_italics;
				$this->DateFontSize   = $a_size;
				break;
			case "about":
				$this->AboutFontBold   = $a_bold;
				$this->AboutFontItalic = $a_italics;
				$this->AboutFontSize   = $a_size;
				break;
			case "billnumber":
				$this->BillNumberFontBold   = $a_bold;
				$this->BillNumberFontItalic = $a_italics;
				$this->BillNumberFontSize   = $a_size;
				break;
			case "title":
				$this->TitleFontBold   = $a_bold;
				$this->TitleFontItalic = $a_italics;
				$this->TitleFontSize   = $a_size;
				break;
			case "calculation":
				$this->CalculationFontBold   = $a_bold;
				$this->CalculationFontItalic = $a_italics;
				$this->CalculationFontSize   = $a_size;
				break;
			case "text":
				$this->TextFontBold   = $a_bold;
				$this->TextFontItalic = $a_italics;
				$this->TextFontSize   = $a_size;
				break;
			case "pagenumber":
				$this->PageNumberFontBold   = $a_bold;
				$this->PageNumberFontItalic = $a_italics;
				$this->PageNumberFontSize   = $a_size;
				break;
		}
	}

	/**
	 * @param string $a_name    Date Font
	 * @param int    $a_size    Date Font Size
	 * @param bool   $a_bold    Date Bold
	 * @param bool   $a_italics Date Italics
	 */
	public function setDateFont($a_name = self::DEFAULT_FONT_NAME, $a_size = self::DEFAULT_FONT_SIZE, $a_bold = self::DEFAULT_IS_BOLD, $a_italics = self::DEFAULT_IS_ITALIC)
	{
		$this->setFonts($a_name, $a_size, $a_bold, $a_italics, "date");
	}

	/**
	 * @param string $a_name    About Font
	 * @param int    $a_size    About Font Size
	 * @param bool   $a_bold    About Bold
	 * @param bool   $a_italics About Italics
	 */
	public function setAboutFont($a_name = self::DEFAULT_FONT_NAME, $a_size = self::DEFAULT_FONT_SIZE, $a_bold = self::DEFAULT_IS_BOLD, $a_italics = self::DEFAULT_IS_ITALIC)
	{
		$this->setFonts($a_name, $a_size, $a_bold, $a_italics, "about");
	}

	/**
	 * @param string $a_name    Billnumber Font
	 * @param int    $a_size    Billnumber Font Size
	 * @param bool   $a_bold    Billnumber Bold
	 * @param bool   $a_italics Billnumber Italics
	 */
	public function setBillNumberFont($a_name = self::DEFAULT_FONT_NAME, $a_size = self::DEFAULT_FONT_SIZE, $a_bold = self::DEFAULT_IS_BOLD, $a_italics = self::DEFAULT_IS_ITALIC)
	{
		$this->setFonts($a_name, $a_size, $a_bold, $a_italics, "billnumber");
	}

	/**
	 * @param string $a_name    Title Font
	 * @param int    $a_size    Title Font Size
	 * @param bool   $a_bold    Title Bold
	 * @param bool   $a_italics Title Italics
	 */
	public function setTitleFont($a_name = self::DEFAULT_FONT_NAME, $a_size = self::DEFAULT_FONT_SIZE, $a_bold = self::DEFAULT_IS_BOLD, $a_italics = self::DEFAULT_IS_ITALIC)
	{
		$this->setFonts($a_name, $a_size, $a_bold, $a_italics, "title");
	}

	/**
	 * @param string $a_name    PageNumber Font
	 * @param int    $a_size    PageNumber Font Size
	 * @param bool   $a_bold    PageNumber Bold
	 * @param bool   $a_italics PageNumber Italics
	 */
	public function setPageNumberFont($a_name = self::DEFAULT_FONT_NAME, $a_size = self::DEFAULT_FONT_SIZE, $a_bold = self::DEFAULT_IS_BOLD, $a_italics = self::DEFAULT_IS_ITALIC)
	{
		$this->setFonts($a_name, $a_size, $a_bold, $a_italics, "pagenumber");
	}

	/**
	 * @param string $a_name    Calculation Font
	 * @param int    $a_size    Calculation Font Size
	 * @param bool   $a_bold    Calculation Bold
	 * @param bool   $a_italics Calculation Italics
	 */
	public function setCalculationFont($a_name = self::DEFAULT_FONT_NAME, $a_size = self::DEFAULT_FONT_SIZE, $a_bold = self::DEFAULT_IS_BOLD, $a_italics = self::DEFAULT_IS_ITALIC)
	{
		$this->setFonts($a_name, $a_size, $a_bold, $a_italics, "calculation");
	}

	/**
	 * @param string $a_name    Text Font
	 * @param int    $a_size    Text Font Size
	 * @param bool   $a_bold    Text Bold
	 * @param bool   $a_italics Text Italics
	 */
	public function setTextFont($a_name = self::DEFAULT_FONT_NAME, $a_size = self::DEFAULT_FONT_SIZE, $a_bold = self::DEFAULT_IS_BOLD, $a_italics = self::DEFAULT_IS_ITALIC)
	{
		$this->setFonts($a_name, $a_size, $a_bold, $a_italics, "text");
	}

	/**
	 * Generates the PDF containing the bill and delivers it to the client.
	 * @throws ilException
	 */
	public function deliver()
	{

		if($this->bill->isFinalized() == false)
		{
			throw new ilException("Bill was not finalized");
		}
		if(!isset($this->bill))
		{
			throw new ilException("Bill was not set");
		}
		$temp_file = ilUtil::ilTempnam();
		$this->build($temp_file);
		ilUtil::deliverFile($temp_file, 'Bill.pdf', 'application/pdf', true, true);
	}

	/**
	 * Generates the PDF containing the Bill for the given Bill and stores it at the given path.
	 * @param string $a_path Storage path in file system, e.g. /srv/www/htdocs/ilias/pdfs/output.pdf
	 * @throws ilException
	 */
	public function build($a_path)
	{
		if(empty($this->bill))
		{
			throw new ilException('Requested storage without any given bill');
		}

		$file_info = pathinfo($a_path);

		if(!is_writeable($file_info['dirname']))
		{
			throw new ilException('Cannot write file to directory: ' . $file_info['dirname']);
		}

		$this->pdf->SetMargins(0, 0, $this->spaceRight);
		$this->pdf->AliasNbPages();
		$this->pdf->AddPage();

		$fontHeight = $this->determineSpacings();

		if($this->background != "")
		{
			$this->pdf->Image($this->background, 0, 0, 21, 29.7);
		}

		$this->createHeadline();
		$this->createAboutTitleDate();
		$this->createSalutationPretext();

		$this->createCalculationHeadline($fontHeight);
		$this->createCalculation($fontHeight, $this->distanceindex);
		$this->createCalculationUnderline($fontHeight, $this->distanceindex, $this->sumPreTax, $this->sumVAT, $this->sumAfterTax);

		$this->createPosttextGreeting($fontHeight, $this->distanceindex);
		$this->createPageNumber();

		$this->resetDeliverMembers();

		$this->pdf->Output($a_path, 'F');
	}

	/**
	 * @return int
	 */
	private function determineSpacings()
	{
		$lineWidth = array(
			"6"  => 4.3, "7" => 2.5, "8" => 2.6, "9" => 2.5, "10" => 2.5, "11" => 5, "12" => 5.1, "13" => 5, "14" => 5.1,
			"15" => 5.2, "16" => 5.3, "17" => 5.6, "18" => 5.7, "19" => 5.7, "20" => 5.8,
		);

		$additionaldistanceXLetters = array(
			"6"  => 0.3, "7" => -0.3, "8" => -0.3, "9" => -0.3, "10" => -0.3, "11" => 0.5, "12" => 0.5,
			"13" => 0.5, "14" => 0.5, "15" => 0.6, "16" => 0.6, "17" => 0.8, "18" => 0.8, "19" => 0.8, "20" => 1,
		);

		$additionaldistanzYLetters = array(
			"6"  => 0.3, "7" => 0.4, "8" => 0.4, "9" => 0.4, "10" => 0.5, "11" => 0.5,
			"12" => 0.5, "13" => 0.5, "14" => 0.5, "15" => 0.5, "16" => 0.6, "17" => 0.7, "18" => 0.8, "19" => 0.8, "20" => 1,
		);
		if($this->TextFontSize < 6)
		{
			$this->addidistX              = 0.4;
			$this->additionaldistanceline = 1;
			$this->addidistY              = 0.4;
		}

		if($this->TextFontSize >= 6 && $this->TextFontSize <= 20)
		{
			$this->addidistX              = $additionaldistanceXLetters[$this->TextFontSize];
			$this->additionaldistanceline = $lineWidth[$this->TextFontSize];
			$this->addidistY              = $additionaldistanzYLetters[$this->TextFontSize];
		}

		$fontHeight = $this->TextFontSize * 0.04 + $this->TextFontSize * 0.04;
		return $fontHeight;
	}

	/**
	 *
	 */
	private function createHeadline()
	{
		
		
		$fontHeight = $this->AddressFontSize * 0.04 + $this->AddressFontSize * 0.04;

		$this->pdf->SetFont($this->AddressFontName, $this->determineIfBoldOrItalic($this->AddressFontBold, $this->AddressFontItalic), $this->AddressFontSize);
		$rec_name = explode(",", $this->bill->getRecipientName());
		$this->pdf->WriteText($this->spaceLeft, $this->spaceAddress, $this->encodeSpecialChars(trim($rec_name[0])));
		$this->pdf->WriteText($this->spaceLeft, $this->spaceAddress + $fontHeight, $this->encodeSpecialChars(trim($rec_name[1])));
		$this->pdf->WriteText($this->spaceLeft, $this->spaceAddress + $fontHeight * 2, ($this->encodeSpecialChars($this->bill->getRecipientStreet())) . " " . $this->encodeSpecialChars($this->bill->getRecipientHousenumber()));
		$this->pdf->WriteText($this->spaceLeft, $this->spaceAddress + $fontHeight * 3, ($this->encodeSpecialChars($this->bill->getRecipientZipcode())) . " " . ($this->encodeSpecialChars($this->bill->getRecipientCity())));
		$this->pdf->WriteText($this->spaceLeft, $this->spaceAddress + $fontHeight * 4, ($this->encodeSpecialChars($this->bill->getRecipientCountry())));

		$this->pdf->SetFont($this->AboutFontName, $this->determineIfBoldOrItalic($this->AboutFontBold, $this->AboutFontItalic), $this->AboutFontSize);
		
		#$this->pdf->WriteText($this->spaceLeft, $this->spaceAbout, ($this->encodeSpecialChars($this->plAbout)));
		$this->forwardingY = $this->pdf->WriteMultiCell($this->spaceLeft, $this->spaceAbout   , $this->encodeSpecialChars($this->plAbout), $this->spaceRight+1);
	}

	/**
	 * @param boolean $bold
	 * @param boolean $italic
	 * @return string
	 */
	private function determineIfBoldOrItalic($bold, $italic)
	{
		$boldItalicMutator = "";
		if($bold == true)
		{
			$boldItalicMutator .= "B";
		}
		if($italic == true)
		{
			$boldItalicMutator .= "I";
		}
		return $boldItalicMutator;
	}

	/**
	 * @param  string $text
	 * @return string
	 */
	private function encodeSpecialChars($text)
	{
		$text = iconv('UTF-8', 'windows-1252', $text);

		return $text;
	}

	/**
	 *
	 */
	private function createAboutTitleDate()

	{
		$old_status = ilDatePresentation::useRelativeDates();
		ilDatePresentation::setUseRelativeDates(false);
		$date = ilDatePresentation::formatDate($this->bill->getDate());
		ilDatePresentation::setUseRelativeDates($old_status);
		
		
		$datedist=0;
		
		if($this->DateFontSize==6){$datedist=1.1;}
		if($this->DateFontSize==7){$datedist=1.3;}
		if($this->DateFontSize==8){$datedist=1.5;}
		if($this->DateFontSize==9){$datedist=1.7;}
		if($this->DateFontSize==10){$datedist=1.8;}
		if($this->DateFontSize==11){$datedist=2;}
		if($this->DateFontSize==12){$datedist=2.2;}
		if($this->DateFontSize==13){$datedist=2.4;}
		if($this->DateFontSize==14){$datedist=2.6;}
		if($this->DateFontSize==15){$datedist=2.7;}
		if($this->DateFontSize==16){$datedist=2.9;}
		if($this->DateFontSize==17){$datedist=3.2;}
		if($this->DateFontSize==18){$datedist=3.4;}
		if($this->DateFontSize==19){$datedist=3.6;}
		if($this->DateFontSize==20){$datedist=3.7;}


		
		

		$this->pdf->SetFont($this->DateFontName, $this->determineIfBoldOrItalic($this->DateFontBold, $this->DateFontItalic), $this->DateFontSize);
		#$this->pdf->WriteText(21 - $this->spaceRight - count($this->bill->getDate()) - $this->spaceRight +$datedist, $this->spaceAbout, ($this->encodeSpecialChars($date)));
		$this->pdf->WriteMultiCell(21-$this->spaceRight-$datedist, $this->spaceAbout, $this->encodeSpecialChars($date), 0);
		$this->pdf->SetFont($this->BillNumberFontName, $this->determineIfBoldOrItalic($this->BillNumberFontBold, $this->BillNumberFontItalic), $this->BillNumberFontSize);
		$this->pdf->WriteText($this->spaceLeft, $this->spaceBillnumber, ($this->encodeSpecialChars($this->plBillNumberLabel)) . ": " . ($this->encodeSpecialChars($this->bill->getBillNumber())));
	
		$this->pdf->SetFont($this->TitleFontName, $this->determineIfBoldOrItalic($this->TitleFontBold, $this->TitleFontItalic), $this->TitleFontSize);
		#$this->pdf->WriteText($this->spaceLeft, $this->spaceTitle, ($this->encodeSpecialChars($this->plTitle)));
		$this->pdf->WriteMultiCell($this->spaceLeft, $this->spaceTitle, $this->encodeSpecialChars($this->plTitle), $this->spaceRight+$this->spaceLeft);
	}

	/**
	 *
	 */
	private function createSalutationPretext()
	{
		$this->pdf->SetFont($this->TextFontName, $this->determineIfBoldOrItalic($this->TextFontBold, $this->TextFontItalic), $this->TextFontSize);
		#$this->pdf->WriteText($this->spaceLeft, $this->spaceText, ($this->encodeSpecialChars($this->plSalutation)));
		$this->forwardingY =$this->pdf->WriteMultiCell($this->spaceLeft, $this->spaceText  , $this->encodeSpecialChars($this->plSalutation), $this->spaceRight+$this->spaceLeft);
		$this->forwardingY += $this->pdf->WriteMultiCell($this->spaceLeft, $this->forwardingY, ($this->encodeSpecialChars($this->plPretext)),$this->spaceRight+$this->spaceLeft);
	}

	/**
	 * @param float $fontHeight
	 */
	private function createCalculationHeadline($fontHeight)
	{

		$info1 = ($this->encodeSpecialChars($this->bill->getCurrency()) . " " . $this->plSideInfoForCurrentPreTaxes);
		//$info2 = ($this->encodeSpecialChars($this->plCalculationTaxAmount));
		$info2 = ($this->encodeSpecialChars("USt (19,00 %)"));
		$info3 = ($this->encodeSpecialChars($this->bill->getCurrency()) . " " . $this->plSideInfoForCurrentAfterTaxes);
		$this->pdf->SetFont($this->CalculationFontName, $this->determineIfBoldOrItalic($this->CalculationFontBold, $this->CalculationFontItalic), $this->CalculationFontSize);

		$this->pdf->setXY($this->spaceLeft + 9.2 + $this->addidistX, $this->spaceText + $fontHeight * 2 + $this->addidistY * 3);
		$this->pdf->Cell(1, 0, $info1, 0, 0, 'R', 0);
		$this->pdf->setXY($this->spaceLeft + 12.2 + $this->addidistX * 2, $this->spaceText + $fontHeight * 2 + $this->addidistY * 3);
		$this->pdf->Cell(1, 0, $info2, 0, 0, 'R', 0);
		$this->pdf->setXY($this->spaceLeft + 15.2 + $this->addidistX * 3, $this->spaceText + $fontHeight * 2 + $this->addidistY * 3);
		$this->pdf->Cell(1, 0, $info3, 0, 0, 'R', 0);

		$this->pdf->Line($this->spaceLeft, $this->spaceText + $fontHeight * 3 + $this->addidistY * 2, $this->spaceLeft + 12.7 + $this->additionaldistanceline, $this->spaceText + $fontHeight * 3 + $this->addidistY * 2);
	}

	/**
	 * @param float $fontHeight
	 */
	private function createCalculation($fontHeight)
	{
		$sumVATSingle=0;

		foreach($this->bill->getItems() as $item)
		{
			$sumVATSingle =round($item->getPreTaxAmount() * $item->getVAT() / 100,2)+$sumVATSingle;
			$amount = round($item->getPreTaxAmount() * $item->getVAT() / 100 + $item->getPreTaxAmount(), 2);
			$this->sumAfterTax += $amount;
			$this->sumPreTax += $item->getPreTaxAmount();
			$this->sumVAT += $item->getVAT();
			$height = $this->spaceText + $fontHeight * 2.5 + $this->distanceindex + $this->addidistY * 1.5;
			$this->pdf->SetFont($this->CalculationFontName, $this->determineIfBoldOrItalic($this->CalculationFontBold, $this->CalculationFontItalic), $this->CalculationFontSize);

			$this->pdf->setXY($this->spaceLeft, $height);
			$this->pdf->Cell(7, 0, ($this->encodeSpecialChars($item->getTitle())), 0, 0, 'L', 0);

			$this->pdf->setXY($this->spaceLeft + 9.2 + $this->addidistX, $height);
			$this->pdf->Cell(1, 0, $this->round($item->getPreTaxAmount()), 0, 0, 'R', 0);

			$this->pdf->setXY($this->spaceLeft + 12.2 + $this->addidistX * 2, $height);
			$this->pdf->Cell(1, 0, ($this->round($amount - $item->getPreTaxAmount())) . " %", 0, 0, 'R', 0);

			$this->pdf->setXY($this->spaceLeft + 15.2 + $this->addidistX * 3, $height);
			$this->pdf->Cell(1, 0, $this->round($amount), 0, 0, 'R', 0);

			$this->distanceindex += $this->addidistY * 1.25;
		}
		$this->sumVAT = $sumVATSingle;
	}

	/**
	 * @param float $val
	 * @return string
	 */
	private function round($val)
	{
		$val = round($val, 2);
		return number_format($val, 2, ",", "");
	}
	
	private function round2($val)
	{
		$val = round($val, 1);
		return number_format($val, 2, ",", "");
	}
	
	

	/**
	 * @param float $fontHeight
	 * @param float $distanceindex
	 */
	private function createCalculationUnderline($fontHeight, $distanceindex)
	{
		$this->pdf->SetLineWidth(0.05);
		$height = $this->spaceText + $fontHeight * 3 + $distanceindex + $this->addidistY * 2;
		$this->pdf->Line($this->spaceLeft, $this->spaceText + $fontHeight * 2.75 + $distanceindex + $this->addidistY, $this->spaceLeft + 12.7 + $this->additionaldistanceline, $this->spaceText + $fontHeight * 2.75 + $distanceindex + $this->addidistY);
		$this->pdf->SetFont($this->CalculationFontName, $this->determineIfBoldOrItalic($this->CalculationFontBold, $this->CalculationFontItalic), $this->CalculationFontSize);
		$this->pdf->setXY($this->spaceLeft, $height);
		$this->pdf->Cell(1, 0, $this->plCalculationTotalAmount, 0, 0, 'L', 0);

		$this->pdf->SetFont($this->CalculationFontName, $this->determineIfBoldOrItalic($this->CalculationFontBold, $this->CalculationFontItalic), $this->CalculationFontSize);
		$this->pdf->setXY($this->spaceLeft + 9.2 + $this->addidistX, $height);
		$this->pdf->Cell(1, 0, $this->round($this->sumPreTax)." ".$this->encodeSpecialChars($this->bill->getCurrency()), 0, 0, 'R', 0);
		$this->pdf->setXY($this->spaceLeft + 12.2 + $this->addidistX * 2, $height);
		$this->pdf->Cell(1, 0, $this->round($this->sumVAT)." ".$this->encodeSpecialChars($this->bill->getCurrency()), 0, 0, 'R', 0);


		$this->pdf->SetFont($this->CalculationFontName, $this->determineIfBoldOrItalic($this->CalculationFontBold, $this->CalculationFontItalic), $this->CalculationFontSize);
		$this->pdf->setXY($this->spaceLeft + 15.2 + $this->addidistX * 3, $height);
		$this->pdf->Cell(1, 0, $this->round($this->sumAfterTax)." ".$this->encodeSpecialChars($this->bill->getCurrency()), 0, 0, 'R', 0);
	}

	/**
	 * @param float $fontHeight
	 * @param float $distanceindex
	 */
	private function createPosttextGreeting($fontHeight, $distanceindex)
	{

		$this->pdf->SetFont($this->TextFontName, $this->determineIfBoldOrItalic($this->TextFontBold, $this->TextFontItalic), $this->TextFontSize);
		$this->forwardingY = $this->pdf->WriteMultiCell($this->spaceLeft, $this->spaceText + $fontHeight * 3.5 + $distanceindex + 2, $this->encodeSpecialChars($this->plPosttext), $this->spaceRight+$this->spaceLeft);
		
		$this->forwardingY += $this->pdf->WriteMultiCell($this->spaceLeft,  $this->forwardingY , $this->encodeSpecialChars($this->plGreetings), $this->spaceRight+$this->spaceLeft);
	}

	/**
	 *
	 */
	private function createPageNumber()
	{
		// gev-patch start
		//$this->pdf->SetFont($this->DateFontName, $this->determineIfBoldOrItalic($this->DateFontBold, $this->DateFontItalic), $this->DateFontSize);
		//$this->pdf->WriteText(21 - $this->spaceRight, 29.7 - $this->spaceBottom, 1);
		//$this->pdf->WriteText($this->spaceLeft, 29.7 - $this->spaceBottom, 1);
		// gev-patch end
	}

	/**
	 *
	 */
	private function resetDeliverMembers()
	{
		$this->distanceindex          = 1.5;
		$this->sumPreTax              = 0;
		$this->sumVAT                 = 0;
		$this->sumAfterTax            = 0;
		$this->addidistX              = 0;
		$this->additionaldistanceline = 0;
		$this->addidistY              = 0;
	}

	/**
	 * @param string $a_message
	 */
	private function log($a_message)
	{
		$this->logger->write(__CLASS__ . ':' . $a_message);
	}
}
