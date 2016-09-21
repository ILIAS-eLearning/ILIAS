<?php

namespace GetId3\Module\AudioVideo;

use GetId3\Lib\Helper;

/////////////////////////////////////////////////////////////////
/// GetId3() by James Heinrich <info@getid3.org>               //
//  available at http://getid3.sourceforge.net                 //
//            or http://www.getid3.org                         //
//                                                             //
//  AVCSequenceParameterSetReader                              //
//  by Seth Kaufman <seth@whirl-i-gig.com>                     //
//                                                             //
/////////////////////////////////////////////////////////////////
//                                                             //
// dependencies: Module Flv                                    //
//                                                            ///
/////////////////////////////////////////////////////////////////

/**
 * AVCSequenceParameterSetReader
 *
 * @author James Heinrich <info@getid3.org>
 * @author Seth Kaufman <seth@whirl-i-gig.com>
 * @uses Flv
 * @link http://getid3.sourceforge.net
 * @link http://www.getid3.org
 */
class AVCSequenceParameterSetReader
{
    public $sps;
    public $start = 0;
    public $currentBytes = 0;
    public $currentBits = 0;
    public $width;
    public $height;

    const H264_AVC_SEQUENCE_HEADER = 0;
    const H264_PROFILE_BASELINE = 66;
    const H264_PROFILE_MAIN = 77;
    const H264_PROFILE_EXTENDED = 88;
    const H264_PROFILE_HIGH = 100;
    const H264_PROFILE_HIGH10 = 110;
    const H264_PROFILE_HIGH422 = 122;
    const H264_PROFILE_HIGH444 = 144;
    const H264_PROFILE_HIGH444_PREDICTIVE = 244;

    /**
     *
     * @param type $sps
     */
    public function __construct($sps)
    {
        $this->sps = $sps;
    }

    /**
     *
     */
    public function readData()
    {
        $this->skipBits(8);
        $this->skipBits(8);
        $profile = $this->getBits(8); //	read profile
        $this->skipBits(16);
        $this->expGolombUe(); //	read sps id
        if (in_array($profile,
                     array(self::H264_PROFILE_HIGH, self::H264_PROFILE_HIGH10, self::H264_PROFILE_HIGH422, self::H264_PROFILE_HIGH444, self::H264_PROFILE_HIGH444_PREDICTIVE))) {
            if ($this->expGolombUe() == 3) {
                $this->skipBits(1);
            }
            $this->expGolombUe();
            $this->expGolombUe();
            $this->skipBits(1);
            if ($this->getBit()) {
                for ($i = 0; $i < 8; $i++) {
                    if ($this->getBit()) {
                        $size = $i < 6 ? 16 : 64;
                        $lastScale = 8;
                        $nextScale = 8;
                        for ($j = 0; $j < $size; $j++) {
                            if ($nextScale != 0) {
                                $deltaScale = $this->expGolombUe();
                                $nextScale = ($lastScale + $deltaScale + 256) % 256;
                            }
                            if ($nextScale != 0) {
                                $lastScale = $nextScale;
                            }
                        }
                    }
                }
            }
        }
        $this->expGolombUe();
        $pocType = $this->expGolombUe();
        if ($pocType == 0) {
            $this->expGolombUe();
        } elseif ($pocType == 1) {
            $this->skipBits(1);
            $this->expGolombSe();
            $this->expGolombSe();
            $pocCycleLength = $this->expGolombUe();
            for ($i = 0; $i < $pocCycleLength; $i++) {
                $this->expGolombSe();
            }
        }
        $this->expGolombUe();
        $this->skipBits(1);
        $this->width = ($this->expGolombUe() + 1) * 16;
        $heightMap = $this->expGolombUe() + 1;
        $this->height = (2 - $this->getBit()) * $heightMap * 16;
    }

    /**
     *
     * @param type $bits
     */
    public function skipBits($bits)
    {
        $newBits = $this->currentBits + $bits;
        $this->currentBytes += (int) floor($newBits / 8);
        $this->currentBits = $newBits % 8;
    }

    /**
     *
     * @return type
     */
    public function getBit()
    {
        $result = (Helper::BigEndian2Int(substr($this->sps,
                                                           $this->currentBytes,
                                                           1)) >> (7 - $this->currentBits)) & 0x01;
        $this->skipBits(1);

        return $result;
    }

    /**
     *
     * @param  type $bits
     * @return type
     */
    public function getBits($bits)
    {
        $result = 0;
        for ($i = 0; $i < $bits; $i++) {
            $result = ($result << 1) + $this->getBit();
        }

        return $result;
    }

    /**
     *
     * @return int
     */
    public function expGolombUe()
    {
        $significantBits = 0;
        $bit = $this->getBit();
        while ($bit == 0) {
            $significantBits++;
            $bit = $this->getBit();

            if ($significantBits > 31) {
                // something is broken, this is an emergency escape to prevent infinite loops
                return 0;
            }
        }

        return (1 << $significantBits) + $this->getBits($significantBits) - 1;
    }

    /**
     *
     * @return type
     */
    public function expGolombSe()
    {
        $result = $this->expGolombUe();
        if (($result & 0x01) == 0) {
            return -($result >> 1);
        } else {
            return ($result + 1) >> 1;
        }
    }

    /**
     *
     * @return type
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     *
     * @return type
     */
    public function getHeight()
    {
        return $this->height;
    }
}
