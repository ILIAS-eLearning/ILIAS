<?php

namespace GetId3\Handler;

use GetId3\Lib\Helper;
use GetId3\GetId3Core;
use GetId3\Exception\DefaultException;

/////////////////////////////////////////////////////////////////
/// GetId3() by James Heinrich <info@getid3.org>               //
//  available at http://getid3.sourceforge.net                 //
//            or http://www.getid3.org                         //
/////////////////////////////////////////////////////////////////
//                                                             //
// Please see readme.txt for more information                  //
//                                                            ///
/////////////////////////////////////////////////////////////////

/**
 *
 * @author James Heinrich <info@getid3.org>
 * @link http://getid3.sourceforge.net
 * @link http://www.getid3.org
 */
abstract class BaseHandler
{
    /**
     * pointer
     *
     * @var GetId3\GetId3
     */
    protected $getid3;

    /**
     * analyzing filepointer or string
     *
     * @var boolean
     */
    protected $data_string_flag = false;

    /**
     * string to analyze
     *
     * @var string
     */
    protected $data_string = '';

    /**
     * seek position in string
     *
     * @var integer
     */
    protected $data_string_position = 0;

    /**
     * string length
     *
     * @var integer
     */
    protected $data_string_length = 0;

    private $dependency_to;

    /**
     *
     * @param GetId3\GetId3 $getid3
     * @param type          $call_module
     */
    public function __construct(GetId3Core $getid3, $call_module = null)
    {
        $this->getid3 = $getid3;

        if (null !== $call_module) {
            $this->dependency_to = $call_module;
        }
    }

    /**
     * Analyze from file pointer
     */
    abstract public function analyze();

    /**
     * Analyze from string instead
     */
    public function AnalyzeString(&$string)
    {
        // Enter string mode
        $this->data_string_flag = true;
        $this->data_string = $string;

        // Save info
        $saved_avdataoffset = $this->getid3->info['avdataoffset'];
        $saved_avdataend = $this->getid3->info['avdataend'];
        $saved_filesize = (isset($this->getid3->info['filesize']) ? $this->getid3->info['filesize'] : null); // may be not set if called as dependency without openfile() call
        // Reset some info
        $this->getid3->info['avdataoffset'] = 0;
        $this->getid3->info['avdataend'] = $this->getid3->info['filesize'] = $this->data_string_length = strlen($string);

        // Analyze
        $this->analyze();

        // Restore some info
        $this->getid3->info['avdataoffset'] = $saved_avdataoffset;
        $this->getid3->info['avdataend'] = $saved_avdataend;
        $this->getid3->info['filesize'] = $saved_filesize;

        // Exit string mode
        $this->data_string_flag = false;
    }

    /**
     *
     * @return type
     */
    protected function ftell()
    {
        if ($this->data_string_flag) {
            return $this->data_string_position;
        }

        return ftell($this->getid3->fp);
    }

    /**
     *
     * @param  type $bytes
     * @return type
     */
    protected function fread($bytes)
    {
        if ($this->data_string_flag) {
            $this->data_string_position += $bytes;

            return substr($this->data_string,
                          $this->data_string_position - $bytes, $bytes);
        }

        return fread($this->getid3->fp, $bytes);
    }

    /**
     *
     * @param  type $bytes
     * @param  type $whence
     * @return int
     */
    protected function fseek($bytes, $whence = SEEK_SET)
    {
        if ($this->data_string_flag) {
            switch ($whence) {
                case SEEK_SET:
                    $this->data_string_position = $bytes;
                    break;

                case SEEK_CUR:
                    $this->data_string_position += $bytes;
                    break;

                case SEEK_END:
                    $this->data_string_position = $this->data_string_length + $bytes;
                    break;
            }

            return 0;
        }

        return fseek($this->getid3->fp, $bytes, $whence);
    }

    /**
     *
     * @return type
     */
    protected function feof()
    {
        if ($this->data_string_flag) {
            return $this->data_string_position >= $this->data_string_length;
        }

        return feof($this->getid3->fp);
    }

    /**
     *
     * @param  type $module
     * @return type
     */
    final protected function isDependencyFor($module)
    {
        return $this->dependency_to == $module;
    }

    /**
     *
     * @param  type    $text
     * @return boolean
     */
    protected function error($text)
    {
        $this->getid3->info['error'][] = $text;

        return false;
    }

    /**
     *
     * @param  type $text
     * @return type
     */
    protected function warning($text)
    {
        return $this->getid3->warning($text);
    }

    /**
     *
     * @param  type      $ThisFileInfoIndex
     * @param  type      $filename
     * @param  type      $offset
     * @param  type      $length
     * @return boolean
     * @throws Exception
     */
    public function saveAttachment(&$ThisFileInfoIndex, $filename, $offset,
                                   $length)
    {
        try {
            if (!Helper::intValueSupported($offset + $length)) {
                throw new DefaultException('it extends beyond the ' . round(PHP_INT_MAX / 1073741824) . 'GB limit');
            }

            // do not extract at all
            if ($this->getid3->option_save_attachments === self::ATTACHMENTS_NONE) {
                unset($ThisFileInfoIndex); // do not set any
            }

            // extract to return array
            else if ($this->getid3->option_save_attachments === self::ATTACHMENTS_INLINE) {

                // get whole data in one pass, till it is anyway stored in memory
                $this->fseek($offset);
                $ThisFileInfoIndex = $this->fread($length);
                if ($ThisFileInfoIndex === false || strlen($ThisFileInfoIndex) != $length) { // verify
                    throw new DefaultException('failed to read attachment data');
                }
            }

            // assume directory path is given
            else {

                // set up destination path
                $dir = rtrim(str_replace(array('/', '\\'), DIRECTORY_SEPARATOR,
                                         $this->getid3->option_save_attachments),
                                         DIRECTORY_SEPARATOR);
                if (!is_dir($dir) || !is_writable($dir)) { // check supplied directory
                    throw new DefaultException('supplied path (' . $dir . ') does not exist, or is not writable');
                }
                $dest = $dir . DIRECTORY_SEPARATOR . $filename;

                // create dest file
                if (($fp_dest = fopen($dest, 'wb')) == false) {
                    throw new DefaultException('failed to create file ' . $dest);
                }

                // copy data
                $this->fseek($offset);
                $buffersize = ($this->data_string_flag ? $length : $this->getid3->fread_buffer_size());
                $bytesleft = $length;
                while ($bytesleft > 0) {
                    if (($buffer = $this->fread(min($buffersize, $bytesleft))) === false || ($byteswritten = fwrite($fp_dest,
                                                                                                                    $buffer)) === false) {
                        fclose($fp_dest);
                        unlink($dest);
                        throw new DefaultException($buffer === false ? 'not enough data to read' : 'failed to write to destination file, may be not enough disk space');
                    }
                    $bytesleft -= $byteswritten;
                }

                fclose($fp_dest);
                $ThisFileInfoIndex = $dest;
            }
        } catch (DefaultException $e) {

            unset($ThisFileInfoIndex); // do not set any is case of error
            $this->warning('Failed to extract attachment ' . $filename . ': ' . $e->getMessage());

            return false;
        }

        return true;
    }
}
