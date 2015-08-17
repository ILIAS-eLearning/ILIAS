(function($){
    $.ccstandard = (function(){
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
        var olddata = {};
        var tooltipPriority = 0;
        var desktopNotifications = {};
        var webkitRequest = 0;
        var lastmessagetime = Math.floor(new Date().getTime());
        var favicon;
        var msg_beep = '';
        var option_button = '';
        var user_tab = '';
        var chat_boxes = '';
        var chat_left = '';
        var chat_right = '';
        var usertab2 = '';
        var checkfirstmessage;
        var chatboxHeight = parseInt('<?php echo $chatboxHeight; ?>');
        var chatboxWidth = parseInt('<?php echo $chatboxWidth; ?>');
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
                var trayDataBody = '';
                if(settings.windowFavicon==1){
                    favicon = new Favico({
                        animation: 'pop'
                    });
                }
                $("body").append('<div id="cometchat"></div><div id="cometchat_hidden"><div id="cometchat_hidden_content"></div></div><div id="cometchat_tooltip"><div class="cometchat_tooltip_content"></div></div>');
                for(x in trayicon){
                    if(trayicon.hasOwnProperty(x)){
                        var icon = trayicon[x];
                        trayData += ('<div id="cometchat_trayicon_'+x+'" class="cometchat_trayicon"><div class="cometchat_trayiconimage"><img src="'+baseUrl+'modules/'+icon[0]+'/icon.png" width="16px"></div>');
                        if(trayicon[x][8]){
                            trayData += '<div class="cometchat_trayicontext">'+trayicon[x][1]+'</div>';
                        }
                        trayData += '</div>';
                        cometchat_popout = "";
                        if(x=="chatrooms"||x=="games"||x=="broadcastmessage"){
                            cometchat_popout = '<div class="cometchat_popout cometchat_pop'+x+'"></div>';
                        }
                        if(icon[3]=='_popup'){
                            trayDataBody += '<div id="cometchat_trayicon_'+x+'_popup" class="cometchat_traypopup" style="display:none"><div class="cometchat_traytitle"><div class="cometchat_name">'+icon[1]+'</div><div class="cometchat_minimizebox"></div>'+cometchat_popout+'<br clear="all"/></div><div class="cometchat_traycontent"><div class="cometchat_traycontenttext" style="height:'+icon[5]+'px"><div class="cometchat_loading"></div><iframe class="cometchat_iframe" allowtransparency="true" frameborder=0 width="'+icon[4]+'" height="'+icon[5]+'" id="cometchat_trayicon_'+x+'_iframe"></iframe></div></div></div>';
                        }
                        if(!isNaN(icon[6])&&Number(icon[6])>0){
                            trayWidth += Number(icon[6]);
                        }else{
                            trayWidth += 16;
                        }
                        trayWidth += 18;
                    }
                }
                var cc_state = $.cookie(settings.cookiePrefix+'state');
                var number = 0;
                if(cc_state!=null){
                    var cc_states = cc_state.split(/:/);
                    number = cc_states[3];
                }
                var ccauthpopup = '';
                var ccauthlogout = '';
                if(settings.ccauth.enabled=="1"){
                    ccauthlogout = '<div class="cometchat_tooltip" id="cometchat_authlogout" title="'+language[80]+'"></div>';
                }
                if(settings.ccauth.enabled=="1"){
                    ccauthpopup = '<div id="cometchat_auth_popup" class="cometchat_tabpopup" style="display:none"><div class="cometchat_userstabtitle"><div class="cometchat_userstabtitletext">'+language[77]+'</div><div class="cometchat_minimizebox cometchat_tooltip" id="cometchat_minimize_auth_popup" title="'+language[78]+'"></div><br clear="all"/></div><div class="cometchat_tabsubtitle">'+language[79]+'</div><div class="cometchat_tabcontent cometchat_optionstyle"><div id="social_login">';
                    var authactive = settings.ccauth.active;
                    authactive.forEach(function(auth) {
                        ccauthpopup += '<img onclick="window.open(\''+baseUrl+'functions/login/signin.php?network='+auth.toLowerCase()+'\',\'socialwindow\',\'location=0,status=0,scrollbars=0,width=1000,height=600\')" src="'+baseUrl+'themes/'+settings.theme+'/images/login'+auth.toLowerCase()+'.png" class="auth_options"></img>';
                    });
                    ccauthpopup += '</div></div></div>';
                }
                var baseCode = '<div id="cometchat_trayicons">'+trayDataBody+'</div><div id="cometchat_base"><div id="cometchat_hide" onclick="jqcc.cometchat.hideBar();"></div><div id="cometchat_optionsbutton" class="cometchat_tab"><div id="cometchat_optionsbutton_icon" class="cometchat_optionsimages"></div></div>'+ccauthpopup+'<div id="cometchat_trayicons">'+trayData+'</div><span id="cometchat_userstab" class="cometchat_tab"><span id="cometchat_userstab_icon"></span><span id="cometchat_userstab_text">'+language[9]+' ('+number+')</span></span><div id="cometchat_chatbox_right"><span class="cometchat_tabtext"></span><span style="top:-5px;display:none" class="cometchat_tabalertlr"></span></div><div id="cometchat_chatboxes"><div id="cometchat_chatboxes_wide"></div></div><div id="cometchat_chatbox_left"><span class="cometchat_tabtext"></span><span class="cometchat_tabalertlr" style="top:-5px;display:none;"></span></div></div><div id="cometchat_optionsbutton_popup" class="cometchat_tabpopup" style="display:none"><div class="cometchat_userstabtitle"><div class="cometchat_userstabtitletext">'+language[0]+'</div>'+ccauthlogout+'<div class="cometchat_minimizebox"></div><br clear="all"/></div><div class="cometchat_tabsubtitle">'+language[1]+'</div><div class="cometchat_tabcontent cometchat_optionstyle"><div id="guestsname"><strong>'+language[43]+'</strong><br/><input type="text" class="cometchat_guestnametextbox"/><div class="cometchat_guestnamebutton">'+language[44]+'</div></div><strong>'+language[2]+'</strong><br/><textarea class="cometchat_statustextarea"></textarea><div class="cometchat_statusbutton">'+language[22]+'</div><div class="cometchat_statusinputs"><strong>'+language[23]+'</strong><br/><span class="cometchat_user_available"></span><span class="cometchat_optionsstatus available">'+language[3]+'</span><span class="cometchat_optionsstatus2 cometchat_user_invisible" ></span><span class="cometchat_optionsstatus invisible">'+language[5]+'</span><div style="clear:both"></div><span class="cometchat_optionsstatus2 cometchat_user_busy"></span><span class="cometchat_optionsstatus busy">'+language[4]+'</span><span class="cometchat_optionsstatus2 cometchat_user_invisible"></span><span class="cometchat_optionsstatus cometchat_gooffline offline">'+language[11]+'</span><br clear="all"/></div><div class="cometchat_options_disable"><div><input type="checkbox" id="cometchat_soundnotifications" style="vertical-align: -2px;">'+language[13]+'</div><div style="clear:both"></div><div><input type="checkbox" id="cometchat_popupnotifications" style="vertical-align: -2px;">'+language[24]+'</div></div></div></div><div id="cometchat_userstab_popup" class="cometchat_tabpopup" style="display:none"><div class="cometchat_userstabtitle"><div class="cometchat_userstabtitletext">'+language[12]+'</div><div class="cometchat_minimizebox"></div><br clear="all"/></div><div class="cometchat_tabsubtitle" id="cometchat_searchbar"><input type="text" name="cometchat_search" class="cometchat_search cometchat_search_light" id="cometchat_search" placeholder="'+language[18]+'"></div><div class="cometchat_tabcontent cometchat_tabstyle"><div id="cometchat_userscontent"><div id="cometchat_userslist"><div class="cometchat_nofriends">'+language[41]+'</div></div></div></div></div>';
                $('#cometchat').html(baseCode);
                $('div.cometchat_trayicontext').each(function(){
                    trayWidth += this.clientWidth+1;
                    id = $(this).parent().attr('id');
                    $('#'+id+'_popup').find('div.cometchat_traycontent').css('background-position', (this.clientWidth)+'px bottom');
                });
                if(jqcc().slimScroll){
                    $('#cometchat_userscontent').slimScroll({height: '200px'});
                }
                jqcc[settings.theme].optionsButton();
                jqcc[settings.theme].chatTab();
                $('#cometchat_chatboxes').attr('unselectable', 'on').css('MozUserSelect', 'none').bind('selectstart.ui', function(){
                    return false;
                });
                $('#cometchat_userscontent').attr('unselectable', 'on').css('MozUserSelect', 'none').bind('selectstart.ui', function(){
                    return false;
                });
                $('div.cometchat_trayicon').attr('unselectable', 'on').css('MozUserSelect', 'none').bind('selectstart.ui', function(){
                    return false;
                });
                $('.cometchat_tab').attr('unselectable', 'on').css('MozUserSelect', 'none').bind('selectstart.ui', function(){
                    return false;
                });
                $('#cometchat_hidden').click(function(){
                    $('#cometchat').css('display', 'block');
                    $('#cometchat_hidden').css('display', 'none');
                    $.cookie(settings.cookiePrefix+"hidebar", '0', {path: '/', expires: 365});
                    if(jqcc.cometchat.getThemeVariable('loggedout')==0){
                        clearTimeout(jqcc.cometchat.getThemeVariable('heartbeatTimer'));
                        jqcc.cometchat.chatHeartbeat();
                    }
                });
                $('#cometchat_hidden').mouseover(function(){
                    if(tooltipPriority==0){
                        jqcc[settings.theme].tooltip('cometchat_hidden_content', language[26], 0);
                    }
                    $(this).addClass("cometchat_tabmouseover");
                });
                $('#cometchat_hidden').mouseout(function(){
                    $(this).removeClass("cometchat_tabmouseover");
                    if(tooltipPriority==0){
                        $("#cometchat_tooltip").css('display', 'none');
                    }
                });
                $('#cometchat_hide').mouseover(function(){
                    if(tooltipPriority==0){
                        jqcc[settings.theme].tooltip('cometchat_hide', language[27], 0);
                    }
                    $(this).addClass("cometchat_tabmouseover");
                });
                $('#cometchat_hide').mouseout(function(){
                    $(this).removeClass("cometchat_tabmouseover");
                    if(tooltipPriority==0){
                        $("#cometchat_tooltip").css('display', 'none');
                    }
                });
                $('#cometchat').find('div.cometchat_trayicon').mouseover(function(){
                    var id = $(this).attr('id').substr(19);
                    if(!trayicon[id][8]){
                        if(tooltipPriority==0){
                            jqcc[settings.theme].tooltip('cometchat_trayicon_'+id, trayicon[id][1], 1);
                        }
                    }
                    $(this).addClass("cometchat_tabmouseover");
                });
                $('#cometchat').find('div.cometchat_trayicon').mouseout(function(){
                    $(this).removeClass("cometchat_tabmouseover");
                    if(tooltipPriority==0){
                        $("#cometchat_tooltip").css('display', 'none');
                    }
                });
                $("#cometchat").find("div.cometchat_traytitle").click(function(){
                    cc_zindex += 2;
                    $('#cometchat_base').css('z-index', 100001+cc_zindex-1);
                    $(this).parent().css('z-index', 100001+cc_zindex);
                    $('#cometchat_optionsbutton_popup').css('z-index', 100001+cc_zindex);
                    $('#cometchat_userstab_popup').css('z-index', 100001+cc_zindex);
                });
                $("#cometchat").find("div.cometchat_minimizebox").click(function(){
                    var id = $(this).parent().parent().attr('id');
                    id = id.substring(19, id.length-6);
                    $("#cometchat_trayicon_"+id).click();
                });
                $("#cometchat").find("div.cometchat_popchatrooms").click(function(){
                    chatroom_location = jqcc("#cometchat").children().find('#cometchat_trayicon_chatrooms_iframe').attr('src');
                    myRef = window.open(chatroom_location+'&popoutmode=1', 'cc_module_chatrooms', 'left=20,top=20,status=0,toolbar=0,menubar=0,directories=0,location=0,status=0,scrollbars=0,resizable=1,width=800,height=600');
                    jqcc.cometchat.closeModule('chatrooms');
                });
                $("#cometchat").find("div.cometchat_popgames").click(function(){
                    games_location = jqcc("#cometchat").children().find('#cometchat_trayicon_games_iframe').attr('src');
                    myRef = window.open(games_location,'cc_module_games','left=20,top=20,status=0,toolbar=0,menubar=0,directories=0,location=0,status=0,scrollbars=0,resizable=1,width='+trayicon['games'][4]+',height='+trayicon['games'][5]+'');
                    jqcc.cometchat.closeModule('games');
                });
                $("#cometchat").find("div.cometchat_popbroadcastmessage").click(function(){
                    broadcastmessage_location = jqcc("#cometchat").children().find('#cometchat_trayicon_broadcastmessage_iframe').attr('src');
                    myRef = window.open(broadcastmessage_location+'&popoutmode=1','cc_module_broadcastmessage','left=20,top=20,status=0,toolbar=0,menubar=0,directories=0,location=0,status=0,scrollbars=0,resizable=1,width='+trayicon['broadcastmessage'][4]+',height='+trayicon['broadcastmessage'][5]+'');
                    jqcc.cometchat.closeModule('broadcastmessage');
                });
                $('#cometchat').find('div.cometchat_trayicon').click(function(){
                    var id = $(this).attr('id').substr(19);
                    jqcc.cometchat.setAlert(id, 0);
                    if(id != 'scrolltotop') {
                        if(jqcc.cometchat.getThemeVariable('openChatboxId')!=''){
                            $('#cometchat_user_'+jqcc.cometchat.getThemeVariable('openChatboxId')+'_popup').removeClass('cometchat_tabopen');
                            $('#cometchat_user_'+jqcc.cometchat.getThemeVariable('openChatboxId')).removeClass('cometchat_tabclick').removeClass("cometchat_usertabclick");
                            $("#cometchat_user_"+jqcc.cometchat.getThemeVariable('openChatboxId')).find('.cometchat_closebox_bottom').removeClass("cometchat_closebox_bottom_click");
                            jqcc.cometchat.setSessionVariable('openChatboxId', '');
                        }
                        $('#cometchat_auth_popup').removeClass('cometchat_tabopen');
                        $('#cometchat_userstab_popup').removeClass('cometchat_tabopen');
                        $('#cometchat_userstab').removeClass('cometchat_userstabclick').removeClass('cometchat_tabclick');
                        $('#cometchat_optionsbutton_popup').removeClass('cometchat_tabopen');
                        $('#cometchat_optionsbutton').removeClass('cometchat_tabclick');
                        jqcc.cometchat.setSessionVariable('buddylist', '0');
                    }
                    var target = "_self";
                    if(trayicon[id][3]){
                        target = trayicon[id][3];
                    }
                    if(target=='_popup'){
                        if(jqcc.cometchat.getThemeVariable('trayOpen')!=id){
                            $("#cometchat_trayicon_"+jqcc.cometchat.getThemeVariable('trayOpen')+'_popup').removeClass("cometchat_tabopen");
                            $("#cometchat_trayicon_"+jqcc.cometchat.getThemeVariable('trayOpen')).removeClass("cometchat_trayclick");
                            jqcc.cometchat.setThemeVariable('trayOpen', '');
                            jqcc.cometchat.setSessionVariable('trayOpen', jqcc.cometchat.getThemeVariable('trayOpen'));
                        }
                        if(jqcc.cometchat.getThemeVariable('trayOpen')==''){
                            $('#cometchat_trayicon_'+id+'_popup').css('left', $('#cometchat_trayicon_'+id).offset().left-1).css('width', trayicon[id][4]);
                            if($("#cometchat_trayicon_"+id+'_iframe').attr('src')===undefined||$("#cometchat_trayicon_"+id+'_iframe').attr('src')=='blank.html'||$("#cometchat_trayicon_"+id+'_iframe').attr('src')==''){
                                $("#cometchat_trayicon_"+id+'_iframe').attr('src', trayicon[id][2]+'?basedata='+jqcc.cometchat.getThemeVariable('baseData'));
                            }
                            $("#cometchat_trayicon_"+id).addClass("cometchat_trayclick");
                            $("#cometchat_trayicon_"+id+'_popup').addClass("cometchat_tabopen");
                            if($("#cometchat_trayicon_"+id).hasClass("cometchat_trayclick")){
                                jqcc.cometchat.setThemeVariable('openChatboxId', '');
                            }
                            cc_zindex += 2;
                            $('#cometchat_base').css('z-index', 100001+cc_zindex-1);
                            $("#cometchat_trayicon_"+id+'_popup').css('z-index', 100001+cc_zindex);
                            $('#cometchat_optionsbutton_popup').css('z-index', 100001+cc_zindex);
                            $('#cometchat_userstab_popup').css('z-index', 100001+cc_zindex);
                            $("#cometchat_user_"+id+'_popup').css('z-index', 100001+cc_zindex);
                            jqcc.cometchat.setThemeVariable('trayOpen', id);
                            jqcc.cometchat.setSessionVariable('trayOpen', jqcc.cometchat.getThemeVariable('trayOpen'));
                        }else{
                            $("#cometchat_trayicon_"+jqcc.cometchat.getThemeVariable('trayOpen')+'_popup').removeClass("cometchat_tabopen");
                            $("#cometchat_trayicon_"+jqcc.cometchat.getThemeVariable('trayOpen')).removeClass("cometchat_trayclick");
                            jqcc.cometchat.setThemeVariable('trayOpen', '');
                            jqcc.cometchat.setSessionVariable('trayOpen', jqcc.cometchat.getThemeVariable('trayOpen'));
                        }
                    }else if(target=='_lightbox'){
                        jqcc.cometchat.lightbox(id);
                    }else{
                        window.open(trayicon[id][2], target);
                    }
                });
                var cometchat_chatbox_right = $('#cometchat_chatbox_right');
                var cometchat_chatbox_left = $('#cometchat_chatbox_left');
                cometchat_chatbox_right.bind('click', function(){
                    jqcc[settings.theme].moveRight();
                });
                cometchat_chatbox_left.bind('click', function(){
                    jqcc[settings.theme].moveLeft();
                });
                jqcc[settings.theme].windowResize();
                jqcc[settings.theme].scrollBars();
                cometchat_chatbox_right.mouseover(function(){
                    $(this).addClass("cometchat_chatbox_lr_mouseover");
                });
                cometchat_chatbox_right.mouseout(function(){
                    $(this).removeClass("cometchat_chatbox_lr_mouseover");
                });
                cometchat_chatbox_left.mouseover(function(){
                    $(this).addClass("cometchat_chatbox_lr_mouseover");
                });
                cometchat_chatbox_left.mouseout(function(){
                    $(this).removeClass("cometchat_chatbox_lr_mouseover");
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
                    if(settings.disableForMobileDevices){
                        $('#cometchat').css('display', 'none');
                        jqcc.cometchat.setThemeVariable('runHeartbeat', 0);
                    }
                }
                document.onmousemove = function(){
                    var nowTime = new Date();
                    jqcc.cometchat.setThemeVariable('idleTime', Math.floor(nowTime.getTime()/1000));
                };
                if($.cookie(settings.cookiePrefix+'hidebar')=='1'){
                    $('#cometchat').css('display', 'none');
                    $('#cometchat_hidden').css('display', 'block');
                    jqcc.cometchat.setThemeVariable('runHeartbeat', 0);
                }
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

                attachPlaceholder('#cometchat_searchbar');
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
                    if(chatboxOpened[buddy.id]!=null){
                        if(buddy.d == 1 && $("#cometchat_user_"+buddy.id).find(".cometchat_closebox_bottom_status.cometchat_mobile").length < 1){
                            $("#cometchat_user_"+buddy.id).find(".cometchat_closebox_bottom_status").addClass('cometchat_mobile').html('<div class="cometchat_dot"></div>');
                        }else if(buddy.d == 0){
                            $("#cometchat_user_"+buddy.id).find(".cometchat_closebox_bottom_status").removeClass('cometchat_mobile').html('');
                        }
                        $("#cometchat_user_"+buddy.id).find("div.cometchat_closebox_bottom_status")
                        .removeClass("cometchat_available")
                        .removeClass("cometchat_busy")
                        .removeClass("cometchat_offline")
                        .removeClass("cometchat_away")
                        .addClass("cometchat_"+buddy.s);
                        $("#cometchat_user_"+buddy.id).find(".cometchat_closebox_bottom_status.cometchat_mobile")
                        .removeClass("cometchat_mobile_available")
                        .removeClass("cometchat_mobile_busy")
                        .removeClass("cometchat_mobile_offline")
                        .removeClass("cometchat_mobile_away")
                        .removeClass("cometchat_available")
                        .removeClass("cometchat_busy")
                        .removeClass("cometchat_offline")
                        .removeClass("cometchat_away")
                        .addClass('cometchat_mobile_'+buddy.s);
                        if($("#cometchat_user_"+buddy.id+"_popup").length>0){
                            $("#cometchat_user_"+buddy.id+"_popup").find("div.cometchat_message").html(buddy.m);
                        }
                    }
                    if(buddy.s!='offline'){
                        onlineNumber++;
                    }
                    totalFriendsNumber++;
                    var group = '';
                    var icon = '';
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
                    var usercontentstatus = buddy.s;
                    if(buddy.d==1){
                       usercontentstatus = 'mobile cometchat_mobile_'+buddy.s;
                       icon = '<div class="cometchat_dot"></div>';
                    }
                    buddylisttemp += group+'<div id="cometchat_userlist_'+buddy.id+'" class="cometchat_userlist" onmouseover="jqcc(this).addClass(\'cometchat_userlist_hover\');" onmouseout="jqcc(this).removeClass(\'cometchat_userlist_hover\');"><span class="cometchat_userscontentname">'+longname+'</span><span class="cometchat_userscontentdot cometchat_'+usercontentstatus+'">'+icon+'</span></div>';
                    buddylisttempavatar += group+'<div id="cometchat_userlist_'+buddy.id+'" class="cometchat_userlist" onmouseover="jqcc(this).addClass(\'cometchat_userlist_hover\');" onmouseout="jqcc(this).removeClass(\'cometchat_userlist_hover\');"><span class="cometchat_userscontentavatar"><img class="cometchat_userscontentavatarimage" original="'+buddy.a+'"></span><span class="cometchat_userscontentname">'+longname+'</span><span class="cometchat_userscontentdot cometchat_'+usercontentstatus+'">'+icon+'</span></div>';
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
                        tooltipMessage += '<div class="cometchat_notification" onclick="javascript:jqcc.cometchat.chatWith(\''+buddy.id+'\')"><div class="cometchat_notification_avatar"><img class="cometchat_notification_avatar_image" src="'+buddy.a+'"></div><div class="cometchat_notification_message">'+buddy.n+message+'<br/><span class="cometchat_notification_status">'+buddy.m+'</span></div><div style="clear:both" /></div>';
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
                if(totalFriendsNumber>settings.thumbnailDisplayNumber){
                    bltemp = buddylisttemp;
                }
                if(bltemp!=''){
                    document.getElementById('cometchat_userslist').style.display = 'block';
                    jqcc.cometchat.replaceHtml('cometchat_userslist', '<div>'+bltemp+'</div>');
                }else{
                    $('#cometchat_userslist').html('<div class="cometchat_nofriends">'+language[14]+'</div>');
                }
                if(jqcc.cometchat.getSessionVariable('buddylist')==1){
                    $("span.cometchat_userscontentavatar").find("img").each(function(){
                        if($(this).attr('original')){
                            $(this).attr("src", $(this).attr('original'));
                            $(this).removeAttr('original');
                        }
                    });
                }
                $("#cometchat_search").keyup();
                $('div.cometchat_userlist').unbind('click');
                $('div.cometchat_userlist').bind('click', function(e){
                    jqcc.cometchat.userClick(e.target);
                });
                $('#cometchat_userstab_text').html(language[9]+' ('+(onlineNumber+jqcc.cometchat.getThemeVariable('jabberOnlineNumber'))+')');
                siteOnlineNumber = onlineNumber;
                jqcc.cometchat.setThemeVariable('lastOnlineNumber', onlineNumber+jqcc.cometchat.getThemeVariable('jabberOnlineNumber'));
                if(totalFriendsNumber+jqcc.cometchat.getThemeVariable('jabberOnlineNumber')>settings.searchDisplayNumber){
                    $('#cometchat_searchbar').css('display', 'block');
                }else{
                    $('#cometchat_searchbar').css('display', 'none');
                }
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
            },
            loggedOut: function(){
                document.title = jqcc.cometchat.getThemeVariable('documentTitle');
                if(settings.ccauth.enabled=="1"){
                    $("#cometchat_optionsbutton_icon").addClass("cometchat_optionsimages_ccauth");
                    $("#cometchat_optionsbutton").attr("title",language[77]);
                }else{
                    $("#cometchat_optionsbutton").addClass("cometchat_optionsimages_exclamation");
                    $("#cometchat_optionsbutton_icon").css('display', 'none');
                    $("#cometchat_optionsbutton").attr("title",language[8]);
                }
                $("#cometchat_userstab").hide();
                $("#cometchat_chatboxes").hide();
                $("#cometchat_chatbox_left").hide();
                $("#cometchat_chatbox_right").hide();
                msg_beep = $("#messageBeep").detach();
                option_button = $("#cometchat_optionsbutton_popup").detach();
                user_tab = $("#cometchat_userstab_popup").detach();
                chat_boxes = $("#cometchat_chatboxes").detach();
                chat_left = $("#cometchat_chatbox_left").detach();
                chat_right = $("#cometchat_chatbox_right").detach();
                usertab2 = $("#cometchat_userstab").detach();
                $("#cometchat_optionsbutton_popup").removeClass("cometchat_tabopen");
                $("#cometchat_userstab_popup").removeClass("cometchat_tabopen");
                $("#cometchat_optionsbutton").removeClass("cometchat_tabclick");
                $("#cometchat_userstab").removeClass("cometchat_tabclick");
                if(jqcc.cometchat.getThemeVariable('openChatboxId')!=''){
                    $("#cometchat_user_"+jqcc.cometchat.getThemeVariable('openChatboxId')+"_popup").removeClass("cometchat_tabopen");
                    jqcc.cometchat.setThemeVariable('openChatboxId', '');
                    jqcc.cometchat.setSessionVariable('openChatboxId', jqcc.cometchat.getThemeVariable('openChatboxId'));
                }
            },
            userStatus: function(item){
                var cometchat_optionsbutton_popup = $('#cometchat_optionsbutton_popup');
                cometchat_optionsbutton_popup.find('textarea.cometchat_statustextarea').val(item.m);
                jqcc.cometchat.setThemeVariable('currentStatus', item.s);
                if(item.s=='offline'){
                    jqcc[settings.theme].goOffline(1);
                }else{
                    jqcc[settings.theme].removeUnderline();
                    jqcc[settings.theme].updateStatus(item.s);
                }
                if(item.id>10000000){
                    $("#guestsname").show();
                    $("#guestsname").find("input.cometchat_guestnametextbox").val((item.n).replace("<?php echo $guestnamePrefix;?>-", ""));
                    cometchat_optionsbutton_popup.find(".cometchat_tabsubtitle").html(language[45]);
                }
                jqcc.cometchat.setThemeVariable('userid', item.id);
                jqcc.cometchat.setThemeArray('buddylistStatus', item.id, item.s);
                jqcc.cometchat.setThemeArray('buddylistMessage', item.id, item.m);
                jqcc.cometchat.setThemeArray('buddylistName', item.id, item.n);
                jqcc.cometchat.setThemeArray('buddylistAvatar', item.id, item.a);
                jqcc.cometchat.setThemeArray('buddylistLink', item.id, item.l);
            },
            typingTo: function(item){
                $(".cometchat_typing").css('display', 'none');
                var typingIds = item.split(',');
                var t = typingIds.length;
                while(t>-1){
                    $("#cometchat_typing_"+typingIds[t]).css('display', 'block');
                    t--;
                }
            },
            createChatboxData: function(id, name, status, message, avatar, link, isdevice, silent, tryOldMessages){
                var cometchat_chatboxes = $("#cometchat_chatboxes");
                if(chatboxOpened[id]!=null){
                    if(!$("#cometchat_user_"+id).hasClass('cometchat_tabclick')&&silent!=1){
                        if(jqcc.cometchat.getThemeVariable('openChatboxId')!=''){
                            $('#cometchat_user_'+jqcc.cometchat.getThemeVariable('openChatboxId')+'_popup').removeClass('cometchat_tabopen');
                            $('#cometchat_user_'+jqcc.cometchat.getThemeVariable('openChatboxId')).removeClass('cometchat_tabclick').removeClass("cometchat_usertabclick");
                            jqcc.cometchat.setThemeVariable('openChatboxId', '');
                            jqcc.cometchat.setSessionVariable('openChatboxId', jqcc.cometchat.getThemeVariable('openChatboxId'));
                        }
                        if(($("#cometchat_user_"+id).offset().left<(cometchat_chatboxes.offset().left+cometchat_chatboxes.width()))&&($("#cometchat_user_"+id).offset().left-cometchat_chatboxes.offset().left)>=0){
                            $("#cometchat_user_"+id).click();
                        }else{
                            $("#cometchat_chatboxes_wide").find("span.cometchat_tabalert").css('display', 'none');
                            var ms = settings.scrollTime;
                            if(jqcc.cometchat.getExternalVariable('initialize')==1){
                                ms = 0;
                            }
                            cometchat_chatboxes.scrollToCC("#cometchat_user_"+id, ms, function(){
                                $("#cometchat_user_"+id).click();
                                jqcc[settings.theme].scrollBars();
                                jqcc[settings.theme].checkPopups();
                            });
                        }
                    }
                    jqcc[settings.theme].scrollBars();
                    return;
                }
                $('#cometchat_chatboxes_wide').width($('#cometchat_chatboxes_wide').width()+152);
                jqcc[settings.theme].windowResize(1);
                shortname = name;
                longname = name;
                $("<span/>").attr("id", "cometchat_user_"+id).addClass("cometchat_tab").html('<div class="cometchat_user_shortname">'+shortname+'</div>').appendTo($("#cometchat_chatboxes_wide"));
                var icon = '';
                var usercontentstatus = status;
                if(isdevice==1){
                   usercontentstatus = 'mobile cometchat_mobile_'+status;
                   icon = '<div class="cometchat_dot"></div>';
                }
                var cometchat_user_id = $("#cometchat_user_"+id);
                cometchat_user_id.append('<div class="cometchat_closebox_bottom_status cometchat_'+usercontentstatus+'">'+icon+'</div>');
                cometchat_user_id.append('<div class="cometchat_closebox_bottom"></div>');
                var cometchat_closebox_bottom = cometchat_user_id.find(".cometchat_closebox_bottom");
                cometchat_closebox_bottom.mouseenter(function(){
                    $(this).addClass("cometchat_closebox_bottomhover");
                });
                cometchat_closebox_bottom.mouseleave(function(){
                    $(this).removeClass("cometchat_closebox_bottomhover");
                });
                cometchat_closebox_bottom.click(function(){
                    $("#cometchat_user_"+id+"_popup").remove();
                    cometchat_user_id.remove();
                    if(jqcc.cometchat.getThemeVariable('openChatboxId')==id){
                        jqcc.cometchat.setThemeVariable('openChatboxId', '');
                        jqcc.cometchat.setSessionVariable('openChatboxId', jqcc.cometchat.getThemeVariable('openChatboxId'));
                    }
                    $('#cometchat_chatboxes_wide').width($('#cometchat_chatboxes_wide').width()-152);
                    cometchat_chatboxes.scrollToCC("-=152px");
                    jqcc[settings.theme].windowResize();
                    jqcc.cometchat.setThemeArray('chatBoxesOrder', id, null);
                    chatboxOpened[id] = null;
                    olddata[id] = 0;
                    jqcc.cometchat.orderChatboxes();
                });
                var pluginshtml = '';
                if(jqcc.cometchat.getThemeArray('isJabber', id)!=1){
                    var pluginslength = settings.plugins.length;
                    if(pluginslength>0){
                        if(pluginslength>8){
                            pluginshtml += '<div style="clear:both;padding-bottom:2px"></div>';
                        }
                        pluginshtml += '<div class="cometchat_plugins">';
                        for(var i = 0; i<pluginslength; i++){
                            var name = 'cc'+settings.plugins[i];
                            if(typeof ($[name])=='object'){
                                pluginshtml += '<div class="cometchat_pluginsicon cometchat_'+settings.plugins[i]+'" title="'+$[name].getTitle()+'" name="'+name+'" to="'+id+'" chatroommode="0"></div>';
                            }
                        }
                        pluginshtml += '</div>';
                    }
                }
                var startlink = '';
                var endlink = '';
                if(link!=''){
                    startlink = '<a href="'+link+'">';
                    endlink = '</a>';
                }
                var avatarsrc = '';
                if(avatar!=''){
                    avatarsrc = '<div class="cometchat_avatarbox">'+startlink+'<img src="'+avatar+'" class="cometchat_avatar" />'+endlink+'</div>';
                }
                var prepend = '';
                var jabber = jqcc.cometchat.getThemeArray('isJabber', id);

                if(jqcc.cometchat.getThemeVariable('prependLimit') != '0' && jabber != 1){
                    prepend = '<div class=\"cometchat_prependMessages\" onclick\="jqcc.standard.prependMessagesInit('+id+')\" id = \"cometchat_prependMessages_'+id+'\">'+language[83]+'</div>';
                }

                $("<div/>").attr("id", "cometchat_user_"+id+"_popup").addClass("cometchat_tabpopup").css('display', 'none').html('<div class="cometchat_tabtitle"><span id="cometchat_typing_'+id+'" class="cometchat_typing"></span><div class="cometchat_name">'+startlink+longname+endlink+'</div></div><div class="cometchat_tabsubtitle">'+avatarsrc+'<div class="cometchat_message">'+message+'</div>'+pluginshtml+'<div style="clear:both"></div>'+'</div>'+prepend+'<div class="cometchat_tabcontent"><div class="cometchat_tabcontenttext" id="cometchat_tabcontenttext_'+id+'">'+'</div><div class="cometchat_tabcontentinput"><textarea class="cometchat_textarea" placeholder="'+language[85]+'"></textarea><div class="cometchat_tabcontentsubmit"></div></div><div style="clear:both"></div></div>').appendTo($("#cometchat"));
                var cometchat_user_popup = $("#cometchat_user_"+id+'_popup');
                if(jqcc().slimScroll){
                    cometchat_user_popup.find("div.cometchat_tabcontenttext").slimScroll({height: (chatboxHeight+11)+'px'});
                }
                cometchat_user_popup.find('.cometchat_pluginsicon').click(function(){
                    var name = $(this).attr('name');
                    var to = $(this).attr('to');
                    var chatroommode = $(this).attr('chatroommode');
                    var controlparameters = {"to":to, "chatroommode":chatroommode};
                    jqcc[name].init(controlparameters);
                });
                cometchat_user_popup.find("textarea.cometchat_textarea").keydown(function(event){
                    return jqcc[settings.theme].chatboxKeydown(event, this, id);
                });
                cometchat_user_popup.find("div.cometchat_tabcontentsubmit").click(function(event){
                    return jqcc[settings.theme].chatboxKeydown(event, cometchat_user_popup.find(".cometchat_textarea"), id, 1);
                });
                cometchat_user_popup.find("textarea.cometchat_textarea").keyup(function(event){
                    return jqcc[settings.theme].chatboxKeyup(event, this, id);
                });
                cometchat_user_popup.find("div.cometchat_tabtitle").append('<div class="cometchat_closebox"></div><div class="cometchat_minimizebox"></div><br clear="all"/>');
                var cometchat_closebox = cometchat_user_popup.find("div.cometchat_closebox");
                var cometchat_minimizebox =  cometchat_user_popup.find("div.cometchat_minimizebox");
                cometchat_closebox.mouseenter(function(){
                    $(this).addClass("cometchat_chatboxmouseoverclose");
                    cometchat_minimizebox.removeClass("cometchat_chatboxtraytitlemouseover");
                });
                cometchat_closebox.mouseleave(function(){
                    $(this).removeClass("cometchat_chatboxmouseoverclose");
                    cometchat_minimizebox.addClass("cometchat_chatboxtraytitlemouseover");
                });
                cometchat_closebox.click(function(){
                    cometchat_user_popup.remove();
                    cometchat_user_id.remove();
                    if(jqcc.cometchat.getThemeVariable('openChatboxId')==id){
                        jqcc.cometchat.setThemeVariable('openChatboxId', '');
                        jqcc.cometchat.setSessionVariable('openChatboxId', jqcc.cometchat.getThemeVariable('openChatboxId'));
                    }
                    $('#cometchat_chatboxes_wide').width($('#cometchat_chatboxes_wide').width()-152);
                    cometchat_chatboxes.scrollToCC("-=152px");
                    jqcc[settings.theme].windowResize();
                    jqcc.cometchat.setThemeArray('chatBoxesOrder', id, null);
                    chatboxOpened[id] = null;
                    olddata[id] = 0;
                    jqcc.cometchat.orderChatboxes();
                });
                var cometchat_tabtitle = cometchat_user_popup.find("div.cometchat_tabtitle");
                cometchat_tabtitle.click(function(){
                    cometchat_user_id.click();
                });
                cometchat_tabtitle.mouseenter(function(){
                    cometchat_minimizebox.addClass("cometchat_chatboxtraytitlemouseover");
                });
                cometchat_tabtitle.mouseleave(function(){
                    cometchat_minimizebox.removeClass("cometchat_chatboxtraytitlemouseover");
                });
                cometchat_user_id.mouseenter(function(){
                    $(this).addClass("cometchat_tabmouseover");
                    cometchat_user_id.find(".cometchat_user_shortname").addClass("cometchat_tabmouseovertext");
                });
                cometchat_user_id.mouseleave(function(){
                    $(this).removeClass("cometchat_tabmouseover");
                    cometchat_user_id.find("div.cometchat_user_shortname").removeClass("cometchat_tabmouseovertext");
                });
                cometchat_user_popup.click(function(){
                    cc_zindex += 2;
                    $('#cometchat_base').css('z-index', 100001+cc_zindex-1);
                    $('#cometchat_userstab_popup').css('z-index', 100001+cc_zindex);
                    $('#cometchat_optionsbutton_popup').css('z-index', 100001+cc_zindex);
                    cometchat_user_popup.css('z-index', 100001+cc_zindex);
                });
                cometchat_user_id.click(function(){
                    cc_zindex += 2;
                    $('#cometchat_base').css('z-index', 100001+cc_zindex-1);
                    $('#cometchat_userstab_popup').css('z-index', 100001+cc_zindex);
                    $('#cometchat_optionsbutton_popup').css('z-index', 100001+cc_zindex);
                    cometchat_user_popup.css('z-index', 100001+cc_zindex);
                    if(jqcc.cometchat.getThemeVariable('trayOpen')!=''){
                        $("#cometchat_trayicon_"+jqcc.cometchat.getThemeVariable('trayOpen')+'_popup').removeClass("cometchat_tabopen");
                        $("#cometchat_trayicon_"+jqcc.cometchat.getThemeVariable('trayOpen')).removeClass("cometchat_trayclick");
                        jqcc.cometchat.setThemeVariable('trayOpen', '');
                        jqcc.cometchat.setSessionVariable('trayOpen', jqcc.cometchat.getThemeVariable('trayOpen'));
                    }
                    if(cometchat_user_id.find("span.cometchat_tabalert").length>0){
                        cometchat_user_id.find("span.cometchat_tabalert").remove();
                        jqcc.cometchat.setThemeArray('chatBoxesOrder', id, 0);
                        chatboxOpened[id] = 0;
                        jqcc.cometchat.orderChatboxes();
                    }
                    if($(this).hasClass('cometchat_tabclick')){
                        $(this).removeClass("cometchat_tabclick").removeClass("cometchat_usertabclick");
                        cometchat_user_popup.removeClass("cometchat_tabopen");
                        cometchat_closebox_bottom.removeClass("cometchat_closebox_bottom_click");
                        jqcc.cometchat.setThemeVariable('openChatboxId', '');
                        jqcc.cometchat.setSessionVariable('openChatboxId', jqcc.cometchat.getThemeVariable('openChatboxId'));
                    }else{
                        var baseLeft = $('#cometchat_base').position().left;
                        if((cometchat_user_id.offset().left<(cometchat_chatboxes.offset().left+cometchat_chatboxes.width()))&&(cometchat_user_id.offset().left-cometchat_chatboxes.offset().left)>=0){
                            if(jqcc.cometchat.getThemeVariable('openChatboxId')!=''&&jqcc.cometchat.getThemeVariable('openChatboxId')!=id){
                                $('#cometchat_user_'+jqcc.cometchat.getThemeVariable('openChatboxId')+'_popup').removeClass('cometchat_tabopen');
                                $('#cometchat_user_'+jqcc.cometchat.getThemeVariable('openChatboxId')).removeClass('cometchat_tabclick').removeClass("cometchat_usertabclick");
                                $("#cometchat_user_"+jqcc.cometchat.getThemeVariable('openChatboxId')).find("div.cometchat_closebox_bottom").removeClass("cometchat_closebox_bottom_click");
                                jqcc.cometchat.setThemeVariable('openChatboxId', '');
                                jqcc.cometchat.setSessionVariable('openChatboxId', jqcc.cometchat.getThemeVariable('openChatboxId'));
                            }
                            var popupLeft = baseLeft+cometchat_user_id.position().left - (cometchat_user_popup.width() - cometchat_user_id.outerWidth(false) - 1);
                            cometchat_user_popup.css('left',popupLeft);
                            $(this).addClass("cometchat_tabclick").addClass("cometchat_usertabclick");
                            cometchat_user_popup.addClass("cometchat_tabopen");
                            cometchat_closebox_bottom.addClass("cometchat_closebox_bottom_click");
                            jqcc.cometchat.setThemeVariable('openChatboxId', [id+'']);
                            jqcc.cometchat.setSessionVariable('openChatboxId', jqcc.cometchat.getThemeVariable('openChatboxId'));
                            if(olddata[id]!=1&&(jqcc.cometchat.getExternalVariable('initialize')!=1||isNaN(id))){
                                jqcc[settings.theme].updateChatbox(id);
                                olddata[id] = 1;
                            }
                        }else{
                            cometchat_user_popup.removeClass('cometchat_tabopen');
                            cometchat_user_id.removeClass('cometchat_tabclick').removeClass("cometchat_usertabclick");
                            var newPosition = ((cometchat_user_id.offset().left-$("#cometchat_chatboxes_wide").offset().left))-((Math.floor((cometchat_chatboxes.width()/152))-1)*152);
                            cometchat_chatboxes.scrollToCC(newPosition+'px', 0, function(){
                                jqcc[settings.theme].checkPopups();
                                jqcc[settings.theme].scrollBars();
                                cometchat_user_id.click();
                            });
                        }
                        jqcc[settings.theme].scrollDown(id);
                    }
                    if(jqcc.cometchat.getInternalVariable('updatingsession')!=1){
                        cometchat_user_popup.find(".cometchat_textarea").focus();
                    }
                });
                if(silent!=1){
                    cometchat_user_id.click();
                }
                attachPlaceholder("#cometchat_user_"+id+'_popup');
                jqcc.cometchat.setThemeArray('chatBoxesOrder', id, 0);
                chatboxOpened[id] = 0;
                jqcc.cometchat.orderChatboxes();
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
                                    jqcc[settings.theme].addMessages([{"from": incoming.from, "message": message, "self": incoming.self, "old": incoming.old, "id": incoming.id, "sent": incoming.sent}]);
                                }
                            }, 2000);
                        }
                    }else{
                        var selfstyle = '';
                        if(parseInt(incoming.self)==1){
                            fromname = language[10];
                            selfstyle = ' cometchat_self';
                        }else{
                            fromname = jqcc.cometchat.getThemeArray('buddylistName', incoming.from);
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
                            $("#cometchat_message_"+incoming.id).find(".cometchat_chatboxmessagecontent").html(message);
                        }else{
                            sentdata = '';
                            if(incoming.sent!=null){
                                var ts = incoming.sent;
                                sentdata = jqcc[settings.theme].getTimeDisplay(ts, incoming.from);
                            }
                            if(!settings.fullName){
                                fromname = fromname.split(" ")[0];
                            }
                            var msg = jqcc[settings.theme].processMessage('<div class="cometchat_chatboxmessage" id="cometchat_message_'+incoming.id+'"><span class="cometchat_chatboxmessagefrom'+selfstyle+'"><strong>'+fromname+'</strong>'+separator+'</span><span class="cometchat_chatboxmessagecontent'+selfstyle+'">'+message+'</span>'+sentdata+'</div>', selfstyle);
                            $("#cometchat_user_"+incoming.from+"_popup").find("div.cometchat_tabcontenttext").append(msg);
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
                            if(typeof (jqcc[settings.theme].addPopup)!=='undefined'){
                                jqcc[settings.theme].addPopup(incoming.from, 1, 1);
                            }
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
                    var chatBoxArray = jqcc.cometchat.getThemeVariable('openChatboxId');
                    if($.inArray(incoming.from + '',chatBoxArray)==-1&&settings.autoPopupChatbox==1&&shouldPop==1&&incoming.self==0){
                        jqcc.cometchat.tryClick(incoming.from);
                    }
                });
            },
            statusSendMessage: function(statustextarea){
                var message = $("#cometchat_optionsbutton_popup").find("textarea.cometchat_statustextarea").val();
                var oldMessage = jqcc.cometchat.getThemeArray('buddylistMessage', jqcc.cometchat.getThemeVariable('userid'));
                if(message!=''&&oldMessage!=message){
                    $('div.cometchat_statusbutton').html('<img src="'+baseUrl+'images/loader.gif" width="16">');
                    jqcc.cometchat.setThemeArray('buddylistMessage', jqcc.cometchat.getThemeVariable('userid'), message);
                    jqcc.cometchat.statusSendMessageSet(message, statustextarea);
                }else{
                    $('div.cometchat_statusbutton').text('<?php echo $language[57]; ?>');
                    setTimeout(function(){
                        $('div.cometchat_statusbutton').text('<?php echo $language[22]; ?>');
                    }, 1500);
                }
            },
            statusSendMessageSuccess: function(statustextarea){
                $(statustextarea).blur();
                setTimeout(function(){
                    $('div.cometchat_statusbutton').text('<?php echo $language[49]; ?>');
                }, 1800);
                setTimeout(function(){
                    $('div.cometchat_statusbutton').text('<?php echo $language[22]; ?>');
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
                $("#cometchat_userstab_icon").removeClass('cometchat_user_available2');
                $("#cometchat_userstab_icon").removeClass('cometchat_user_busy2');
                $("#cometchat_userstab_icon").removeClass('cometchat_user_invisible2');
                $("#cometchat_userstab_icon").removeClass('cometchat_user_offline2');
                $("#cometchat_userstab_icon").removeClass('cometchat_user_away2');
            },
            updateStatus: function(status){
                $("#cometchat_userstab_icon").addClass('cometchat_user_'+status+'2');
                $('span.cometchat_optionsstatus.'+status).css('text-decoration', 'underline');
            },
            goOffline: function(silent){
                jqcc.cometchat.setThemeVariable('offline', 1);
                jqcc[settings.theme].removeUnderline();
                if(silent!=1){
                    jqcc.cometchat.sendStatus('offline');
                }else{
                    jqcc[settings.theme].updateStatus('offline');
                }
                $('#cometchat_auth_popup').removeClass('cometchat_tabopen');
                $('#cometchat_userstab_popup').removeClass('cometchat_tabopen');
                $('#cometchat_userstab').removeClass('cometchat_userstabclick').removeClass('cometchat_tabclick');
                $('#cometchat_optionsbutton_popup').removeClass('cometchat_tabopen');
                $('#cometchat_optionsbutton').removeClass('cometchat_tabclick');
                jqcc.cometchat.setSessionVariable('buddylist', '0');
                $('#cometchat_userstab_text').html(language[17]);
                if(jqcc.cometchat.getThemeVariable('openChatboxId')!=''){
                    $("#cometchat_user_"+jqcc.cometchat.getThemeVariable('openChatboxId')+" .cometchat_closebox_bottom").click();
                    jqcc.cometchat.setThemeVariable('openChatboxId', '');
                    jqcc.cometchat.setSessionVariable('openChatboxId', jqcc.cometchat.getThemeVariable('openChatboxId'));
                }
                for(chatbox in jqcc.cometchat.getThemeVariable('chatBoxesOrder')){
                    if(jqcc.cometchat.getThemeVariable('chatBoxesOrder').hasOwnProperty(chatbox)){
                        if(jqcc.cometchat.getThemeVariable('chatBoxesOrder')[chatbox]!=null){
                            $("#cometchat_user_"+chatbox).find("div.cometchat_closebox_bottom").click();
                        }
                    }
                }
                $('.cometchat_container').remove();
                if(typeof window.cometuncall_function=='function'){
                    cometuncall_function(cometid);
                }
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
                            document.title = jqcc.cometchat.getThemeVariable('documentTitle');
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
                            if(value!=jqcc.cometchat.getSessionVariable('activeChatboxes')){
                                var newActiveChatboxes = {};
                                var oldActiveChatboxes = {};
                                if(value!=''){
                                    var count = 0;
                                    var chatboxData = value.split(/,/);
                                    for(i = 0; i<chatboxData.length; i++){
                                        var chatboxIds = chatboxData[i].split(/\|/);
                                        newActiveChatboxes[chatboxIds[0]] = chatboxIds[1];
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
                                        oldActiveChatboxes[chatboxIds[0]] = chatboxIds[1];
                                    }
                                }
                                for(r in newActiveChatboxes){
                                    if(newActiveChatboxes.hasOwnProperty(r)){
                                        if(typeof (jqcc[settings.theme].addPopup)!=='undefined'){
                                            jqcc[settings.theme].addPopup(r, parseInt(newActiveChatboxes[r]), 0);
                                        }
                                        if(parseInt(newActiveChatboxes[r])>0){
                                            jqcc.cometchat.setThemeVariable('newMessages', 1);
                                        }
                                    }
                                }
                                for(y in oldActiveChatboxes){
                                    if(oldActiveChatboxes.hasOwnProperty(y)){
                                        if(newActiveChatboxes[y]==null){
                                            $("#cometchat_user_"+y+"_popup").find("div.cometchat_closebox").click();
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
                            if(value!=jqcc.cometchat.getThemeVariable('openChatboxId')){
                                if(jqcc.cometchat.getThemeVariable('openChatboxId')!=''){
                                    jqcc.cometchat.tryClickSync(jqcc.cometchat.getThemeVariable('openChatboxId'));
                                }
                                if(value!=''){
                                    jqcc.cometchat.tryClickSync(value);
                                }
                            }
                            if(cc_states[4]==1){
                                jqcc[settings.theme].goOffline(1);
                            }
                        }
                        if(cc_states[4]==0&&jqcc.cometchat.getThemeVariable('offline')==1){
                            jqcc.cometchat.setThemeVariable('offline', 0);
                            $('#cometchat_userstab_text').html(language[9]+' ('+jqcc.cometchat.getThemeVariable('lastOnlineNumber')+')');
                            jqcc.cometchat.chatHeartbeat(1);
                            jqcc[settings.theme].removeUnderline();
                            jqcc[settings.theme].updateStatus('available');
                        }
                        if(cc_states[5]!=' '&&cc_states[5]!=''&&cc_states[5]!=jqcc.cometchat.getThemeVariable('trayOpen')&&settings.autoLoadModules==1){
                            $('#cometchat_trayicon_'+cc_states[5]).click();
                        }
                    }
                    jqcc.cometchat.setInternalVariable('updatingsession', '0');
                    clearTimeout(resynchTimer);
                    resynchTimer = setTimeout(function(){
                        jqcc[settings.theme].resynch();
                    }, 5000);
                }
            },
            setModuleAlert: function(id, number){
                if((!$('#cometchat_trayicon_'+id+'_popup').hasClass('cometchat_tabopen')) && (jqcc('#cometchat_trayicon_'+id+'_popup').length>0)){
                    if($("#cometchat_trayicon_"+id).find("span.cometchat_tabalert").length > 0){
                        $("#cometchat_trayicon_"+id).find("span.cometchat_tabalert").remove();
                    }
                    if(number!=0){
                        $("<span>").css('top', '-10px').addClass("cometchat_tabalert").html(number).appendTo($("#cometchat_trayicon_"+id));
                    }
                }
            },
            addPopup: function(id, amount, add){
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
                    var cometchat_user_id = $("#cometchat_user_"+id);
                    var cometchat_tabalert = cometchat_user_id.find("span.cometchat_tabalert");
                    jqcc.cometchat.userDoubleClick(id);
                    if(add==1){
                        if(cometchat_tabalert.length>0){
                            amount = parseInt(cometchat_user_id.find("span.cometchat_tabalert").html())+parseInt(amount);
                        }
                    }
                    if(amount==0){
                        cometchat_tabalert.remove();
                    }else{
                        if(cometchat_tabalert.length>0){
                            cometchat_tabalert.html(amount);
                        }else{
                            $("<span/>").css('top', '-5px').addClass("cometchat_tabalert").html(amount).appendTo($("#cometchat_user_"+id).find(".cometchat_closebox_bottom_status"));
                        }
                    }
                    jqcc.cometchat.setThemeArray('chatBoxesOrder', id, amount);
                    jqcc.cometchat.orderChatboxes();
                    jqcc[settings.theme].checkPopups();
                }
            },
            getTimeDisplay: function(ts, id){
                ts = parseInt(ts);
                var style = "style=\"display:none;\"";
                if(typeof (jqcc.ccchattime)!='undefined'&&jqcc.ccchattime.getEnabled(id,0)){
                    style = "style=\"display:inline;\"";
                }
                var time = getTimeDisplay(ts);
                if((ts+"").length == 10){
                    ts = ts*1000;
                }
                var timeDataStart = "<span class=\"cometchat_ts\" "+style+">("+time.hour+":"+time.minute+time.ap;
                var timeDataEnd = ")</span>";
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
                var id = data.id;
                var name = data.n;
                var status = data.s;
                var message = data.m;
                var avatar = data.a;
                var link = data.l;
                var isdevice = data.d;
                jqcc.cometchat.setThemeArray('buddylistStatus', id, status);
                jqcc.cometchat.setThemeArray('buddylistMessage', id, message);
                jqcc.cometchat.setThemeArray('buddylistAvatar', id, avatar);
                jqcc.cometchat.setThemeArray('buddylistName', id, name);
                jqcc.cometchat.setThemeArray('buddylistLink', id, link);
                jqcc.cometchat.setThemeArray('buddylistIsDevice', id, isdevice);
                if(chatboxOpened[id]!=null){
                    $("#cometchat_user_"+id).find("div.cometchat_closebox_bottom_status")
                            .removeClass("cometchat_available")
                            .removeClass("cometchat_busy")
                            .removeClass("cometchat_offline")
                            .removeClass("cometchat_away")
                            .addClass("cometchat_"+status);
                    if($("#cometchat_user_"+id+"_popup").length>0){
                        $("#cometchat_user_"+id+"_popup").find("div.cometchat_message").html(message);
                    }
                }
                jqcc.cometchat.setThemeVariable('trying', id, 5);
                if(id!=null&&id!=''&&name!=null&&name!=''){
                    if(typeof (jqcc[settings.theme].createChatboxData)!=='undefined'){
                        jqcc[settings.theme].createChatboxData(id, name, status, message, avatar, link, isdevice, silent, tryOldMessages);
                    }
                }
            },
            tooltip: function(id, message, orientation){
                var cometchat_tooltip = $('#cometchat_tooltip');
                cometchat_tooltip.css('display', 'none').removeClass("cometchat_tooltip_left").css('left', '-100000px').find(".cometchat_tooltip_content").html(message);
                var pos = $('#'+id).offset();
                var width = $('#'+id).width();
                var tooltipWidth = cometchat_tooltip.width();
                if(orientation==1){
                    cometchat_tooltip.css('left', (pos.left+width)-16).addClass("cometchat_tooltip_left");
                }else{
                    var leftposition = (pos.left+width)-tooltipWidth;
                    leftposition += 16;
                    cometchat_tooltip.removeClass("cometchat_tooltip_left").css('left', leftposition);
                }

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
                    jqcc[settings.theme].scrollBars();
                });
            },
            chatTab: function(){
                var cometchat_search = $("#cometchat_search");
                var cometchat_userscontent = $('#cometchat_userscontent');
                cometchat_search.click(function(){
                    var searchString = $(this).val();
                    if(searchString==language[18]){
                        cometchat_search.val('');
                        cometchat_search.addClass('cometchat_search_light');
                    }
                });
                cometchat_search.blur(function(){
                    var searchString = $(this).val();
                    if(searchString==''){
                        cometchat_search.addClass('cometchat_search_light');
                        cometchat_search.val(language[18]);
                    }
                });
                cometchat_search.keyup(function(){
                    var searchString = $(this).val();
                    if(searchString.length>0&&searchString!=language[18]){
                        cometchat_userscontent.find('div.cometchat_userlist').hide().parent().find('div.cometchat_userlist:icontains('+searchString+')').show();
                        cometchat_userscontent.find('.cometchat_subsubtitle').hide();
                        cometchat_search.removeClass('cometchat_search_light');
                    }else{
                        cometchat_userscontent.find('div.cometchat_userlist').show();
                        cometchat_userscontent.find('.cometchat_subsubtitle').show();
                    }
                });
                var cometchat_userstabtitle = $("#cometchat_userstab_popup").find("div.cometchat_userstabtitle");
                var cometchat_userstab = $('#cometchat_userstab');
                cometchat_userstabtitle.click(function(){
                    cometchat_userstab.click();
                });
                cometchat_userstabtitle.mouseenter(function(){
                    cometchat_userstabtitle.find("div.cometchat_minimizebox").addClass("cometchat_chatboxtraytitlemouseover");
                });
                cometchat_userstabtitle.mouseleave(function(){
                    cometchat_userstabtitle.find("div.cometchat_minimizebox").removeClass("cometchat_chatboxtraytitlemouseover");
                });
                cometchat_userstab.mouseover(function(){
                    $(this).addClass("cometchat_tabmouseover");
                });
                cometchat_userstab.mouseout(function(){
                    $(this).removeClass("cometchat_tabmouseover");
                });
                cometchat_userstab.click(function(){
                    if(jqcc.cometchat.getThemeVariable('trayOpen')!=''){
                        $("#cometchat_trayicon_"+jqcc.cometchat.getThemeVariable('trayOpen')+'_popup').removeClass("cometchat_tabopen");
                        $("#cometchat_trayicon_"+jqcc.cometchat.getThemeVariable('trayOpen')).removeClass("cometchat_trayclick");
                        jqcc.cometchat.setThemeVariable('trayOpen', '');
                        jqcc.cometchat.setSessionVariable('trayOpen', jqcc.cometchat.getThemeVariable('trayOpen'));
                    }
                    if(jqcc.cometchat.getThemeVariable('offline')==1){
                        jqcc.cometchat.setThemeVariable('offline', 0);
                        jqcc.cometchat.setThemeArray('buddylistStatus', jqcc.cometchat.getThemeVariable('userid'), 'available');
                        jqcc[settings.theme].removeUnderline();
                        jqcc[settings.theme].updateStatus('available');
                        $('#cometchat_userstab_text').html(language[9]+' ('+jqcc.cometchat.getThemeVariable('lastOnlineNumber')+')');
                        jqcc.cometchat.chatHeartbeat(1);
                        jqcc.cometchat.sendStatus('available');
                        $("#cometchat_optionsbutton_popup").find("span.available").click();
                    }
                    $('#cometchat_optionsbutton_popup').removeClass('cometchat_tabopen');
                    $('#cometchat_optionsbutton').removeClass('cometchat_tabclick');
                    if($(this).hasClass("cometchat_tabclick")){
                        jqcc.cometchat.setSessionVariable('buddylist', '0');
                    }else{
                        jqcc.cometchat.setSessionVariable('buddylist', '1');
                        $("#cometchat_tooltip").css('display', 'none');
                        $(".cometchat_userscontentavatar").find("img").each(function(){
                            if($(this).attr('original')){
                                $(this).attr("src", $(this).attr('original'));
                                $(this).removeAttr('original');
                            }
                        });
                    }
                    var baseLeft = $('#cometchat_base').position().left;
                    var barActualWidth = jqcc('#cometchat_base').width();
                    $('#cometchat_userstab_popup').css('left', baseLeft+barActualWidth-223-32);
                    $(this).toggleClass("cometchat_tabclick").toggleClass("cometchat_userstabclick");
                    $('#cometchat_userstab_popup').toggleClass("cometchat_tabopen");
                });
            },
            optionsButton: function(){
                var cometchat_optionsbutton_popup = $("#cometchat_optionsbutton_popup");
                var cometchat_auth_popup = $("#cometchat_auth_popup");
                cometchat_optionsbutton_popup.find("span.cometchat_gooffline").click(function(){
                    jqcc[settings.theme].goOffline();
                });
                $("#cometchat_soundnotifications").click(function(event){
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
                cometchat_optionsbutton_popup.find("span.available").click(function(event){
                    if(jqcc.cometchat.getThemeVariable('currentStatus')!='available'){
                        jqcc.cometchat.setThemeArray('buddylistStatus', jqcc.cometchat.getThemeVariable('userid'), 'available');
                        jqcc[settings.theme].removeUnderline();
                        jqcc.cometchat.sendStatus('available');
                    }
                });
                cometchat_optionsbutton_popup.find("div.cometchat_statusbutton").click(function(event){
                    jqcc[settings.theme].statusSendMessage();
                });
                $("#guestsname").find("div.cometchat_guestnamebutton").click(function(event){
                    jqcc[settings.theme].setGuestName();
                });
                cometchat_optionsbutton_popup.find("span.busy").click(function(event){
                    if(jqcc.cometchat.getThemeVariable('currentStatus')!='busy'){
                        jqcc.cometchat.setThemeArray('buddylistStatus', jqcc.cometchat.getThemeVariable('userid'), 'busy');
                        jqcc[settings.theme].removeUnderline();
                        jqcc.cometchat.sendStatus('busy');
                    }
                });
                cometchat_optionsbutton_popup.find("span.invisible").click(function(event){
                    if(jqcc.cometchat.getThemeVariable('currentStatus')!='invisible'){
                        jqcc.cometchat.setThemeArray('buddylistStatus', jqcc.cometchat.getThemeVariable('userid'), 'invisible');
                        jqcc[settings.theme].removeUnderline();
                        jqcc.cometchat.sendStatus('invisible');
                    }
                });
                cometchat_optionsbutton_popup.find("textarea.cometchat_statustextarea").keydown(function(event){
                    return jqcc.cometchat.statusKeydown(event, this);
                });
                cometchat_optionsbutton_popup.find("input.cometchat_guestnametextbox").keydown(function(event){
                    return jqcc.cometchat.guestnameKeydown(event, this);
                });
                var cometchat_optionsbutton = $('#cometchat_optionsbutton');
                cometchat_optionsbutton.mouseover(function(){
                    if(!cometchat_optionsbutton_popup.hasClass("cometchat_tabopen") && !cometchat_auth_popup.hasClass("cometchat_tabopen")){
                        if(jqcc.cometchat.getThemeVariable('loggedout')==0){
                            if(tooltipPriority==0){
                                jqcc[settings.theme].tooltip('cometchat_optionsbutton', language[0]);
                            }
                        }else{
                            if(tooltipPriority==0){
                                jqcc[settings.theme].tooltip('cometchat_optionsbutton', jqcc(this).attr("title"));
                            }
                        }
                    }
                    $(this).addClass("cometchat_tabmouseover");
                });
                cometchat_optionsbutton.mouseout(function(){
                    $(this).removeClass("cometchat_tabmouseover");
                    if(tooltipPriority==0){
                        $("#cometchat_tooltip").css('display', 'none');
                    }
                });
                cometchat_optionsbutton.click(function(){
                    if(jqcc.cometchat.getThemeVariable('trayOpen')!=''){
                        $("#cometchat_trayicon_"+jqcc.cometchat.getThemeVariable('trayOpen')+'_popup').removeClass("cometchat_tabopen");
                        $("#cometchat_trayicon_"+jqcc.cometchat.getThemeVariable('trayOpen')).removeClass("cometchat_trayclick");
                        jqcc.cometchat.setThemeVariable('trayOpen', '');
                        jqcc.cometchat.setSessionVariable('trayOpen', jqcc.cometchat.getThemeVariable('trayOpen'));
                    }
                    if(jqcc.cometchat.getThemeVariable('loggedout')==0){
                        if(jqcc.cometchat.getThemeVariable('offline')==1){
                            jqcc.cometchat.setThemeVariable('offline', 0);
                            $('#cometchat_userstab_text').html(language[9]+' ('+jqcc.cometchat.getThemeVariable('lastOnlineNumber')+')');
                            jqcc.cometchat.chatHeartbeat(1);
                            cometchat_optionsbutton_popup.find("span.available").click();
                        }
                        $("#cometchat_tooltip").css('display', 'none');
                        var baseLeft = $('#cometchat_base').position().left;
                        var barActualWidth = $('#cometchat_base').width();
                        $('#cometchat_optionsbutton_popup').css('left', baseLeft+barActualWidth-223-32);
                        $(this).toggleClass("cometchat_tabclick");
                        cometchat_optionsbutton_popup.toggleClass("cometchat_tabopen");
                        $('#cometchat_userstab_popup').removeClass('cometchat_tabopen');
                        $('#cometchat_userstab').removeClass('cometchat_userstabclick').removeClass('cometchat_tabclick');
                        jqcc.cometchat.setSessionVariable('buddylist', '0');
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
                    }else{
                        if(settings.ccauth.enabled == "1"){
                            $("#cometchat_tooltip").css('display', 'none');
                            var baseLeft = $('#cometchat_base').position().left;
                            var cometchat_hide = $('#cometchat_hide').outerWidth(false);
                            cometchat_auth_popup.css('right', baseLeft+cometchat_hide);
                            $(this).toggleClass("cometchat_tabclick");
                            cometchat_auth_popup.toggleClass("cometchat_tabopen");
                        }else if(language[16]!=''){
                            location.href = language[16];
                        }
                    }
                });
                var auth_cometchat_userstabtitle = cometchat_auth_popup.find("div.cometchat_userstabtitle");
                var auth_cometchat_minimize = auth_cometchat_userstabtitle.find("div.cometchat_minimizebox");

                auth_cometchat_userstabtitle.click(function(){
                    cometchat_optionsbutton.click();
                });
                auth_cometchat_userstabtitle.mouseenter(function(){
                    auth_cometchat_minimize.addClass("cometchat_chatboxtraytitlemouseover");
                });
                auth_cometchat_userstabtitle.mouseleave(function(){
                    auth_cometchat_minimize.removeClass("cometchat_chatboxtraytitlemouseover");
                });

                var cometchat_userstabtitle = cometchat_optionsbutton_popup.find(".cometchat_userstabtitle");
                var auth_logout = cometchat_userstabtitle.find("div#cometchat_authlogout");
                cometchat_userstabtitle.click(function(){
                    $('#cometchat_optionsbutton').click();
                });
                cometchat_userstabtitle.mouseenter(function(){
                    cometchat_userstabtitle.find("div.cometchat_minimizebox").addClass("cometchat_chatboxtraytitlemouseover");
                });
                cometchat_userstabtitle.mouseleave(function(){
                    cometchat_userstabtitle.find("div.cometchat_minimizebox").removeClass("cometchat_chatboxtraytitlemouseover");
                });
                auth_logout.mouseenter(function(){
                    auth_logout.css('opacity','1');
                    cometchat_optionsbutton_popup.find("div.cometchat_minimizebox").removeClass("cometchat_chatboxtraytitlemouseover");
                });
                auth_logout.mouseleave(function(){
                    auth_logout.css('opacity','0.5');
                    cometchat_optionsbutton_popup.find("div.cometchat_minimizebox").addClass("cometchat_chatboxtraytitlemouseover");
                });
                logout_click();
                function logout_click(){
                    auth_logout.click(function(event){
                        auth_logout.unbind('click');
                        event.stopPropagation();
                        auth_logout.css('background','url('+baseUrl+'themes/standard/images/loading.gif) no-repeat top left');
                        jqcc.ajax({
                            url: baseUrl+'functions/login/logout.php',
                            dataType: 'jsonp',
                            success: function(){
                                if(typeof(cometuncall_function)==="function"){
                                    cometuncall_function(jqcc.cometchat.getThemeVariable('cometid'));
                                    jqcc.cometchat.setThemeVariable('cometid','');
                                }
                                auth_logout.css('background','url('+baseUrl+'themes/standard/images/logout.png) no-repeat top left');
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

                var adjustedHeight = chatboxtextarea.clientHeight;
                var maxHeight = 94;
                clearTimeout(typingTimer);
                jqcc.cometchat.setThemeVariable('typingTo', id);
                typingTimer = setTimeout(function(){
                    jqcc.cometchat.resetTypingTo(id);
                }, settings.typingTimeout);
                if(maxHeight>adjustedHeight){
                    adjustedHeight = Math.max(chatboxtextarea.scrollHeight, adjustedHeight);
                    if(maxHeight)
                        adjustedHeight = Math.min(maxHeight, adjustedHeight);
                    if(adjustedHeight>chatboxtextarea.clientHeight){
                        $(chatboxtextarea).css('height', adjustedHeight+4+'px');
                        $("#cometchat_user_"+id+"_popup").find("div.cometchat_tabcontenttext").css('height', (chatboxHeight-(adjustedHeight-25))+'px');
                        $("#cometchat_user_"+id+"_popup").find("div.slimScrollDiv").css('height', (chatboxHeight-(adjustedHeight-25))+'px');
                    }
                }else{
                    $(chatboxtextarea).css('overflow-y', 'auto');
                }
            },
            chatboxKeydown: function(event, chatboxtextarea, id, force){
                var condition = 1;
                if((event.keyCode==13&&event.shiftKey==0)||force==1 && !$(chatboxtextarea).hasClass('placeholder')){
                    var message = $(chatboxtextarea).val();
                    message = message.replace(/^\s+|\s+$/g, "");
                    $(chatboxtextarea).val('');
                    $(chatboxtextarea).css('height', '18px');
                    $("#cometchat_user_"+id+"_popup").find("div.slimScrollDiv").css('height', ((chatboxHeight)+11)+'px');
                    $("#cometchat_user_"+id+"_popup").find("div.cometchat_tabcontenttext").css('height', (chatboxHeight)+'px');
                    $(chatboxtextarea).css('overflow-y', 'hidden');
                    $(chatboxtextarea).focus();
                    if(settings.floodControl){
                        condition = ((Math.floor(new Date().getTime()))-lastmessagetime>2000);
                    }
                    if(message!=''){
                        if(condition){
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
            scrollBars: function(silent){
                var hidden = 0;
                var change = 0;
                var change2 = 0;
                var cometchat_chatboxes = $("#cometchat_chatboxes");
                var cometchat_chatbox_right = $('#cometchat_chatbox_right');
                var cometchat_chatbox_left = $('#cometchat_chatbox_left');
                if(cometchat_chatbox_right.hasClass('cometchat_chatbox_right_last')){
                    change = 1;
                }
                if(cometchat_chatbox_right.hasClass('cometchat_chatbox_lr')){
                    change2 = 1;
                }
                if(cometchat_chatboxes.scrollLeft()==0){
                    cometchat_chatbox_left.addClass('cometchat_chatbox_left_last').find('span.cometchat_tabtext').html('0');
                    hidden++;
                }else{
                    var number = Math.floor(cometchat_chatboxes.scrollLeft()/152);
                    cometchat_chatbox_left.find('span.cometchat_tabtext').html(number);
                    cometchat_chatbox_left.removeClass('cometchat_chatbox_left_last');
                }
                if((cometchat_chatboxes.scrollLeft()+cometchat_chatboxes.width())==$("#cometchat_chatboxes_wide").width()){
                    cometchat_chatbox_right.addClass('cometchat_chatbox_right_last').find('span.cometchat_tabtext').html('0');
                    hidden++;
                }else{
                    var number = Math.floor(($("#cometchat_chatboxes_wide").width()-(cometchat_chatboxes.scrollLeft()+cometchat_chatboxes.width()))/152);
                    cometchat_chatbox_right.removeClass('cometchat_chatbox_right_last').find('span.cometchat_tabtext').html(number);
                }
                if(hidden==2){
                    cometchat_chatbox_right.addClass('cometchat_chatbox_lr');
                    cometchat_chatbox_left.addClass('cometchat_chatbox_lr');
                }else{
                    cometchat_chatbox_right.removeClass('cometchat_chatbox_lr');
                    cometchat_chatbox_left.removeClass('cometchat_chatbox_lr');
                }
                if((!cometchat_chatbox_right.hasClass('cometchat_chatbox_right_last')&&change==1)||(cometchat_chatbox_right.hasClass('cometcha t_chatbox_right_last')&&change==0)||(!cometchat_chatbox_right.hasClass('cometchat_chatbox_lr')&&change2==1)||(cometchat_chatbox_right.hasClass('cometchat_chatbox_lr')&&change2==0)){
                    jqcc[settings.theme].windowResize(silent);
                }
            },
            scrollDown: function(id,scrollForce){
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
                $("#cometchat_tabcontenttext_"+id).html('');
                if(typeof (jqcc[settings.theme].addMessages)!=='undefined'&&data.hasOwnProperty('messages')){
                    jqcc[settings.theme].addMessages(data['messages']);
                }
                jqcc[settings.theme].scrollDown(id);
            },
            windowResize: function(silent){
                var baseWidth = $(window).width();
                var extraWidth = trayWidth+32;
                if(extraWidth<80){
                    extraWidth = 80;
                }
                var cometchat_base = $('#cometchat_base');
                var cometchat_chatboxes = $('#cometchat_chatboxes');
                if(settings.barType=='fixed'){
                    cometchat_base.css('width', settings.barWidth);
                    if(settings.barAlign=='center'){
                        var distance = (baseWidth-settings.barWidth)/2;
                        cometchat_base.css('left', distance);
                    }
                    if(settings.barAlign=='right'){
                        var distance = (baseWidth-settings.barWidth);
                        cometchat_base.css('left', distance-settings.barPadding);
                    }
                    if(settings.barAlign=='left'){
                        cometchat_base.css('left', settings.barPadding);
                    }
                }else{
                    if(baseWidth<400+extraWidth+settings.barPadding+20){
                        baseWidth = 400+extraWidth+settings.barPadding+20;
                    }
                    cometchat_base.css('left', settings.barPadding);
                    cometchat_base.css('width', baseWidth-(settings.barPadding*2));
                }
                if(cometchat_base.length){
                    var baseLeft = cometchat_base.position().left;
                    var barActualWidth = cometchat_base.width();
                    $('#cometchat_userstab_popup').css('left', baseLeft+barActualWidth-223-32);
                    $('#cometchat_optionsbutton_popup').css('left', baseLeft+barActualWidth-223-32);
                }
                if(jqcc.cometchat.getThemeVariable('trayOpen')!=''){
                    $('#cometchat_trayicon_'+jqcc.cometchat.getThemeVariable('trayOpen')+'_popup').css('left', $('#cometchat_trayicon_'+jqcc.cometchat.getThemeVariable('trayOpen')).offset().left).css('width', trayicon[jqcc.cometchat.getThemeVariable('trayOpen')][4]);
                }
                if($('#cometchat_chatboxes_wide').width()<=(cometchat_base.width()-26-178-44-extraWidth)){
                    cometchat_chatboxes.css('width', $('#cometchat_chatboxes_wide').width());
                    cometchat_chatboxes.scrollToCC("0px", 0);
                }else{
                    var change = cometchat_chatboxes.width();
                    cometchat_chatboxes.css('width', Math.floor((cometchat_base.width()-26-178-44-extraWidth)/152)*152);
                    var newChange = cometchat_chatboxes.width();
                    if(change!=newChange){
                        cometchat_chatboxes.scrollToCC("-=152px", 0);
                    }
                }
                if(jqcc.cometchat.getThemeVariable('openChatboxId')!=''&&silent!=1){
                    if(($("#cometchat_user_"+jqcc.cometchat.getThemeVariable('openChatboxId')).offset().left<(cometchat_chatboxes.offset().left+cometchat_chatboxes.width()))&&($("#cometchat_user_"+jqcc.cometchat.getThemeVariable('openChatboxId')).offset().left-cometchat_chatboxes.offset().left)>=0){
                        $("#cometchat_user_"+jqcc.cometchat.getThemeVariable('openChatboxId')+'_popup').css('left', baseLeft+$("#cometchat_user_"+jqcc.cometchat.getThemeVariable('openChatboxId')).position().left-((chatboxWidth)-230+77));
                    }else{
                        $('#cometchat_user_'+jqcc.cometchat.getThemeVariable('openChatboxId')+'_popup').removeClass('cometchat_tabopen');
                        $('#cometchat_user_'+jqcc.cometchat.getThemeVariable('openChatboxId')).removeClass('cometchat_tabclick').removeClass("cometchat_usertabclick");
                        var newPosition = (($("#cometchat_user_"+jqcc.cometchat.getThemeVariable('openChatboxId')).offset().left-$("#cometchat_chatboxes_wide").offset().left))-((Math.floor((cometchat_chatboxes.width()/152))-1)*152);
                        cometchat_chatboxes.scrollToCC(newPosition+'px', 0, function(){
                            $("#cometchat_user_"+jqcc.cometchat.getThemeVariable('openChatboxId')).click();
                        });
                    }
                }
                jqcc[settings.theme].checkPopups(silent);
                jqcc[settings.theme].scrollBars(silent);
            },
            chatWith: function(id){
                if(jqcc.cometchat.getThemeVariable('loggedout')==0){
                    if(jqcc.cometchat.getThemeVariable('offline')==1){
                        jqcc.cometchat.setThemeVariable('offline', 0);
                        $('#cometchat_userstab_text').html(language[9]+' ('+jqcc.cometchat.getThemeVariable('lastOnlineNumber')+')');
                        jqcc.cometchat.chatHeartbeat(1);
                        $("#cometchat_optionsbutton_popup").find("span.available").click();
                    }
                    if(typeof (jqcc[settings.theme].createChatbox)!=='undefined' && jqcc.cometchat.getUserID() != id){
                        jqcc[settings.theme].createChatbox(id, jqcc.cometchat.getThemeArray('buddylistName', id), jqcc.cometchat.getThemeArray('buddylistStatus', id), jqcc.cometchat.getThemeArray('buddylistMessage', id), jqcc.cometchat.getThemeArray('buddylistAvatar', id), jqcc.cometchat.getThemeArray('buddylistLink', id), jqcc.cometchat.getThemeArray('buddylistIsDevice', id));
                    }
                }
            },
            scrollFix: function(){
                var elements = ['cometchat_base', 'cometchat_userstab_popup', 'cometchat_optionsbutton_popup', 'cometchat_tooltip', 'cometchat_hidden'];
                if(jqcc.cometchat.getThemeVariable('openChatboxId')!=''){
                    elements.push('cometchat_user_'+jqcc.cometchat.getThemeVariable('openChatboxId')+'_popup');
                }
                if(jqcc.cometchat.getThemeVariable('trayOpen')!=''&&jqcc.cometchat.getThemeVariable('trayOpen')!=0){
                    elements.push('cometchat_trayicon_'+jqcc.cometchat.getThemeVariable('trayOpen')+'_popup');
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
                var cometchat_tabalertlr_left = $("#cometchat_chatbox_left").find("span.cometchat_tabalertlr");
                var cometchat_tabalertlr_right = $("#cometchat_chatbox_right").find("span.cometchat_tabalertlr");
                cometchat_tabalertlr_left.html('0').css('display', 'none');
                cometchat_tabalertlr_right.html('0').css('display', 'none');
                $("#cometchat_chatboxes_wide").find("span.cometchat_tabalert").each(function(){
                    if(($(this).parent().offset().left<($("#cometchat_chatboxes").offset().left+$("#cometchat_chatboxes").width()))&&($(this).parent().offset().left-$("#cometchat_chatboxes").offset().left)>=0){
                        $(this).css('display', 'block');
                    }else{
                        $(this).css('display', 'none');
                        if(($(this).parent().offset().left-$("#cometchat_chatboxes").offset().left)>=0){
                            cometchat_tabalertlr_right.html(parseInt($("#cometchat_chatbox_right").find("span.cometchat_tabalertlr").html())+parseInt($(this).html())).css('display', 'block');
                        }else{
                            cometchat_tabalertlr_right.html(parseInt($("#cometchat_chatbox_left").find("span.cometchat_tabalertlr").html())+parseInt($(this).html())).css('display', 'block');
                        }
                    }
                });
            },
            launchModule: function(id){
                if(!$('#cometchat_trayicon_'+id+'_popup').hasClass('cometchat_tabopen')){
                    $("#cometchat_trayicon_"+id).click();
                }
            },
            toggleModule: function(id){
                $("#cometchat_trayicon_"+id).click();
            },
            closeModule: function(id){
                if($('#cometchat_trayicon_'+id+'_popup').hasClass('cometchat_tabopen')){
                    $("#cometchat_trayicon_"+id).click();
                }
            },
            joinChatroom: function(roomid, inviteid, roomname){
                $("#cometchat_trayicon_chatrooms").click();
                jqcc('#cometchat_trayicon_chatrooms_iframe,.cometchat_embed_chatrooms').attr('src', baseUrl+'modules/chatrooms/index.php?roomid='+roomid+'&inviteid='+inviteid+'&roomname='+roomname+'&basedata='+jqcc.cometchat.getThemeVariable('baseData'));
                jqcc.cometchat.setThemeVariable('openChatboxId', '');
            },
            hideBar: function(){
                jqcc[settings.theme].goOffline();
                $('#cometchat').css('display', 'none');
                $('#cometchat_hidden').css('display', 'block');
                $.cookie(settings.cookiePrefix+"hidebar", '1', {path: '/', expires: 365});
            },
            closeTooltip: function(){
                $("#cometchat_tooltip").css('display', 'none');
            },
            scrollToTop: function(){
                $("html,body").animate({scrollTop: 0}, {"duration": "slow"});
            },
            reinitialize: function(){
                if(jqcc.cometchat.getThemeVariable('loggedout')==1){
                    $('#cometchat_auth_popup').removeClass("cometchat_tabopen");
                    $("#cometchat_optionsbutton_icon").removeClass("cometchat_optionsimages_ccauth");
                    $("#cometchat_optionsbutton").removeClass("cometchat_optionsimages_exclamation");
                    $("#cometchat_optionsbutton_icon").css('display', 'block');
                    $("body").append(msg_beep);
                    $("#cometchat").append(option_button);
                    $("#cometchat").append(user_tab);
                    $("#cometchat_base").append(usertab2);
                    $("#cometchat_base").append(chat_right);
                    $("#cometchat_base").append(chat_boxes);
                    $("#cometchat_base").append(chat_left);
                    $("#cometchat_userstab").show();
                    $("#cometchat_chatboxes").show();
                    $("#cometchat_chatbox_left").show();
                    $("#cometchat_chatbox_right").show();
                    jqcc.cometchat.setThemeVariable('loggedout', 0);
                    jqcc.cometchat.setExternalVariable('initialize', '1');
                    jqcc.cometchat.chatHeartbeat();
                    $("#cometchat_userstab").click();
                }
            },
            updateHtml: function(id, temp){
                if($("#cometchat_user_"+id+"_popup").length>0){
                    document.getElementById("cometchat_tabcontenttext_"+id).innerHTML = '<div>'+temp+'</div>';
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
                    id = $(listing).parents('.cometchat_userlist').attr('id');
                }
                id = id.substr(19);
                if(typeof (jqcc[settings.theme].createChatbox)!=='undefined'){
                    jqcc[settings.theme].createChatbox(id, jqcc.cometchat.getThemeArray('buddylistName', id), jqcc.cometchat.getThemeArray('buddylistStatus', id), jqcc.cometchat.getThemeArray('buddylistMessage', id), jqcc.cometchat.getThemeArray('buddylistAvatar', id), jqcc.cometchat.getThemeArray('buddylistLink', id), jqcc.cometchat.getThemeArray('buddylistIsDevice', id));
                }
            },
            messageBeep: function(baseUrl){
                $('<audio id="messageBeep" style="display:none;"><source src="'+baseUrl+'mp3/beep.mp3" type="audio/mpeg"><source src="'+baseUrl+'mp3/beep.ogg" type="audio/ogg"><source src="'+baseUrl+'mp3/beep.wav" type="audio/wav"></audio>').appendTo($("body"));
            },
            ccClicked: function(id){
                $(id).click();
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
                $(".cometchat_tabpopup").each(function(index){
                    if($(this).hasClass('cometchat_tabopen')){
                        $('#'+$(this).attr('id')).find('div.cometchat_minimizebox').click();
                    }
                });
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
                            if(parseInt(incoming.self)==1){
                                fromname = language[10];
                                selfstyle = ' cometchat_self';
                            }else{
                                fromname = jqcc.cometchat.getThemeArray('buddylistName', incoming.from);
                            }

                            var message = jqcc.cometchat.processcontrolmessage(incoming);

                            if(message == null){
                                return;
                            }

                           if(incoming.sent!=null){
                                var ts = incoming.sent;
                                sentdata = jqcc[settings.theme].getTimeDisplay(ts, incoming.from);
                            }
                            var separator = ':&nbsp;&nbsp;';
                            var msg = jqcc[settings.theme].processMessage('<div class="cometchat_chatboxmessage" id="cometchat_message_'+incoming.id+'"><span class="cometchat_chatboxmessagefrom'+selfstyle+'"><strong>'+fromname+'</strong>'+separator+'</span><span class="cometchat_chatboxmessagecontent'+selfstyle+'">'+message+'</span>'+sentdata+'</div>', selfstyle);
                            oldMessages+=msg;
                        });
                    }
                });
                jqcc('#cometchat_tabcontenttext_'+id).prepend(oldMessages);
                $('#cometchat_prependMessages_'+id).text(language[83]);
                if((count - parseInt(jqcc.cometchat.getThemeVariable('prependLimit')) < 0)){
                    $('#cometchat_prependMessages_'+id).text(language[84]);
                    jqcc('#cometchat_prependMessages_'+id).attr('onclick','');
                    jqcc('#cometchat_prependMessages_'+id).css('cursor','default');
                }else{
                    jqcc('#cometchat_prependMessages_'+id).attr('onclick','jqcc.standard.prependMessagesInit('+id+')');
                }
            }
        };
    })();
})(jqcc);

if(typeof(jqcc.standard) === "undefined"){
    jqcc.standard=function(){};
}

jqcc.extend(jqcc.standard, jqcc.ccstandard);