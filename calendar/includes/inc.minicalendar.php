<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source															  |
	|	Dateplaner Modul														  |													
	+-----------------------------------------------------------------------------+
	| Copyright (c) 2004 ILIAS open source & University of Applied Sciences Bremen|
	|                                                                             |
	| This program is free software; you can redistribute it and/or               |
	| modify it under the terms of the GNU General Public License                 |
	| as published by the Free Software Foundation; either version 2              |
	| of the License, or (at your option) any later version.                      |
	|                                                                             |
	| This program is distributed in the hope that it will be useful,             |
	| but WITHOUT ANY WARRANTY; without even the implied warranty of              |
	| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
	| GNU General Public License for more details.                                |
	|                                                                             |
	| You should have received a copy of the GNU General Public License           |
	| along with this program; if not, write to the Free Software                 |
	| Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
	+-----------------------------------------------------------------------------+
*/

/**
* mini calendare picture
*
* this file should manage the mini calendar
*
* @author		Timo Weichler 
* @author       Frank Gruemmert <gruemmert@feuerwelt.de>    
* @version      $Id$                                    
* @module       inc.minicalendar.php                            
* @modulegroup  dateplaner                    
* @package		dateplaner-functions
*/
//require('./classes/class.ilMiniCal.php');

// it is an action required to generate images..
//if ($_GET[action] == "show") {
	
//	$im = ImageCreate (162, 160)
//    	or die ("Kann keinen neuen GD-Bild-Stream erzeugen");
// 	$im = showMinicalendar($_GET[month],$_GET[year], $im, $_GET[DP_Lang]);
//	$CALENDAR = $MiniCal->show($month, $year);
	// Ilias3 + GD >1.6.1
//	ImagePNG($im);

	// Ilias2 + GD <1.6.1
	//ImageGif($im);
	
//}



/**
* 	void function showMinicalendar($month,$year, $im)
* 	@description :  generate an Image with callenden properties
* 	@param int $month
* 	@param int $year
*   @param string $im				( pointer to image )
* 	@global Array DP_Lang			( Name of the active Language )
*/
//
//include ('./classes/class.ilMiniCal.php');

function showMinicalendar($month,$year, $im, $DP_Lang)
{

/*		
	//language
	if(file_exists('../lang/dp_'.$DP_Lang.'.lang'))		//checks whether lang-file exists; if not english is used as fallback solution
	{	
		
		$z = 0;
		$test = $DP_language;
		$array_tmp = @file('../lang/dp_'.$DP_Lang.'.lang');
		foreach($array_tmp as $v)
		{
			if ((substr(trim($v),0,13)=='dateplaner#:#') && (substr_count($v,'#:#')>=2))
			{//Line mustn't start with a ';' and must contain at least one '=' symbol.
				$z++;				
				$pos		= strpos($v, '#:#', '13');
				$offset1	= strpos($v, '#:#', '13')-13;
				$offset2	= strpos($v, '###', '13')-$offset1-16;
				if($offset2 != (-$offset1-16)) 
				{
					$DP_language[trim(substr($v,13,$offset1))] = trim(substr($v, $pos+3,$offset2));
				}
				else 
				{
				$DP_language[trim(substr($v,13,$offset1))] = trim(substr($v, $pos+3));
				}
			}
			
		}

	}
	else
	{
		$DP_Lang = "en";
		$array_tmp = @file('../lang/dp_'.$DP_Lang.'.lang');
		foreach($array_tmp as $v)
		{
			if ((substr(trim($v),0,13)=='dateplaner#:#') && (substr_count($v,'#:#')>=2))
			{//Line mustn't start with a ';' and must contain at least one '=' symbol.
				$pos		= strpos($v, '#:#', '13');
				$offset1	= strpos($v, '#:#', '13')-13;
				$offset2	= strpos($v, '###', '13')-$offset1-16;
				if($offset2 != (-$offset1-16)) 
				{
					$DP_language[trim(substr($v,13,$offset1))] = trim(substr($v, $pos+3,$offset2));
				}
				else 
				{
				$DP_language[trim(substr($v,13,$offset1))] = trim(substr($v, $pos+3));
				}
			}
		}			
	}


	if(!$month || !$year)
	{
		$month = date(m);
		$year = date(Y);	
	}

	//Wieviele Tage hat der vorherige month
	$lastday = strftime("%d.", mktime (0,0,0,$month,0,$year));
	//Welcher Wochentag ist der 1. des Monats
	$firstday = strftime ("%u", mktime(0,0,0,$month,1,$year))-2;
	if ($firstday == -1) $firstday = 6; 
	$startday = $lastday - $firstday;

    $Tagesnamen = array($DP_language[wk_short], $DP_language[Mo_short], $DP_language[Tu_short], $DP_language[We_short], $DP_language[Th_short], $DP_language[Fr_short], $DP_language[Sa_short], $DP_language[Su_short]);
	$Monatabk = array("", $DP_language[short_01], $DP_language[short_02], $DP_language[short_03], $DP_language[short_04], $DP_language[short_05], $DP_language[short_06], $DP_language[short_07], $DP_language[short_08], $DP_language[short_09], $DP_language[short_10],  $DP_language[short_11], $DP_language[short_12]);
	
	
	ImageColorAllocate ($im, 144, 144, 144);
	$color[1] = ImageColorAllocate ($im,0,0,0); //schwarz
	$color[2] = ImageColorAllocate ($im,144,144,144); //blau
	$color[3] = ImageColorAllocate ($im,43,43,43); // dunkelblau
	$color[4] = ImageColorAllocate ($im,255,255,255); //weiss
	$color[5] = ImageColorAllocate ($im,200,200,200); //hellblau
	$color[6] = ImageColorAllocate ($im,0,255,0); //ausblenden hellblau
	$color[7] = ImageColorAllocate ($im,255,200,150); //rot

	imagefilledrectangle ($im, 1, 49, 160, 65, $color[3]);
	imagefilledrectangle ($im, 1, 66, 20, 158, $color[3]);

	//ANZEIGE DES jahres und dessen navigation
	imagerectangle ($im, 10, 2, 25, 17, $color[4]);
	imagerectangle ($im, 135, 2, 150, 17, $color[4]);
	ImageString ($im, 5, 64, 2, $year, $color[4]);
	$points = array("20","6","20","12","15","9");
	imagefilledpolygon ($im, $points, 3, $color[4]);
	$points = array("140","6","140","12","145","9");
	imagefilledpolygon ($im, $points, 3, $color[4]);

	//Anzeige des monats und der navigation
	imageline ( $im, 2, 20 ,158 ,20 , $color[3]);
	imageline ( $im, 2, 33 ,158 ,33 , $color[3]);
	imageline ( $im, 2, 47 ,158 ,47 , $color[3]);
	imageline ( $im, 2, 20 ,2 ,47 , $color[3]);
	imageline ( $im, 158, 20 ,158 ,47 , $color[3]);
	
	imageline ( $im, 27, 20 ,27 ,47 , $color[3]);
	imageline ( $im, 54, 20 ,54 ,47 , $color[3]);
	imageline ( $im, 79, 20 ,79 ,47 , $color[3]);
	imageline ( $im, 105, 20 ,105 ,47 , $color[3]);
	imageline ( $im, 131, 20 ,131 ,47 , $color[3]);

	$c0 = 1;
	for($y = 20; $y <= 40; $y = $y+13)
	{
		for($x = 5; $x <= 137; $x = $x+26)
		{
			if($month == $c0)
			{
				$c1=4;
			}
			else
			{
				$c1=3;
			}
			ImageString ($im, 3, $x, $y, $Monatabk[$c0++], $color[$c1]);
		}
	}

	//Kalenderwoche
	$kw=strftime ("%V", mktime(0,0,0,$month,1,$year));
	if (date("w",mktime(0,0,0,$month,1,$year))==1) 
	{
		$kw = $kw-1;
	}
	else 
	{
		$kw = $kw+0;
	}
	for($y = 66; $y <= 145; $y = $y+15)
	{
		ImageString ($im, 3, 4, $y, $kw++, $color[2]);
	}
	
	//Wochentage MO DI MI...
	$c0 = 0;
	for($x = 5; $x <= 145; $x = $x+20)
	{
		ImageString ($im, 3, $x, 50, $Tagesnamen[$c0++], $color[5]);
	}
	
	//Tage des Monats, bzw. der Tagesnummern
	$c0 = 3;
	for($y = 66; $y <= 145; $y = $y+15)
	{
		for($x = 25; $x <= 145; $x = $x+20)
		{
			if($startday > $lastday)
			{
				if ($c0 == 5)
				{
					$c0 = 3;
				}
				else
				{
					$c0 = 5;
				}
				$startday = 1;		
				$monat1 = $month;
				$jahr1 = $year;
				if($month == 12)
				{
					$monat1 = 0;
					$jahr1 = $year + 1;
				}
				$lastday = strftime("%d.", mktime (0,0,0,$monat1+1,0,$jahr1));
				//Prüfung, ob Schaltjahr
				if($lastday == 29 && !date(L, mktime (0,0,0,$monat1+1,0,$jahr1))) $lastday = 28;
			}
			if($c0 == 5 && $startday == $tag)
			{
				imagefilledrectangle ($im, $x-2, $y, $x+14, $y+13, $color[3]);
				if($startday == date("d")&& $year == date("Y") && $month == date("n"))
				{
					ImageString ($im, 3, $x, $y, $startday++ , $color[7]);	
				}
				else
				{
					ImageString ($im, 3, $x, $y, $startday++ , $color[4]);
				}
			}
			else if($c0 == 5 && $startday == date("d") && $year == date("Y") && $month == date("n"))
			{
				ImageString ($im, 3, $x, $y, $startday++ , $color[7]);
			}
			else
			{	
				ImageString ($im, 3, $x, $y, $startday++ , $color[$c0]);
			}
		}
	}
	
	return $im;
*/	
} // end func


/**
* 	void function setMinicalendar($month,$year)
* 	@description :  generate ImageMaps on the Image with callenden properties
* 	@param int $month
* 	@param int $year
*/

function setMinicalendar($month, $year, $DP_Lang, $app)
{
/*		
	
	if(!$month || !$year)
	{
		$month = date(m);
		$year = date(Y);	
	}


	$lastday		= strftime("%d", mktime (0,0,0,$month,0,$year));
	$firstday		= strftime ("%u", mktime(0,0,0,$month,1,$year))-2;
	
	if ($firstday == -1) $firstday = 6; 
	
	$startday = $lastday - $firstday;

	// generiere Bildmaps
	$minical_show = "
<map name='Calendar'>"; 
	
	//Kalenderwoche
	$kw=strftime ("%V", mktime(0,0,0,$month,1,$year));
	$week_ts=mktime (0,0,0,$month,1,$year);
	if (date("w",$week_ts)==1) 
	{
		$week_ts =  strtotime ("-1 week", $week_ts );
	}
	$x = 4;
	for($y = 66; $y <= 145; $y = $y+15)
	{
			$x1 = $x + 15;
			$y1 = $y + 15;
			$minical_show = $minical_show. "
			<area shape=rect coords='$x,$y,$x1,$y1' href='dateplaner.php?app=week&timestamp=$week_ts'>";
			$week_ts= strtotime ("+1 week", $week_ts );
	}


	$c0 = false;
	for($y = 66; $y <= 145; $y = $y+15)
	{
		for($x = 25; $x <= 145; $x = $x+20)
		{
			if($startday > $lastday || $startday == 1)
			{
				if ($c0 == true)
				{
					$c0 = false;
				}
				else
				{
					$c0 = true;
				}
				$startday = 1;		
				$monat1 = $month;
				$jahr1 = $year;
				if($month == 12)
				{
					$monat1 = 0;
					$jahr1 = $year + 1;
				}
				$monat1 = $month+1;
				$lastday = strftime("%d.", mktime (0,0,0,$monat1,0,$jahr1));
			}
			if($c0)
				{
				$x1 = $x + 15;
				$y1 = $y + 15;
				$day_ts=mktime (0,0,0,$month,$startday,$year);
				$minical_show = $minical_show. "
	<area shape=rect coords='$x,$y,$x1,$y1' href='dateplaner.php?app=day&timestamp=$day_ts'>";
			}
			$startday++;
		}
	}

	//Monatsnavigation
	$c0 = 1;
	for($y = 20; $y <= 40; $y = $y+13)
		{
		for($x = 5; $x <= 137; $x = $x+26)
			{
			$x1 = $x + 20;
			$y1 = $y + 10;
			$month_ts=mktime (0,0,0,$c0,1,$year);
			$minical_show = $minical_show. "
	<area shape=rect coords='$x,$y,$x1,$y1' href='dateplaner.php?app=$app&month=$c0&year=$year&timestamp=$month_ts'>";
			$c0++;
		}	
	}
	$minical_show = $minical_show. "
	<area shape=rect coords='10,2,25,17' href='dateplaner.php?app=$app&month=$month&year=$year&action=last'>
	<area shape=rect coords='135,2,150,17' href='dateplaner.php?app=$app&month=$month&year=$year&action=next'>
</map>

<img src='.".DATEPLANER_ROOT_DIR."/includes/inc.minicalendar.php?action=show&month=".$month."&year=".$year."&DP_Lang=".$DP_Lang."' usemap='#Calendar' border=0>
";
	return $minical_show;
*/		
} // end func
?>
