<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

class ilCharSelectorConfig
{
	/**
	 * Availabilities
	 * INACTIVE/INHERIT corresponds to an unconfigured selector (no database entries)
	 */
	const INACTIVE = 0;				// default for admin: deactivates the whole tool
	const INHERIT = 0;				// default for user and test: take the plattform default					
	const ENABLED = 1;				// enable the selector
	const DISABLED = 2;				// disable the selector
	
	/**
	 * Configuration contexts
	 */
	const CONTEXT_NONE = '';		// no context => no initialisation
	const CONTEXT_ADMIN = 'admin';	// administrative settings 
	const CONTEXT_USER = 'user';	// user specific settings
	const CONTEXT_TEST = 'test';	// test specific settings
	
	/**
	 * Code ranges for the unicode blocks
	 * @var array	 
	 */
	static $unicode_blocks = array 
	(
		'basic_latin' => array('Basic Latin', '0020', '007F'), // only printing characters
		'latin_1_supplement' => array('Latin-1 Supplement', '00A0', '00FF'), // only printing characters
		'latin_extended_a' => array('Latin Extended-A', '0100', '017F'),
		'latin_extended_b' => array('Latin Extended-B', '0180', '024F'),
		'ipa_extensions' => array('IPA Extensions', '0250', '02AF'),
		'spacing_modifier_letters' => array('Spacing Modifier Letters', '02B0', '02FF'),
		'combining_diacritical_marks' => array('Combining Diacritical Marks', '0300', '036F'),
		'greek_and_coptic' => array('Greek and Coptic', '0370', '03FF'),
		'cyrillic' => array('Cyrillic', '0400', '04FF'),
		'cyrillic_supplement' => array('Cyrillic Supplement', '0500', '052F'),
		'armenian' => array('Armenian', '0530', '058F'),
		'hebrew' => array('Hebrew', '0590', '05FF'),
		'arabic' => array('Arabic', '0600', '06FF'),
		'syriac' => array('Syriac', '0700', '074F'),
		'arabic_supplement' => array('Arabic Supplement', '0750', '077F'),
		'thaana' => array('Thaana', '0780', '07BF'),
		'nko' => array('NKo', '07C0', '07FF'),
		'samaritan' => array('Samaritan', '0800', '083F'),
		'mandaic' => array('Mandaic', '0840', '085F'),
		'arabic_extended_a' => array('Arabic Extended-A', '08A0', '08FF'),
		'devanagari' => array('Devanagari', '0900', '097F'),
		'bengali' => array('Bengali', '0980', '09FF'),
		'gurmukhi' => array('Gurmukhi', '0A00', '0A7F'),
		'gujarati' => array('Gujarati', '0A80', '0AFF'),
		'oriya' => array('Oriya', '0B00', '0B7F'),
		'tamil' => array('Tamil', '0B80', '0BFF'),
		'telugu' => array('Telugu', '0C00', '0C7F'),
		'kannada' => array('Kannada', '0C80', '0CFF'),
		'malayalam' => array('Malayalam', '0D00', '0D7F'),
		'sinhala' => array('Sinhala', '0D80', '0DFF'),
		'thai' => array('Thai', '0E00', '0E7F'),
		'lao' => array('Lao', '0E80', '0EFF'),
		'tibetan' => array('Tibetan', '0F00', '0FFF'),
		'myanmar' => array('Myanmar', '1000', '109F'),
		'georgian' => array('Georgian', '10A0', '10FF'),
		'hangul_jamo' => array('Hangul Jamo', '1100', '11FF'),
		'ethiopic' => array('Ethiopic', '1200', '137F'),
		'ethiopic_supplement' => array('Ethiopic Supplement', '1380', '139F'),
		'cherokee' => array('Cherokee', '13A0', '13FF'),
		'unified_canadian_aboriginal_syllabics' => array('Unified Canadian Aboriginal Syllabics', '1400', '167F'),
		'ogham' => array('Ogham', '1680', '169F'),
		'runic' => array('Runic', '16A0', '16FF'),
		'tagalog' => array('Tagalog', '1700', '171F'),
		'hanunoo' => array('Hanunoo', '1720', '173F'),
		'buhid' => array('Buhid', '1740', '175F'),
		'tagbanwa' => array('Tagbanwa', '1760', '177F'),
		'khmer' => array('Khmer', '1780', '17FF'),
		'mongolian' => array('Mongolian', '1800', '18AF'),
		'unified_canadian_aboriginal_syllabics_extended' => array('Unified Canadian Aboriginal Syllabics Extended', '18B0', '18FF'),
		'limbu' => array('Limbu', '1900', '194F'),
		'tai_le' => array('Tai Le', '1950', '197F'),
		'new_tai_lue' => array('New Tai Lue', '1980', '19DF'),
		'khmer_symbols' => array('Khmer Symbols', '19E0', '19FF'),
		'buginese' => array('Buginese', '1A00', '1A1F'),
		'tai_tham' => array('Tai Tham', '1A20', '1AAF'),
		'balinese' => array('Balinese', '1B00', '1B7F'),
		'sundanese' => array('Sundanese', '1B80', '1BBF'),
		'batak' => array('Batak', '1BC0', '1BFF'),
		'lepcha' => array('Lepcha', '1C00', '1C4F'),
		'ol_chiki' => array('Ol Chiki', '1C50', '1C7F'),
		'sundanese_supplement' => array('Sundanese Supplement', '1CC0', '1CCF'),
		'vedic_extensions' => array('Vedic Extensions', '1CD0', '1CFF'),
		'phonetic_extensions' => array('Phonetic Extensions', '1D00', '1D7F'),
		'phonetic_extensions_supplement' => array('Phonetic Extensions Supplement', '1D80', '1DBF'),
		'combining_diacritical_marks_supplement' => array('Combining Diacritical Marks Supplement', '1DC0', '1DFF'),
		'latin_extended_additional' => array('Latin Extended Additional', '1E00', '1EFF'),
		'greek_extended' => array('Greek Extended', '1F00', '1FFF'),
		'general_punctuation' => array('General Punctuation', '2000', '206F'),
		'superscripts_and_subscripts' => array('Superscripts and Subscripts', '2070', '209F'),
		'currency_symbols' => array('Currency Symbols', '20A0', '20CF'),
		'combining_diacritical_marks_for_symbols' => array('Combining Diacritical Marks for Symbols', '20D0', '20FF'),
		'letterlike_symbols' => array('Letterlike Symbols', '2100', '214F'),
		'number_forms' => array('Number Forms', '2150', '218F'),
		'arrows' => array('Arrows', '2190', '21FF'),
		'mathematical_operators' => array('Mathematical Operators', '2200', '22FF'),
		'miscellaneous_technical' => array('Miscellaneous Technical', '2300', '23FF'),
		'control_pictures' => array('Control Pictures', '2400', '243F'),
		'optical_character_recognition' => array('Optical Character Recognition', '2440', '245F'),
		'enclosed_alphanumerics' => array('Enclosed Alphanumerics', '2460', '24FF'),
		'box_drawing' => array('Box Drawing', '2500', '257F'),
		'block_elements' => array('Block Elements', '2580', '259F'),
		'geometric_shapes' => array('Geometric Shapes', '25A0', '25FF'),
		'miscellaneous_symbols' => array('Miscellaneous Symbols', '2600', '26FF'),
		'dingbats' => array('Dingbats', '2700', '27BF'),
		'miscellaneous_mathematical_symbols_a' => array('Miscellaneous Mathematical Symbols-A', '27C0', '27EF'),
		'supplemental_arrows_a' => array('Supplemental Arrows-A', '27F0', '27FF'),
		'braille_patterns' => array('Braille Patterns', '2800', '28FF'),
		'supplemental_arrows_b' => array('Supplemental Arrows-B', '2900', '297F'),
		'miscellaneous_mathematical_symbols_b' => array('Miscellaneous Mathematical Symbols-B', '2980', '29FF'),
		'supplemental_mathematical_operators' => array('Supplemental Mathematical Operators', '2A00', '2AFF'),
		'miscellaneous_symbols_and_arrows' => array('Miscellaneous Symbols and Arrows', '2B00', '2BFF'),
		'glagolitic' => array('Glagolitic', '2C00', '2C5F'),
		'latin_extended_c' => array('Latin Extended-C', '2C60', '2C7F'),
		'coptic' => array('Coptic', '2C80', '2CFF'),
		'georgian_supplement' => array('Georgian Supplement', '2D00', '2D2F'),
		'tifinagh' => array('Tifinagh', '2D30', '2D7F'),
		'ethiopic_extended' => array('Ethiopic Extended', '2D80', '2DDF'),
		'cyrillic_extended_a' => array('Cyrillic Extended-A', '2DE0', '2DFF'),
		'supplemental_punctuation' => array('Supplemental Punctuation', '2E00', '2E7F'),
		'cjk_radicals_supplement' => array('CJK Radicals Supplement', '2E80', '2EFF'),
		'kangxi_radicals' => array('Kangxi Radicals', '2F00', '2FDF'),
		'ideographic_description_characters' => array('Ideographic Description Characters', '2FF0', '2FFF'),
		'cjk_symbols_and_punctuation' => array('CJK Symbols and Punctuation', '3000', '303F'),
		'hiragana' => array('Hiragana', '3040', '309F'),
		'katakana' => array('Katakana', '30A0', '30FF'),
		'bopomofo' => array('Bopomofo', '3100', '312F'),
		'hangul_compatibility_jamo' => array('Hangul Compatibility Jamo', '3130', '318F'),
		'kanbun' => array('Kanbun', '3190', '319F'),
		'bopomofo_extended' => array('Bopomofo Extended', '31A0', '31BF'),
		'cjk_strokes' => array('CJK Strokes', '31C0', '31EF'),
		'katakana_phonetic_extensions' => array('Katakana Phonetic Extensions', '31F0', '31FF'),
		'enclosed_cjk_letters_and_months' => array('Enclosed CJK Letters and Months', '3200', '32FF'),
		'cjk_compatibility' => array('CJK Compatibility', '3300', '33FF'),
		'cjk_unified_ideographs_extension_a' => array('CJK Unified Ideographs Extension A', '3400', '4DBF'),
		'yijing_hexagram_symbols' => array('Yijing Hexagram Symbols', '4DC0', '4DFF'),
		'cjk_unified_ideographs' => array('CJK Unified Ideographs', '4E00', '9FFF'),
		'yi_syllables' => array('Yi Syllables', 'A000', 'A48F'),
		'yi_radicals' => array('Yi Radicals', 'A490', 'A4CF'),
		'lisu' => array('Lisu', 'A4D0', 'A4FF'),
		'vai' => array('Vai', 'A500', 'A63F'),
		'cyrillic_extended_b' => array('Cyrillic Extended-B', 'A640', 'A69F'),
		'bamum' => array('Bamum', 'A6A0', 'A6FF'),
		'modifier_tone_letters' => array('Modifier Tone Letters', 'A700', 'A71F'),
		'latin_extended_d' => array('Latin Extended-D', 'A720', 'A7FF'),
		'syloti_nagri' => array('Syloti Nagri', 'A800', 'A82F'),
		'common_indic_number_forms' => array('Common Indic Number Forms', 'A830', 'A83F'),
		'phags_pa' => array('Phags-pa', 'A840', 'A87F'),
		'saurashtra' => array('Saurashtra', 'A880', 'A8DF'),
		'devanagari_extended' => array('Devanagari Extended', 'A8E0', 'A8FF'),
		'kayah_li' => array('Kayah Li', 'A900', 'A92F'),
		'rejang' => array('Rejang', 'A930', 'A95F'),
		'hangul_jamo_extended_a' => array('Hangul Jamo Extended-A', 'A960', 'A97F'),
		'javanese' => array('Javanese', 'A980', 'A9DF'),
		'cham' => array('Cham', 'AA00', 'AA5F'),
		'myanmar_extended_a' => array('Myanmar Extended-A', 'AA60', 'AA7F'),
		'tai_viet' => array('Tai Viet', 'AA80', 'AADF'),
		'meetei_mayek_extensions' => array('Meetei Mayek Extensions', 'AAE0', 'AAFF'),
		'ethiopic_extended_a' => array('Ethiopic Extended-A', 'AB00', 'AB2F'),
		'meetei_mayek' => array('Meetei Mayek', 'ABC0', 'ABFF'),
		'hangul_syllables' => array('Hangul Syllables', 'AC00', 'D7AF'),
		'hangul_jamo_extended_b' => array('Hangul Jamo Extended-B', 'D7B0', 'D7FF'),
		'high_surrogates' => array('High Surrogates', 'D800', 'DB7F'),
		'high_private_use_surrogates' => array('High Private Use Surrogates', 'DB80', 'DBFF'),
		'low_surrogates' => array('Low Surrogates', 'DC00', 'DFFF'),
		'private_use_area' => array('Private Use Area', 'E000', 'F8FF'),
		'cjk_compatibility_ideographs' => array('CJK Compatibility Ideographs', 'F900', 'FAFF'),
		'alphabetic_presentation_forms' => array('Alphabetic Presentation Forms', 'FB00', 'FB4F'),
		'arabic_presentation_forms_a' => array('Arabic Presentation Forms-A', 'FB50', 'FDFF'),
		'variation_selectors' => array('Variation Selectors', 'FE00', 'FE0F'),
		'vertical_forms' => array('Vertical Forms', 'FE10', 'FE1F'),
		'combining_half_marks' => array('Combining Half Marks', 'FE20', 'FE2F'),
		'cjk_compatibility_forms' => array('CJK Compatibility Forms', 'FE30', 'FE4F'),
		'small_form_variants' => array('Small Form Variants', 'FE50', 'FE6F'),
		'arabic_presentation_forms_b' => array('Arabic Presentation Forms-B', 'FE70', 'FEFF'),
		'halfwidth_and_fullwidth_forms' => array('Halfwidth and Fullwidth Forms', 'FF00', 'FFEF'),
		'specials' => array('Specials', 'FFF0', 'FFFF'),
		/* here ends the Basic Multilinguage Plane (BMP) */
		
		/* 
		 * The following blocks are not yet supported well. 
		 * It seems that the chars are taken from the BMP instead
		 *  
		'linear_b_syllabary' => array('Linear B Syllabary', '10000', '1007F'),
		'linear_b_ideograms' => array('Linear B Ideograms', '10080', '100FF'),
		'aegean_numbers' => array('Aegean Numbers', '10100', '1013F'),
		'ancient_greek_numbers' => array('Ancient Greek Numbers', '10140', '1018F'),
		'ancient_symbols' => array('Ancient Symbols', '10190', '101CF'),
		'phaistos_disc' => array('Phaistos Disc', '101D0', '101FF'),
		'lycian' => array('Lycian', '10280', '1029F'),
		'carian' => array('Carian', '102A0', '102DF'),
		'old_italic' => array('Old Italic', '10300', '1032F'),
		'gothic' => array('Gothic', '10330', '1034F'),
		'ugaritic' => array('Ugaritic', '10380', '1039F'),
		'old_persian' => array('Old Persian', '103A0', '103DF'),
		'deseret' => array('Deseret', '10400', '1044F'),
		'shavian' => array('Shavian', '10450', '1047F'),
		'osmanya' => array('Osmanya', '10480', '104AF'),
		'cypriot_syllabary' => array('Cypriot Syllabary', '10800', '1083F'),
		'imperial_aramaic' => array('Imperial Aramaic', '10840', '1085F'),
		'phoenician' => array('Phoenician', '10900', '1091F'),
		'lydian' => array('Lydian', '10920', '1093F'),
		'meroitic_hieroglyphs' => array('Meroitic Hieroglyphs', '10980', '1099F'),
		'meroitic_cursive' => array('Meroitic Cursive', '109A0', '109FF'),
		'kharoshthi' => array('Kharoshthi', '10A00', '10A5F'),
		'old_south_arabian' => array('Old South Arabian', '10A60', '10A7F'),
		'avestan' => array('Avestan', '10B00', '10B3F'),
		'inscriptional_parthian' => array('Inscriptional Parthian', '10B40', '10B5F'),
		'inscriptional_pahlavi' => array('Inscriptional Pahlavi', '10B60', '10B7F'),
		'old_turkic' => array('Old Turkic', '10C00', '10C4F'),
		'rumi_numeral_symbols' => array('Rumi Numeral Symbols', '10E60', '10E7F'),
		'brahmi' => array('Brahmi', '11000', '1107F'),
		'kaithi' => array('Kaithi', '11080', '110CF'),
		'sora_sompeng' => array('Sora Sompeng', '110D0', '110FF'),
		'chakma' => array('Chakma', '11100', '1114F'),
		'sharada' => array('Sharada', '11180', '111DF'),
		'takri' => array('Takri', '11680', '116CF'),
		'cuneiform' => array('Cuneiform', '12000', '123FF'),
		'cuneiform_numbers_and_punctuation' => array('Cuneiform Numbers and Punctuation', '12400', '1247F'),
		'egyptian_hieroglyphs' => array('Egyptian Hieroglyphs', '13000', '1342F'),
		'bamum_supplement' => array('Bamum Supplement', '16800', '16A3F'),
		'miao' => array('Miao', '16F00', '16F9F'),
		'kana_supplement' => array('Kana Supplement', '1B000', '1B0FF'),
		'byzantine_musical_symbols' => array('Byzantine Musical Symbols', '1D000', '1D0FF'),
		'musical_symbols' => array('Musical Symbols', '1D100', '1D1FF'),
		'ancient_greek_musical_notation' => array('Ancient Greek Musical Notation', '1D200', '1D24F'),
		'tai_xuan_jing_symbols' => array('Tai Xuan Jing Symbols', '1D300', '1D35F'),
		'counting_rod_numerals' => array('Counting Rod Numerals', '1D360', '1D37F'),
		'mathematical_alphanumeric_symbols' => array('Mathematical Alphanumeric Symbols', '1D400', '1D7FF'),
		'arabic_mathematical_alphabetic_symbols' => array('Arabic Mathematical Alphabetic Symbols', '1EE00', '1EEFF'),
		'mahjong_tiles' => array('Mahjong Tiles', '1F000', '1F02F'),
		'domino_tiles' => array('Domino Tiles', '1F030', '1F09F'),
		'playing_cards' => array('Playing Cards', '1F0A0', '1F0FF'),
		'enclosed_alphanumeric_supplement' => array('Enclosed Alphanumeric Supplement', '1F100', '1F1FF'),
		'enclosed_ideographic_supplement' => array('Enclosed Ideographic Supplement', '1F200', '1F2FF'),
		'miscellaneous_symbols_and_pictographs' => array('Miscellaneous Symbols And Pictographs', '1F300', '1F5FF'),
		'emoticons' => array('Emoticons', '1F600', '1F64F'),
		'transport_and_map_symbols' => array('Transport And Map Symbols', '1F680', '1F6FF'),
		'alchemical_symbols' => array('Alchemical Symbols', '1F700', '1F77F'),
		'cjk_unified_ideographs_extension_b' => array('CJK Unified Ideographs Extension B', '20000', '2A6DF'),
		'cjk_unified_ideographs_extension_c' => array('CJK Unified Ideographs Extension C', '2A700', '2B73F'),
		'cjk_unified_ideographs_extension_d' => array('CJK Unified Ideographs Extension D', '2B740', '2B81F'),
		'cjk_compatibility_ideographs_supplement' => array('CJK Compatibility Ideographs Supplement', '2F800', '2FA1F'),
		'tags' => array('Tags', 'E0000', 'E007F'),
		'variation_selectors_supplement' => array('Variation Selectors Supplement', 'E0100', 'E01EF'),
		'supplementary_private_use_area_a' => array('Supplementary Private Use Area-A', 'F0000', 'FFFFF'),
		'supplementary_private_use_area_b' => array('Supplementary Private Use Area-B', '100000', '10FFFF')
		 */
	); 
	
	/**
	 * @var string		settings context
	 */
	private $context = self::CONTEXT_NONE;
	
	/**
	 * @var integer		availability of the character selector
	 */
	private $availability = self::INHERIT;
		
	/**
	 * @var array		list of added unicode block names
	 */
	private $added_blocks = array();
	
	/**
	 * @var array		list of custom selector items (splitted custom block)
	 */
	private $custom_items = array();

	
	/**
	 * Constructor
	 * @param string	context identifier
	 * @param bool		read the settings for the given context
	 */
	public function __construct($a_context = self::CONTEXT_NONE)
	{
		switch($a_context)
		{
			case self::CONTEXT_ADMIN:
			case self::CONTEXT_TEST:
			case self::CONTEXT_USER:
				$this->context = $a_context;
				break;
			default:
				$this->context = self::CONTEXT_NONE;
		}
	}
	
	/**
	 * Get the configuration that should be used for the current selector
	 * @param	object	(optional) current running test 
	 * @return	ilCharSelectorConfig
	 */
	static function _getCurrentConfig(ilObjTest $a_test_obj = null)
	{
		global $ilSetting, $ilUser;
		
		// check configuration from administration settings
		$admin_config = new self(self::CONTEXT_ADMIN, true);
		$admin_config->setAvailability($ilSetting->get('char_selector_availability'));
		$admin_config->setDefinition($ilSetting->get('char_selector_definition'));
		if ($admin_config->getAvailability() == self::INACTIVE)
		{
			// a globally inactive selector can't be overwritten by users or tests
			return $admin_config;
		}
		
		// a test configuration is relevant for test runs
		if (isset($a_test_obj))
		{
			$test_config = new self(self::CONTEXT_TEST, false);
			$test_config->setAvailability($a_test_obj->getCharSelectorAvailability());
			$test_config->setDefinition($a_test_obj->getCharSelectorDefinition());
			if ($test_config->getAvailability() != self::INHERIT)
			{
				// a specific test configuration has precedence over user configuration
				return $test_config;
			}
		}
		
		// check configuration from user settings
		$user_config = new self(self::CONTEXT_USER, true);
		$user_config->setAvailability($ilUser->getPref('char_selector_availability'));
		$user_config->setDefinition($ilUser->getPref('char_selector_definition'));
		if ($user_config->getAvailability() != self::INHERIT)
		{
			// take user specific config
			return $user_config;
		}
		else
		{
			// take admin config as default
			return $admin_config;
		}
	}

	
	/**
	 * get the context of the configuration
	 * (the context is set at initialisation and can't be changed)
	 * @return	string	context identifier
	 */
	public function getContext()
	{
		return $this->context;
	}
	

	/**
	 * set the availability of the selector
	 * @param int availability
	 */
	public function setAvailability($a_availability)
	{
		switch((int) $a_availability)
		{
			case self::INACTIVE:
			case self::INHERIT:
			case self::ENABLED:
			case self::DISABLED:
				$this->availability = (int) $a_availability;
				break;
			default:
				$this->availability = self::INHERIT;
		}
	}
	
	
	/**
	 * get the availability of the selector
	 * @return int availability
	 */
	public function getAvailability()
	{
		return $this->availability;
	}
	
	/**
	 * set the added unicode blocks
	 * @param array list of block names
	 */
	public function setAddedBlocks($a_blocks = array())
	{
		$this->added_blocks = array();
		foreach ($a_blocks as $block_name)
		{
			if ($block_name == "all" or
				in_array($block_name, array_keys(self::$unicode_blocks)))
			{
				array_push($this->added_blocks, $block_name);
			}
		}
	}

	/**
	 * set the added unicode blocks
	 * @return array list of block names
	 */
	public function getAddedBlocks()
	{
		return $this->added_blocks;
	}
	
	/**
	 * set the custom items
	 * @param array list of strings
	 * @see self::setDefinition() for item syntax
	 */
	public function setCustomItems($a_items = '')
	{
		$this->custom_items = explode(' ', $a_items);
	}
	
	/**
	 * set the custom items
	 * @return array list of strings
	 * @see self::setDefinition() for item syntax
	 */
	public function getCustomItems()
	{
		return implode(' ', $this->custom_items);
	}
	
	/**
	 * Set the definition of the available characters
	 * It combines user defined custom items with selected unicode blocks
	 * 
	 * Syntax for the custom block:
	 * a B C			Characters are separated by space, generating separate buttons
	 * BC				It is possible to put more characters on one button
	 * A-Z				Ranges of characters can be defined with a "-"
	 * U+00C0			Character can also be defined in unicode notation (case insensitive)
	 * U+00C0-U+00CF	Ranges are also possible in unicode notation
	 * 
	 * The selected unicode blocks are added with space separations
	 * [basic_latin1]	Add the basic latin1 block	
	 * 
	 * @var	string	definition string
	 */
	public function setDefinition($a_definition = '')
	{
		// first reset all previous settings
		$this->added_blocks = array();
		$this->custom_items = array();

        // set the default definition to all unicode blocks
		if (trim($a_definition) == '')
		{
            $a_definition = "[all]";
		}
		
		// analyze definition items
		$items = explode(' ', $a_definition);
		foreach ($items as $item)
		{
			if (strlen($block_name = $this->extractUnicodeBlock($item)))
			{
				array_push($this->added_blocks, $block_name);
			}
			else if ($item != '')
			{
				array_push($this->custom_items, trim($item));
			}
		}
	}
	
	/**
	 * Set the definition of the available characters
	 * @return string
	 * @see self::setDefinition()
	 */
	public function getDefinition()
	{
		$definition = implode(' ', $this->custom_items);
		foreach($this->added_blocks as $block_name)
		{
			$definition = $definition . ' [' . $block_name . ']';
		}
		return trim($definition);
	}

	
	/**
	 * get the options for a block selection
	 * @return array	options
	 */
	public function getBlockOptions()
	{
		global $lng;
		
		$options = array(
			'' => $lng->txt('please_select'),
			'all' => $lng->txt('char_selector_unicode_all')
		);
		foreach (array_keys(self::$unicode_blocks) as $block_name)
		{
			$options[$block_name] = $this->getBlockTitle($block_name);
		}
		return $options;
	}
	
	
	/**
	 * Get the title of a unicode block for display or selection
	 * A translation is used if it exists
	 * 
	 * @param	string	block name
	 * @return	string	title
	 */
	public function getBlockTitle($a_block_name)
	{
		global $lng;
		
		$langvar = 'char_selector_unicode_'. $a_block_name;
		if ($lng->txt($langvar) != '-'.$langvar.'-' )
		{
			return $lng->txt($langvar);
		}
		else
		{
			return self::$unicode_blocks[$a_block_name][0];
		}
	}
	
	/**
	 * Extract the unicode block name from a definition item
	 * 
	 * @param string	definition item
	 * @return string	unicode block name
	 */
	private function extractUnicodeBlock($a_item = '')
	{
		$a_item = trim($a_item);
		$matches = array();
		if (preg_match('/^\[(.+)\]$/', $a_item, $matches))
		{
			$block_name = $matches[1];
			if ($block_name == 'all' 
				or in_array($block_name, array_keys(self::$unicode_blocks)))
			{
				return $block_name;
			}
		}	
		return '';
	}
	
	
	/**
	 * Get the character pages
	 * 
	 * @return array	[["page1", "A", "BC", [123,456], ...], ["page2], "X", ...], ...]
	 */
	public function getCharPages()
	{
		global $lng;
		
		$pages = array();
		
		// add custom block
		//
		$page = array($lng->txt('char_selector_custom_items'));
		foreach ($this->custom_items as $item)
		{
			if (strpos($item, '-') > 0)
			{
				// handle range
				$subitems = explode('-', $item);
				$start = $this->getItemCodepoint($subitems[0]);
				$end = $this->getItemCodepoint($subitems[1]);
				array_push($page, array($start, $end));
			}
			else
			{
				// handle normal item
				array_push($page, $this->getItemParsed($item));
			}
		}
		if (count($page) > 1)
		{
			array_push($pages, $page);
		}
	
		// add unicode blocks
		//
		$blocks = in_array('all', $this->added_blocks) ?
			array_keys(self::$unicode_blocks) :
			$this->added_blocks;
		
		foreach ($blocks as $block_name)
		{
			$start = hexdec(self::$unicode_blocks[$block_name][1]);
			$end = hexdec(self::$unicode_blocks[$block_name][2]);
			$page = array($this->getBlockTitle($block_name), array($start, $end));
			array_push($pages, $page);
		}
		
		return $pages;
	}	

	
	/**
	 * get the unicode index of an item
	 * @param string $a_item
	 */
	private function getItemCodepoint($a_item)
	{
		if (preg_match('/^[uU]\+[0-9a-fA-F]+$/', $a_item))
		{
			return (int) hexdec(substr($a_item, 2));
		}
		else
		{
			//take the codepoint of the first character
			require_once "include/Unicode/UtfNormalUtil.php";
			return (int) utf8ToCodepoint($a_item);
		}
	}
	
	/**
	 * replace unicode notations with their utf8 chars in a string
	 * @param string $a_item
	 * @return string parsed string
	 */
	private function getItemParsed($a_item)
	{
		return preg_replace_callback(
			'/[uU]\+[0-9a-fA-F]+/',
			array($this,'getItemParsedCallback'),
			$a_item
		);
	}
	
	/**
	 * callback for replacement of unicode notations
	 * @param	array	preg matches
	 * @return	string	replacement string
	 */
	private function getItemParsedCallback($matches)
	{
		require_once "include/Unicode/UtfNormalUtil.php";
		return codepointToUtf8(hexdec(substr($matches[0], 2)));
	}
	
}
?>
