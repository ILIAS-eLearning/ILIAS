<?php

/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* bookmark import export
*
* @author Manfred Thaler <manfred.thaler@endo7.com>
* @version $Id$
* @ingroup ServicesBookmarks
*/
class ilBookmarkImportExport
{
    /**
    * parse Bookmark file static method returns 3 dimensional array of
    * bookmarks and folders
    * @param	string		file
    */
    public static function _parseFile($file)
    {
        if (file_exists($file) && is_file($file)) {
            $fp = fopen($file, "r-");
            while ($line = fgets($fp)) {
                $line = trim($line);
                if (preg_match('/<!DOCTYPE NETSCAPE-Bookmark-file-1>/i', $line)) {
                    return ilBookmarkImportExport::__parseNetscape($fp);
                }
            }
        }
        return false;
    }
    /**
    * parse Netscape bookmark file
    *
    * @param	int		filepointer
    * @access	private
    */
    public static function __parseNetscape(&$fp)
    {
        $result = array();
        $parent = array();
        $start_id = 0;
        $id = 0;
        $ok = false;
        while ($line = fgets($fp)) {
            $line = trim($line);
            if (preg_match('/<DL>/i', $line)) {
                $parent_id = $id;
            } elseif (preg_match('/<\/DL>/i', $line)) {
                $parent_id = array_pop($parent);
            } elseif (preg_match('/<DD>(.+)$/i', $line, $match)) {
                // extract description
                $desc = ilBookmarkImportExport::_convertCharset(trim($match[1]), $charset);
                $desc = ilBookmarkImportExport::_decodeEntities($desc);
                $result[$parent_id][$id]['description'] = strip_tags($desc);
            } elseif (preg_match('/<DT><H3[^>]*>(.*)<\/H3>/i', $line, $match)) {
                //bookmark folder
                array_push($parent, $parent_id);
                $name = ilBookmarkImportExport::_convertCharset(trim($match[1]), $charset);
                $name = ilBookmarkImportExport::_decodeEntities($name);
                $id++;
                $result[$parent_id][$id] = array(
                    'type' => 'bmf',
                    'title' => strip_tags($name),
                );
            } elseif (preg_match('/<DT><A HREF="([^"]*)[^>]*>(.*)<\/A>/i', $line, $match)) {
                $id++;
                // extract url and title
                $url = ilBookmarkImportExport::_convertCharset(trim($match[1]), $charset);
                $url = ilBookmarkImportExport::_decodeEntities($url);
                $name = ilBookmarkImportExport::_convertCharset(trim($match[2]), $charset);
                $name = ilBookmarkImportExport::_decodeEntities($name);
                // extract dates
                if (preg_match("/ADD_DATE=\"([^\"]*)/i", $line, $match)) {
                    $add_date = $match[1];
                } else {
                    $add_date = 0;
                }
                if (preg_match("/LAST_VISIT=\"([^\"]*)/i", $line, $match)) {
                    $visited = $match[1];
                } else {
                    $visited = 0;
                }
                if (preg_match("/LAST_MODIFIED=\"([^\"]*)/i", $line, $match)) {
                    $modified = $match[1];
                } else {
                    $modified = 0;
                }
                $result[$parent_id][$id] = array(
                    'type' => 'bm',
                    'target' => strip_tags($url),
                    'title' => strip_tags($name),
                    'add_date' => $add_date,
                    'visited' => $visited,
                    'modified' => $modified,
                );
            } elseif (preg_match("/<META\s+HTTP-EQUIV=\"Content-Type\".+CONTENT=\"([^\"]*)\"/i", $line, $match)) {
                preg_match("/charset=([^ ]+)/", $match[1], $match);
                $charset = $match[1];
            }
        }
        return $result;
    }
    /**
    * export bookmarks static method return html string
    *
    * @param	array		array of bookmark ids to export
    * @param	bool		true for recursive export
    * @param	string		title of html page
    */
    public static function _exportBookmark($obj_ids, $recursive = true, $title = '')
    {
        $htmlCont = '<!DOCTYPE NETSCAPE-Bookmark-file-1>' . "\n";
        $htmlCont .= '<!-- Created by ilias - www.ilias.de -->' . "\n";
        $htmlCont .= '<!-- on ' . date('r') . ' -->' . "\n\n";
        $htmlCont .= '<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8">' . "\n";
        $htmlCont .= '<TITLE>' . ilUtil::prepareFormOutput($title) . '</TITLE>' . "\n";
        $htmlCont .= '<H1>' . ilUtil::prepareFormOutput($title) . '</H1>' . "\n\n";
        $htmlCont .= '<DL><p>' . "\n";
        foreach ($obj_ids as $obj_id) {
            $object = ilBookmarkFolder::getObject($obj_id);
            $htmlCont .= ilBookmarkImportExport::__parseExport($object, 1, $recursive);
        }
        $htmlCont .= '</DL><p>' . "\n";
        //echo htmlentities($htmlCont); exit;
        return $htmlCont;
    }
    /**
    * recursive methode generates bookmark output for export
    *
    * @param	array	node date
    * @param	int		depth of recursion
    * @param	bool	true for recursive export
    * @access	private
    */
    public static function __parseExport($object, $depth = 1, $recursive = true)
    {
        switch ($object['type']) {
            case 'bm':
                $result .= str_repeat("\t", $depth);
                $result .= '<DT><A HREF="' . ilUtil::prepareFormOutput($object['target']) . '" ';
                $result .= 'ADD_DATE="' . intval(0) . '" ';
                $result .= 'LAST_VISIT="' . intval(0) . '" ';
                $result .= 'LAST_MODIFIED="' . intval(0) . '">';
                $result .= ilUtil::prepareFormOutput($object['title']) . '</A>' . "\n";
                if ($object['description']) {
                    $result .= '<DD>' .
                    ilUtil::prepareFormOutput($object['description']) . "\n";
                }
            break;
            case 'bmf':
                $result .= str_repeat("\t", $depth) . '<DT><H3 ADD_DATE="0">' .
                    ilUtil::prepareFormOutput($object['title']) . '</H3>' . "\n";
                if ($object['description']) {
                    $result .= '<DD>' .
                    ilUtil::prepareFormOutput($object['description']) . "\n";
                }
                $result .= str_repeat("\t", $depth) . '<DL><p>' . "\n";
                if ($recursive) {
                    $depth++;
                    $sub_objects = ilBookmarkFolder::getObjects($object['child']);
                    foreach ($sub_objects as $sub_object) {
                        $result .= ilBookmarkImportExport::__parseExport(
                            $sub_object,
                            $depth,
                            $recursive
                        );
                    }
                    $depth--;
                }
                $result .= str_repeat("\t", $depth) . '</DL><p>' . "\n";
            break;
        }
        return $result;
    }
    /**
    * decode html entities of given string
    *
    * @param	string	string to decode
    * @access	public
    */
    public static function _decodeEntities($string)
    {
        if (function_exists('html_entity_decode')) {
            $string = html_entity_decode($string, ENT_QUOTES, "ISO-8859-15"); #NOTE: UTF-8 does not work!
        } else {
            $trans_table = array_flip(get_html_translation_table(HTML_ENTITIES, ENT_QUOTES));
            $string = strtr($string, $trans_table);
        }
        $string = preg_replace_callback(
            '/&#(\d+);/m',
            function ($hit) {
                return chr($hit[1]);
            },
            $string
        ); #decimal notation
        $string = preg_replace_callback(
            '/&#x([a-f0-9]+);/mi',
            function ($hit) {
                return chr(hexdec($hit[1]));
            },
            $string
        );  #hex notation
        return $string;
    }
    /**
    * converts charset of given string
    *
    * @param	string	string to converte
    * @param	string	from charset
    * @param	string	to charset
    * @access	public
    */
    public static function _convertCharset($string, $from_charset = '', $to_charset = 'UTF-8')
    {
        if (extension_loaded("mbstring")) {
            if (!$from_charset) {
                // try to detect charset
                mb_detect_order("ASCII, JIS, UTF-8, EUC-JP, SJIS, ISO-8859-15, Windows-1252");
                $from_charset = mb_detect_encoding($string);
            }
            if (strtoupper($from_charset) != $to_charset) {
                return @mb_convert_encoding($string, $to_charset, $from_charset);
            }
        }
        return $string;
    }
}
