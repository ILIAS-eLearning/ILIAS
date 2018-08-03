<?php

require_once './Services/Html/interfaces/interface.ilHtmlPurifierInterface.php';

/**
 * 
 * Class ilForumBbCodePurifier
 * @author Nadia Matuschek <nmatuschek@databay.de>
 */
class ilForumBbCodePurifier implements ilHtmlPurifierInterface
{
	/**
	 * @var string
	 */
	protected $bb_tag          = 'quote';

	/**
	 * @var string
	 */
	protected $html_equivalent = 'blockquote';
	
	protected $begin_tag_stack = array();
	protected $purified_tag_stack = array();
	protected $purified_end_tags = array();
	

	/**
	 * @param string $bb_tag
	 * @param string $html_equivalent
	 */
	public function __construct($bb_tag = 'quote', $html_equivalent = 'blockquote')
	{
		$this->bb_tag          = $bb_tag;
		$this->html_equivalent = $html_equivalent;
	}

	
	
	/**
	 * @param int $first_number
	 * @param int $second_number
	 * @return int
	 */
	protected function getDifferenceBetweenTags($first_number, $second_number)
	{
		return $first_number - $second_number;
	}

	/**
	 * @param int $number_of_opening_tags
	 * @param int $number_of_closing_tags
	 * @return bool
	 */
	protected function areOpeningTagsMissing($number_of_opening_tags, $number_of_closing_tags)
	{
		return $number_of_opening_tags < $number_of_closing_tags;
	}

	/**
	 * @param int $number_of_opening_tags
	 * @param int $number_of_closing_tags
	 * @return bool
	 */
	protected function areClosingTagsMissing($number_of_opening_tags, $number_of_closing_tags)
	{
		return $number_of_opening_tags > $number_of_closing_tags;
	}

	/**
	 * @param $a_html
	 * @return bool
	 */
	protected function textContainsHtmlEquivalentTags($a_html)
	{
		return strpos($a_html, $this->html_equivalent) != false;
	}

	/**
	 * @param string $a_html
	 * @return mixed|string
	 */
	public function purify($a_html)
	{
		if($this->textContainsHtmlEquivalentTags($a_html))
		{
			$a_html = $this->purifyHtmlEquivalentTags($a_html);
		}	
		
		if($this->textContainsBbCode($a_html))
		{
			return $this->purifyBbCode($a_html);
		}
		
		return $a_html;
	}
	
	/**
	 * @param string    $a_html
	 * @param int       $offset
	 * @return string
	 */
	private function purifyHtmlEquivalentTags($a_html, $offset = 0)
	{
		if(!$this->textContainsHtmlEquivalentTags($a_html))
		{
			return $a_html;
		}

		$number_of_opening_bq_tags = preg_match_all('/<'.$this->html_equivalent.'/', $a_html, $opening_bq_tag_string);
		$number_of_closing_bq_tags = preg_match_all('/<\/'.$this->html_equivalent.'/', $a_html, $closing_bq_tag_string);
		
		for($i = 0; $i < ($number_of_opening_bq_tags + $number_of_closing_bq_tags-1); $i++)
		{
			$range_begin  = strpos($a_html, $this->html_equivalent, $offset);

			$next_begin  = strpos($a_html, '<'.$this->html_equivalent, $range_begin+strlen($this->html_equivalent));
			$next_end 	=  strpos($a_html, '</'.$this->html_equivalent, $range_begin+strlen($this->html_equivalent));

			$range_end = $next_end;
			if($next_begin !== false && $next_begin < $next_end)
			{
				$range_end = $next_begin;
			}

			if($range_end > $range_begin)
			{
				$a_html = $this->purifyRange($a_html, $range_begin, $range_end);
			}	
			$offset =  strpos($a_html, $this->html_equivalent, $range_end+1);
		}
		return $a_html;
	}
	
	/**
	 * @param $a_html
	 * @param $range_begin
	 * @param $range_end
	 * @return string
	 */
	private function purifyRange($a_html, $range_begin, $range_end)
	{
		$substring  = substr($a_html, $range_begin, $range_end - $range_begin);
		$match      = preg_match('/blockquote(.*)>/', $substring, $len);
		$tag_length = strlen($len[0]);

		$substring_start = substr($a_html, 0, $range_begin + $tag_length);
		$substring       = substr($a_html, strlen($substring_start), $range_end - strlen($substring_start));
		$substring_end   = substr($a_html, $range_end);

		$a_html = $substring_start . $this->purifyBbCode($substring) . $substring_end;
		return $a_html;
	}
	
	/**
	 * @param string $a_html
	 * @return bool
	 */
	protected function textContainsBbCode($a_html)
	{
		return strpos($a_html, $this->bb_tag) !== false;
	}
	
	/**
	 * @param $a_html
	 * @return string
	 */
	private function purifyBbCode($a_html)
	{
		if(!$this->textContainsBbCode($a_html))
		{
			return $a_html;
		}

		// check for quotation
		$number_of_opening_tags = substr_count($a_html, '[' . $this->bb_tag); // also count [quote="..."]
		$number_of_closing_tags = substr_count($a_html, '[/' . $this->bb_tag . ']');

		if($this->areClosingTagsMissing($number_of_opening_tags, $number_of_closing_tags))
		{
			$diff = $this->getDifferenceBetweenTags($number_of_opening_tags, $number_of_closing_tags);
			for($i = 0; $i < $diff; $i++)
			{
				$a_html .= '[/' . $this->bb_tag . ']';
			}
		}
		else if($this->areOpeningTagsMissing($number_of_opening_tags, $number_of_closing_tags))
		{
			$diff = $this->getDifferenceBetweenTags($number_of_closing_tags, $number_of_opening_tags);
			for($i = 0; $i < $diff; $i++)
			{
				$a_html = '[' . $this->bb_tag . ']' . $a_html;
			}
		}

		return $a_html;
	}

	/**
	 * @param array $a_array_of_html
	 * @return array
	 */
	public function purifyArray(Array $a_array_of_html)
	{
		foreach($a_array_of_html as $key => &$val)
		{
			$val = $this->purifyBbCode($val);
		}
		return $a_array_of_html;
	}
	
	
	
	/**
	 * @param     $a_html
	 * @param int $offset
	 * @return mixed
	 */
	private function purifyHtmlEquivalentTags_stack($a_html, $offset = 0)
	{
		if(!$this->textContainsHtmlEquivalentTags($a_html))
		{
			return $a_html;
		}
		
		// @todo: Replace hard coded "blockquote" with $this->html_equivalent
		
		$number_of_opening_bq_tags = preg_match_all('/<blockquote/', $a_html, $opening_bq_tag_string);
		$number_of_closing_bq_tags = preg_match_all('/<\/blockquote/', $a_html, $closing_bq_tag_string);
		
		for($i = 0; $i < $number_of_opening_bq_tags; $i++)
		{
			$found_begin = strpos($a_html, $opening_bq_tag_string[0][$i], $offset);
			$found_end   = strpos($a_html, $closing_bq_tag_string[0][$i], $found_begin);

			if($offset > 0)
			{
				$found_begin  = strpos($a_html, $opening_bq_tag_string[0][$i], $offset);
				$found_end   = strpos($a_html, $closing_bq_tag_string[0][$i], $found_begin);
				if(isset($this->purified_tag_stack[$offset]))
				{
					//				$cnt_pts = count($this->purified_tag_stack)-1;
					//					$cnt_cbs      = count($this->begin_tag_stack) - 1;
					$purify_range = $this->purified_tag_stack[$offset];
					$found_begin  = strpos($a_html, $opening_bq_tag_string[0][$i], $purify_range['begin']);
					$found_end    = strpos($a_html, $closing_bq_tag_string[0][$i], $purify_range['end']);
					//				array_pop($this->begin_tag_stack);
				}
			}

			if($number_of_opening_bq_tags > 1)
			{
				$found_next_begin = strpos($a_html, $opening_bq_tag_string[0][$i], $found_begin + 1);
				
				if($found_next_begin < $found_end
					&&  !in_array($found_next_begin, array_keys($this->purified_tag_stack)))
				{
					$this->begin_tag_stack[] = $found_begin;
					// merke found begin -> suche nächstes begin und speichere als to_purify_range
					
					$this->purified_tag_stack[$found_next_begin] = array('begin' => $found_next_begin, 'end' => $found_end);
					
					$this->purifyHtmlEquivalentTags($a_html, $found_next_begin);
				}
				else if($found_next_begin < $found_end
					&& in_array($found_end, $this->purified_end_tags)
					&& count($purify_range) == 0)
				{
					//					$found_begin = $purify_range['begin'];
					//					$found_end = $purify_range['end'];
					$found_next_end = 0;
					while(in_array($found_end, $this->purified_end_tags) && $found_end <> $found_next_end)
					{
						$found_next_end = strpos($a_html, $closing_bq_tag_string[0][$i], $found_end + 1);
						if($found_next_end > $found_end)
						{
							$found_end = $found_next_end;
						}
					}
				}
			}
			
			$a_html = $this->purifyTag($a_html, $found_begin, $found_end);
			$found_end    = strpos($a_html, $closing_bq_tag_string[0][$i], $found_end);
			$this->purified_tag_stack[$found_begin]['begin'] = $found_begin;
			$this->purified_tag_stack[$found_begin]['end'] = $found_end;
			
			$this->purified_end_tags[$found_end] = $found_end;
			$purify_range = array();


			// get new offset position after changing a_html
			$offset = strpos($a_html, $closing_bq_tag_string[0][$i], $found_begin);
			if(($pts = count($this->purified_tag_stack)) > 0)
			{
				$cnt_bts = count($this->begin_tag_stack) -1;
				
				$offset = $this->begin_tag_stack[$cnt_bts];
			}
		}
		
		return $a_html;
	}

}
