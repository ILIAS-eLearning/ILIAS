<?php
/* vim: set ts=4 sw=4: */
/**
 * Class for working with BibTex data
 *
 * A class which provides common methods to access and
 * create Strings in BibTex format
 *
 * PHP versions 4 and 5
 *
 * LICENSE: This source file is subject to version 3.0 of the PHP license
 * that is available through the world-wide-web at the following URI:
 * http://www.php.net/license/3_0.txt.  If you did not receive a copy of
 * the PHP License and are unable to obtain it through the web, please
 * send a note to license@php.net so we can mail you a copy immediately.
 *
 * @category   Structures
 * @package    Structures_BibTex
 * @author     Elmar Pitschke <elmar.pitschke@gmx.de>
 * @copyright  1997-2005 The PHP Group
 * @license    http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version    CVS: $Id: BibTex.php 304756 2010-10-25 10:19:43Z clockwerx $
 * @link       http://pear.php.net/package/Structures_BibTex
 */

require_once 'PEAR.php';
/**
 * Structures_BibTex
 *
 * A class which provides common methods to access and
 * create Strings in BibTex format.
 * Example 1: Parsing a BibTex File and returning the number of entries
 * <code>
 * $bibtex = new Structures_BibTex();
 * $ret    = $bibtex->loadFile('foo.bib');
 * if (PEAR::isError($ret)) {
 *   die($ret->getMessage());
 * }
 * $bibtex->parse();
 * print "There are ".$bibtex->amount()." entries";
 * </code>
 * Example 2: Parsing a BibTex File and getting all Titles
 * <code>
 * $bibtex = new Structures_BibTex();
 * $ret    = $bibtex->loadFile('bibtex.bib');
 * if (PEAR::isError($ret)) {
 *   die($ret->getMessage());
 * }
 * $bibtex->parse();
 * foreach ($bibtex->data as $entry) {
 *  print $entry['title']."<br />";
 * }
 * </code>
 * Example 3: Adding an entry and printing it in BibTex Format
 * <code>
 * $bibtex                         = new Structures_BibTex();
 * $addarray                       = array();
 * $addarray['entryType']          = 'Article';
 * $addarray['cite']               = 'art2';
 * $addarray['title']              = 'Titel2';
 * $addarray['author'][0]['first'] = 'John';
 * $addarray['author'][0]['last']  = 'Doe';
 * $addarray['author'][1]['first'] = 'Jane';
 * $addarray['author'][1]['last']  = 'Doe';
 * $bibtex->addEntry($addarray);
 * print nl2br($bibtex->bibTex());
 * </code>
 *
 * @category   Structures
 * @package    Structures_BibTex
 * @author     Elmar Pitschke <elmar.pitschke@gmx.de>
 * @copyright  1997-2005 The PHP Group
 * @license    http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version    Release: @package_version@
 * @link       http://pear.php.net/Structures/Structure_BibTex
 */
class Structures_BibTex {

	/**
	 * Array with the BibTex Data
	 *
	 * @access public
	 * @var array
	 */
	var $data;
	/**
	 * String with the BibTex content
	 *
	 * @access public
	 * @var string
	 */
	var $content;
	/**
	 * Array with possible Delimiters for the entries
	 *
	 * @access private
	 * @var array
	 */
	var $_delimiters;
	/**
	 * Array to store warnings
	 *
	 * @access public
	 * @var array
	 */
	var $warnings;
	/**
	 * Run-time configuration options
	 *
	 * @access private
	 * @var array
	 */
	var $_options;
	/**
	 * RTF Format String
	 *
	 * @access public
	 * @var string
	 */
	var $rtfstring;
	/**
	 * HTML Format String
	 *
	 * @access public
	 * @var string
	 */
	var $htmlstring;
	/**
	 * Array with the "allowed" entry types
	 *
	 * @access public
	 * @var array
	 */
	var $allowedEntryTypes;
	/**
	 * Author Format Strings
	 *
	 * @access public
	 * @var string
	 */
	var $authorstring;


	/**
	 * Constructor
	 *
	 * @access public
	 * @return void
	 */
	function Structures_BibTex($options = array()) {
		$this->_delimiters = array(
			'"' => '"',
			'{' => '}'
		);
		$this->data = array();
		$this->content = '';
		//$this->_stripDelimiter = $stripDel;
		//$this->_validate       = $val;
		$this->warnings = array();
		$this->_options = array(
			'stripDelimiter' => true,
			'validate' => true,
			'unwrap' => false,
			'wordWrapWidth' => false,
			'wordWrapBreak' => "\n",
			'wordWrapCut' => 0,
			'removeCurlyBraces' => false,
			'extractAuthors' => true,
		);
		foreach ($options as $option => $value) {
			$test = $this->setOption($option, $value);
			if (PEAR::isError($test)) {
				//Currently nothing is done here, but it could for example raise an warning
			}
		}
		$this->rtfstring = 'AUTHORS, "{\b TITLE}", {\i JOURNAL}, YEAR';
		$this->htmlstring = 'AUTHORS, "<strong>TITLE</strong>", <em>JOURNAL</em>, YEAR<br />';
		$this->allowedEntryTypes = array(
			'article',
			'book',
			'booklet',
			'confernce',
			'inbook',
			'incollection',
			'inproceedings',
			'manual',
			'mastersthesis',
			'misc',
			'phdthesis',
			'proceedings',
			'techreport',
			'unpublished'
		);
		$this->authorstring = 'VON LAST, JR, FIRST';
	}


	/**
	 * Sets run-time configuration options
	 *
	 * @access public
	 *
	 * @param string $option option name
	 * @param mixed  $value  value for the option
	 *
	 * @return mixed true on success PEAR_Error on failure
	 */
	function setOption($option, $value) {
		$ret = true;
		if (array_key_exists($option, $this->_options)) {
			$this->_options[$option] = $value;
		} else {
			$ret = PEAR::raiseError('Unknown option ' . $option);
		}

		return $ret;
	}


	/**
	 * Reads a give BibTex File
	 *
	 * @access public
	 *
	 * @param string $filename Name of the file
	 *
	 * @return mixed true on success PEAR_Error on failure
	 */
	function loadFile($filename) {
		if (file_exists($filename)) {
			if (($this->content = @file_get_contents($filename)) === false) {
				return PEAR::raiseError('Could not open file ' . $filename);
			} else {
				$this->_pos = 0;
				$this->_oldpos = 0;

				return true;
			}
		} else {
			return PEAR::raiseError('Could not find file ' . $filename);
		}
	}


	/**
	 * Parses what is stored in content and clears the content if the parsing is successfull.
	 *
	 * @access public
	 * @return boolean true on success and PEAR_Error if there was a problem
	 */
	function parse() {
		//The amount of opening braces is compared to the amount of closing braces
		//Braces inside comments are ignored
		$this->warnings = array();
		$this->data = array();
		$valid = true;
		$open = 0;
		$entry = false;
		$char = '';
		$lastchar = '';
		$buffer = '';
		for ($i = 0; $i < strlen($this->content); $i ++) {
			$char = substr($this->content, $i, 1);
			if ((0 != $open) && ('@' == $char)) {
				if (! $this->_checkAt($buffer)) {
					$this->_generateWarning('WARNING_MISSING_END_BRACE', '', $buffer);
					//To correct the data we need to insert a closing brace
					$char = '}';
					$i --;
				}
			}
			if ((0 == $open) && ('@' == $char)) { //The beginning of an entry
				$entry = true;
			} elseif ($entry && ('{' == $char)
				&& ('\\' != $lastchar)
			) { //Inside an entry and non quoted brace is opening
				$open ++;
			} elseif ($entry && ('}' == $char)
				&& ('\\' != $lastchar)
			) { //Inside an entry and non quoted brace is closing
				$open --;
				if ($open < 0) { //More are closed than opened
					$valid = false;
				}
				if (0 == $open) { //End of entry
					$entry = false;
					$entrydata = $this->_parseEntry($buffer);
					if (! $entrydata) {
						/**
						 * This is not yet used.
						 * We are here if the Entry is either not correct or not supported.
						 * But this should already generate a warning.
						 * Therefore it should not be necessary to do anything here
						 */
					} else {
						$this->data[] = $entrydata;
					}
					$buffer = '';
				}
			}
			if ($entry) { //Inside entry
				$buffer .= $char;
			}
			$lastchar = $char;
		}
		//If open is one it may be possible that the last ending brace is missing
		if (1 == $open) {
			$entrydata = $this->_parseEntry($buffer);
			if (! $entrydata) {
				$valid = false;
			} else {
				$this->data[] = $entrydata;
				$buffer = '';
				$open = 0;
			}
		}
		//At this point the open should be zero
		if (0 != $open) {
			$valid = false;
		}
		//Are there Multiple entries with the same cite?
		if ($this->_options['validate']) {
			$cites = array();
			foreach ($this->data as $entry) {
				$cites[] = $entry['cite'];
			}
			$unique = array_unique($cites);
			if (sizeof($cites) != sizeof($unique)) { //Some values have not been unique!
				$notuniques = array();
				for ($i = 0; $i < sizeof($cites); $i ++) {
					if ('' == $unique[$i]) {
						$notuniques[] = $cites[$i];
					}
				}
				$this->_generateWarning('WARNING_MULTIPLE_ENTRIES', implode(',', $notuniques));
			}
		}
		if ($valid) {
			$this->content = '';

			return true;
		} else {
			return PEAR::raiseError('Unbalanced parenthesis');
		}
	}


	/**
	 * Extracting the data of one content
	 *
	 * The parse function splits the content into its entries.
	 * Then every entry is parsed by this function.
	 * It parses the entry backwards.
	 * First the last '=' is searched and the value extracted from that.
	 * A copy is made of the entry if warnings should be generated. This takes quite
	 * some memory but it is needed to get good warnings. If nor warnings are generated
	 * then you don have to worry about memory.
	 * Then the last ',' is searched and the field extracted from that.
	 * Again the entry is shortened.
	 * Finally after all field=>value pairs the cite and type is extraced and the
	 * authors are splitted.
	 * If there is a problem false is returned.
	 *
	 * @access private
	 *
	 * @param string $entry The entry
	 *
	 * @return array The representation of the entry or false if there is a problem
	 */
	function _parseEntry($entry) {
		$entrycopy = '';
		if ($this->_options['validate']) {
			$entrycopy = $entry; //We need a copy for printing the warnings
		}
		$ret = array();
		if ('@string' == strtolower(substr($entry, 0, 7))) {
			//String are not yet supported!
			if ($this->_options['validate']) {
				$this->_generateWarning('STRING_ENTRY_NOT_YET_SUPPORTED', '', $entry . '}');
			}
		} elseif ('@preamble' == strtolower(substr($entry, 0, 9))) {
			//Preamble not yet supported!
			if ($this->_options['validate']) {
				$this->_generateWarning('PREAMBLE_ENTRY_NOT_YET_SUPPORTED', '', $entry . '}');
			}
		} else {
			//Parsing all fields
			while (strrpos($entry, '=') !== false) {
				$position = strrpos($entry, '=');
				//Checking that the equal sign is not quoted or is not inside a equation (For example in an abstract)
				$proceed = true;
				if (substr($entry, $position - 1, 1) == '\\') {
					$proceed = false;
				}
				if ($proceed) {
					$proceed = $this->_checkEqualSign($entry, $position);
				}
				while (! $proceed) {
					$substring = substr($entry, 0, $position);
					$position = strrpos($substring, '=');
					$proceed = true;
					if (substr($entry, $position - 1, 1) == '\\') {
						$proceed = false;
					}
					if ($proceed) {
						$proceed = $this->_checkEqualSign($entry, $position);
					}
				}
				$value = trim(substr($entry, $position + 1));
				$entry = substr($entry, 0, $position);
				if (',' == substr($value, strlen($value) - 1, 1)) {
					$value = substr($value, 0, - 1);
				}
				if ($this->_options['validate']) {
					$this->_validateValue($value, $entrycopy);
				}
				if ($this->_options['stripDelimiter']) {
					$value = $this->_stripDelimiter($value);
				}
				if ($this->_options['unwrap']) {
					$value = $this->_unwrap($value);
				}
				if ($this->_options['removeCurlyBraces']) {
					$value = $this->_removeCurlyBraces($value);
				}
				$position = strrpos($entry, ',');
				$field = strtolower(trim(substr($entry, $position + 1)));
				$ret[$field] = $value;
				$entry = substr($entry, 0, $position);
			}
			//Parsing cite and entry type
			$arr = explode('{', $entry);
			$ret['cite'] = trim($arr[1]);
			$ret['entryType'] = strtolower(trim($arr[0]));
			if ('@' == $ret['entryType']{0}) {
				$ret['entryType'] = substr($ret['entryType'], 1);
			}
			if ($this->_options['validate']) {
				if (! $this->_checkAllowedEntryType($ret['entryType'])) {
					$this->_generateWarning('WARNING_NOT_ALLOWED_ENTRY_TYPE', $ret['entryType'], $entry . '}');
				}
			}
			//Handling the authors
			if (in_array('author', array_keys($ret)) && $this->_options['extractAuthors']) {
				$ret['author'] = $this->_extractAuthors($ret['author']);
			}
		}

		return $ret;
	}


	/**
	 * Checking whether the position of the '=' is correct
	 *
	 * Sometimes there is a problem if a '=' is used inside an entry (for example abstract).
	 * This method checks if the '=' is outside braces then the '=' is correct and true is returned.
	 * If the '=' is inside braces it contains to a equation and therefore false is returned.
	 *
	 * @access private
	 *
	 * @param string $entry The text of the whole remaining entry
	 * @param        int    the current used place of the '='
	 *
	 * @return bool true if the '=' is correct, false if it contains to an equation
	 */
	function _checkEqualSign($entry, $position) {
		$ret = true;
		//This is getting tricky
		//We check the string backwards until the position and count the closing an opening braces
		//If we reach the position the amount of opening and closing braces should be equal
		$length = strlen($entry);
		$open = 0;
		for ($i = $length - 1; $i >= $position; $i --) {
			$precedingchar = substr($entry, $i - 1, 1);
			$char = substr($entry, $i, 1);
			if (('{' == $char) && ('\\' != $precedingchar)) {
				$open ++;
			}
			if (('}' == $char) && ('\\' != $precedingchar)) {
				$open --;
			}
		}
		if (0 != $open) {
			$ret = false;
		}
		//There is still the posibility that the entry is delimited by double quotes.
		//Then it is possible that the braces are equal even if the '=' is in an equation.
		if ($ret) {
			$entrycopy = trim($entry);
			$lastchar = $entrycopy{strlen($entrycopy) - 1};
			if (',' == $lastchar) {
				$lastchar = $entrycopy{strlen($entrycopy) - 2};
			}
			if ('"' == $lastchar) {
				//The return value is set to false
				//If we find the closing " before the '=' it is set to true again.
				//Remember we begin to search the entry backwards so the " has to show up twice - ending and beginning delimiter
				$ret = false;
				$found = 0;
				for ($i = $length; $i >= $position; $i --) {
					$precedingchar = substr($entry, $i - 1, 1);
					$char = substr($entry, $i, 1);
					if (('"' == $char) && ('\\' != $precedingchar)) {
						$found ++;
					}
					if (2 == $found) {
						$ret = true;
						break;
					}
				}
			}
		}

		return $ret;
	}


	/**
	 * Checking if the entry type is allowed
	 *
	 * @access private
	 *
	 * @param string $entry The entry to check
	 *
	 * @return bool true if allowed, false otherwise
	 */
	function _checkAllowedEntryType($entry) {
		return in_array($entry, $this->allowedEntryTypes);
	}


	/**
	 * Checking whether an at is outside an entry
	 *
	 * Sometimes an entry misses an entry brace. Then the at of the next entry seems to be
	 * inside an entry. This is checked here. When it is most likely that the at is an opening
	 * at of the next entry this method returns true.
	 *
	 * @access private
	 *
	 * @param string $entry The text of the entry until the at
	 *
	 * @return bool true if the at is correct, false if the at is likely to begin the next entry.
	 */
	function _checkAt($entry) {
		$ret = false;
		$opening = array_keys($this->_delimiters);
		$closing = array_values($this->_delimiters);
		//Getting the value (at is only allowd in values)
		if (strrpos($entry, '=') !== false) {
			$position = strrpos($entry, '=');
			$proceed = true;
			if (substr($entry, $position - 1, 1) == '\\') {
				$proceed = false;
			}
			while (! $proceed) {
				$substring = substr($entry, 0, $position);
				$position = strrpos($substring, '=');
				$proceed = true;
				if (substr($entry, $position - 1, 1) == '\\') {
					$proceed = false;
				}
			}
			$value = trim(substr($entry, $position + 1));
			$open = 0;
			$char = '';
			$lastchar = '';
			for ($i = 0; $i < strlen($value); $i ++) {
				$char = substr($this->content, $i, 1);
				if (in_array($char, $opening) && ('\\' != $lastchar)) {
					$open ++;
				} elseif (in_array($char, $closing) && ('\\' != $lastchar)) {
					$open --;
				}
				$lastchar = $char;
			}
			//if open is grater zero were are inside an entry
			if ($open > 0) {
				$ret = true;
			}
		}

		return $ret;
	}


	/**
	 * Stripping Delimiter
	 *
	 * @access private
	 *
	 * @param string $entry The entry where the Delimiter should be stripped from
	 *
	 * @return string Stripped entry
	 */
	function _stripDelimiter($entry) {
		$beginningdels = array_keys($this->_delimiters);
		$length = strlen($entry);
		$firstchar = substr($entry, 0, 1);
		$lastchar = substr($entry, - 1, 1);
		while (in_array($firstchar, $beginningdels)) { //The first character is an opening delimiter
			if ($lastchar == $this->_delimiters[$firstchar]) { //Matches to closing Delimiter
				$entry = substr($entry, 1, - 1);
			} else {
				break;
			}
			$firstchar = substr($entry, 0, 1);
			$lastchar = substr($entry, - 1, 1);
		}

		return $entry;
	}


	/**
	 * Unwrapping entry
	 *
	 * @access private
	 *
	 * @param string $entry The entry to unwrap
	 *
	 * @return string unwrapped entry
	 */
	function _unwrap($entry) {
		$entry = preg_replace('/\s+/', ' ', $entry);

		return trim($entry);
	}


	/**
	 * Wordwrap an entry
	 *
	 * @access private
	 *
	 * @param string $entry The entry to wrap
	 *
	 * @return string wrapped entry
	 */
	function _wordwrap($entry) {
		if (('' != $entry) && (is_string($entry))) {
			$entry = wordwrap($entry, $this->_options['wordWrapWidth'], $this->_options['wordWrapBreak'], $this->_options['wordWrapCut']);
		}

		return $entry;
	}


	/**
	 * Extracting the authors
	 *
	 * @access private
	 *
	 * @param string $entry The entry with the authors
	 *
	 * @return array the extracted authors
	 */
	function _extractAuthors($entry) {
		$entry = $this->_unwrap($entry);
		$authorarray = array();
		$authorarray = explode(' and ', $entry);
		for ($i = 0; $i < sizeof($authorarray); $i ++) {
			$author = trim($authorarray[$i]);
			/*The first version of how an author could be written (First von Last)
			 has no commas in it*/
			$first = '';
			$von = '';
			$last = '';
			$jr = '';
			if (strpos($author, ',') === false) {
				$tmparray = array();
				//$tmparray = explode(' ', $author);
				$tmparray = explode(' |~', $author);
				$size = sizeof($tmparray);
				if (1 == $size) { //There is only a last
					$last = $tmparray[0];
				} elseif (2 == $size) { //There is a first and a last
					$first = $tmparray[0];
					$last = $tmparray[1];
				} else {
					$invon = false;
					$inlast = false;
					for ($j = 0; $j < ($size - 1); $j ++) {
						if ($inlast) {
							$last .= ' ' . $tmparray[$j];
						} elseif ($invon) {
							$case = $this->_determineCase($tmparray[$j]);
							if (PEAR::isError($case)) {
								// IGNORE?
							} elseif ((0 == $case) || (- 1 == $case)) { //Change from von to last
								//You only change when there is no more lower case there
								$islast = true;
								for ($k = ($j + 1); $k < ($size - 1); $k ++) {
									$futurecase = $this->_determineCase($tmparray[$k]);
									if (PEAR::isError($case)) {
										// IGNORE?
									} elseif (0 == $futurecase) {
										$islast = false;
									}
								}
								if ($islast) {
									$inlast = true;
									if (- 1 == $case) { //Caseless belongs to the last
										$last .= ' ' . $tmparray[$j];
									} else {
										$von .= ' ' . $tmparray[$j];
									}
								} else {
									$von .= ' ' . $tmparray[$j];
								}
							} else {
								$von .= ' ' . $tmparray[$j];
							}
						} else {
							$case = $this->_determineCase($tmparray[$j]);
							if (PEAR::isError($case)) {
								// IGNORE?
							} elseif (0 == $case) { //Change from first to von
								$invon = true;
								$von .= ' ' . $tmparray[$j];
							} else {
								$first .= ' ' . $tmparray[$j];
							}
						}
					}
					//The last entry is always the last!
					$last .= ' ' . $tmparray[$size - 1];
				}
			} else { //Version 2 and 3
				$tmparray = array();
				$tmparray = explode(',', $author);
				//The first entry must contain von and last
				$vonlastarray = array();
				$vonlastarray = explode(' ', $tmparray[0]);
				$size = sizeof($vonlastarray);
				if (1 == $size) { //Only one entry->got to be the last
					$last = $vonlastarray[0];
				} else {
					$inlast = false;
					for ($j = 0; $j < ($size - 1); $j ++) {
						if ($inlast) {
							$last .= ' ' . $vonlastarray[$j];
						} else {
							if (0 != ($this->_determineCase($vonlastarray[$j]))) { //Change from von to last
								$islast = true;
								for ($k = ($j + 1); $k < ($size - 1); $k ++) {
									$this->_determineCase($vonlastarray[$k]);
									$case = $this->_determineCase($vonlastarray[$k]);
									if (PEAR::isError($case)) {
										// IGNORE?
									} elseif (0 == $case) {
										$islast = false;
									}
								}
								if ($islast) {
									$inlast = true;
									$last .= ' ' . $vonlastarray[$j];
								} else {
									$von .= ' ' . $vonlastarray[$j];
								}
							} else {
								$von .= ' ' . $vonlastarray[$j];
							}
						}
					}
					$last .= ' ' . $vonlastarray[$size - 1];
				}
				//Now we check if it is version three (three entries in the array (two commas)
				if (3 == sizeof($tmparray)) {
					$jr = $tmparray[1];
				}
				//Everything in the last entry is first
				$first = $tmparray[sizeof($tmparray) - 1];
			}
			$authorarray[$i] = array(
				'first' => trim($first),
				'von' => trim($von),
				'last' => trim($last),
				'jr' => trim($jr)
			);
		}

		return $authorarray;
	}


	/**
	 * Case Determination according to the needs of BibTex
	 *
	 * To parse the Author(s) correctly a determination is needed
	 * to get the Case of a word. There are three possible values:
	 * - Upper Case (return value 1)
	 * - Lower Case (return value 0)
	 * - Caseless   (return value -1)
	 *
	 * @access private
	 *
	 * @param string $word
	 *
	 * @return int The Case or PEAR_Error if there was a problem
	 */
	function _determineCase($word) {
		$ret = - 1;
		$trimmedword = trim($word);
		/*We need this variable. Without the next of would not work
		 (trim changes the variable automatically to a string!)*/
		if (is_string($word) && (strlen($trimmedword) > 0)) {
			$i = 0;
			$found = false;
			$openbrace = 0;
			while (! $found && ($i <= strlen($word))) {
				$letter = substr($trimmedword, $i, 1);
				$ord = ord($letter);
				if ($ord == 123) { //Open brace
					$openbrace ++;
				}
				if ($ord == 125) { //Closing brace
					$openbrace --;
				}
				if (($ord >= 65) && ($ord <= 90) && (0 == $openbrace)) { //The first character is uppercase
					$ret = 1;
					$found = true;
				} elseif (($ord >= 97) && ($ord <= 122) && (0 == $openbrace)) { //The first character is lowercase
					$ret = 0;
					$found = true;
				} else { //Not yet found
					$i ++;
				}
			}
		} else {
			$ret = PEAR::raiseError('Could not determine case on word: ' . (string)$word);
		}

		return $ret;
	}


	/**
	 * Validation of a value
	 *
	 * There may be several problems with the value of a field.
	 * These problems exist but do not break the parsing.
	 * If a problem is detected a warning is appended to the array warnings.
	 *
	 * @access private
	 *
	 * @param string $entry      The entry aka one line which which should be validated
	 * @param string $wholeentry The whole BibTex Entry which the one line is part of
	 *
	 * @return void
	 */
	function _validateValue($entry, $wholeentry) {
		//There is no @ allowed if the entry is enclosed by braces
		if (preg_match('/^{.*@.*}$/', $entry)) {
			$this->_generateWarning('WARNING_AT_IN_BRACES', $entry, $wholeentry);
		}
		//No escaped " allowed if the entry is enclosed by double quotes
		if (preg_match('/^\".*\\".*\"$/', $entry)) {
			$this->_generateWarning('WARNING_ESCAPED_DOUBLE_QUOTE_INSIDE_DOUBLE_QUOTES', $entry, $wholeentry);
		}
		//Amount of Braces is not correct
		$open = 0;
		$lastchar = '';
		$char = '';
		for ($i = 0; $i < strlen($entry); $i ++) {
			$char = substr($entry, $i, 1);
			if (('{' == $char) && ('\\' != $lastchar)) {
				$open ++;
			}
			if (('}' == $char) && ('\\' != $lastchar)) {
				$open --;
			}
			$lastchar = $char;
		}
		if (0 != $open) {
			$this->_generateWarning('WARNING_UNBALANCED_AMOUNT_OF_BRACES', $entry, $wholeentry);
		}
	}


	/**
	 * Remove curly braces from entry
	 *
	 * @access private
	 *
	 * @param string $value The value in which curly braces to be removed
	 * @param        string Value with removed curly braces
	 */
	function _removeCurlyBraces($value) {
		//First we save the delimiters
		$beginningdels = array_keys($this->_delimiters);
		$firstchar = substr($entry, 0, 1);
		$lastchar = substr($entry, - 1, 1);
		$begin = '';
		$end = '';
		while (in_array($firstchar, $beginningdels)) { //The first character is an opening delimiter
			if ($lastchar == $this->_delimiters[$firstchar]) { //Matches to closing Delimiter
				$begin .= $firstchar;
				$end .= $lastchar;
				$value = substr($value, 1, - 1);
			} else {
				break;
			}
			$firstchar = substr($value, 0, 1);
			$lastchar = substr($value, - 1, 1);
		}
		//Now we get rid of the curly braces
		$pattern = '/([^\\\\])\{(.*?[^\\\\])\}/';
		$replacement = '$1$2';
		$value = preg_replace($pattern, $replacement, $value);
		//Reattach delimiters
		$value = $begin . $value . $end;

		return $value;
	}


	/**
	 * Generates a warning
	 *
	 * @access private
	 *
	 * @param string $type       The type of the warning
	 * @param string $entry      The line of the entry where the warning occurred
	 * @param string $wholeentry OPTIONAL The whole entry where the warning occurred
	 */
	function _generateWarning($type, $entry, $wholeentry = '') {
		$warning['warning'] = $type;
		$warning['entry'] = $entry;
		$warning['wholeentry'] = $wholeentry;
		$this->warnings[] = $warning;
	}


	/**
	 * Cleares all warnings
	 *
	 * @access public
	 */
	function clearWarnings() {
		$this->warnings = array();
	}


	/**
	 * Is there a warning?
	 *
	 * @access public
	 * @return true if there is, false otherwise
	 */
	function hasWarning() {
		if (sizeof($this->warnings) > 0) {
			return true;
		} else {
			return false;
		}
	}


	/**
	 * Returns the amount of available BibTex entries
	 *
	 * @access public
	 * @return int The amount of available BibTex entries
	 */
	function amount() {
		return sizeof($this->data);
	}


	/**
	 * Returns the author formatted
	 *
	 * The Author is formatted as setted in the authorstring
	 *
	 * @access private
	 *
	 * @param array $array Author array
	 *
	 * @return string the formatted author string
	 */
	function _formatAuthor($array) {
		if (! array_key_exists('von', $array)) {
			$array['von'] = '';
		} else {
			$array['von'] = trim($array['von']);
		}
		if (! array_key_exists('last', $array)) {
			$array['last'] = '';
		} else {
			$array['last'] = trim($array['last']);
		}
		if (! array_key_exists('jr', $array)) {
			$array['jr'] = '';
		} else {
			$array['jr'] = trim($array['jr']);
		}
		if (! array_key_exists('first', $array)) {
			$array['first'] = '';
		} else {
			$array['first'] = trim($array['first']);
		}
		$ret = $this->authorstring;
		$ret = str_replace("VON", $array['von'], $ret);
		$ret = str_replace("LAST", $array['last'], $ret);
		$ret = str_replace("JR", $array['jr'], $ret);
		$ret = str_replace("FIRST", $array['first'], $ret);

		return trim($ret);
	}


	/**
	 * Converts the stored BibTex entries to a BibTex String
	 *
	 * In the field list, the author is the last field.
	 *
	 * @access public
	 * @return string The BibTex string
	 */
	function bibTex() {
		$bibtex = '';
		foreach ($this->data as $entry) {
			//Intro
			$bibtex .= '@' . strtolower($entry['entryType']) . ' { ' . $entry['cite'] . ",\n";
			//Other fields except author
			foreach ($entry as $key => $val) {
				if ($this->_options['wordWrapWidth'] > 0) {
					$val = $this->_wordWrap($val);
				}
				if (! in_array($key, array( 'cite', 'entryType', 'author' ))) {
					$bibtex .= "\t" . $key . ' = {' . $val . "},\n";
				}
			}
			//Author
			if (array_key_exists('author', $entry)) {
				if ($this->_options['extractAuthors']) {
					$tmparray = array(); //In this array the authors are saved and the joind with an and
					foreach ($entry['author'] as $authorentry) {
						$tmparray[] = $this->_formatAuthor($authorentry);
					}
					$author = join(' and ', $tmparray);
				} else {
					$author = $entry['author'];
				}
			} else {
				$author = '';
			}
			$bibtex .= "\tauthor = {" . $author . "}\n";
			$bibtex .= "}\n\n";
		}

		return $bibtex;
	}


	/**
	 * Adds a new BibTex entry to the data
	 *
	 * @access public
	 *
	 * @param array $newentry The new data to add
	 *
	 * @return void
	 */
	function addEntry($newentry) {
		$this->data[] = $newentry;
	}


	/**
	 * Returns statistic
	 *
	 * This functions returns a hash table. The keys are the different
	 * entry types and the values are the amount of these entries.
	 *
	 * @access public
	 * @return array Hash Table with the data
	 */
	function getStatistic() {
		$ret = array();
		foreach ($this->data as $entry) {
			if (array_key_exists($entry['entryType'], $ret)) {
				$ret[$entry['entryType']] ++;
			} else {
				$ret[$entry['entryType']] = 1;
			}
		}

		return $ret;
	}


	/**
	 * Returns the stored data in RTF format
	 *
	 * This method simply returns a RTF formatted string. This is done very
	 * simple and is not intended for heavy using and fine formatting. This
	 * should be done by BibTex! It is intended to give some kind of quick
	 * preview or to send someone a reference list as word/rtf format (even
	 * some people in the scientific field still use word). If you want to
	 * change the default format you have to override the class variable
	 * "rtfstring". This variable is used and the placeholders simply replaced.
	 * Lines with no data cause an warning!
	 *
	 * @return string the RTF Strings
	 */
	function rtf() {
		$ret = "{\\rtf\n";
		foreach ($this->data as $entry) {
			$line = $this->rtfstring;
			$title = '';
			$journal = '';
			$year = '';
			$authors = '';
			if (array_key_exists('title', $entry)) {
				$title = $this->_unwrap($entry['title']);
			}
			if (array_key_exists('journal', $entry)) {
				$journal = $this->_unwrap($entry['journal']);
			}
			if (array_key_exists('year', $entry)) {
				$year = $this->_unwrap($entry['year']);
			}
			if (array_key_exists('author', $entry)) {
				if ($this->_options['extractAuthors']) {
					$tmparray = array(); //In this array the authors are saved and the joind with an and
					foreach ($entry['author'] as $authorentry) {
						$tmparray[] = $this->_formatAuthor($authorentry);
					}
					$authors = join(', ', $tmparray);
				} else {
					$authors = $entry['author'];
				}
			}
			if (('' != $title) || ('' != $journal) || ('' != $year) || ('' != $authors)) {
				$line = str_replace("TITLE", $title, $line);
				$line = str_replace("JOURNAL", $journal, $line);
				$line = str_replace("YEAR", $year, $line);
				$line = str_replace("AUTHORS", $authors, $line);
				$line .= "\n\\par\n";
				$ret .= $line;
			} else {
				$this->_generateWarning('WARNING_LINE_WAS_NOT_CONVERTED', '', print_r($entry, 1));
			}
		}
		$ret .= '}';

		return $ret;
	}


	/**
	 * Returns the stored data in HTML format
	 *
	 * This method simply returns a HTML formatted string. This is done very
	 * simple and is not intended for heavy using and fine formatting. This
	 * should be done by BibTex! It is intended to give some kind of quick
	 * preview. If you want to change the default format you have to override
	 * the class variable "htmlstring". This variable is used and the placeholders
	 * simply replaced.
	 * Lines with no data cause an warning!
	 *
	 * @return string the HTML Strings
	 */
	function html() {
		$ret = "<p>\n";
		foreach ($this->data as $entry) {
			$line = $this->htmlstring;
			$title = '';
			$journal = '';
			$year = '';
			$authors = '';
			if (array_key_exists('title', $entry)) {
				$title = $this->_unwrap($entry['title']);
			}
			if (array_key_exists('journal', $entry)) {
				$journal = $this->_unwrap($entry['journal']);
			}
			if (array_key_exists('year', $entry)) {
				$year = $this->_unwrap($entry['year']);
			}
			if (array_key_exists('author', $entry)) {
				if ($this->_options['extractAuthors']) {
					$tmparray = array(); //In this array the authors are saved and the joind with an and
					foreach ($entry['author'] as $authorentry) {
						$tmparray[] = $this->_formatAuthor($authorentry);
					}
					$authors = join(', ', $tmparray);
				} else {
					$authors = $entry['author'];
				}
			}
			if (('' != $title) || ('' != $journal) || ('' != $year) || ('' != $authors)) {
				$line = str_replace("TITLE", $title, $line);
				$line = str_replace("JOURNAL", $journal, $line);
				$line = str_replace("YEAR", $year, $line);
				$line = str_replace("AUTHORS", $authors, $line);
				$line .= "\n";
				$ret .= $line;
			} else {
				$this->_generateWarning('WARNING_LINE_WAS_NOT_CONVERTED', '', print_r($entry, 1));
			}
		}
		$ret .= "</p>\n";

		return $ret;
	}
}

?>
