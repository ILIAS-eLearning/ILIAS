<?php
    include_once(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'config.php');
    include_once(dirname(dirname(dirname(dirname(__FILE__)))).DIRECTORY_SEPARATOR."modules.php");
    include_once(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR."lang".DIRECTORY_SEPARATOR."en.php");
    if (file_exists(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR."lang".DIRECTORY_SEPARATOR.$lang.".php")) {
            include_once(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR."lang".DIRECTORY_SEPARATOR.$lang.".php");
    }
?>
var gamessource = {};
var gamesheight = {};
var gameswidth = {};
var gamesname = {};
var keywords = "<?php echo $keywordlist; ?>";
var keywordmatch = new RegExp(keywords.toLowerCase());
var apiAccess = 0;
var lightboxWindows = '<?php echo $lightboxWindows;?>';
var baseurl = '<?php echo BASE_URL; ?>';
$(function() {
    try {
        if (parent.jqcc.cometchat.ping() == 1) {
            apiAccess = 1;
        }
    } catch (e) {
    }
    $('#loader').css('display', 'block');
    var categoriesinfo = '';
    var firstcat = 0;
    for (x in gamesJson) {

        if (x.toLowerCase().match(keywordmatch) == null) {
            if (firstcat == 0) {
                firstcat = 'all games';
            }
            categoriesinfo += '<li id=\'' + x + '\'>' + x + '</li>';
        }
    }

    $('#loader').css('display', 'none');
    $('#optionList').find('ul').append(categoriesinfo);
    if (firstcat) {
        getCategory(firstcat);
    }

    $('#categories').click(function() {
        $(this).toggleClass('open');
        $('#optionList').toggleClass('openListHeight');
    });

    $('#optionList').on('click', 'li', function() {
        var currValue = $(this).html();
        $('.selected').html(currValue);
        $('#optionList').find('li').removeClass('active');
        $(this).addClass('active');
        getCategory(currValue);
    });

    resizeWindow();
});

function getCategory(catname) {
    gamessource = {};
    gamesheight = {};
    gameswidth = {};
    gamesname = {};
    if (jQuery().slimScroll) {
        $("#games").slimScroll({height: '263px', width: '100%', allowPageScroll: false});
        $(".slimScrollBar").css('top', '0px');
    }
    $("#games").scrollTop(0);
    $('#loader').css('display', 'block');
    var gamesList = '';
    catname = $.trim(catname);
    if (catname == 'all games') {
        $.each(gamesJson, function(cat, info) {
            $.each(info, function(key, val){
                if(typeof (gamesname[val.e]) != 'undefined') {
                    return;
                }
                var name = val.n;
                if(name.toLowerCase().match(keywordmatch) == null){
                    var thumbnail = 'http://e.miniclip.com/content/game-icons/medium/' + val.t;
                    var width = val.w;
                    var height = val.h;
                    var gameLink = val.e;
                    var source = baseurl + 'modules/games/index.php?gameLink=' + gameLink + '&width=' + width + '&height=' + height + '&name=' + name;
                    gamessource[gameLink] = source;
                    gamesheight[gameLink] = height;
                    gameswidth[gameLink] = width;
                    gamesname[gameLink] = name;
                    gamesList += '<div class="gamelist ' + gameLink + '" onclick="javascript:loadGame(\'' + gameLink + '\')"><img src="' + thumbnail + '"/><br/><div class="title">' + name + '</div></div>';
                }
            });
        });
    } else {
        $.each(gamesJson[catname], function(key, val) {
            var name = val.n;
            if (name.toLowerCase().match(keywordmatch) == null) {
                var thumbnail = 'http://e.miniclip.com/content/game-icons/medium/' + val.t;
                var width = val.w;
                var height = val.h;
                var gameLink = val.e;
                var source = baseurl + 'modules/games/index.php?gameLink=' + gameLink + '&width=' + width + '&height=' + height + '&name=' + name;
                gamessource[gameLink] = source;
                gamesheight[gameLink] = height;
                gameswidth[gameLink] = width;
                gamesname[gameLink] = name;
                gamesList += '<div class="gamelist ' + gameLink + '" onclick="javascript:loadGame(\'' + gameLink + '\')"><img src="' + thumbnail + '"/><br/><div class="title">' + name + '</div></div>';
            }
        });
    }
    $('#games').html(gamesList);
    $('#loader').css('display', 'none');

    if (jQuery().slimScroll) {
        $("#games").slimScroll({resize: '1'});
    }
}

function loadGame(id) {
    var url = gamessource[id];
    var name = "singleplayergame";

    var width = parseInt(gameswidth[id])+20;
    var height = parseInt(gamesheight[id])+20;
    var properties = "status=0,toolbar=0,menubar=0,directories=0,resizable=0,location=0,status=0,scrollbars=0, width="+width+",height="+height+"";
    var title = gamesname[id];
    if (apiAccess == 1 && lightboxWindows == 1) {
        parent.loadCCPopup(url, name, properties, width, height, title, 1, null , null);
    } else {
        var w = window.open(url, name, properties);
        w.focus();
    }
}

$(window).resize(function(){
    resizeWindow();
});

function resizeWindow(){
    var newHeight = ($(window).outerHeight(false) - $('#topcont').outerHeight(false)) + "px";
    if (jQuery().slimScroll) {
        $(".slimScrollDiv").css('height',newHeight);
    } else {
        $('.gamecontainer').css('height',newHeight);
    }
}