/**
 * Created with JetBrains PhpStorm.
 * User: oskar
 * Date: 21/02/13
 * Time: 5:07 PM
 * To change this template use File | Settings | File Templates.
 */

$(document).ready(function(){
    $(".dcl_reference_record").hover(
    function(){
        var ref = $(this).attr("rec_id");
        $(".dcl_reference_hover[rec_id="+ref+"]").fadeIn(0);
    },
    function(){
        var ref = $(this).attr("rec_id");
        $(".dcl_reference_hover[rec_id="+ref+"]").fadeOut(0);
    });
});