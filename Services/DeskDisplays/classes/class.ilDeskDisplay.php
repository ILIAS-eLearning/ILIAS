<?php
/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/DeskDisplays/classes/class.ilDeskDisplayPDF.php';
require_once 'Services/Exceptions/classes/class.ilException.php';

/**
 * @author  Maximilian Frings <mfrings@databay.de>
 * @author  Michael Jansen <mjansen@databay.de>
 * @version $Id$
 */
class ilDeskDisplay
{
	const LINE1_DEFAULT_FONT_NAME        = 'Arial';
	const LINE1_DEFAULT_FONT_SIZE        = 48;
	const LINE1_DEFAULT_IS_BOLD          = false;
	const LINE1_DEFAULT_IS_ITALIC        = false;
	const LINE2_DEFAULT_FONT_NAME        = 'Arial';
	const LINE2_DEFAULT_FONT_SIZE        = 90;
	const LINE2_DEFAULT_IS_BOLD          = true;
	const LINE2_DEFAULT_IS_ITALIC        = false;
	const LINE_1_DEFAULT_RGB_VALUE_RED   = 255;
	const LINE_1_DEFAULT_RGB_VALUE_GREEN = 255;
	const LINE_1_DEFAULT_RGB_VALUE_BLUE  = 255;
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
	 * @var ilDeskDisplayPDF|FPDF
	 */
	private $pdf;
	/**
	 * @var ilLog
	 */
	private $logger;
	/**
	 * @var float
	 */
	private $space_left = 3.0;
	/**
	 * @var float
	 */
	private $space_bottom1 = 10.0;
	/**
	 * @var float
	 */
	private $space_bottom2 = 5.0;
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
	private $mode = 'P';
	/**
	 * @var array
	 */
	private $users = array();
	/**
	 * @var array
	 */
	private $texts = array();
	/**
	 * @var string
	 */
	private $line1_font_name = self::LINE1_DEFAULT_FONT_NAME;
	/**
	 * @var int
	 */
	private $line1_font_size = self::LINE1_DEFAULT_FONT_SIZE;
	/**
	 * @var bool
	 */
	private $line1_is_bold = self::LINE1_DEFAULT_IS_BOLD;
	/**
	 * @var bool
	 */
	private $line1_is_italic = self::LINE1_DEFAULT_IS_ITALIC;
	/**
	 * @var string
	 */
	private $line2_font_name = self::LINE2_DEFAULT_FONT_NAME;
	/**
	 * @var int
	 */
	private $line2_font_size = self::LINE2_DEFAULT_FONT_SIZE;
	/**
	 * @var bool
	 */
	private $line2_is_bold = self::LINE2_DEFAULT_IS_BOLD;
	/**
	 * @var bool
	 */
	private $line2_is_italic = self::LINE2_DEFAULT_IS_ITALIC;
	/**
	 * @var string
	 */
	private $background = '';
	/**
	 * @var int
	 */
	private $line1_rgb_value_red = self::LINE_1_DEFAULT_RGB_VALUE_RED;
	/**
	 * @var int
	 */
	private $line1_rgb_value_green = self::LINE_1_DEFAULT_RGB_VALUE_GREEN;
	/**
	 * @var int
	 */
	private $line1_rgb_value_blue = self::LINE_1_DEFAULT_RGB_VALUE_BLUE;
	/**
	 * @var int
	 */
	private $line2_rgb_value_red = self::LINE_2_DEFAULT_RGB_VALUE_RED;
	/**
	 * @var int
	 */
	private $line2_rgb_value_green = self::LINE_2_DEFAULT_RGB_VALUE_GREEN;
	/**
	 * @var int
	 */
	private $line2_rgb_value_blue = self::LINE_2_DEFAULT_RGB_VALUE_BLUE;
	/**
	 * @var ilDB
	 */
	private $database;

	/**
	 * Constructor
	 */
	public function __construct(ilDB $database, ilLog $logger)
	{
		$this->database = $database;
		$this->logger   = $logger;
		$this->pdf      = new ilDeskDisplayPDF($this->mode, $this->unit, $this->format);
	}

	/**
	 * @param string $a_name    Line1 Font
	 * @param int    $a_size    Line1 Font Size
	 * @param bool   $a_bold    Line1 Bold
	 * @param bool   $a_italics Line1 Italics
	 */
	public function setLine1Font($a_name = self::LINE1_DEFAULT_FONT_NAME, $a_size = self::LINE1_DEFAULT_FONT_SIZE, $a_bold = self::LINE1_DEFAULT_IS_BOLD, $a_italics = self::LINE1_DEFAULT_IS_ITALIC)
	{
		if(!is_string($a_name))
		{
			$a_name = self::LINE1_DEFAULT_FONT_NAME;
		}
		if(!is_int($a_size) && (int)$a_size != $a_size)
		{
			$a_size = self::LINE1_DEFAULT_FONT_SIZE;
		}
		if(!is_bool($a_bold))
		{
			$a_bold = self::LINE1_DEFAULT_IS_BOLD;
		}
		if(!is_bool($a_italics))
		{
			$a_italics = self::LINE1_DEFAULT_IS_ITALIC;
		}
		if(in_array(strtolower($a_name), array_map('strtolower', self::getSupportedFonts())))
		{
			$this->line1_font_name = $a_name;
		}
		else
		{
			$this->line1_font_name = self::LINE1_DEFAULT_FONT_NAME;
			$this->log("Passed font '" . $a_name . "' is not supported, fell back to Arial");
		}
		if($a_size <= 0)
		{
			$a_size = self::LINE1_DEFAULT_FONT_SIZE;
		}
		if($a_size > 140)
		{
			$a_size = self::LINE1_DEFAULT_FONT_SIZE;
		}

		$this->line1_is_bold   = $a_bold;
		$this->line1_is_italic = $a_italics;
		$this->line1_font_size = $a_size;
	}

	/**
	 * @param ilDB $db
	 */
	public function setDatabaseAdapter(ilDB $db)
	{
		$this->database = $db;
	}

	/**
	 * @return ilDB
	 */
	public function getDatabaseAdapter()
	{
		return $this->database;
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
	 * @param string $a_message
	 */
	private function log($a_message)
	{
		$this->logger->write(__CLASS__ . ':' . $a_message);
	}

	/**
	 * @param string $a_name    Line2 Font
	 * @param int    $a_size    Line2 Font Size
	 * @param bool   $a_bold    Line2 Bold
	 * @param bool   $a_italics Line2 Italics
	 */
	public function setLine2Font($a_name = self::LINE2_DEFAULT_FONT_NAME, $a_size = self::LINE2_DEFAULT_FONT_SIZE, $a_bold = self::LINE2_DEFAULT_IS_BOLD, $a_italics = self::LINE2_DEFAULT_IS_ITALIC)
	{
		if(!is_string($a_name))
		{
			$a_name = self::LINE2_DEFAULT_FONT_NAME;
		}
		if(!is_int($a_size) && (int)$a_size != $a_size)
		{
			$a_size = self::LINE2_DEFAULT_FONT_SIZE;
		}
		if(!is_bool($a_bold))
		{
			$a_bold = self::LINE2_DEFAULT_IS_BOLD;
		}
		if(!is_bool($a_italics))
		{
			$a_italics = self::LINE2_DEFAULT_IS_ITALIC;
		}
		if(in_array(strtolower($a_name), array_map('strtolower', self::getSupportedFonts())))
		{
			$this->line2_font_name = $a_name;
		}
		else
		{
			$this->line2_font_name = self::LINE2_DEFAULT_FONT_NAME;
			$this->log("Passed font '" . $a_name . "' is not supported, fell back to Arial");
		}
		if($a_size > 140)
		{
			$a_size = self::LINE2_DEFAULT_FONT_SIZE;
		}
		if($a_size <= 0)
		{
			$a_size = self::LINE2_DEFAULT_FONT_SIZE;
		}
		$this->line2_is_bold   = $a_bold;
		$this->line2_is_italic = $a_italics;
		$this->line2_font_size = $a_size;
	}

	/**
	 * @param int $a_r Line1 Color R
	 * @param int $a_g Line1 Color G
	 * @param int $a_b Line1 Color B
	 * @throws ilException
	 */
	public function setLine1Color($a_r = self::LINE_1_DEFAULT_RGB_VALUE_RED, $a_g = self::LINE_1_DEFAULT_RGB_VALUE_GREEN, $a_b = self::LINE_1_DEFAULT_RGB_VALUE_BLUE)
	{
		$args = func_get_args();
		for($i = 0; $i < count($args); $i++)
		{
			if(
				!is_int($args[$i]) ||
				$args[$i] < 0 ||
				$args[$i] > 255
			)
			{
				throw new ilException('The ' . ($i + 1) . '. parameter is not a valid RGB value');
			}
		}

		$this->line1_rgb_value_red   = $a_r;
		$this->line1_rgb_value_green = $a_g;
		$this->line1_rgb_value_blue  = $a_b;
	}

	/**
	 * @param int $a_r Line2 Color R
	 * @param int $a_g Line2 Color G
	 * @param int $a_b Line2 Color B
	 * @throws ilException
	 */
	public function setLine2Color($a_r = self::LINE_2_DEFAULT_RGB_VALUE_RED, $a_g = self::LINE_2_DEFAULT_RGB_VALUE_GREEN, $a_b = self::LINE_2_DEFAULT_RGB_VALUE_BLUE)
	{
		$args = func_get_args();
		for($i = 0; $i < count($args); $i++)
		{
			if(
				!is_int($args[$i]) ||
				$args[$i] < 0 ||
				$args[$i] > 255
			)
			{
				throw new ilException('The ' . ($i + 1) . '. parameter is not a valid RGB value');
			}
		}

		$this->line2_rgb_value_red   = $a_r;
		$this->line2_rgb_value_green = $a_g;
		$this->line2_rgb_value_blue  = $a_b;
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
	public function getLine1FontName()
	{
		return $this->line1_font_name;
	}

	/**
	 * @return int
	 */
	public function getLine1FontSize()
	{
		return $this->line1_font_size;
	}

	/**
	 * @return boolean
	 */
	public function getLine1IsBold()
	{
		return $this->line1_is_bold;
	}

	/**
	 * @return boolean
	 */
	public function getLine1IsItalic()
	{
		return $this->line1_is_italic;
	}

	/**
	 * @return int
	 */
	public function getLine1RgbValueBlue()
	{
		return $this->line1_rgb_value_blue;
	}

	/**
	 * @return int
	 */
	public function getLine1RgbValueGreen()
	{
		return $this->line1_rgb_value_green;
	}

	/**
	 * @return int
	 */
	public function getLine1RgbValueRed()
	{
		return $this->line1_rgb_value_red;
	}

	/**
	 * @return string
	 */
	public function getLine2FontName()
	{
		return $this->line2_font_name;
	}

	/**
	 * @return int
	 */
	public function getLine2FontSize()
	{
		return $this->line2_font_size;
	}

	/**
	 * @return boolean
	 */
	public function getLine2IsBold()
	{
		return $this->line2_is_bold;
	}

	/**
	 * @return boolean
	 */
	public function getLine2IsItalic()
	{
		return $this->line2_is_italic;
	}

	/**
	 * @return int
	 */
	public function getLine2RgbValueGreen()
	{
		return $this->line2_rgb_value_green;
	}

	/**
	 * @return int
	 */
	public function getLine2RgbValueBlue()
	{
		return $this->line2_rgb_value_blue;
	}

	/**
	 * @return int
	 */
	public function getLine2RgbValueRed()
	{
		return $this->line2_rgb_value_red;
	}

	/**
	 * @return float
	 */
	public function getSpaceBottom1()
	{
		return $this->space_bottom1;
	}

	/**
	 * @param float $a_space_bottom1 Bottom space to label1 on PDF page
	 * @throws ilException
	 */
	public function setSpaceBottom1($a_space_bottom1)
	{
		if($a_space_bottom1 > 29.7 || $a_space_bottom1 <= 0)
		{
			throw new ilException("Space left dimensions for the current format");
		}

		$this->space_bottom1 = $a_space_bottom1;
	}

	/**
	 * @return float
	 */
	public function getSpaceBottom2()
	{
		return $this->space_bottom2;
	}

	/**
	 * @param float $a_space_bottom2 Bottom space to label2 on PDF page
	 * @throws ilException
	 */
	public function setSpaceBottom2($a_space_bottom2)
	{
		if($a_space_bottom2 > 29.7 || $a_space_bottom2 <= 0)
		{
			throw new ilException("Space left dimensions for the current format");
		}

		$this->space_bottom2 = $a_space_bottom2;
	}

	/**
	 * @return float
	 */
	public function getSpaceLeft()
	{
		return $this->space_left;
	}

	/**
	 * @param float $a_space_left Left space on PDF page
	 * @throws ilException
	 */
	public function setSpaceLeft($a_space_left)
	{
		if($a_space_left > 21.0 || $a_space_left <= 0)
		{
			throw new ilException("Space left dimensions for the current format");
		}

		$this->space_left = $a_space_left;
	}

	/**
	 * @return string
	 */
	public function getUnit()
	{
		return $this->unit;
	}

	/**
	 * @return array
	 */
	public function getUsers()
	{
		return $this->users;
	}

	/**
	 * @param array $a_users
	 * @throws ilException
	 */
	public function setUsers(array $a_users)
	{
		$users = array_unique(array_filter($a_users));
		if(empty($users))
		{
			throw new ilException('Empty user array set');
		}

		$this->users = $a_users;
		$this->texts = array();
	}

	/**
	 * @return string
	 */
	public function getMode()
	{
		return $this->mode;
	}

	/**
	 * Generates the PDF containing the desk displays and delivers it to the client.
	 * @throws ilException
	 */
	public function deliver()
	{
		$temp_file = ilUtil::ilTempnam();
		$this->build($temp_file);
		ilUtil::deliverFile($temp_file, 'DeskDisplays.pdf', 'application/pdf', false, true);
	}

	/**
	 * Generates the PDF containing the desk displays for the given users and stores it at the given path.
	 * @param string $a_path Storage path in file system, e.g. /srv/www/htdocs/ilias/pdfs/output.pdf
	 * @throws ilException
	 */
	public function build($a_path)
	{
		if(empty($this->users))
		{
			throw new ilException('Requested storage without any given users');
		}

		$file_info = pathinfo($a_path);
		if(!is_writeable($file_info['dirname']))
		{
			throw new ilException('Cannot write file to directory: ' . $file_info['dirname']);
		}

		$this->fetchTexts();

		$pixelfactor = 595 / 21;

		$letterfactors = array(
			"A" => 2, "B" => 7, "C" => 4, "D" => 8, "E" => 8, "F" => 8, "G" => 4, "H" => 7,
			"I" => 9, "J" => 1, "K" => 7, "L" => 7, "M" => 7, "N" => 7, "O" => 3, "P" => 8, "Q" => 3, "R" => 8,
			"S" => 6, "T" => 2, "U" => 8, "V" => 3, "W" => 3, "X" => 4, "Y" => 2, "Z" => 5
		);
		$shift_param1  = 0;
		$shift_param2  = 0;

		for($index = 0; $index < count($this->texts); $index++)
		{
			$this->pdf->SetMargins(0, 0, 0);
			$this->pdf->AliasNbPages();
			$this->pdf->AddPage();

			#textlines
			$line1 = $this->texts[$index][0];
			$line2 = $this->texts[$index][1];

			if(ctype_upper($line1{0}))
			{
				$shift_param1 = $letterfactors[$line1{0}] / 90 * ($this->line1_font_size - 10) / $pixelfactor;
			}

			if(ctype_upper($line2{0}))
			{
				$shift_param2 = $letterfactors[$line2{0}] / 90 * ($this->line2_font_size - 10) / $pixelfactor;
			}

			#background image
			if($this->getBackground())
			{
				$this->pdf->Image($this->getBackground(), 0, 0, 21, 29.7);
			}

			#right up
			$fatrb = "";
			if($this->line1_is_bold == true)
			{
				$fatrb .= "B";
			}
			if($this->line1_is_italic == true)
			{
				$fatrb .= "I";
			}
			$this->pdf->SetFont($this->line1_font_name, $fatrb, $this->line1_font_size);

			$this->pdf->RotatedText(21 - $this->space_left + $shift_param1, $this->space_bottom1, $line1, 180, $this->line1_rgb_value_red, $this->line1_rgb_value_green, $this->line1_rgb_value_blue);

			$fatrb = "";

			if($this->line2_is_bold == true)
			{
				$fatrb .= "B";
			}
			if($this->line2_is_italic == true)
			{
				$fatrb .= "I";
			}
			$this->pdf->SetFont($this->line2_font_name, $fatrb, $this->line2_font_size);

			$this->pdf->RotatedText(21 - $this->space_left + $shift_param2, $this->space_bottom2, $line2, 180, $this->line2_rgb_value_red, $this->line2_rgb_value_green, $this->line2_rgb_value_blue);

			#left down
			$fatrb = "";
			if($this->line1_is_bold == true)
			{
				$fatrb .= "B";
			}
			if($this->line1_is_italic == true)
			{
				$fatrb .= "I";
			}
			$this->pdf->SetTextColor($this->line1_rgb_value_red, $this->line1_rgb_value_green, $this->line1_rgb_value_blue);
			$this->pdf->SetFont($this->line1_font_name, $fatrb, $this->line1_font_size);
			$this->pdf->RotatedText($this->space_left - $shift_param1, 29.7 - $this->space_bottom1, $line1, 0, $this->line1_rgb_value_red, $this->line1_rgb_value_green, $this->line1_rgb_value_blue);

			$fatrb = "";

			if($this->line2_is_bold == true)
			{
				$fatrb .= "B";
			}
			if($this->line2_is_italic == true)
			{
				$fatrb .= "I";
			}

			$this->pdf->SetFont($this->line2_font_name, $fatrb, $this->line2_font_size);
			$this->pdf->RotatedText($this->space_left - $shift_param2, 29.7 - $this->space_bottom2, $line2, 0, $this->line2_rgb_value_red, $this->line2_rgb_value_green, $this->line2_rgb_value_blue);
		}
		$this->pdf->Output($a_path, 'F');
	}

	/**
	 *
	 */
	protected function fetchTexts()
	{
		$offset = 0;
		$total  = count($this->users);
		$limit  = 1000;

		while($offset < $total)
		{
			$users_to_handle = array_slice($this->users, $offset, $limit);
			$query           = 'SELECT usr_id, title, firstname, lastname FROM usr_data WHERE ' . $this->database->in('usr_id', $users_to_handle, false, 'integer');
			$res             = $this->database->query($query);
			while($row = $this->database->fetchAssoc($res))
			{
				$this->texts[] = array(
					implode('', array_filter(array_map('trim', array(iconv('UTF-8', 'windows-1252', $row['title']),  iconv('UTF-8', 'windows-1252', $row['firstname']))))),
					iconv('UTF-8', 'windows-1252', $row['lastname'])
				);
			}

			$offset = $offset + $limit;
		}
	}

	/**
	 * @return string
	 */
	public function getBackground()
	{
		return $this->background;
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
		}

		$this->background = $path;
	}
}