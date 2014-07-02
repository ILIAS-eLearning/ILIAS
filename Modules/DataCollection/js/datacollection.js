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

    /**
     * Increment comments count after saving comment with ajax
     * @param o Object
     */
    if (typeof ilNotes != 'undefined') {
        ilNotes.callbackSuccess = function(o) {
            if (o && o.argument.mode == 'cmd') {
                var $elem = $('tr.dcl_comments_active .dcl_comment').find('.ilHActProp');
                var count = parseInt($elem.text());
                $elem.html(++count);
            }
        }
    }

    var dcl = {};

    dcl.removeHighlightedRows = function() {
        $('.dcl_comments_active').removeClass('dcl_comments_active');
    };

    /**
     * @var $tr tr object to highlight
     */
    dcl.highlightRow = function($tr) {
        this.removeHighlightedRows();
        $tr.addClass('dcl_comments_active');
    };

    $('a.dcl_comment').click(function() {
        $tr = $(this).parents('tr');
        dcl.highlightRow($tr);
    });

    $('.dcl_actions a[id$="comment"]').click(function(){
        $tr = $(this).parents('td.dcl_actions').parent('tr');
       dcl.highlightRow($tr);
    });

    $('#fixed_content').click(function() {
        dcl.removeHighlightedRows();
    });

    /**
     * Formula fields
     */
    $('a.dclPropExpressionField').click(function(){
        var placeholder = '[[' + $(this).text() + ']]';
        var $expression = $('#prop_12');
        var caretPos = document.getElementById('prop_12').selectionStart;
        var expression = $expression.val();
        $expression.val(expression.substring(0, caretPos) + placeholder + expression.substring(caretPos));
    });


});