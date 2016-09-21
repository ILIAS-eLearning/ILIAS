<?php

namespace GetId3\Module\AudioVideo;

/////////////////////////////////////////////////////////////////
/// GetId3() by James Heinrich <info@getid3.org>               //
//  available at http://getid3.sourceforge.net                 //
//            or http://www.getid3.org                         //
//                                                             //
//  AMFReader                                                  //
//  by Seth Kaufman <seth@whirl-i-gig.com>                     //
//                                                             //
/////////////////////////////////////////////////////////////////
//                                                             //
// dependencies: None                                          //
//                                                            ///
/////////////////////////////////////////////////////////////////

/**
 * AMFReader
 *
 * @author James Heinrich <info@getid3.org>
 * @author Seth Kaufman <seth@whirl-i-gig.com>
 * @link http://getid3.sourceforge.net
 * @link http://www.getid3.org
 */
class AMFReader
{
    public $stream;

    /**
     *
     * @param type $stream
     */
    public function __construct(&$stream)
    {
        $this->stream = & $stream;
    }

    /**
     *
     * @return null|string
     */
    public function readData()
    {
        $value = null;

        $type = $this->stream->readByte();
        switch ($type) {

            // Double
            case 0:
                $value = $this->readDouble();
                break;

            // Boolean
            case 1:
                $value = $this->readBoolean();
                break;

            // String
            case 2:
                $value = $this->readString();
                break;

            // Object
            case 3:
                $value = $this->readObject();
                break;

            // null
            case 6:
                return null;
                break;

            // Mixed array
            case 8:
                $value = $this->readMixedArray();
                break;

            // Array
            case 10:
                $value = $this->readArray();
                break;

            // Date
            case 11:
                $value = $this->readDate();
                break;

            // Long string
            case 13:
                $value = $this->readLongString();
                break;

            // XML (handled as string)
            case 15:
                $value = $this->readXML();
                break;

            // Typed object (handled as object)
            case 16:
                $value = $this->readTypedObject();
                break;

            // Long string
            default:
                $value = '(unknown or unsupported data type)';
                break;
        }

        return $value;
    }

    /**
     *
     * @return type
     */
    public function readDouble()
    {
        return $this->stream->readDouble();
    }

    /**
     *
     * @return type
     */
    public function readBoolean()
    {
        return $this->stream->readByte() == 1;
    }

    /**
     *
     * @return type
     */
    public function readString()
    {
        return $this->stream->readUTF();
    }

    /**
     *
     * @return type
     */
    public function readObject()
    {
        // Get highest numerical index - ignored
//		$highestIndex = $this->stream->readLong();

        $data = array();

        while ($key = $this->stream->readUTF()) {
            $data[$key] = $this->readData();
        }
        // Mixed array record ends with empty string (0x00 0x00) and 0x09
        if (($key == '') && ($this->stream->peekByte() == 0x09)) {
            // Consume byte
            $this->stream->readByte();
        }

        return $data;
    }

    /**
     *
     * @return type
     */
    public function readMixedArray()
    {
        // Get highest numerical index - ignored
        $highestIndex = $this->stream->readLong();

        $data = array();

        while ($key = $this->stream->readUTF()) {
            if (is_numeric($key)) {
                $key = (float) $key;
            }
            $data[$key] = $this->readData();
        }
        // Mixed array record ends with empty string (0x00 0x00) and 0x09
        if (($key == '') && ($this->stream->peekByte() == 0x09)) {
            // Consume byte
            $this->stream->readByte();
        }

        return $data;
    }

    /**
     *
     * @return type
     */
    public function readArray()
    {
        $length = $this->stream->readLong();
        $data = array();

        for ($i = 0; $i < $length; $i++) {
            $data[] = $this->readData();
        }

        return $data;
    }

    /**
     *
     * @return type
     */
    public function readDate()
    {
        $timestamp = $this->stream->readDouble();
        $timezone = $this->stream->readInt();

        return $timestamp;
    }

    /**
     *
     * @return type
     */
    public function readLongString()
    {
        return $this->stream->readLongUTF();
    }

    /**
     *
     * @return type
     */
    public function readXML()
    {
        return $this->stream->readLongUTF();
    }

    /**
     *
     * @return type
     */
    public function readTypedObject()
    {
        $className = $this->stream->readUTF();

        return $this->readObject();
    }
}
