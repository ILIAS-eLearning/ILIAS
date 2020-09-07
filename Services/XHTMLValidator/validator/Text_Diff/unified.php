<?php
/**
 * "Unified" diff renderer.
 *
 * This class renders the diff in classic "unified diff" format.
 *
 * $Horde: framework/Text_Diff/Diff/Renderer/unified.php,v 1.4 2005/03/07 14:58:30 jan Exp $
 *
 * @package Text_Diff
 */
class Text_Diff_Renderer_unified extends Text_Diff_Renderer
{

    /**
     * Number of leading context "lines" to preserve.
     */
    public $_leading_context_lines = 4;

    /**
     * Number of trailing context "lines" to preserve.
     */
    public $_trailing_context_lines = 4;

    public function _blockHeader($xbeg, $xlen, $ybeg, $ylen)
    {
        if ($xlen != 1) {
            $xbeg .= ',' . $xlen;
        }
        if ($ylen != 1) {
            $ybeg .= ',' . $ylen;
        }
        return "@@ -$xbeg +$ybeg @@";
    }

    public function _added($lines)
    {
        return $this->_lines($lines, '+');
    }

    public function _deleted($lines)
    {
        return $this->_lines($lines, '-');
    }

    public function _changed($orig, $final)
    {
        return $this->_deleted($orig) . $this->_added($final);
    }
}
