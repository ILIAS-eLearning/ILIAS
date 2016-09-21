<?php

namespace GetId3\Module\AudioVideo;

use GetId3\Lib\Helper;

/////////////////////////////////////////////////////////////////
/// GetId3() by James Heinrich <info@getid3.org>               //
//  available at http://getid3.sourceforge.net                 //
//            or http://www.getid3.org                         //
//                                                             //
//  AMFStream                                                  //
//  by Seth Kaufman <seth@whirl-i-gig.com>                     //
//                                                             //
/////////////////////////////////////////////////////////////////
//                                                             //
// dependencies: None                                          //
//                                                            ///
/////////////////////////////////////////////////////////////////

/**
 * AMFStream
 *
 * @author James Heinrich <info@getid3.org>
 * @author Seth Kaufman <seth@whirl-i-gig.com>
 * @link http://getid3.sourceforge.net
 * @link http://www.getid3.org
 */
class AMFStream
{
    public $bytes;
    public $pos;

    /**
     *
     * @param type $bytes
     */
    public function __construct(&$bytes)
    {
        $this->bytes = & $bytes;
        $this->pos = 0;
    }

    /**
     *
     * @return type
     */
    public function readByte()
    {
        return Helper::BigEndian2Int(substr($this->bytes,
                                                       $this->pos++, 1));
    }

    /**
     *
     * @return type
     */
    public function readInt()
    {
        return ($this->readByte() << 8) + $this->readByte();
    }

    /**
     *
     * @return type
     */
    public function readLong()
    {
        return ($this->readByte() << 24) + ($this->readByte() << 16) + ($this->readByte() << 8) + $this->readByte();
    }

    /**
     *
     * @return type
     */
    public function readDouble()
    {
        return Helper::BigEndian2Float($this->read(8));
    }

    /**
     *
     * @return type
     */
    public function readUTF()
    {
        $length = $this->readInt();

        return $this->read($length);
    }

    /**
     *
     * @return type
     */
    public function readLongUTF()
    {
        $length = $this->readLong();

        return $this->read($length);
    }

    /**
     *
     * @param  type $length
     * @return type
     */
    public function read($length)
    {
        $val = substr($this->bytes, $this->pos, $length);
        $this->pos += $length;

        return $val;
    }

    /**
     *
     * @return type
     */
    public function peekByte()
    {
        $pos = $this->pos;
        $val = $this->readByte();
        $this->pos = $pos;

        return $val;
    }

    /**
     *
     * @return type
     */
    public function peekInt()
    {
        $pos = $this->pos;
        $val = $this->readInt();
        $this->pos = $pos;

        return $val;
    }

    /**
     *
     * @return type
     */
    public function peekLong()
    {
        $pos = $this->pos;
        $val = $this->readLong();
        $this->pos = $pos;

        return $val;
    }

    /**
     *
     * @return type
     */
    public function peekDouble()
    {
        $pos = $this->pos;
        $val = $this->readDouble();
        $this->pos = $pos;

        return $val;
    }

    /**
     *
     * @return type
     */
    public function peekUTF()
    {
        $pos = $this->pos;
        $val = $this->readUTF();
        $this->pos = $pos;

        return $val;
    }

    /**
     *
     * @return type
     */
    public function peekLongUTF()
    {
        $pos = $this->pos;
        $val = $this->readLongUTF();
        $this->pos = $pos;

        return $val;
    }
}
