<?php
    foreach ($trayicon as $value){
        if($value[0]=='chatrooms'){
            if(file_exists(dirname(dirname(dirname(dirname(__FILE__)))).DIRECTORY_SEPARATOR."modules".DIRECTORY_SEPARATOR."chatrooms".DIRECTORY_SEPARATOR."themes".DIRECTORY_SEPARATOR.$theme.DIRECTORY_SEPARATOR."js".DIRECTORY_SEPARATOR.$theme.".js")){
            include_once(dirname(dirname(dirname(dirname(__FILE__)))).DIRECTORY_SEPARATOR."modules".DIRECTORY_SEPARATOR."chatrooms".DIRECTORY_SEPARATOR."themes".DIRECTORY_SEPARATOR.$theme.DIRECTORY_SEPARATOR."js".DIRECTORY_SEPARATOR.$theme.".js");
            }
        }
    }
?>
(function($){
    $.ccsynergy = (function(){
        var settings = {};
        var baseUrl;
        var language;
        var trayicon;
        var typingTimer;
        var resynchTimer;
        var notificationTimer;
        var chatboxOpened = {};
        var trayWidth = 0;
        var siteOnlineNumber = 0;
        var tooltipPriority = 0;
        var desktopNotifications = {};
        var webkitRequest = 0;
        var lastmessagetime = Math.floor(new Date().getTime());
        var favicon;
        var checkfirstmessage;
        var cometchat_lefttab;
        var cometchat_righttab;
        var chromeReorderFix = '_';
        var hasChatroom = 0;
        var newmesscr;
        return {
            playSound: function(){
                var flag = 0;
                try{
                    if(settings.messageBeep==1&&settings.beepOnAllMessages==0&&checkfirstmessage==1){
                        flag = 1;
                    }
                    if((settings.messageBeep==1&&settings.beepOnAllMessages==1)||flag==1){
                        document.getElementById('messageBeep').play();
                    }
                }catch(error){
                }
            },
            initialize: function(){
                settings = jqcc.cometchat.getSettings();
                baseUrl = jqcc.cometchat.getBaseUrl();
                language = jqcc.cometchat.getLanguage();
                trayicon = jqcc.cometchat.getTrayicon();
                var trayData = '';
                var tabWidth = 'width: 100%;';

                if(settings.windowFavicon==1){
                    favicon = new Favico({
                        animation: 'pop'
                    });
                }
                $("body").append('<div id="cometchat"></div><div id="cometchat_tooltip"><div class="cometchat_tooltip_content"></div></div>');
                var optionsbutton = '';
                var optionsbuttonpop = '';
                var ccauthpopup = '';
                var ccauthlogout = '';
                var usertab = '';
                var usertabpop = '';
                var optionsbuttonpadding = '';
                if(settings.ccauth.enabled=="1"){
                    ccauthlogout = '<div id="cometchat_authlogout" title="'+language[80]+'"></div>';
                    optionsbuttonpadding = 'style="margin-right: 0px;"';
                }
                if(settings.ccauth.enabled=="1"){
                    ccauthpopup = '<div id="cometchat_auth_popup" class="cometchat_tabpopup" style="display:none"><div class="cometchat_userstabtitle"><div class="cometchat_userstabtitletext">'+language[77]+'</div><div class="cometchat_minimizebox cometchat_tooltip" id="cometchat_minimize_auth_popup" title="'+language[78]+'"></div><br clear="all"/></div><div class="cometchat_tabsubtitle">'+language[79]+'</div><div class="cometchat_tabcontent cometchat_optionstyle"><div id="social_login">';
                    var authactive = settings.ccauth.active;
                    authactive.forEach(function(auth) {
                        ccauthpopup += '<img onclick="window.open(\''+baseUrl+'functions/login/signin.php?network='+auth.toLowerCase()+'\',\'socialwindow\',\'location=0,status=0,scrollbars=0,width=1000,height=600\')" src="'+baseUrl+'themes/'+settings.theme+'/images/login'+auth.toLowerCase()+'.png" class="auth_options"></img>';
                    });
                    ccauthpopup += '</div></div></div>';
                }
                if(settings.showSettingsTab==1){
                    optionsbuttonpop = '<div id="cometchat_optionsbutton_popup" class="cometchat_dropdownpopup cometchat_tabpopup" style="display:none"><div class="cometchat_optionstriangle"></div><div class="cometchat_optionstriangle cometchat_optionsinnertriangle"></div><div class="cometchat_tabsubtitle">'+language[1]+'</div><div class="cometchat_optionstyle"><div id="guestsname"><strong>'+language[43]+'</strong><br/><input type="text" class="cometchat_guestnametextbox"/><div class="cometchat_guestnamebutton">'+language[44]+'</div></div><strong>'+language[2]+'</strong><br/><textarea class="cometchat_statustextarea"></textarea><div class="cometchat_statusbutton">'+language[22]+'</div><div class="cometchat_statusinputs"><strong>'+language[23]+'</strong><br/><span class="cometchat_user_available"></span><span class="cometchat_optionsstatus available">'+language[3]+'</span><span class="cometchat_optionsstatus2 cometchat_user_invisible" ></span><span class="cometchat_optionsstatus invisible">'+language[5]+'</span><div style="clear:both"></div><span class="cometchat_optionsstatus2 cometchat_user_busy"></span><span class="cometchat_optionsstatus busy">'+language[4]+'</span><span class="cometchat_optionsstatus2 cometchat_user_invisible"></span><span class="cometchat_optionsstatus cometchat_gooffline offline">'+language[11]+'</span><br clear="all"/></div><div class="cometchat_options_disable"><div><input type="checkbox" id="cometchat_soundnotifications" style="vertical-align: -2px;">'+language[13]+'</div><div style="clear:both"></div><div><input type="checkbox" id="cometchat_popupnotifications" style="vertical-align: -2px;">'+language[24]+'</div></div></div></div>';
                    optionsbutton = '<div id="cometchat_optionsbutton" '+optionsbuttonpadding+' title = "'+language[0]+'"><div id="cometchat_optionsbutton_icon" class="cometchat_optionsimages"></div>'+optionsbuttonpop+'</div>'+ccauthlogout;
                }
                var selfDetails = '<div id="cometchat_self_container"><div id="cometchat_self_left"></div><div id="cometchat_self_right">'+optionsbutton+'</div></div>';
                if(settings.showModules==1){
                    trayData += '<div id="cometchat_trayicons" class="cometchat_tabsubtitle">';
                    for(x in trayicon){
                        if(trayicon.hasOwnProperty(x)){
                            if(x!='chatrooms'){
                                var icon = trayicon[x];
                                if(x != 'home' && x != 'scrolltotop' && x != 'themechanger'){
                                    trayData += '<span id="cometchat_trayicon_'+x+'" class="cometchat_trayiconimage" title="'+trayicon[x][1]+'" name="'+x+'" ><img class="'+x+'icon" src="'+baseUrl+'themes/'+settings.theme+'/images/modules/'+x+'.png" width="16px"></span>';
                                }
                            }else{
                                tabWidth = 'width: 50%;left: 0;';
                                hasChatroom = 1;
                            }
                        }
                    }
                    trayData += '</div>';
                }
                var cc_state = $.cookie(settings.cookiePrefix+'state');
                var number = 0;
                if(cc_state!=null){
                    var cc_states = cc_state.split(/:/);
                    number = cc_states[3];
                }
                if(settings.showOnlineTab==1){
                    usertab = '<span id="cometchat_userstab" class="cometchat_tab" style="'+tabWidth+'"><span id="cometchat_userstab_text" class="cometchat_tabstext">'+language[9]+' ('+number+')</span></span>';
                    usertabpop = '<div id="cometchat_popup_container"><div id="cometchat_userstab_popup" class="cometchat_tabpopup" style="display:none"><div class="cometchat_tabsubtitle" id="cometchat_user_searchbar"><input type="text" name="cometchat_user_search" class="cometchat_search cometchat_search_light" id="cometchat_user_search" value="'+language[18]+'"></div><div class="cometchat_tabcontent cometchat_tabstyle"><div id="cometchat_userscontent"><div id="cc_gotoPrevNoti"></div><div id="cc_gotoNextNoti"></div><div id="cometchat_activechatboxes_popup"></div><div id="cometchat_userslist"><div class="cometchat_nofriends">'+language[41]+'</div></div></div></div></div></div>';
                }
                var tabscontainer = '<div id="cometchat_tabcontainer">'+usertab+'</div>';
                if(hasChatroom != 1){
                    tabscontainer = '';
                }
                var baseCode = '<div class="cometchat_offline_overlay"><h3>'+language[92]+'</h3></div><div id="cometchat_lefttab">'+''+selfDetails+''+trayData+tabscontainer+usertabpop+'</div><div id="cometchat_righttab"><div class="cometchat_noactivity"><h1>'+language[89]+' <span id="cometchat_welcome_username"></span>'+language[91]+'</h1><h3>'+language[90]+'</h3></div></div>';

                document.getElementById('cometchat').innerHTML = baseCode;
                if(hasChatroom == 1){
                    jqcc.crsynergy.chatroomInit();
                }
                if(settings.showSettingsTab==0){
                    $('#cometchat_userstab').addClass('cometchat_extra_width');
                    $('#cometchat_userstab_popup').find('div.cometchat_tabstyle').addClass('cometchat_border_bottom');
                }
                if(jqcc().slimScroll){
                    $('#cometchat_userscontent').slimScroll({height: 'auto'});
                }
                jqcc[settings.theme].optionsButton();
                jqcc[settings.theme].chatTab();
                $('#cometchat_userscontent').attr('unselectable', 'on').css('MozUserSelect', 'none').bind('selectstart.ui', function(){
                    return false;
                });
                $('.cometchat_trayicon').attr('unselectable', 'on').css('MozUserSelect', 'none').bind('selectstart.ui', function(){
                    return false;
                });
                $('.cometchat_tab').attr('unselectable', 'on').css('MozUserSelect', 'none').bind('selectstart.ui', function(){
                    return false;
                });
                $(window).bind('resize', function(){
                    jqcc[settings.theme].windowResize();
                });
                if(typeof document.body.style.maxHeight==="undefined"){
                    jqcc[settings.theme].scrollFix();
                    $(window).bind('scroll', function(){
                        jqcc[settings.theme].scrollFix();
                    });
                }else if(jqcc.cometchat.getThemeVariable('mobileDevice')){
                    var mobileOverlay = '<div class="cometchat_mobile_overlay"><p>'+language[94]+'</p></div>';
                    if(settings.disableForMobileDevices){
                        $('#cometchat').html(mobileOverlay);
                        jqcc.cometchat.setThemeVariable('runHeartbeat', 0);
                    }else{

                    }
                }
                $('.cometchat_trayiconimage').click(function(event){
                    event.stopImmediatePropagation();
                    var moduleName = $(this).attr('name');
                    if(window.top == window.self) {
                        jqcc.cometchat.lightbox(moduleName);
                    } else {
                        var controlparameters = {"type":"modules", "name":"cometchat", "method":"lightbox", "params":{"moduleName":moduleName}};
                        controlparameters = JSON.stringify(controlparameters);
                        parent.postMessage('CC^CONTROL_'+controlparameters,'*');
                    }

                });
                document.onmousemove = function(){
                    var nowTime = new Date();
                    jqcc.cometchat.setThemeVariable('idleTime', Math.floor(nowTime.getTime()/1000));
                };
                var extlength = settings.extensions.length;
                if(extlength>0){
                    for(var i = 0; i<extlength; i++){
                        var name = 'cc'+settings.extensions[i];
                        if(typeof ($[name])=='object'){
                            $[name].init();
                        }
                    }
                }
                if($.inArray('block', settings.plugins)>-1){
                    $.ccblock.addCode();
                }

                $('#cometchat_userscontent').on('DOMMouseScroll mousewheel', function(event){
                    clearTimeout($.data(this, 'timer'));
                    $.data(this, 'timer', setTimeout(function() {
                            jqcc[settings.theme].calcPrevNoti();
                            jqcc[settings.theme].calcNextNoti();
                    }, 250));
                });
                $('#cometchat_userstab_popup').find('.cometchat_tabcontent .slimScrollBar').on('mouseup', function(event){
                            jqcc[settings.theme].calcPrevNoti();
                            jqcc[settings.theme].calcNextNoti();
                });
                $('#cometchat_userstab_popup').find('.cometchat_tabcontent .slimScrollBar').on('mousedown', function(event){
                            jqcc[settings.theme].calcPrevNoti();
                            jqcc[settings.theme].calcNextNoti();
                });
                $('#cc_gotoPrevNoti').click(function(event){
                    var mindiff = 0;
                    var bar =$('#cometchat_userstab_popup').find('.cometchat_tabcontent .slimScrollBar');
                    var cometchat_userslist = $('#cometchat_userslist');
                    var cometchat_activechatboxes_popup = $('#cometchat_activechatboxes_popup');
                    var fullheight = cometchat_userslist.outerHeight() + cometchat_activechatboxes_popup.outerHeight();
                    var cometchat_userscontent = $('#cometchat_userscontent');
                    var cometchat_userscontent_ht = cometchat_userscontent.outerHeight();
                    var railMinusBarHt =  (cometchat_userscontent_ht-bar.outerHeight());
                    var percentScroll =  parseFloat(bar.css('top')) / railMinusBarHt;
                    var heightScrolled = parseFloat(percentScroll*fullheight)-(cometchat_userscontent_ht*percentScroll);
                    var userHeight = $('.cometchat_userlist').outerHeight();
                    var grpDividerHeight = $(".cometchat_subsubtitle").outerHeight(true);
                    var scrolltomsg;
                    $('.cometchat_userlist').each(function(){
                        var diff = 0;
                        if($(this).find('.cometchat_msgcount').length>0){
                            var userHeightFromTop = 0;
                            activeChatboxesHeight = ($(this).parents().prevAll('#cometchat_activechatboxes_popup').outerHeight());
                            if(typeof(activeChatboxesHeight) != "number"){
                                activeChatboxesHeight =0;
                            }
                            userHeightFromTop = (userHeight * ($(this).prevAll('.cometchat_userlist').length)) + (grpDividerHeight *$(this).prevAll('.cometchat_subsubtitle').length)+ (grpDividerHeight *$(this).parent().prevAll('.cometchat_subsubtitle').length) + activeChatboxesHeight;
                            diff = Math.round(heightScrolled - userHeightFromTop);
                            if((diff > 0 && diff < mindiff)||(diff > 0 && mindiff == 0)){
                                mindiff = Math.round(diff) ;
                                scrolltomsg = userHeightFromTop;
                            }
                        }
                    });
                    if(mindiff > 0){
                        scrolltomsg = (scrolltomsg  < 0)?0:scrolltomsg;
                        cometchat_userscontent.scrollTop(scrolltomsg);
                        var newpercentScroll = scrolltomsg/fullheight ;
                        var bartop = newpercentScroll*cometchat_userscontent_ht;
                        bartop = (bartop > railMinusBarHt)?railMinusBarHt:bartop;
                        bar.css('top',bartop+'px');
                        jqcc[settings.theme].calcPrevNoti();
                        jqcc[settings.theme].calcNextNoti();
                    }
                    jqcc[settings.theme].calcPrevNoti();
                    jqcc[settings.theme].calcNextNoti();
                });
                $('#cc_gotoNextNoti').click(function(event){
                    var mindiff = 0;
                    var bar =$('#cometchat_userstab_popup').find('.cometchat_tabcontent .slimScrollBar');
                    var cometchat_userslist = $('#cometchat_userslist');
                    var cometchat_activechatboxes_popup = $('#cometchat_activechatboxes_popup');
                    var fullheight = cometchat_userslist.outerHeight() + cometchat_activechatboxes_popup.outerHeight();
                    var cometchat_userscontent = $('#cometchat_userscontent');
                    var cometchat_userscontent_ht = cometchat_userscontent.outerHeight();
                    var railMinusBarHt =  (cometchat_userscontent_ht-bar.outerHeight());
                    var percentScroll =  parseFloat(bar.css('top')) / railMinusBarHt;
                    var heightScrolled = parseFloat(percentScroll*fullheight)+(cometchat_userscontent_ht*(1-percentScroll));
                    var userHeight = $('.cometchat_userlist').outerHeight();
                    var grpDividerHeight = $(".cometchat_subsubtitle").outerHeight(true);
                    var scrolltomsg = 0 ;
                    $('.cometchat_userlist').each(function(){
                        var diff = 0;
                        if($(this).find('.cometchat_msgcount').length>0){
                            var userHeightFromTop = 0;
                            activeChatboxesHeight = ($(this).parents().prevAll('#cometchat_activechatboxes_popup').outerHeight());
                            if(typeof(activeChatboxesHeight) != "number"){
                                activeChatboxesHeight =0;
                            }
                            userHeightFromTop = (userHeight * ($(this).prevAll('.cometchat_userlist').length)) + (grpDividerHeight *$(this).prevAll('.cometchat_subsubtitle').length)+ (grpDividerHeight *$(this).parent().prevAll('.cometchat_subsubtitle').length) + activeChatboxesHeight;
							diff = Math.round(userHeightFromTop - heightScrolled + userHeight);
                            if((diff > 0 && diff < mindiff)||(diff > 0 && mindiff == 0)){
                                mindiff = diff;
                                scrolltomsg = userHeightFromTop;
                            }
                        }
                    });
                    if(mindiff >0){
                        scrolltomsg = (scrolltomsg  > fullheight)?fullheight:scrolltomsg;
                        cometchat_userscontent.scrollTop(scrolltomsg);
                        var newpercentScroll = scrolltomsg/fullheight ;
                        var bartop = newpercentScroll*cometchat_userscontent_ht;
                        bartop = (bartop > railMinusBarHt)?railMinusBarHt:bartop;
                        bar.css('top',bartop+'px');
                        jqcc[settings.theme].calcPrevNoti();
                        jqcc[settings.theme].calcNextNoti();
                    }
                });
                $('.cometchat_offline_overlay').click(function(){
                    $('.cometchat_offline_overlay').css('display','none');
                    if(jqcc.cometchat.getThemeVariable('offline')==1){
                        jqcc.cometchat.setThemeVariable('offline', 0);
                        jqcc.cometchat.setThemeArray('buddylistStatus', jqcc.cometchat.getThemeVariable('userid'), 'available');
                        $('#cometchat_userstab_text').html(language[9]+' ('+jqcc.cometchat.getThemeVariable('lastOnlineNumber')+')');
                        jqcc.cometchat.chatHeartbeat(1);
                        jqcc.cometchat.sendStatus('available');
                        $('.cometchat_noactivity').css('display','block');
                        $('#cometchat_userstab').click();
                    }
                });
                jqcc[settings.theme].calcPrevNoti();
                jqcc[settings.theme].calcNextNoti();
            },
            calcNextNoti: function(){
                var mindiff = 0;
                var bar =$('#cometchat_userstab_popup').find('.cometchat_tabcontent .slimScrollBar');
                var cometchat_userslist = $('#cometchat_userslist');
                var cometchat_activechatboxes_popup = $('#cometchat_activechatboxes_popup');
                var fullheight = cometchat_userslist.outerHeight() + cometchat_activechatboxes_popup.outerHeight();
                var cometchat_userscontent = $('#cometchat_userscontent');
                var cometchat_userscontent_ht = cometchat_userscontent.outerHeight();
                var railMinusBarHt =  (cometchat_userscontent_ht-bar.outerHeight());
                var percentScroll =  parseFloat(bar.css('top')) / railMinusBarHt;
                var heightScrolled = parseFloat(percentScroll*fullheight)+(cometchat_userscontent_ht*(1-percentScroll));
                var userHeight = $('.cometchat_userlist').outerHeight();
                var grpDividerHeight = $(".cometchat_subsubtitle").outerHeight(true);
                $('.cometchat_userlist').each(function(){
                    var diff = 0;
                    if($(this).find('.cometchat_msgcount').length>0){
                        var userHeightFromTop = 0;
                        activeChatboxesHeight = ($(this).parents().prevAll('#cometchat_activechatboxes_popup').outerHeight());
                        if(typeof(activeChatboxesHeight) != "number"){
                            activeChatboxesHeight =0;
                        }
                        userHeightFromTop = (userHeight * ($(this).prevAll('.cometchat_userlist').length)) + (grpDividerHeight *$(this).prevAll('.cometchat_subsubtitle').length)+ (grpDividerHeight *$(this).parent().prevAll('.cometchat_subsubtitle').length) + activeChatboxesHeight;
                        diff = Math.round(userHeightFromTop - heightScrolled);
                        if((diff > 0 && diff < mindiff && userHeightFromTop > cometchat_userscontent_ht)||(diff > 0 && mindiff == 0 && userHeightFromTop > cometchat_userscontent_ht)){
                            mindiff = diff;
                        }
                    }
                });
                if(mindiff<=0){
                    $("#cc_gotoNextNoti").hide();
                }else{
                    $("#cc_gotoNextNoti").show();
                }
            },
            calcPrevNoti: function(){
                var mindiff = 0;
                var bar =$('#cometchat_userstab_popup').find('.cometchat_tabcontent .slimScrollBar');
                var cometchat_userslist = $('#cometchat_userslist');
                var cometchat_activechatboxes_popup = $('#cometchat_activechatboxes_popup');
                var fullheight = cometchat_userslist.outerHeight() + cometchat_activechatboxes_popup.outerHeight();
                var cometchat_userscontent = $('#cometchat_userscontent');
                var cometchat_userscontent_ht = cometchat_userscontent.outerHeight();
                var railMinusBarHt =  (cometchat_userscontent_ht-bar.outerHeight());
                var percentScroll =  parseFloat(bar.css('top')) / railMinusBarHt;
                var heightScrolled = parseFloat(percentScroll*fullheight)-(cometchat_userscontent_ht*percentScroll);
                var userHeight = $('.cometchat_userlist').outerHeight();
                var grpDividerHeight = $(".cometchat_subsubtitle").outerHeight(true);
                $('.cometchat_userlist').each(function(){
                    var diff = 0;
                    if($(this).find('.cometchat_msgcount').length>0){

                        var userHeightFromTop = 0;
                        activeChatboxesHeight = ($(this).parents().prevAll('#cometchat_activechatboxes_popup').outerHeight());
                        if(typeof(activeChatboxesHeight) != "number"){
                            activeChatboxesHeight =0;
                        }
                        userHeightFromTop = (userHeight * ($(this).prevAll('.cometchat_userlist').length)) + (grpDividerHeight *$(this).prevAll('.cometchat_subsubtitle').length)+ (grpDividerHeight *$(this).parent().prevAll('.cometchat_subsubtitle').length) + activeChatboxesHeight;
                        diff = Math.round(heightScrolled - userHeightFromTop);
                        if((diff > 0 && diff < mindiff)||(diff > 0 && mindiff == 0)){
                            mindiff = Math.round(diff) ;
                        }
                    }
                });
                if(mindiff<=0){
                    $("#cc_gotoPrevNoti").hide();
                }else{
                    $("#cc_gotoPrevNoti").show();
                }
            },
            newAnnouncement: function(item){
                if($.cookie(settings.cookiePrefix+"popup")&&$.cookie(settings.cookiePrefix+"popup")=='true'){
                }else{
                    tooltipPriority = 100;
                    message = '<div class="cometchat_announcement">'+item.m+'</div>';
                    if(item.o){
                        var notifications = (item.o-1);
                        if(notifications>0){
                            message += '<div class="cometchat_notification" onclick="javascript:jqcc.cometchat.launchModule(\'announcements\')"><div class="cometchat_notification_message cometchat_notification_message_and">'+language[36]+notifications+language[37]+'</div><div style="clear:both" /></div>';
                        }
                    }else{
                        $.cookie(settings.cookiePrefix+"an", item.id, {path: '/', expires: 365});
                    }
                    jqcc[settings.theme].tooltip("cometchat_userstab", message, 0);
                    clearTimeout(notificationTimer);
                    notificationTimer = setTimeout(function(){
                        $('#cometchat_tooltip').css('display', 'none');
                        tooltipPriority = 0;
                    }, settings.announcementTime);
                }
            },
            buddyList: function(item){
                var onlineNumber = 0;
                var totalFriendsNumber = 0;
                var lastGroup = '';
                var groupNumber = 0;
                var tooltipMessage = '';
                var buddylisttemp = '';
                var buddylisttempavatar = '';
                $.each(item, function(i, buddy){
                    longname = buddy.n;
                    var usercontentstatus = buddy.s;
                    var icon = '';
                    if(buddy.d==1){
                        mobilestatus = 'mobile';
                        usercontentstatus = 'mobile cometchat_mobile_'+buddy.s;
                        icon = '<div class="cometchat_dot"></div>';
                    }
                    if(chatboxOpened[buddy.id]!=null){
                        $("#cometchat_user_"+buddy.id+"_popup").find("span.cometchat_userscontentdot")
                            .removeClass("cometchat_available")
                            .removeClass("cometchat_busy")
                            .removeClass("cometchat_offline")
                            .removeClass("cometchat_away")
                            .removeClass("cometchat_mobile")
                            .removeClass("cometchat_mobile_available")
                            .removeClass("cometchat_mobile_busy")
                            .removeClass("cometchat_mobile_offline")
                            .removeClass("cometchat_mobile_away")
                            .addClass("cometchat_"+usercontentstatus);
                        if(icon == ''){
                            $("#cometchat_user_"+buddy.id+"_popup").find("div.cometchat_dot").remove();
                        }else if($("#cometchat_user_"+buddy.id+"_popup").find("div.cometchat_dot").length<1){
                            $("#cometchat_user_"+buddy.id+"_popup").find("span.cometchat_userscontentdot").append(icon);
                        }
                        if($("#cometchat_user_"+buddy.id+"_popup").length>0){
                            $("#cometchat_user_"+buddy.id+"_popup").find("div.cometchat_userdisplaystatus").html(buddy.m);
                        }
                    }
                    if(buddy.s!='offline'){
                        onlineNumber++;
                    }
                    totalFriendsNumber++;
                    var group = '';
                    if(buddy.g!=lastGroup&&typeof buddy.g!="undefined"){
                        if(buddy.g==''){
                            groupName = language[40];
                        }else{
                            groupName = buddy.g;
                        }
                        if(groupNumber==0){
                            group = '<div class="cometchat_subsubtitle cometchat_subsubtitle_top"><hr class="hrleft">'+groupName+'<hr class="hrright"></div>';
                        }else{
                            group = '<div class="cometchat_subsubtitle"><hr class="hrleft">'+groupName+'<hr class="hrright"></div>';
                        }
                        groupNumber++;
                        lastGroup = buddy.g;
                    }
                    buddylisttemp += group+'<div id="cometchat_userlist_'+buddy.id+'" class="cometchat_userlist" onmouseover="jqcc(this).addClass(\'cometchat_userlist_hover\');" onmouseout="jqcc(this).removeClass(\'cometchat_userlist_hover\');"><span class="cometchat_userscontentavatar"><img class="cometchat_userscontentavatarimage" original="themes/'+settings.theme+'/images/cometchat_'+buddy.s+'.png"><span class="cometchat_userscontentdot cometchat_'+usercontentstatus+'">'+icon+'</span></span><div class="cometchat_chatboxDisplayDetails"><div class="cometchat_userdisplayname">'+longname+'</div><div class="cometchat_userdisplaystatus">'+buddy.m+'</div></div></div>';
                    buddylisttempavatar += group+'<div id="cometchat_userlist_'+buddy.id+'" class="cometchat_userlist" onmouseover="jqcc(this).addClass(\'cometchat_userlist_hover\');" onmouseout="jqcc(this).removeClass(\'cometchat_userlist_hover\');"><span class="cometchat_userscontentavatar"><img class="cometchat_userscontentavatarimage" original="'+buddy.a+'"><span class="cometchat_userscontentdot cometchat_'+usercontentstatus+'">'+icon+'</span></span><div class="cometchat_chatboxDisplayDetails"><div class="cometchat_userdisplayname">'+longname+'</div><div class="cometchat_userdisplaystatus">'+buddy.m+'</div></div></div>';
                    var message = '';
                    if(settings.displayOnlineNotification==1&&jqcc.cometchat.getExternalVariable('initialize')!=1&&jqcc.cometchat.getThemeArray('buddylistStatus', buddy.id)!=buddy.s&&buddy.s=='available'){
                        message = language[19];
                    }
                    if(settings.displayBusyNotification==1&&jqcc.cometchat.getExternalVariable('initialize')!=1&&jqcc.cometchat.getThemeArray('buddylistStatus', buddy.id)!=buddy.s&&buddy.s=='busy'){
                        message = language[21];
                    }
                    if(settings.displayOfflineNotification==1&&jqcc.cometchat.getExternalVariable('initialize')!=1&&jqcc.cometchat.getThemeArray('buddylistStatus', buddy.id)!='offline'&&buddy.s=='offline'){
                        message = language[20];
                    }
                    if(message!=''){
                        tooltipMessage += '<div class="cometchat_notification" onclick="javascript:jqcc.cometchat.chatWith(\''+buddy.id+'\')"><div class="cometchat_notification_avatar"><img class="cometchat_notification_avatar_image" src="'+buddy.a+'"></div><div class="cometchat_notification_message">'+buddy.n+message+'</div><div style="clear:both" /></div>';
                    }
                    jqcc.cometchat.setThemeArray('buddylistStatus', buddy.id, buddy.s);
                    jqcc.cometchat.setThemeArray('buddylistMessage', buddy.id, buddy.m);
                    jqcc.cometchat.setThemeArray('buddylistName', buddy.id, buddy.n);
                    jqcc.cometchat.setThemeArray('buddylistAvatar', buddy.id, buddy.a);
                    jqcc.cometchat.setThemeArray('buddylistLink', buddy.id, buddy.l);
                    jqcc.cometchat.setThemeArray('buddylistIsDevice', buddy.id, buddy.d);
                });
                if(groupNumber>0){
                    $('.cometchat_subsubtitle_siteusers').css('display', 'none');
                }
                var bltemp = buddylisttempavatar;
                jqcc.cometchat.setThemeVariable('showAvatar','1');
                if(totalFriendsNumber>settings.thumbnailDisplayNumber){
                    bltemp = buddylisttemp;
                    jqcc.cometchat.setThemeVariable('showAvatar','0');
                }
                if(document.getElementById('cometchat_userslist')){
                    if(bltemp!=''){
                        document.getElementById('cometchat_userslist').style.display = 'block';
                        jqcc.cometchat.replaceHtml('cometchat_userslist', '<div>'+bltemp+'</div>');
                    }else{
                        $('#cometchat_userslist').html('<div class="cometchat_nofriends">'+language[14]+'</div>');
                    }
                }
                if(jqcc.cometchat.getSessionVariable('buddylist')==1){
                    $(".cometchat_userscontentavatar").find("img").each(function(){
                        if($(this).attr('original')){
                            $(this).attr("src", $(this).attr('original'));
                            $(this).removeAttr('original');
                        }
                    });
                }
                jqcc[settings.theme].activeChatBoxes();
                $("#cometchat_user_search").keyup();
                $('div.cometchat_userlist').die('click');
                $('div.cometchat_userlist').live('click', function(e){
                    jqcc.cometchat.userClick(e.target);
                });
                $('#cometchat_userstab_text').html(language[9]+' ('+(onlineNumber+jqcc.cometchat.getThemeVariable('jabberOnlineNumber'))+')');
                siteOnlineNumber = onlineNumber;
                jqcc.cometchat.setThemeVariable('lastOnlineNumber', onlineNumber+jqcc.cometchat.getThemeVariable('jabberOnlineNumber'));
                if(tooltipMessage!=''&&!$('#cometchat_userstab_popup').hasClass('cometchat_tabopen')&&!$('#cometchat_optionsbutton_popup').hasClass('cometchat_tabopen')){
                    if(tooltipPriority<10){
                        if($.cookie(settings.cookiePrefix+"popup")&&$.cookie(settings.cookiePrefix+"popup")=='true'){
                        }else{
                            tooltipPriority = 108;
                            jqcc[settings.theme].tooltip("cometchat_userstab", tooltipMessage, 0);
                            clearTimeout(notificationTimer);
                            notificationTimer = setTimeout(function(){
                                $('#cometchat_tooltip').css('display', 'none');
                                tooltipPriority = 0;
                            }, settings.notificationTime);
                        }
                    }
                }
                var chatBoxesOrder = jqcc.cometchat.getThemeVariable('chatBoxesOrder');
                for (var key in chatBoxesOrder)
                {
                    if (chatBoxesOrder.hasOwnProperty(key) && parseInt(chatBoxesOrder[key])!=0)
                    {
                        if(typeof (jqcc[settings.theme].addPopup)!=='undefined'){
                            jqcc[settings.theme].addPopup(key, parseInt(chatBoxesOrder[key]), 0);
                        }
                    }
                }
            },
            loggedOut: function(){
                document.title = jqcc.cometchat.getThemeVariable('documentTitle');
				cometchat_lefttab = $('#cometchat_lefttab').detach();
                cometchat_righttab = $('#cometchat_righttab').detach();
                if(settings.ccauth.enabled=="1"){
                    var ccauthpopup = '<div class="cc_overlay" onclick=""></div><div id="cometchat_social_login"><div class="login_container"><div class="login_image_container"><p>'+language[93]+'</p>';
                    var authactive = settings.ccauth.active;
                    authactive.forEach(function(auth) {
                        ccauthpopup += '<img onclick="window.open(\''+baseUrl+'functions/login/signin.php?network='+auth.toLowerCase()+'\',\'socialwindow\')" src="'+baseUrl+'themes/mobile/images/login'+auth.toLowerCase()+'.png" class="auth_options"></img>';
                    });
                    ccauthpopup += '</div></div></div>';
                    $('#cometchat').html(ccauthpopup);
                }else{
                    $('#cometchat').html('<div id="cometchat_loggedout_container"><div id="cometchat_loggedout"><div><img class="cometchat_loggedout_icon" src="'+baseUrl+'themes/'+settings.theme+'/images/exclamation.png" /></div><div>'+language[8]+'</div></div></div>');
                }
            },
            userStatus: function(item){
                var usercontentstatus = item.s;
                var icon = '';
                if(usercontentstatus=='invisible'){
                    usercontentstatus = 'offline';
                }
                if(item.d==1){
                    usercontentstatus = 'mobile cometchat_mobile_'+usercontentstatus;
                    icon = '<div class="cometchat_dot"></div>';
                }
                var userDetails = '<div id="cometchat_self"><span class="cometchat_userscontentavatar"><img class="cometchat_userscontentavatarimage" src="'+item.a+'"><span class="cometchat_userscontentdot cometchat_'+usercontentstatus+'">'+icon+'</span></span><div id="cometchat_selfDetails"><div class="cometchat_userdisplayname">'+item.n+'</div><div class="cometchat_userdisplaystatus">'+item.m+'</div></div></div>';
                var cometchat_optionsbutton_popup = $('#cometchat_optionsbutton_popup');
                cometchat_optionsbutton_popup.find('textarea.cometchat_statustextarea').val(item.m);
                if(item.s=='offline'){
                    jqcc[settings.theme].goOffline(1);
                }else{
                    jqcc[settings.theme].removeUnderline();
                    jqcc[settings.theme].updateStatus(item.s);
                }
                if(item.id>10000000){
                    $("#guestsname").show();
                    $("#guestsname").find("input.cometchat_guestnametextbox").val((item.n).replace("<?php echo $guestnamePrefix;?>-", ""));
                    cometchat_optionsbutton_popup.find("div.cometchat_tabsubtitle").html(language[45]);
                }
                jqcc.cometchat.setThemeVariable('userid', item.id);
                jqcc.cometchat.setThemeVariable('currentStatus',item.s);
                jqcc.cometchat.setThemeArray('buddylistStatus', item.id, item.s);
                jqcc.cometchat.setThemeArray('buddylistMessage', item.id, item.m);
                jqcc.cometchat.setThemeArray('buddylistName', item.id, item.n);
                jqcc.cometchat.setThemeArray('buddylistAvatar', item.id, item.a);
                jqcc.cometchat.setThemeArray('buddylistLink', item.id, item.l);
                $('#cometchat_self_left').html(userDetails);
                $('#cometchat_welcome_username').text(item.n);
            },
            typingTo: function(item){
                $("span.cometchat_typing").css('display', 'none');
                var typingIds = item.split(',');
                var t = typingIds.length;
                while(t>-1){
                    $("#cometchat_typing_"+typingIds[t]).css('display', 'block');
                    t--;
                }
            },
            createChatboxData: function(id, name, status, message, avatar, link, isdevice, silent, tryOldMessages){
                jqcc[settings.theme].hideMenuPopup();
                if(jqcc.cometchat.getThemeVariable('trayOpen')!='chatrooms'){
                    $('#currentroom').hide();
                }
                var cometchat_user_popup = $("#cometchat_user_"+id+"_popup");
                if(typeof(cometchat_user_popup)=='undefined' || cometchat_user_popup.length<1){
                    shortname = name;
                    longname = name;
                    var usercontentstatus = status;
                    var icon = '';
                    if(jqcc.cometchat.getThemeArray('buddylistIsDevice', id) == '1'){
                        usercontentstatus = 'mobile cometchat_mobile_'+status;
                        icon = '<div class="cometchat_dot"></div>';
                    }
                    var pluginshtml = '';
                    var avchathtml = '';
                    var smilieshtml = '';
                    var filetransferhtml = '';
                    if(jqcc.cometchat.getThemeArray('isJabber', id)!=1){
                        var pluginslength = settings.plugins.length;
                        if(pluginslength>0){
                            for(var i = 0; i<pluginslength; i++){
                                var name = 'cc'+settings.plugins[i];
                                if(settings.plugins[i]=='avchat'){
                                    avchathtml='<div class="cometchat_menuOption cometchat_avchatOption"><img class="ccplugins  cometchat_menuOptionIcon" src="'+baseUrl+'themes/'+settings.theme+'/images/avchaticon.png" title="'+$[name].getTitle()+'" name="'+name+'" to="'+id+'" chatroommode="0" /></div>';
                                }else if(settings.plugins[i]=='smilies'){
                                    smilieshtml='<div class="ccplugins cometchat_smilies" title="'+$[name].getTitle()+'" name="'+name+'" to="'+id+'" chatroommode="0" >&#9786;</div>';
                                }else if(settings.plugins[i]=='filetransfer'){
                                    filetransferhtml='<img src="'+baseUrl+'themes/'+settings.theme+'/images/attachment.png" class="ccplugins cometchat_transfericon cometchat_filetransfer" title="'+$[name].getTitle()+'" name="'+name+'" to="'+id+'" chatroommode="0"/>';
                                }else if(typeof ($[name])=='object'){
                                    if(pluginshtml == ""){
                                        pluginshtml = '<div class="cometchat_menuOption cometchat_pluginsOption" title="'+language[95]+'"><img class="cometchat_pluginsIcon cometchat_menuOptionIcon" src="'+baseUrl+'themes/'+settings.theme+'/images/pluginsicon.png"/><div class="cometchat_plugins menuOptionPopup cometchat_tabpopup cometchat_dropdownpopup"><div class="cometchat_optionstriangle"></div><div class="cometchat_optionstriangle cometchat_optionsinnertriangle"></div><div id="plugin_container">';
                                    }
                                    if(name!='ccchattime'){
                                        pluginshtml += '<div class="ccplugins cometchat_pluginsicon cometchat_'+settings.plugins[i]+'" title="'+$[name].getTitle()+'" name="'+name+'" to="'+id+'" chatroommode="0"><span>'+$[name].getTitle()+'</span></div>';
                                    }
                                }
                            }
                            pluginshtml += '</div></div></div>';
                        }
                    }

                    var startlink = '';
                    var endlink = '';
                    if(link!=''){
                        startlink = '<a href="'+link+'">';
                        endlink = '</a>';
                    }
                    var prepend = '';
                    var jabber = jqcc.cometchat.getThemeArray('isJabber', id);

                    if(jqcc.cometchat.getThemeVariable('prependLimit') != '0' && jabber != 1){
                        prepend = '<div class=\"cometchat_prependMessages\" onclick\="jqcc.synergy.prependMessagesInit('+id+')\" id = \"cometchat_prependMessages_'+id+'\">'+language[83]+'</div>';
                    }
                    var avatarsrc = '';
                    if(avatar!=''){
                        avatarsrc = '<div class="cometchat_userscontentavatar">'+startlink+'<img src="'+avatar+'" class="cometchat_userscontentavatarimage" />'+endlink+'<span class="cometchat_userscontentdot cometchat_'+usercontentstatus+'">'+icon+'</span></div>';
                    }

                    $("<div/>").attr("id", "cometchat_user_"+id+"_popup").addClass("cometchat_userchatbox").addClass("cometchat_tabpopup").css('display', 'none').html('<div class="cometchat_tabsubtitle"><div class="cometchat_chatboxLeftDetails">'+avatarsrc+'<div class="cometchat_chatboxDisplayDetails"><div class="cometchat_userdisplayname">'+startlink+longname+endlink+'</div><div class="cometchat_userdisplaystatus">'+message+'</div></div></div><div class="cometchat_user_closebox" title="Close Chat Box">X</div><div class="cometchat_chatboxMenuOptions">'+avchathtml+pluginshtml+'</div></div>'+prepend+'<div class="cometchat_tabcontent"><div class="cometchat_tabcontenttext" id="cometchat_tabcontenttext_'+id+'"><div class="cometchat_message_container"></div></div><div class="cometchat_tabinputcontainer">'+filetransferhtml+'<div class="cometchat_tabcontentsubmit cometchat_sendicon" title="Send"></div><div class="cometchat_tabcontentinput">'+smilieshtml+'<div style="margin-right:28px;"><textarea class="cometchat_textarea"></textarea></div></div></div></div>').appendTo($("#cometchat_righttab"));
                    cometchat_user_popup = $("#cometchat_user_"+id+"_popup");
                    if(jqcc().slimScroll){
                        cometchat_user_popup.find(".cometchat_tabcontenttext").slimScroll({height: 'auto',width: 'auto'});
                        cometchat_user_popup.find("#plugin_container").slimScroll({width: 'auto'});
                    }
                    cometchat_user_popup.find("textarea.cometchat_textarea").keydown(function(event){
                        return jqcc[settings.theme].chatboxKeydown(event, this, id);
                    });
                    cometchat_user_popup.find("div.cometchat_tabcontentsubmit").click(function(event){
                        jqcc[settings.theme].chatboxKeydown(event, cometchat_user_popup.find("textarea.cometchat_textarea"), id, 1);
                        jqcc[settings.theme].chatboxKeyup(event, cometchat_user_popup.find("textarea.cometchat_textarea"), id);
                    });
                    cometchat_user_popup.find("textarea.cometchat_textarea").keyup(function(event){
                        return jqcc[settings.theme].chatboxKeyup(event, this, id);
                    });
                    var cometchat_user_id = $("#cometchat_user_"+id);
                    cometchat_user_popup.find('.ccplugins').click(function(event){
                        event.stopImmediatePropagation();
                        var name = $(this).attr('name');
                        var to = $(this).attr('to');
                        var chatroommode = $(this).attr('chatroommode');
                        if(window.top == window.self) {
                            var controlparameters = {"to":to, "chatroommode":chatroommode};
                            jqcc[name].init(controlparameters);
                        } else {
                            var controlparameters = {"type":"plugins", "name":name, "method":"init", "params":{"to":to, "chatroommode":chatroommode}};
                            controlparameters = JSON.stringify(controlparameters);
                            parent.postMessage('CC^CONTROL_'+controlparameters,'*');
                        }
                    });
                    cometchat_user_popup.find('div.cometchat_user_closebox').mouseenter(function(){
                        $(this).addClass("cometchat_user_closebox_hover");
                    });
                    cometchat_user_popup.find('div.cometchat_user_closebox').mouseleave(function(){
                        $(this).removeClass("cometchat_user_closebox_hover");
                    });
                    cometchat_user_popup.find('div.cometchat_user_closebox').click(function(){
                        var chatboxid = cometchat_user_popup.attr('id').split('_')[2];
                        $('#cometchat_userlist_'+chatboxid).show();
                        cometchat_user_popup.remove();
                        jqcc.cometchat.unsetThemeArray('chatBoxesOrder', chromeReorderFix+id);
                        var chatBoxesOrder = jqcc.cometchat.getThemeVariable('chatBoxesOrder');
                        if(jqcc.isEmptyObject(chatBoxesOrder)&&$.cookie(settings.cookiePrefix+'chatroom')!=null&&$.cookie(settings.cookiePrefix+'chatroom')!='' && hasChatroom=='1'){
                            var cc_chatroom = base64_decode($.cookie(settings.cookiePrefix+'chatroom'));
                            var cc_chatroomdetails = cc_chatroom.split(/:/);
                            jqcc.cometchat.chatroom(cc_chatroomdetails[0],cc_chatroomdetails[2],'0',cc_chatroomdetails[1],'1','1');
                        }else{
                            var nextChatBox;
                            for(chatBoxId in chatBoxesOrder){
                                nextChatBox = chatBoxId.replace('_','');
                            }
                            chatboxOpened[id] = null;
                            $("#cometchat_user_"+nextChatBox+"_popup").addClass('cometchat_tabopen');
                            jqcc[settings.theme].addPopup(nextChatBox,0,0);
                            jqcc.cometchat.setThemeVariable('openChatboxId', nextChatBox);
                            jqcc.cometchat.setSessionVariable('openChatboxId', nextChatBox);
                        }
                        if(settings.extensions.indexOf('ads') > -1){
                            jqcc.ccads.init();
                        }
                    $('.cometchat_noactivity').css('display','block');
                        jqcc[settings.theme].activeChatBoxes();
                        jqcc.cometchat.orderChatboxes();
                    });
                    cometchat_user_popup.find('.cometchat_pluginsOption').click(function(){
                        $(this).find('.cometchat_menuOptionIcon').toggleClass('cometchat_menuOptionIconClick');
                        $('.cometchat_plugins').toggleClass('cometchat_tabopen');
                    });
                    jqcc[settings.theme].scrollDown(id);
                    if(jqcc.cometchat.getInternalVariable('updatingsession')!=1){
                        cometchat_user_popup.find("textarea.cometchat_textarea").focus();
                    }
                    if(jqcc.cometchat.getExternalVariable('initialize')!=1||isNaN(id)){
                        jqcc[settings.theme].updateChatbox(id);
                    }
                    cometchat_user_popup.css('left', 0);
                }
                if(jqcc.cometchat.getThemeVariable('openChatboxId')==id&&jqcc.cometchat.getThemeVariable('trayOpen')!='chatrooms'){
                    jqcc.cometchat.unsetThemeArray('chatBoxesOrder', chromeReorderFix+id);
                    $('#cometchat_user_'+jqcc.cometchat.getSessionVariable('openChatboxId')+'_popup').removeClass('cometchat_tabopen');
                    jqcc.cometchat.setSessionVariable('openChatboxId', id);
                    cometchat_user_popup.addClass('cometchat_tabopen');
                }
                if(settings.extensions.indexOf('ads') > -1){
                    jqcc.ccads.init();
                }
                jqcc.cometchat.setThemeArray('chatBoxesOrder', chromeReorderFix+id, 0);
                chatboxOpened[id] = 0;
                jqcc.cometchat.orderChatboxes();
                jqcc[settings.theme].activeChatBoxes();
                jqcc.cometchat.setThemeArray('trying', id, 5);
                jqcc[settings.theme].windowResize();
                $('.cometchat_noactivity').css('display','none');
            },
            activeChatBoxes: function(){
                $('.cometchat_menuOptionIcon').removeClass('cometchat_menuOptionIconClick');
                var chatBoxesOrder = jqcc.cometchat.getThemeVariable('chatBoxesOrder');
                var openChatboxId = jqcc.cometchat.getThemeVariable('openChatboxId');
                var oneononeflag = '0';
                var cometchat_activechatboxes = '';
                for(chatBoxId in chatBoxesOrder){
                    chatBoxId = chatBoxId.replace('_','');
                    oneononeflag = '1';
                    var userstatus = jqcc.cometchat.getThemeArray('buddylistStatus', chatBoxId);
                    var usercontentstatus = userstatus;
                    var icon = '';
                    if(jqcc.cometchat.getThemeArray('buddylistIsDevice', chatBoxId)==1){
                        mobilestatus = 'mobile';
                        usercontentstatus = 'mobile cometchat_mobile_'+userstatus;
                        icon = '<div class="cometchat_dot"></div>';
                    }
                    if(jqcc.cometchat.getThemeVariable('showAvatar')==0){
                        cometchat_activechatboxes = '<div id="cometchat_activech_'+chatBoxId+'" class="cometchat_userlist" onmouseover="jqcc(this).addClass(\'cometchat_userlist_hover\');" onmouseout="jqcc(this).removeClass(\'cometchat_userlist_hover\');"><span class="cometchat_userscontentavatar"><img class="cometchat_userscontentavatarimage" src="themes/'+settings.theme+'/images/cometchat_'+userstatus+'.png"><span class="cometchat_userscontentdot cometchat_'+usercontentstatus+'">'+icon+'</span></span><div class="cometchat_chatboxDisplayDetails"><div class="cometchat_userdisplayname">'+jqcc.cometchat.getThemeArray('buddylistName', chatBoxId)+'</div><div class="cometchat_userdisplaystatus">'+jqcc.cometchat.getThemeArray('buddylistMessage', chatBoxId)+'</div></div></div>'+cometchat_activechatboxes;
                    }else{
                        if(typeof(jqcc.cometchat.getThemeArray('buddylistAvatar', chatBoxId)) != 'undefined') {
                            cometchat_activechatboxes = '<div id="cometchat_activech_'+chatBoxId+'" class="cometchat_userlist" onmouseover="jqcc(this).addClass(\'cometchat_userlist_hover\');" onmouseout="jqcc(this).removeClass(\'cometchat_userlist_hover\');"><span class="cometchat_userscontentavatar"><img class="cometchat_userscontentavatarimage" src="'+jqcc.cometchat.getThemeArray('buddylistAvatar', chatBoxId)+'"><span class="cometchat_userscontentdot cometchat_'+usercontentstatus+'">'+icon+'</span></span><div class="cometchat_chatboxDisplayDetails"><div class="cometchat_userdisplayname">'+jqcc.cometchat.getThemeArray('buddylistName', chatBoxId)+'</div><div class="cometchat_userdisplaystatus">'+jqcc.cometchat.getThemeArray('buddylistMessage', chatBoxId)+'</div></div></div>'+cometchat_activechatboxes;
                        }
                    }
                }
                if(oneononeflag=='1'){
                    cometchat_activechatboxes = '<div style="font-weight:bold;" class="cometchat_subsubtitle"><hr style="height:3px;" class="hrleft">'+language[86]+'<hr style="height:3px;" class="hrright"></div>'+cometchat_activechatboxes;
                    if($('#cometchat_allusers').length<1){
                        $('#cometchat_userslist').prepend('<div class="cometchat_subsubtitle" style="font-weight:bold;" id="cometchat_allusers"><hr style="height:3px;" class="hrleft">'+language[87]+'<hr style="height:3px;" class="hrright"></div>');
                    }
                }else{
                    $('#cometchat_allusers').remove();
                }
                $('#cometchat_activechatboxes_popup').html(cometchat_activechatboxes);

                var chatBoxesOrder = jqcc.cometchat.getThemeVariable('chatBoxesOrder');
                for (var key in chatBoxesOrder)
                {
                    if (chatBoxesOrder.hasOwnProperty(key) && parseInt(chatBoxesOrder[key])!=0)
                    {
                        if(typeof (jqcc[settings.theme].addPopup)!=='undefined'){
                            jqcc[settings.theme].addPopup(key, parseInt(chatBoxesOrder[key]), 0);
                        }
                    }
                }
            },
            addMessages: function(item){
                $.each(item, function(i, incoming){
                    if(typeof(incoming.self) ==='undefined' && typeof(incoming.old) ==='undefined' && typeof(incoming.sent) ==='undefined'){
                        incoming.sent = Math.floor(new Date().getTime()/1000);
                        incoming.old = incoming.self = 1;
                    }
                    if(typeof(incoming.m)!== 'undefined'){
                        incoming.message = incoming.m;
                    }
                    var message = jqcc.cometchat.processcontrolmessage(incoming);
                    if(message == null || message == ""){
                        return;
                    }
                    if(typeof(incoming.nopopup) === "undefined" || incoming.nopopup =="") {
                        incoming.nopopup = 0;
                    }
                    if(incoming.self ==1 ){
                         incoming.nopopup = 1;
                    }
                    checkfirstmessage = ($("#cometchat_tabcontenttext_"+incoming.from+" .cometchat_chatboxmessage").length) ? 0 : 1;
                    var shouldPop = 0;
                    if($('#cometchat_user_'+incoming.from).length == 0){
                            shouldPop = 1;
                    }
                    if(jqcc.cometchat.getThemeArray('trying', incoming.from)===undefined){
                        if(typeof (jqcc[settings.theme].createChatbox)!=='undefined' && incoming.nopopup == 0){
                            jqcc[settings.theme].createChatbox(incoming.from, jqcc.cometchat.getThemeArray('buddylistName', incoming.from), jqcc.cometchat.getThemeArray('buddylistStatus', incoming.from), jqcc.cometchat.getThemeArray('buddylistMessage', incoming.from), jqcc.cometchat.getThemeArray('buddylistAvatar', incoming.from), jqcc.cometchat.getThemeArray('buddylistLink', incoming.from), jqcc.cometchat.getThemeArray('buddylistIsDevice', incoming.from), 1, 1);
                        }
                    }
                    if(jqcc.cometchat.getThemeArray('buddylistName', incoming.from)==null||jqcc.cometchat.getThemeArray('buddylistName', incoming.from)==''){
                        if(jqcc.cometchat.getThemeArray('trying', incoming.from)<5){
                            setTimeout(function(){
                                if(typeof (jqcc[settings.theme].addMessages)!=='undefined'){
                                    jqcc[settings.theme].addMessages([{"from": incoming.from, "message": incoming.message, "self": incoming.self, "old": incoming.old, "id": incoming.id, "sent": incoming.sent}]);
                                }
                            }, 2000);
                        }
                    }else{
                        var selfstyle = '';
                        var fromavatar = '';
                        if(parseInt(incoming.self)==1){
                            fromname = language[10];
                            selfstyle = ' cometchat_self';
                        }else{
                            fromname = jqcc.cometchat.getThemeArray('buddylistName', incoming.from);
                            fromavatar = '<img class="cometchat_userscontentavatarsmall" src="'+jqcc.cometchat.getThemeArray('buddylistAvatar', incoming.from)+'">';
                        }
                        if(incoming.self!=1){
                            if($.cookie(settings.cookiePrefix+"sound")&&$.cookie(settings.cookiePrefix+"sound")=='true'){
                            }else{
                                if(incoming.old!=1){
                                    jqcc[settings.theme].playSound();
                                }
                            }
                        }
                        separator = ':&nbsp;&nbsp;';
                        if($("#cometchat_message_"+incoming.id).length>0){
                            $("#cometchat_message_"+incoming.id).find(".cometchat_chatboxmessagecontent").html(incoming.message);
                        }else{
                            sentdata = '';
                            if(incoming.sent!=null){
                                var ts = incoming.sent;
                                sentdata = jqcc[settings.theme].getTimeDisplay(ts, incoming.from);
                            }
                            if(!settings.fullName){
                                fromname = fromname.split(" ")[0];
                            }
                            var msg = jqcc[settings.theme].processMessage('<div class="cometchat_messagebox">'+fromavatar+sentdata+'<div class="cometchat_chatboxmessage'+selfstyle+'" id="cometchat_message_'+incoming.id+'"><div class="cometchat_messagearrow"></div><span class="cometchat_chatboxmessagecontent">'+message+'</span></div></div>', selfstyle);
                            $("#cometchat_user_"+incoming.from+"_popup").find("div.cometchat_tabcontenttext").find('.cometchat_message_container').append(msg);
                            $("#cometchat_typing_"+incoming.from).css('display', 'none');
                            jqcc[settings.theme].scrollDown(incoming.from);
                            var nowTime = new Date();
                            var idleDifference = Math.floor(nowTime.getTime()/1000)-jqcc.cometchat.getThemeVariable('idleTime');
                            if(idleDifference>5){
                                if(settings.windowTitleNotify==1){
                                    document.title = language[15];
                                }
                            }
                        }
                        if(jqcc.cometchat.getThemeVariable('openChatboxId')!=incoming.from&&incoming.old!=1){
                            jqcc[settings.theme].addPopup(incoming.from, 1, 1);
                        }
                    }
                    var newMessage = 0;
                    if((jqcc.cometchat.getThemeVariable('isMini')==1||(jqcc.cometchat.getThemeVariable('openChatboxId')!=incoming.from))&&incoming.self!=1&&settings.desktopNotifications==1&&incoming.old==0){
                        var callChatboxEvent = function(){
                            if(typeof incoming.from!='undefined'){
                                for(x in desktopNotifications){
                                    for(y in desktopNotifications[x]){
                                        desktopNotifications[x][y].close();
                                    }
                                }
                                desktopNotifications = {};
                                if(jqcc.cometchat.getThemeVariable('isMini')==1){
                                    window.focus();
                                }
                                jqcc.cometchat.chatWith(incoming.from);
                            }
                        };
                        if(typeof desktopNotifications[incoming.from]!='undefined'){
                            var newMessageCount = 0;
                            for(x in desktopNotifications[incoming.from]){
                                ++newMessageCount;
                                desktopNotifications[incoming.from][x].close();
                            }
                            jqcc.cometchat.notify((++newMessageCount)+' '+language[46]+' '+jqcc.cometchat.getThemeArray('buddylistName', incoming.from), jqcc.cometchat.getThemeArray('buddylistName', incoming.from), language[47], callChatboxEvent, incoming.from, incoming.id);
                        }else{
                            jqcc.cometchat.notify(language[48]+' '+jqcc.cometchat.getThemeArray('buddylistName', incoming.from), jqcc.cometchat.getThemeArray('buddylistAvatar', incoming.from), message, callChatboxEvent, incoming.from, incoming.id);
                        }
                    }
                });
            },
            statusSendMessage: function(){
                var message = $("#cometchat_optionsbutton_popup").find("textarea.cometchat_statustextarea").val();
                var oldMessage = jqcc.cometchat.getThemeArray('buddylistMessage', jqcc.cometchat.getThemeVariable('userid'));
                if(message!=''&&oldMessage!=message){
                    $('div.cometchat_statusbutton').html('<img src="'+baseUrl+'images/loader.gif" width="16">');
                    jqcc.cometchat.setThemeArray('buddylistMessage', jqcc.cometchat.getThemeVariable('userid'), message);
                    jqcc.cometchat.statusSendMessageSet(message);
                }else{
                    $('div.cometchat_statusbutton').text('<?php echo $language[57]; ?>');
                    setTimeout(function(){
                        $('div.cometchat_statusbutton').text('<?php echo $language[22]; ?>');
                    }, 1500);
                }
            },
            statusSendMessageSuccess: function(){
                setTimeout(function(){
                    $('div.cometchat_statusbutton').text('<?php echo $language[49]; ?>');
                }, 1800);
                setTimeout(function(){
                    $('div.cometchat_statusbutton').text('<?php echo $language[22]; ?>');
                    $('#cometchat_selfDetails .cometchat_userdisplaystatus').text($('.cometchat_statustextarea').val());
                }, 2500);
            },
            statusSendMessageError: function(){
                setTimeout(function(){
                    $('div.cometchat_statusbutton').text('<?php echo $language[50]; ?>');
                }, 1800);
                setTimeout(function(){
                    $('div.cometchat_statusbutton').text('<?php echo $language[22]; ?>');
                }, 2500);
            },
            setGuestName: function(guestnametextarea){
                var guestname = $("#cometchat_optionsbutton_popup").find("input.cometchat_guestnametextbox").val();
                var oldguestname = jqcc.cometchat.getThemeArray('buddylistName', jqcc.cometchat.getThemeVariable('userid'));
                if(guestname!=''&&oldguestname!=guestname){
                    $('div.cometchat_guestnamebutton').html('<img src="'+baseUrl+'images/loader.gif" width="16">');
                    jqcc.cometchat.setThemeArray('buddylistName', jqcc.cometchat.getThemeVariable('userid'), guestname);
                    jqcc.cometchat.setGuestNameSet(guestname, guestnametextarea);
                }else{
                    $('div.cometchat_guestnamebutton').text('<?php echo $language[57]; ?>');
                    setTimeout(function(){
                        $('div.cometchat_guestnamebutton').text('<?php echo $language[44]; ?>');
                    }, 1500);
                }
            },
            setGuestNameSuccess: function(guestnametextarea){
                $(guestnametextarea).blur();
                setTimeout(function(){
                    $('div.cometchat_guestnamebutton').text('<?php echo $language[49]; ?>');
                }, 1800);
                setTimeout(function(){
                    $('div.cometchat_guestnamebutton').text('<?php echo $language[44]; ?>');
                }, 2500);
            },
            setGuestNameError: function(){
                setTimeout(function(){
                    $('div.cometchat_guestnamebutton').text('<?php echo $language[50]; ?>');
                }, 1800);
                setTimeout(function(){
                    $('div.cometchat_guestnamebutton').text('<?php echo $language[44]; ?>');
                }, 2500);
            },
            removeUnderline: function(){
                $("#cometchat_optionsbutton_popup").find("span.busy").css('text-decoration', 'none');
                $("#cometchat_optionsbutton_popup").find("span.invisible").css('text-decoration', 'none');
                $("#cometchat_optionsbutton_popup").find("span.offline").css('text-decoration', 'none');
                $("#cometchat_optionsbutton_popup").find("span.available").css('text-decoration', 'none');
                jqcc[settings.theme].removeUnderline2();
            },
            removeUnderline2: function(){
                $("#cometchat_self .cometchat_userscontentdot").removeClass('cometchat_available');
                $("#cometchat_self .cometchat_userscontentdot").removeClass('cometchat_busy');
                $("#cometchat_self .cometchat_userscontentdot").removeClass('cometchat_invisible');
                $("#cometchat_self .cometchat_userscontentdot").removeClass('cometchat_offline');
                $("#cometchat_self .cometchat_userscontentdot").removeClass('cometchat_away');
            },
            updateStatus: function(status){
                $("#cometchat_self .cometchat_userscontentdot").addClass('cometchat_'+status);
                $('span.cometchat_optionsstatus.'+status).css('text-decoration', 'underline');
                var userid = jqcc.cometchat.getUserID();
                jqcc.cometchat.getUserDetails(userid);
                $('#cometchat_selfDetails .cometchat_userdisplaystatus').text(jqcc.cometchat.getThemeArray('buddylistMessage', userid));
            },
            goOffline: function(silent){
                jqcc.cometchat.setThemeVariable('offline', 1);
                if(silent!=1){
                    jqcc.cometchat.sendStatus('offline');
                }else{
                    jqcc[settings.theme].updateStatus('offline');
                 }
                if(hasChatroom=='1'){
                    jqcc[settings.theme].chatroomOffline();
                }
                $('#cometchat_userstab_popup').removeClass('cometchat_tabopen');
                $('#cometchat_userstab').removeClass('cometchat_tabclick');
                $('#cometchat_optionsbutton_popup').removeClass('cometchat_tabopen');
                $('#cometchat_optionsbutton').removeClass('cometchat_tabclick');
                var chatBoxesOrder = jqcc.cometchat.getThemeVariable('chatBoxesOrder');
                for(chatBoxId in chatBoxesOrder){
                    $("#cometchat_user"+chatBoxId+"_popup").remove();
                    jqcc.cometchat.unsetThemeArray('chatBoxesOrder',chatBoxId);
                }
                $('#currentroom').find('div.cometchat_user_closebox').click();
                jqcc.cometchat.orderChatboxes();
                jqcc.cometchat.setThemeVariable('openChatboxId', '');
                jqcc.cometchat.setSessionVariable('openChatboxId', '');
                $('.cometchat_offline_overlay').css('display','table');
                if(typeof window.cometuncall_function=='function'){
                    cometuncall_function(cometid);
                }
                $('.cometchat_noactivity').css('display','none');
                jqcc.cometchat.setChatroomVars('newMessages',0);
                jqcc.synergy.activeChatBoxes();
            },
            tryAddMessages: function(id, atleastOneNewMessage){
                if(jqcc.cometchat.getThemeArray('buddylistName', id)==null||jqcc.cometchat.getThemeArray('buddylistName', id)==''){
                    if(jqcc.cometchat.getThemeArray('trying', id)<5){
                        setTimeout(function(){
                            if(typeof (jqcc[settings.theme].tryAddMessages)!=='undefined'){
                                jqcc[settings.theme].tryAddMessages(id, atleastOneNewMessage);
                            }
                        }, 1000);
                    }
                }else{
                    $("#cometchat_typing_"+id).css('display', 'none');
                    jqcc[settings.theme].scrollDown(id);
                    chatboxOpened[id] = 1;
                    if(atleastOneNewMessage==1){
                        var nowTime = new Date();
                        var idleDifference = Math.floor(nowTime.getTime()/1000)-jqcc.cometchat.getThemeVariable('idleTime');
                        if(idleDifference>5){
                            document.title = language[15];
                        }
                    }
                    if($.cookie(settings.cookiePrefix+"sound")&&$.cookie(settings.cookiePrefix+"sound")=='true'){
                    }else{
                        if(atleastOneNewMessage==1){
                            jqcc[settings.theme].playSound();
                        }
                    }
                }
            },
            countMessage: function(){
                if(jqcc.cometchat.getThemeVariable('loggedout')==0){
                    var cc_state = $.cookie(settings.cookiePrefix+'state');
                    jqcc.cometchat.setInternalVariable('updatingsession', '1');
                    if(cc_state!=null){
                        var cc_states = cc_state.split(/:/);
                        if(jqcc.cometchat.getThemeVariable('offline')==0){
                            var value = 0;
                            if(cc_states[0]!=' '&&cc_states[0]!=''){
                                value = cc_states[0];
                            }
                            if((value==0&&$('#cometchat_userstab').hasClass("cometchat_tabclick"))||(value==1&&!($('#cometchat_userstab').hasClass("cometchat_tabclick")))){
                                $('#cometchat_userstab').click();
                            }
                            value = '';
                            if(cc_states[1]!=' '&&cc_states[1]!=''){
                                value = cc_states[1];
                            }
                            if(value==jqcc.cometchat.getSessionVariable('activeChatboxes')){
                                var newActiveChatboxes = {};
                                if(value!=''){
                                    var badge = 0;
                                    var chatboxData = value.split(/,/);
                                    for(i = 0; i<chatboxData.length; i++){
                                        var chatboxIds = chatboxData[i].split(/\|/);
                                        newActiveChatboxes[chatboxIds[0]] = chatboxIds[1];
                                        badge += parseInt(chatboxIds[1]);
                                    }
                                    favicon.badge(badge);
                                }
                            }
                        }
                    }
                }
            },
            resynch: function(){
                if(jqcc.cometchat.getThemeVariable('loggedout')==0){
                    var cc_state = $.cookie(settings.cookiePrefix+'state');
                    jqcc.cometchat.setInternalVariable('updatingsession', '1');
                    if(cc_state!=null){
                        var cc_states = cc_state.split(/:/);
                        if(jqcc.cometchat.getThemeVariable('offline')==0){
                            $('.cometchat_offline_overlay').css('display','none');
                            var value = 0;
                            if(cc_states[0]!=' '&&cc_states[0]!=''){
                                value = cc_states[0];
                            }
                            if((value==0&&$('#cometchat_userstab').hasClass("cometchat_tabclick"))||(value==1&&!($('#cometchat_userstab').hasClass("cometchat_tabclick")))){
                                $('#cometchat_userstab').click();
                            }else if(hasChatroom==1&&((value==1&&$('#cometchat_chatroomstab').hasClass("cometchat_tabclick"))||(value==0&&!($('#cometchat_chatroomstab').hasClass("cometchat_tabclick"))))) {
                                $('#cometchat_chatroomstab').click();
                            }
                            if(typeof($.cookie(settings.cookiePrefix+'chatroom'))!=='undefined' && hasChatroom==1 && $.cookie(settings.cookiePrefix+'chatroom')!=null && $.cookie(settings.cookiePrefix+'chatroom')!=''){
                                if(cc_states[5]=='chatrooms'&&jqcc.cometchat.getThemeVariable('trayOpen')!=cc_states[5]){
                                    jqcc.cometchat.setThemeVariable('trayOpen',cc_states[5]);
                                    var cc_chatroom = base64_decode($.cookie(settings.cookiePrefix+'chatroom'));
                                    var cc_chatroomdetails = cc_chatroom.split(/:/);
                                    if(typeof(cc_chatroomdetails[0])!='undefined' && typeof(cc_chatroomdetails[1])!='undefined' && typeof(cc_chatroomdetails[2])!='undefined'&&jqcc.cometchat.getThemeVariable('chatroomOpen')!=cc_chatroomdetails[0]){
                                        jqcc.cometchat.chatroom(cc_chatroomdetails[0],cc_chatroomdetails[2],'0',cc_chatroomdetails[1],'1');
                                        jqcc.cometchat.setThemeVariable('chatroomOpen',cc_chatroomdetails[0]);
                                    }
                                }
                            }
                            value = '';
                            if(cc_states[1]!=' '&&cc_states[1]!=''){
                                value = cc_states[1];
                            }
                            if(value!=jqcc.cometchat.getSessionVariable('activeChatboxes')){
                                var newActiveChatboxes = {};
                                var oldActiveChatboxes = {};
                                if(value!=''){
                                    var count = 0;
                                    var chatboxData = value.split(/,/);
                                    for(i = 0; i<chatboxData.length; i++){
                                        var chatboxIds = chatboxData[i].split(/\|/);
                                        newActiveChatboxes[chromeReorderFix+chatboxIds[0]] = chatboxIds[1];
                                        count += parseInt(chatboxIds[1]);
                                    }
                                    if(settings.windowFavicon==1){
                                        favicon.badge(count);
                                    }
                                }
                                if(jqcc.cometchat.getSessionVariable('activeChatboxes')!=''){
                                    var chatboxData = jqcc.cometchat.getSessionVariable('activeChatboxes').split(/,/);
                                    for(i = 0; i<chatboxData.length; i++){
                                        var chatboxIds = chatboxData[i].split(/\|/);
                                        oldActiveChatboxes[chromeReorderFix+chatboxIds[0]] = chatboxIds[1];
                                    }
                                }
                                for(r in newActiveChatboxes){
                                    if(newActiveChatboxes.hasOwnProperty(r)){
                                        var id = r.replace('_','');
                                        if($('#cometchat_user_'+id+'_popup').length<1){
                                            jqcc[settings.theme].createChatbox(id, jqcc.cometchat.getThemeArray('buddylistName', id), jqcc.cometchat.getThemeArray('buddylistStatus', id), jqcc.cometchat.getThemeArray('buddylistMessage', id), jqcc.cometchat.getThemeArray('buddylistAvatar', id), jqcc.cometchat.getThemeArray('buddylistLink', id), jqcc.cometchat.getThemeArray('buddylistIsDevice', id), 0, null);
                                        }
                                        jqcc.cometchat.setThemeArray('chatBoxesOrder', chromeReorderFix+id,parseInt(newActiveChatboxes[r]));
                                        if(parseInt(newActiveChatboxes[r])>0){
                                            jqcc.cometchat.setThemeVariable('newMessages', 1);
                                        }
                                    }
                                }
                                for(y in oldActiveChatboxes){
                                    if(oldActiveChatboxes.hasOwnProperty(y)){
                                        if(newActiveChatboxes[y]==null){
                                            y = y.replace('_','');
                                            $("#cometchat_user_"+y+"_popup").find("div.cometchat_user_closebox").click();
                                        }
                                    }
                                }
                            }
                            if(jqcc.cometchat.getThemeVariable('newMessages')>0){
                                if(settings.windowFavicon==1){
                                    jqcc[settings.theme].countMessage();
                                }
                                if(document.title==language[15]){
                                    document.title = jqcc.cometchat.getThemeVariable('documentTitle');
                                }else{
                                    if(settings.windowTitleNotify==1){
                                        document.title = language[15];
                                    }
                                }
                            }else{
                                var nowTime = new Date();
                                var idleDifference = Math.floor(nowTime.getTime()/1000)-jqcc.cometchat.getThemeVariable('idleTime');
                                if(idleDifference<5){
                                    document.title = jqcc.cometchat.getThemeVariable('documentTitle');
                                    if(settings.windowFavicon==1){
                                        favicon.badge(0);
                                    }
                                }
                            }
                            value = 0;
                            if(cc_states[2]!=' '&&cc_states[2]!=''){
                                value = cc_states[2];
                            }
                            if(value!=jqcc.cometchat.getSessionVariable('openChatboxId')&&cc_states[5]!='chatrooms'){
                                if(jqcc.cometchat.getSessionVariable('openChatboxId')!=''){
                                    jqcc.cometchat.tryClickSync(jqcc.cometchat.getSessionVariable('openChatboxId'));
                                }
                                if(value!=''){
                                    jqcc.cometchat.setSessionVariable('openChatboxId',value);
                                    jqcc.cometchat.tryClickSync(value);
                                }
                            }
                            if(cc_states[4]==1){
                                jqcc[settings.theme].goOffline(1);
                            }
                        }else{
                            $('.cometchat_offline_overlay').css('display','table');
                        }
                        if(cc_states[4]==0&&jqcc.cometchat.getThemeVariable('offline')==1){
                            jqcc.cometchat.setThemeVariable('offline', 0);
                            jqcc[settings.theme].removeUnderline();
                            jqcc.cometchat.sendStatus('available');
                            $('#cometchat_userstab_text').html(language[9]+' ('+jqcc.cometchat.getThemeVariable('lastOnlineNumber')+')');
                            jqcc.cometchat.chatHeartbeat(1);
                            jqcc[settings.theme].resynch();
                        }
                    }else{
                        $('#cometchat_userstab').click();
                    }
                    if(jqcc.cometchat.getSessionVariable('activeChatboxes') == '' && ($.cookie(settings.cookiePrefix+'chatroom')==null ||$.cookie(settings.cookiePrefix+'chatroom')== '' ) && (jqcc.cometchat.getThemeVariable('offline') != '1')){
                        $('.cometchat_noactivity').css('display','block');
                    }
                    if(hasChatroom!=1){
                        $('#cometchat_userstab_popup').css('display','block');
                    }
                    jqcc.cometchat.setInternalVariable('updatingsession', '0');
                    clearTimeout(resynchTimer);
                    resynchTimer = setTimeout(function(){
                        jqcc[settings.theme].resynch();
                    }, 5000);
                }
            },
            setModuleAlert: function(id, number){
            },
            addPopup: function(id, amount, add){
                if(typeof(id)=='string')
                    id = id.replace( /^\D+/g, '');
                if(jqcc.cometchat.getThemeArray('buddylistName', id)==null||jqcc.cometchat.getThemeArray('buddylistName', id)==''){
                    if(jqcc.cometchat.getThemeArray('trying', id)===undefined){
                        jqcc[settings.theme].createChatbox(id, null, null, null, null, null, null, 1, null);
                    }
                    if(jqcc.cometchat.getThemeArray('trying', id)<5){
                        setTimeout(function(){
                            jqcc[settings.theme].addPopup(id, amount, add);
                        }, 5000);
                    }
                }else{
                    var cometchat_user_id = $("#cometchat_userlist_"+id);
                    var cometchat_activech = $("#cometchat_activech_"+id);
                    if($("#cometchat_activech_"+id).length<1){
                        jqcc[settings.theme].activeChatBoxes();
                        cometchat_activech = $("#cometchat_activech_"+id);
                    }
                    var cometchat_msgcount = cometchat_user_id.find('.cometchat_msgcount');
                    var cometchat_msgcount_a = cometchat_activech.find('.cometchat_msgcount');
                    if(cometchat_msgcount_a.length > 0 && add==1){
                        amount = parseInt(cometchat_msgcount_a.find(".cometchat_msgcounttext").text())+parseInt(amount);
                    }

                    if(amount==0 && add==0){
                        cometchat_msgcount.remove();
                        cometchat_msgcount_a.remove();
                    }else{
                        if(cometchat_msgcount.length>0){
                            cometchat_msgcount.find(".cometchat_msgcounttext").text(amount);
                        }else{
                            cometchat_user_id.append("<span class='cometchat_msgcount'><div class='cometchat_msgcounttext'>"+amount+"</div></span>");
                            cometchat_msgcount.find(".cometchat_msgcounttext").text(amount);
                        }
                        if(cometchat_msgcount_a.length>0){
                            cometchat_msgcount_a.find(".cometchat_msgcounttext").text(amount);
                        }else{
                            cometchat_activech.append("<span class='cometchat_msgcount'><div class='cometchat_msgcounttext'>"+amount+"</div></span>");
                            cometchat_msgcount_a.find(".cometchat_msgcounttext").text(amount);
                        }
                    }
                    jqcc.cometchat.setThemeArray('chatBoxesOrder', chromeReorderFix+id, amount);
                    jqcc.cometchat.orderChatboxes();
                }
                if($("#cometchat_chatroomstab.cometchat_tabclick").length>0){
                    var newOneonOneMessages = 0;
                    jqcc('#cometchat_activechatboxes_popup .cometchat_msgcount').each(function(){
                        newOneonOneMessages += parseInt(jqcc(this).children('.cometchat_msgcounttext').text());
                    });
                    if(newOneonOneMessages>0){
                        $('#cometchat_userstab_text').text('<?php echo $language[88]?> ('+newOneonOneMessages+')');
                    }
                }
                jqcc[settings.theme].calcPrevNoti();
                jqcc[settings.theme].calcNextNoti();
            },
            getTimeDisplay: function(ts, id){
                ts = parseInt(ts);
                if((ts+"").length == 10){
                    ts = ts*1000;
                }
                var time = getTimeDisplay(ts);
                var timeDataStart = "<span class=\"cometchat_ts\">"+time.hour+":"+time.minute+time.ap;
                var timeDataEnd = "</span>";
                if(ts<jqcc.cometchat.getThemeVariable('todays12am')){
                    return timeDataStart+" "+time.date+time.type+" "+time.month+timeDataEnd;
                }else{
                    return timeDataStart+timeDataEnd;
                }
            },
            createChatbox: function(id, name, status, message, avatar, link, isdevice, silent, tryOldMessages){
                if(id==null||id==''){
                    return;
                }
                if(jqcc.cometchat.getThemeArray('buddylistName', id)==null||jqcc.cometchat.getThemeArray('buddylistName', id)==''){
                    if(jqcc.cometchat.getThemeArray('trying', id)===undefined){
                        jqcc.cometchat.setThemeArray('trying', id, 1);
                        if(!isNaN(id)){
                            jqcc.cometchat.createChatboxSet(id, name, status, message, avatar, link, isdevice, silent, tryOldMessages);
                        }else{
                            setTimeout(function(){
                                if(typeof (jqcc[settings.theme].createChatbox)!=='undefined'){
                                    jqcc[settings.theme].createChatbox(id, jqcc.cometchat.getThemeArray('buddylistName', id), jqcc.cometchat.getThemeArray('buddylistStatus', id), jqcc.cometchat.getThemeArray('buddylistMessage', id), jqcc.cometchat.getThemeArray('buddylistAvatar', id), jqcc.cometchat.getThemeArray('buddylistLink', id), jqcc.cometchat.getThemeArray('buddylistIsDevice', id), silent, tryOldMessages);
                                }
                            }, 5000);
                        }
                    }else{
                        if(jqcc.cometchat.getThemeArray('trying', id)<5){
                            jqcc.cometchat.incrementThemeVariable('trying['+id+']');
                            setTimeout(function(){
                                if(typeof (jqcc[settings.theme].createChatbox)!=='undefined'){
                                    jqcc[settings.theme].createChatbox(id, jqcc.cometchat.getThemeArray('buddylistName', id), jqcc.cometchat.getThemeArray('buddylistStatus', id), jqcc.cometchat.getThemeArray('buddylistMessage', id), jqcc.cometchat.getThemeArray('buddylistAvatar', id), jqcc.cometchat.getThemeArray('buddylistLink', id), jqcc.cometchat.getThemeArray('buddylistIsDevice', id), silent, tryOldMessages);
                                }
                            }, 5000);
                        }
                    }
                }else{
                    if(typeof (jqcc[settings.theme].createChatboxData)!=='undefined'){
                        jqcc[settings.theme].createChatboxData(id, jqcc.cometchat.getThemeArray('buddylistName', id), jqcc.cometchat.getThemeArray('buddylistStatus', id), jqcc.cometchat.getThemeArray('buddylistMessage', id), jqcc.cometchat.getThemeArray('buddylistAvatar', id), jqcc.cometchat.getThemeArray('buddylistLink', id), jqcc.cometchat.getThemeArray('buddylistIsDevice', id), silent, tryOldMessages);
                    }
                }
            },
            createChatboxSuccess: function(data, silent, tryOldMessages){
            },
            tooltip: function(id, message, orientation){
                var cometchat_tooltip = $('#cometchat_tooltip');
                $('#cometchat_tooltip').find(".cometchat_tooltip_content").html(message);
                cometchat_tooltip.css('display', 'block');
            },
            moveBar: function(relativePixels){
                if(jqcc.cometchat.getThemeVariable('openChatboxId')!=''){
                    $('#cometchat_user_'+jqcc.cometchat.getThemeVariable('openChatboxId')+'_popup').removeClass('cometchat_tabopen');
                    $('#cometchat_user_'+jqcc.cometchat.getThemeVariable('openChatboxId')).removeClass('cometchat_tabclick').removeClass("cometchat_usertabclick");
                }
                $("#cometchat_chatboxes_wide").find("span.cometchat_tabalert").css('display', 'none');
                var ms = settings.scrollTime;
                if(jqcc.cometchat.getExternalVariable('initialize')==1){
                    ms = 0;
                }
                $("#cometchat_chatboxes").scrollToCC(relativePixels, ms, function(){
                    if(jqcc.cometchat.getThemeVariable('openChatboxId')!=''){
                        if(($("#cometchat_user_"+jqcc.cometchat.getThemeVariable('openChatboxId')).offset().left<($("#cometchat_chatboxes").offset().left+$("#cometchat_chatboxes").width()))&&($("#cometchat_user_"+jqcc.cometchat.getThemeVariable('openChatboxId')).offset().left-$("#cometchat_chatboxes").offset().left)>=0){
                            $("#cometchat_user_"+jqcc.cometchat.getThemeVariable('openChatboxId')).click();
                        }else{
                            jqcc.cometchat.setSessionVariable('openChatboxId', '');
                        }
                        jqcc.cometchat.setSessionVariable('openChatboxId', jqcc.cometchat.getThemeVariable('openChatboxId'));
                    }
                    jqcc[settings.theme].checkPopups();
                });
            },
            chatTab: function(){
                var cometchat_user_search = $("#cometchat_user_search");
                var cometchat_userscontent = $('#cometchat_userscontent');
                cometchat_user_search.click(function(){
                    var searchString = $(this).val();
                    if(searchString==language[18]){
                        cometchat_user_search.val('');
                        cometchat_user_search.addClass('cometchat_search_light');
                    }
                });
                cometchat_user_search.blur(function(){
                    var searchString = $(this).val();
                    if(searchString==''){
                        cometchat_user_search.addClass('cometchat_search_light');
                        cometchat_user_search.val(language[18]);
                    }
                });
                cometchat_user_search.keyup(function(){
                    var searchString = $(this).val();
                    if(searchString.length>0&&searchString!=language[18]){
                        cometchat_userscontent.find('div.cometchat_userlist').hide();
                        cometchat_userscontent.find('.cometchat_subsubtitle').hide();
                        cometchat_userscontent.find('#cometchat_activechatboxes_popup').hide();
                        var searchcount = cometchat_userscontent.find('.cometchat_chatboxDisplayDetails > .cometchat_userdisplayname:icontains('+searchString+')').length;
                        if(searchcount >= 1 ){
                            cometchat_userscontent.find('#cometchat_userslist').find('.cometchat_chatboxDisplayDetails > .cometchat_userdisplayname:icontains('+searchString+')').parent().parent().show();
                        }
                        cometchat_user_search.removeClass('cometchat_search_light');
                    }else{
                        cometchat_userscontent.find('div.cometchat_userlist').show();
                        cometchat_userscontent.find('.cometchat_subsubtitle').show();
                        cometchat_userscontent.find('#cometchat_activechatboxes_popup').show();
                    }
                });
                var cometchat_userstab = $('#cometchat_userstab');
                var cometchat_chatroomstab = $('#cometchat_chatroomstab');
                cometchat_userstab.click(function(){
                    jqcc[settings.theme].hideMenuPopup();
                    $('#cometchat_userstab_text').html(language[9]+' ('+jqcc.cometchat.getThemeVariable('lastOnlineNumber')+')');
                    if(typeof(newmesscr)!="undefined"){
                        clearInterval(newmesscr);
                    }
					newmesscr = setInterval(function(){
                        if($("#cometchat_chatroomstab.cometchat_tabclick").length<1){
                            if(hasChatroom == 1){
                                var newCrMessages = jqcc.cometchat.getChatroomVars('newMessages');
                                if(newCrMessages>0){
                                    $('#cometchat_chatroomstab_text').text(language[88]+' ('+newCrMessages+')');
                                }
                                setTimeout(function(){
                                        jqcc.crsynergy.updateChatroomsTabtext();
                                },2000);
                            }
                        }else{
                            if(typeof(newmesscr)!='undefined'){
                                clearInterval(newmesscr);
                            }
                        }
                    },4000);
                    if(jqcc.cometchat.getThemeVariable('offline')==1){
                        jqcc.cometchat.setThemeVariable('offline', 0);
                        jqcc.cometchat.setThemeArray('buddylistStatus', jqcc.cometchat.getThemeVariable('userid'), 'available');
                        $('#cometchat_userstab_text').html(language[9]+' ('+jqcc.cometchat.getThemeVariable('lastOnlineNumber')+')');
                        jqcc.cometchat.chatHeartbeat(1);
                        jqcc.cometchat.sendStatus('available');
                    }
                    jqcc.cometchat.setSessionVariable('buddylist', '1');
                    $("#cometchat_tooltip").css('display', 'none');
                    $(".cometchat_userscontentavatar").find('img').each(function(){
                        if($(this).attr('original')){
                            $(this).attr("src", $(this).attr('original'));
                            $(this).removeAttr('original');
                        }
                    });
                    $(this).addClass("cometchat_tabclick");
                    cometchat_chatroomstab.removeClass("cometchat_tabclick");
                    $('#cometchat_chatroomstab_popup').removeClass("cometchat_tabopen");
                    $('#cometchat_userstab_popup').addClass("cometchat_tabopen");
                    jqcc[settings.theme].windowResize();
                });
                if(hasChatroom == 1){
                    jqcc.crsynergy.chatroomTab();
                }
            },
            optionsButton: function(){
                var cometchat_optionsbutton_popup = $("#cometchat_optionsbutton_popup");
                cometchat_optionsbutton_popup.click(function(e){
                    e.stopPropagation();
                });
                cometchat_optionsbutton_popup.find("span.cometchat_gooffline").click(function(){
                    jqcc[settings.theme].goOffline();
                });
                $("#cometchat_soundnotifications").click(function(){
                    var notification = 'false';
                    if($("#cometchat_soundnotifications").is(":checked")){
                        notification = 'true';
                    }
                    $.cookie(settings.cookiePrefix+"sound", notification, {path: '/', expires: 365});
                });
                $("#cometchat_popupnotifications").click(function(event){
                    var notification = 'false';
                    if($("#cometchat_popupnotifications").is(":checked")){
                        notification = 'true';
                    }
                    $.cookie(settings.cookiePrefix+"popup", notification, {path: '/', expires: 365});
                });
                cometchat_optionsbutton_popup.find("span.available").click(function(){
                    if(jqcc.cometchat.getThemeVariable('currentStatus')!='available'){
                        jqcc.cometchat.sendStatus('available');
                    }
                });
                cometchat_optionsbutton_popup.find("span.busy").click(function(){
                    if(jqcc.cometchat.getThemeVariable('currentStatus')!='busy'){
                        jqcc.cometchat.sendStatus('busy');
                    }
                });
                cometchat_optionsbutton_popup.find("span.invisible").click(function(){
                    if(jqcc.cometchat.getThemeVariable('currentStatus')!='invisible'){
                        jqcc.cometchat.sendStatus('invisible');
                    }
                });
                cometchat_optionsbutton_popup.find("div.cometchat_statusbutton").click(function(){
                    jqcc[settings.theme].statusSendMessage();
                });
                $("#guestsname").find("div.cometchat_guestnamebutton").click(function(){
                    jqcc[settings.theme].setGuestName();
                });
                cometchat_optionsbutton_popup.find("textarea.cometchat_statustextarea").keydown(function(event){
                    return jqcc.cometchat.statusKeydown(event, this);
                });
                cometchat_optionsbutton_popup.find("input.cometchat_guestnametextbox").keydown(function(event){
                    return jqcc.cometchat.guestnameKeydown(event, this);
                });
                $('#cometchat_optionsbutton').mouseover(function(){
                    if(!cometchat_optionsbutton_popup.hasClass("cometchat_tabopen")){
                        $(this).addClass("cometchat_tabmouseover");
                    }
                });
                $('#cometchat_optionsbutton').mouseout(function(){
                    $(this).removeClass("cometchat_tabmouseover");
                    if(tooltipPriority==0){
                        $("#cometchat_tooltip").css('display', 'none');
                    }
                });
                $('#cometchat_optionsbutton').click(function(){
                    if(jqcc.cometchat.getThemeVariable('offline')==1){
                        jqcc.cometchat.sendStatus('available');
                        jqcc.cometchat.setThemeVariable('offline', 0);
                        jqcc.cometchat.setSessionVariable('offline', 0);
                        $('#cometchat_userstab_text').html(language[9]+' ('+jqcc.cometchat.getThemeVariable('lastOnlineNumber')+')');
                        jqcc[settings.theme].resynch();
                        jqcc.cometchat.chatHeartbeat(1);
                    }
                    $("#cometchat_tooltip").css('display', 'none');
                    if($.cookie(settings.cookiePrefix+"sound")){
                        if($.cookie(settings.cookiePrefix+"sound")=='true'){
                            $("#cometchat_soundnotifications").attr("checked", true);
                        }else{
                            $("#cometchat_soundnotifications").attr("checked", false);
                        }
                    }
                    if($.cookie(settings.cookiePrefix+"popup")){
                        if($.cookie(settings.cookiePrefix+"popup")=='true'){
                            $("#cometchat_popupnotifications").attr("checked", true);
                        }else{
                            $("#cometchat_popupnotifications").attr("checked", false);
                        }
                    }
                    if(settings.showSettingsTab==1){
                        $('#cometchat_optionsbutton_popup').toggleClass('cometchat_tabopen');
                    }
                });
                var auth_logout = $("div#cometchat_authlogout");
                logout_click();
                function logout_click(){
                    auth_logout.click(function(event){
                        auth_logout.unbind('click');
                        event.stopPropagation();
                        auth_logout.css('background','url('+baseUrl+'themes/'+settings.theme+'/images/loading.gif) no-repeat 0px 6px');
                        jqcc.ajax({
                            url: baseUrl+'functions/login/logout.php',
                            dataType: 'jsonp',
                            success: function(){
                                auth_logout.css('background','url('+baseUrl+'themes/'+settings.theme+'/images/logout.png) no-repeat 0px 8px');
                                logout_click();
                                $("#cometchat_user_"+jqcc.cometchat.getThemeVariable('openChatboxId')).find('.cometchat_closebox_bottom').click();
                                jqcc.cometchat.setSessionVariable('openChatboxId', '');
                                $.cookie(settings.cookiePrefix+"loggedin", null, {path: '/'});
                                $.cookie(settings.cookiePrefix+"state", null, {path: '/'});
                                $.cookie(settings.cookiePrefix+"jabber", null, {path: '/'});
                                $.cookie(settings.cookiePrefix+"jabber_type", null, {path: '/'});
                                $.cookie(settings.cookiePrefix+"hidebar", null, {path: '/'});
                                jqcc[settings.theme].loggedOut();
                                jqcc.cometchat.setThemeVariable('loggedout', 1);
                                clearTimeout(jqcc.cometchat.getCcvariable().heartbeatTimer);
                            },
                            error: function(){
                                logout_click();
                                alert(language[81]);
                            }
                        });
                    });
                }
            },
            chatboxKeyup: function(event, chatboxtextarea, id){
                if(event.keyCode==8&&$(chatboxtextarea).val()==''){
                    $(chatboxtextarea).css('height', '25px');
                    jqcc[settings.theme].windowResize();
                }
                var chatboxtextareaheight  = $(chatboxtextarea).height();
                var maxHeight = 94;
                clearTimeout(typingTimer);
                jqcc.cometchat.setThemeVariable('typingTo', id);
                typingTimer = setTimeout(function(){
                    jqcc.cometchat.resetTypingTo(id);
                }, settings.typingTimeout);
                chatboxtextareaheight = Math.max(chatboxtextarea.scrollHeight, chatboxtextareaheight);
                chatboxtextareaheight = Math.min(maxHeight, chatboxtextareaheight);
                if(chatboxtextareaheight>chatboxtextarea.clientHeight && chatboxtextareaheight<maxHeight){
                    $(chatboxtextarea).css('height', chatboxtextareaheight+'px');
                }else if(chatboxtextareaheight>chatboxtextarea.clientHeight){
                    $(chatboxtextarea).css('height', maxHeight+'px');
                    $(chatboxtextarea).css('overflow-y', 'auto');
                }
                jqcc[settings.theme].windowResize();
            },
            chatboxKeydown: function(event, chatboxtextarea, id, force){
                var condition = 1;
                if((event.keyCode==13&&event.shiftKey==0)||force==1){
                    var message = $(chatboxtextarea).val();
                    message = message.replace(/^\s+|\s+$/g, "");
                    $(chatboxtextarea).val('');
                    $(chatboxtextarea).css('height', '25px');
                    $(chatboxtextarea).css('overflow-y', 'hidden');
                    $(chatboxtextarea).focus();
                    if(settings.floodControl){
                        condition = ((Math.floor(new Date().getTime()))-lastmessagetime>2000);
                    }
                    if(message!=''){
                        if(condition){
                            var messageLength = message.length;
                            lastmessagetime = Math.floor(new Date().getTime());
                            if(jqcc.cometchat.getThemeArray('isJabber', id)!=1){
                                jqcc.cometchat.chatboxKeydownSet(id, message);
                            }else{
                                jqcc.ccjabber.sendMessage(id, message);
                            }
                        }else{
                            alert(language[53]);
                        }
                    }
                    return false;
                }
            },
            scrollDown: function(id){
               if(jqcc().slimScroll){
                    $('#cometchat_tabcontenttext_'+id).slimScroll({scroll: '1'});
                }else{
                    setTimeout(function(){
                        $("#cometchat_tabcontenttext_"+id).scrollTop(50000);
                    }, 100);
                }
            },
            updateChatbox: function(id){
                if(jqcc.cometchat.getThemeArray('isJabber', id)!=1){
                    jqcc.cometchat.updateChatboxSet(id);
                }else{
                    jqcc.ccjabber.getRecentData(id);
                }
            },
            updateChatboxSuccess: function(id, data){
                var name = jqcc.cometchat.getThemeArray('buddylistName', id);
                $("#cometchat_tabcontenttext_"+id).find('.cometchat_message_container').html('');
                if(typeof (jqcc[settings.theme].addMessages)!=='undefined'&&data.hasOwnProperty('messages')){
                    jqcc[settings.theme].addMessages(data['messages']);
                }
                jqcc[settings.theme].scrollDown(id);
            },
            windowResize: function(silent){
                var w=window,d=document,e=d.documentElement,g=d.getElementsByTagName('body')[0],winWidth=w.innerWidth||e.clientWidth||g.clientWidth,winHt=w.innerHeight||e.clientHeight||g.clientHeight;
                var searchbar_Height = $('#cometchat_user_searchbar').is(':visible') ? $('#cometchat_user_searchbar').outerHeight(true) : 0;
                var jabber_Height = $('#jabber_login').is(':visible') ? $('#jabber_login').outerHeight(true) : 0;
                var usercontentHeight = winHt-$('#cometchat_self_container').outerHeight(true)-$('#cometchat_tabcontainer').outerHeight(true)-$('#cometchat_trayicons').outerHeight(true)-searchbar_Height-jabber_Height+'px';
                $('#cometchat_userscontent').parent('.slimScrollDiv').css('height',usercontentHeight);
                $('#cometchat_userscontent').css('height',usercontentHeight);
                var openChatboxId = jqcc.cometchat.getThemeVariable('openChatboxId');
                var openChatbox = $("#cometchat_user_"+openChatboxId+"_popup");
                var chatboxHeight = winHt-openChatbox.find('.cometchat_ad').outerHeight(true)-openChatbox.find('.cometchat_tabsubtitle').outerHeight(true)-openChatbox.find('.cometchat_prependMessages').outerHeight(true)-openChatbox.find(".cometchat_tabinputcontainer").outerHeight(true);
                $(".cometchat_userchatbox").find(".cometchat_tabcontent").find("div.slimScrollDiv").css('height', chatboxHeight+'px');
                $(".cometchat_userchatbox").find("div.cometchat_tabcontenttext").css('height',chatboxHeight+'px');

                if(hasChatroom == 1){
                    jqcc.crsynergy.chatroomWindowResize();
                }
            },
            chatWith: function(id){
                jqcc('#cometchat_userlist_'+id+" .cometchat_msgcount").remove();
                jqcc[settings.theme].calcPrevNoti();
                jqcc[settings.theme].calcNextNoti();
                if(jqcc.cometchat.getThemeVariable('loggedout')==0){
                    if(jqcc.cometchat.getThemeVariable('offline')==1){
                        jqcc.cometchat.setThemeVariable('offline', 0);
                        $('#cometchat_userstab_text').html(language[9]+' ('+jqcc.cometchat.getThemeVariable('lastOnlineNumber')+')');
                        jqcc.cometchat.chatHeartbeat(1);
                        jqcc.cometchat.sendStatus('available');
                    }
                    jqcc.cometchat.setThemeVariable('trayOpen','');
                    jqcc.cometchat.setThemeVariable('chatroomOpen','');
                    jqcc.cometchat.setThemeVariable('openChatboxId',id);
                    if(typeof (jqcc[settings.theme].createChatbox)!=='undefined'){
                        jqcc[settings.theme].createChatbox(id, jqcc.cometchat.getThemeArray('buddylistName', id), jqcc.cometchat.getThemeArray('buddylistStatus', id), jqcc.cometchat.getThemeArray('buddylistMessage', id), jqcc.cometchat.getThemeArray('buddylistAvatar', id), jqcc.cometchat.getThemeArray('buddylistLink', id), jqcc.cometchat.getThemeArray('buddylistIsDevice', id));
                    }
                }
            },
            scrollFix: function(){
                var elements = ['cometchat_tabcontainer', 'cometchat_userstab_popup', 'cometchat_optionsbutton_popup', 'cometchat_tooltip', 'cometchat_hidden'];
                if(jqcc.cometchat.getThemeVariable('openChatboxId')!=''){
                    elements.push('cometchat_user_'+jqcc.cometchat.getThemeVariable('openChatboxId')+'_popup');
                }
                for(x in elements){
                    $('#'+elements[x]).css('position', 'absolute');
                    var bottom = parseInt($('#'+elements[x]).css('bottom'));
                    if(x==0){
                        bottom = 0;
                    }
                    var height = parseInt($('#'+elements[x]).height());
                    if(windowHeights[elements[x]]&&x!=3){
                        height = windowHeights[elements[x]];
                    }else{
                        windowHeights[elements[x]] = height;
                    }
                    $('#'+elements[x]).css('top', (parseInt($(window).height())-bottom-height+parseInt($(window).scrollTop()))+'px');
                }
            },
            checkPopups: function(silent){

            },
            launchModule: function(id){
                if($('#cometchat_container_'+id).length == 0){
                    $("#cometchat_trayicon_"+id).click();
                }
            },
            toggleModule: function(id){
                if($('#cometchat_container_'+id).length == 0){
                    $("#cometchat_trayicon_"+id).click();
                }
            },
            closeModule: function(id){
                if(jqcc(document).find('#cometchat_closebox_'+id).length > 0){
                    jqcc(document).find('#cometchat_closebox_'+id)[0].click();
                }
            },
            joinChatroom: function(roomid, inviteid, roomname){
                jqcc.cometchat.chatroom(roomid,roomname,0,inviteid,1,1);
            },
            closeTooltip: function(){
                $("#cometchat_tooltip").css('display', 'none');
            },
            scrollToTop: function(){
                $("html,body").animate({scrollTop: 0}, {"duration": "slow"});
            },
            reinitialize: function(){
                if(jqcc.cometchat.getThemeVariable('loggedout')==1){
                    $('#cometchat').html(cometchat_lefttab);
                    $('#cometchat').append(cometchat_righttab);
                    jqcc.cometchat.setThemeVariable('loggedout', 0);
                    jqcc.cometchat.setExternalVariable('initialize', '1');
                    jqcc.cometchat.chatHeartbeat();
                }
            },
            updateHtml: function(id, temp){
                if($("#cometchat_user_"+id+"_popup").length>0){
                    document.getElementById("cometchat_tabcontenttext_"+id).find('.cometchat_message_container').innerHTML = '<div>'+temp+'</div>';
                    jqcc[settings.theme].scrollDown(id);
                }else{
                    if(jqcc.cometchat.getThemeArray('trying', id)===undefined||jqcc.cometchat.getThemeArray('trying', id)<5){
                        setTimeout(function(){
                            $.cometchat.updateHtml(id, temp);
                        }, 1000);
                    }
                }
            },
            updateJabberOnlineNumber: function(number){
                jqcc.cometchat.setThemeVariable('jabberOnlineNumber', number);
                jqcc.cometchat.setThemeVariable('lastOnlineNumber', jqcc.cometchat.getThemeVariable('jabberOnlineNumber')+siteOnlineNumber);
                if(jqcc.cometchat.getThemeVariable('offline')==0){
                    $('#cometchat_userstab_text').html(language[9]+' ('+jqcc.cometchat.getThemeVariable('lastOnlineNumber')+')');
                }
            },
            userClick: function(listing){
                var id = $(listing).attr('id');
                if(typeof id==="undefined"||$(listing).attr('id')==''){
                    id = $(listing).parents('div.cometchat_userlist').attr('id');
                }
                id = id.substr(19);
                jqcc.cometchat.setThemeVariable('trayOpen','');
                jqcc.cometchat.setThemeVariable('chatroomOpen','');
                jqcc.cometchat.setThemeVariable('openChatboxId',id);
                if(typeof (jqcc[settings.theme].createChatbox)!=='undefined'){
                    jqcc[settings.theme].createChatbox(id, jqcc.cometchat.getThemeArray('buddylistName', id), jqcc.cometchat.getThemeArray('buddylistStatus', id), jqcc.cometchat.getThemeArray('buddylistMessage', id), jqcc.cometchat.getThemeArray('buddylistAvatar', id), jqcc.cometchat.getThemeArray('buddylistLink', id), jqcc.cometchat.getThemeArray('buddylistIsDevice', id));
                }
                $("#cometchat_userlist_"+id).find(".cometchat_msgcount").remove();
                $("#cometchat_activech_"+id).find(".cometchat_msgcount").remove();
                $(listing).find(".cometchat_msgcount").remove();
                jqcc[settings.theme].calcPrevNoti();
                jqcc[settings.theme].calcNextNoti();
                jqcc[settings.theme].hideMenuPopup();
            },
            hideMenuPopup: function(){
                $('#cometchat_plugins').removeClass('cometchat_tabopen');
                $('.cometchat_pluginsOption').find('.cometchat_menuOptionIcon').removeClass('cometchat_menuOptionIconClick');
                $('#cometchat_moderator_opt').removeClass('cometchat_tabopen');
                $('.cometchat_chatroomModOption').find('.cometchat_menuOptionIcon').removeClass('cometchat_menuOptionIconClick');
                $('#chatroomusers_popup').removeClass('cometchat_tabopen');
                $('.cometchat_chatroomUsersOption').find('.cometchat_menuOptionIcon').removeClass('cometchat_menuOptionIconClick');
                $('.menuOptionPopup.cometchat_tabpopup.cometchat_tabopen').removeClass('cometchat_tabopen');
            },
            messageBeep: function(baseUrl){
                $('<audio id="messageBeep" style="display:none;"><source src="'+baseUrl+'mp3/beep.mp3" type="audio/mpeg"><source src="'+baseUrl+'mp3/beep.ogg" type="audio/ogg"><source src="'+baseUrl+'mp3/beep.wav" type="audio/wav"></audio>').appendTo($("body"));
            },
            ccClicked: function(id){
                $(id).click();
            },
            ccAddClass: function(id, classadded){
                $(id).addClass(classadded);
            },
            moveLeft: function(){
                jqcc[settings.theme].moveBar("-=152px");
            },
            moveRight: function(){
                jqcc[settings.theme].moveBar("+=152px");
            },
            processMessage: function(message, self){
                if(settings.iPhoneView){
                    if(self==null||self==''){
                        return '<table class="cometchat_iphone" cellpadding=0 cellspacing=0 style="float:right"><tr><td class="cometchat_tl"></td><td class="cometchat_tc"></td><td class="cometchat_tr"></td></tr><tr><td class="cometchat_cl"></td><td class="cometchat_cc">'+message+'</td><td class="cometchat_cr"></td></tr><tr><td class="cometchat_bl"></td><td class="cometchat_bc"></td><td class="cometchat_br"></td></tr></table><div style="clear:both"></div>';
                    }else{
                        return '<table class="cometchat_iphone" cellpadding=0 cellspacing=0><tr><td class="cometchat_xtl"></td><td class="cometchat_xtc"></td><td class="cometchat_xtr"></td></tr><tr><td class="cometchat_xcl"></td><td class="cometchat_xcc">'+message+'</td><td class="cometchat_xcr"></td></tr><tr><td class="cometchat_xbl"></td><td class="cometchat_xbc"></td><td class="cometchat_xbr"></td></tr></table><div style="clear:both"></div>';
                    }
                }
                return message;
            },
            minimizeAll: function(){
                $("div.cometchat_tabpopup").each(function(){
                    if($(this).hasClass('cometchat_tabopen')){
                        if($(this).find('div.cometchat_minimizebox').length != 0){
                            $(this).find('div.cometchat_minimizebox').click();
                        }else{
                            $(this).removeClass('cometchat_tabopen');
                        }
                    }
                });
            },
            minimizeOpenChatbox: function(){
                jqcc('.cometchat_tabpopup.cometchat_tabopen[id!=cometchat_userstab_popup]').find('.cometchat_minimizebox').click()[0];
            },
			prependMessagesInit: function(id){
                var messages = jqcc('#cometchat_tabcontenttext_'+id).find('.cometchat_chatboxmessage');
                $('#cometchat_prependMessages_'+id).text(language[41]);
                jqcc('#cometchat_prependMessages_'+id).attr('onclick','');
                if(messages.length > 0){
                    prepend = messages[0].id.split('_')[2];
                }else{
                    prepend = -1;
                }
                jqcc.cometchat.updateChatboxSet(id,prepend);
            },
            prependMessages:function(id,data){
                var oldMessages = '';
                var count = 0;
                $.each(data, function(type, item){
                    if(type=="messages"){
                        $.each(item, function(i, incoming){
                            count = count+1;
                            var selfstyle = '';
                            var fromname = jqcc.cometchat.getThemeArray('buddylistName', incoming.from);
                            var fromavatar = '';
                            if(parseInt(incoming.self)==1){
                                fromname = language[10];
                                selfstyle = ' cometchat_self';
                            }else{
                                fromavatar = '<img class="cometchat_userscontentavatarsmall" src="'+jqcc.cometchat.getThemeArray('buddylistAvatar', incoming.from)+'">';
                            }
                            var message = jqcc.cometchat.processcontrolmessage(incoming);
                            if(message == null){
                                return;
                            }

                           if(incoming.sent!=null){
                                var ts = incoming.sent;
                                sentdata = jqcc[settings.theme].getTimeDisplay(ts, incoming.from);
                            }
                            var msg = jqcc[settings.theme].processMessage('<div class="cometchat_messagebox">'+fromavatar+sentdata+'<div class="cometchat_chatboxmessage'+selfstyle+'" id="cometchat_message_'+incoming.id+'"><div class="cometchat_messagearrow"></div><span class="cometchat_chatboxmessagecontent">'+message+'</span></div></div>', selfstyle);
                            oldMessages+=msg;
                        });
                    }
                });

                jqcc('#cometchat_tabcontenttext_'+id).find('.cometchat_message_container').prepend(oldMessages);
                $('#cometchat_prependMessages_'+id).text(language[83]);
                if((count - parseInt(jqcc.cometchat.getThemeVariable('prependLimit')) < 0)){
                    $('#cometchat_prependMessages_'+id).text(language[84]);
                    jqcc('#cometchat_prependMessages_'+id).attr('onclick','');
                    jqcc('#cometchat_prependMessages_'+id).css('cursor','default');
                }else{
                    jqcc('#cometchat_prependMessages_'+id).attr('onclick','jqcc.synergy.prependMessagesInit('+id+')');
                }
            }
        };
    })();
})(jqcc);

if(typeof(jqcc.synergy) === "undefined"){
    jqcc.synergy=function(){};
}

jqcc.extend(jqcc.synergy, jqcc.ccsynergy);
