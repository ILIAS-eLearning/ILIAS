(function($){
    $.cchangout = (function(){
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
        var chatbottom = [];
        var resynch = 0;
        var reload = 0;
        var lastmessagetime = Math.floor(new Date().getTime());
        var favicon;
        var msg_beep = '';
        var option_button = '';
        var user_tab = '';
        var user_tab2 = '';
        var chat_boxes = '';
        var checkfirstmessage;
        var chatboxHeight = parseInt('<?php echo $chatboxHeight; ?>');
        var chatboxWidth = parseInt('<?php echo $chatboxWidth; ?>');
        var removeOpenChatboxId = function(id){
            var openChatBoxIds = jqcc.cometchat.getSessionVariable('openChatboxId').split(',');
            openChatBoxIds.splice(openChatBoxIds.indexOf(id), 1);
            jqcc.cometchat.setSessionVariable('openChatboxId', openChatBoxIds.join(','));
            jqcc.cometchat.setThemeVariable('openChatboxId', openChatBoxIds);
        };
        var addOpenChatboxId = function(id){
            var openChatBoxIds = jqcc.cometchat.getSessionVariable('openChatboxId').split(',');
            if(openChatBoxIds.indexOf(id)>-1){
                return;
            }
            if(openChatBoxIds[0]==""){
                openChatBoxIds[0] = id;
            }else{
                openChatBoxIds.push(id);
            }
            jqcc.cometchat.setSessionVariable('openChatboxId', openChatBoxIds.join(','));
            jqcc.cometchat.setThemeVariable('openChatboxId', openChatBoxIds);
        };
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
                if(settings.windowFavicon==1){
                    favicon = new Favico({
                        animation: 'pop'
                    });
                }
                $("body").append('<div id="cometchat"></div><div id="cometchat_tooltip"><div class="cometchat_tooltip_content"></div></div>');
                if(settings.showModules==1){
                    trayData += '<div class="cometchat_tabsubtitle">';
                    for(x in trayicon){
                        if(trayicon.hasOwnProperty(x)){
                            var icon = trayicon[x];
                            var onclick = '';
                            if(x=='home'){
                                onclick = 'onclick="javascript:window.open(\''+trayicon[x][2]+'\',\'_self\')"';
                            }else if(x=='scrolltotop'){
                                onclick = 'onclick="javascript:jqcc.cometchat.scrollToTop()"';
                            }else{
                            	onclick = 'onclick="jqcc.cometchat.lightbox(\''+x+'\')"';
                            }
                            trayData += '<span id="cometchat_trayicon_'+x+'" class="cometchat_trayiconimage cometchat_tooltip" title="'+trayicon[x][1]+'" '+onclick+'><img class="'+x+'icon" src="'+baseUrl+'themes/'+settings.theme+'/images/modules/'+x+'.png" onerror="jqcc.'+settings.theme+'.iconNotFound(this,\''+icon[0]+'\')"></span>';
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
                var optionsbutton = '';
                var optionsbuttonpop = '';
                var ccauthpopup = '';
                var ccauthlogout = '';
                var usertab = '';
                var usertabpop = '';
                if(settings.ccauth.enabled=="1"){
                    ccauthlogout = '<div class="cometchat_tooltip" id="cometchat_authlogout" title="'+language[80]+'"></div>';
                }
                if(settings.showSettingsTab==1){
                    optionsbutton = '<div id="cometchat_optionsbutton" class="cometchat_tab"><div id="cometchat_optionsbutton_icon" class="cometchat_optionsimages"></div></div>';
                    optionsbuttonpop = '<div id="cometchat_optionsbutton_popup" class="cometchat_tabpopup" style="display:none"><div class="cometchat_userstabtitle"><div class="cometchat_userstabtitletext">'+language[0]+'</div>'+ccauthlogout+'<div class="cometchat_minimizebox cometchat_tooltip" id="cometchat_minimize_optionsbutton_popup" title="'+language[63]+'"></div><br clear="all"/></div><div class="cometchat_tabsubtitle">'+language[1]+'</div><div class="cometchat_tabcontent cometchat_optionstyle"><div id="guestsname"><strong>'+language[43]+'</strong><br/><input type="text" class="cometchat_guestnametextbox"/><div class="cometchat_guestnamebutton">'+language[44]+'</div></div><strong>'+language[2]+'</strong><br/><textarea class="cometchat_statustextarea"></textarea><div class="cometchat_statusbutton">'+language[22]+'</div><div class="cometchat_statusinputs"><strong>'+language[23]+'</strong><br/><span class="cometchat_user_available"></span><span class="cometchat_optionsstatus available">'+language[3]+'</span><span class="cometchat_optionsstatus2 cometchat_user_invisible" ></span><span class="cometchat_optionsstatus invisible1">'+language[5]+'</span><div style="clear:both"></div><span class="cometchat_optionsstatus2 cometchat_user_busy"></span><span class="cometchat_optionsstatus busy">'+language[4]+'</span><span class="cometchat_optionsstatus2 cometchat_user_invisible"></span><span class="cometchat_optionsstatus cometchat_gooffline offline">'+language[11]+'</span><br clear="all"/></div><div class="cometchat_options_disable"><div><input type="checkbox" id="cometchat_soundnotifications" style="vertical-align: -2px;">'+language[13]+'</div><div style="clear:both"></div><div><input type="checkbox" id="cometchat_popupnotifications" style="vertical-align: -2px;">'+language[24]+'</div></div></div></div>';
                }
                if(settings.ccauth.enabled=="1"){
                    ccauthpopup = '<div id="cometchat_auth_popup" class="cometchat_tabpopup" style="display:none"><div class="cometchat_userstabtitle"><div class="cometchat_userstabtitletext">'+language[77]+'</div><div class="cometchat_minimizebox cometchat_tooltip" id="cometchat_minimize_auth_popup" title="'+language[78]+'"></div><br clear="all"/></div><div class="cometchat_tabsubtitle">'+language[79]+'</div><div class="cometchat_tabcontent cometchat_optionstyle"><div id="social_login">';
                    var authactive = settings.ccauth.active;
                    authactive.forEach(function(auth) {
                        ccauthpopup += '<img onclick="window.open(\''+baseUrl+'functions/login/signin.php?network='+auth.toLowerCase()+'\',\'socialwindow\',\'location=0,status=0,scrollbars=0,width=1000,height=600\')" src="'+baseUrl+'themes/'+settings.theme+'/images/login'+auth.toLowerCase()+'.png" class="auth_options"></img>';
                    });
                    ccauthpopup += '</div></div></div>';
                }
                if(settings.showOnlineTab==1){
                    usertab = '<span id="cometchat_userstab" class="cometchat_tab"><span id="cometchat_userstab_icon"></span><span id="cometchat_userstab_text">'+language[9]+' ('+number+')</span></span>';
                    usertabpop = '<div id="cometchat_userstab_popup" class="cometchat_tabpopup" style="display:none"><div class="cometchat_userstabtitle"><div class="cometchat_userstabtitletext">'+language[12]+'</div><div class="cometchat_minimizebox cometchat_tooltip" id="cometchat_minimize_userstab_popup" title="'+language[62]+'"></div><br clear="all"/></div>'+trayData+'<div class="cometchat_tabsubtitle" id="cometchat_searchbar"><input type="text" name="cometchat_search" class="cometchat_search cometchat_search_light" id="cometchat_search" placeholder="'+language[18]+'"></div><div class="cometchat_tabcontent cometchat_tabstyle"><div id="cometchat_userscontent"><div id="cometchat_userslist"><div class="cometchat_nofriends">'+language[41]+'</div></div></div></div></div>';
                }
                var baseCode = '<div id="cometchat_base">'+optionsbutton+''+usertab+'<div id="cometchat_chatboxes"><div id="cometchat_chatboxes_wide"></div></div></div>'+optionsbuttonpop+''+ccauthpopup+''+usertabpop;
                document.getElementById('cometchat').innerHTML = baseCode;
                if(settings.showSettingsTab==0){
                    $('#cometchat_userstab').addClass('cometchat_extra_width');
                    $('#cometchat_userstab_popup').find('div.cometchat_tabstyle').addClass('cometchat_border_bottom');
                }
                if(jqcc().slimScroll){
                    $('#cometchat_userscontent').slimScroll({height: $('#cometchat_userscontent').outerHeight(false)});
                }
                jqcc[settings.theme].optionsButton();
                jqcc[settings.theme].chatTab();
                $('#cometchat_chatboxes').attr('unselectable', 'on').css('MozUserSelect', 'none').bind('selectstart.ui', function(){
                    return false;
                });
                $('#cometchat_userscontent').attr('unselectable', 'on').css('MozUserSelect', 'none').bind('selectstart.ui', function(){
                    return false;
                });
                $('.cometchat_trayicon').attr('unselectable', 'on').css('MozUserSelect', 'none').bind('selectstart.ui', function(){
                    return false;
                });
                $('.cometchat_tab').attr('unselectable', 'on').css('MozUserSelect', 'none').bind('selectstart.ui', function(){
                    return false;
                });
                var cometchat_chatbox_right = $('#cometchat_chatbox_right');
                var cometchat_chatbox_left = $('#cometchat_chatbox_left');
                cometchat_chatbox_right.bind('click', function(){
                    jqcc[settings.theme].moveRight();
                });
                cometchat_chatbox_right.bind('click', function(){
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
                $('body .cometchat_tooltip').live('mouseover',function(){
                    var currElem = $(this);
                    var tId = currElem.attr('id');
                    var tMsg = currElem.data('title');
                    if(tMsg != null){
                        $.hangout.tooltip(tId, tMsg);
                    }
                });
                $('body .cometchat_tooltip').live('mouseout',function(){
                    $.hangout.closeTooltip();
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
                document.onmousemove = function(e){
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
                        var cometchat_user_popup = $("#cometchat_user_"+buddy.id+"_popup");
                        cometchat_user_popup.find("div.cometchat_tabtitle")
                                .removeClass("cometchat_tabtitle_available")
                                .removeClass("cometchat_tabtitle_busy")
                                .removeClass("cometchat_tabtitle_offline")
                                .removeClass("cometchat_tabtitle_away")
                                .addClass('cometchat_tabtitle_'+buddy.s);
                        if(cometchat_user_popup.length>0){
                            cometchat_user_popup.find("div.cometchat_message").html(buddy.m);
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
                    if(buddy.d==1){
						icon = '<span class="cometchat_mobile"><div class="cometchat_dot"></div></span>';
                    }
					buddylisttempavatar += group+'<div id="cometchat_userlist_'+buddy.id+'" class="cometchat_userlist" onmouseover="jqcc(this).addClass(\'cometchat_userlist_hover\');" onmouseout="jqcc(this).removeClass(\'cometchat_userlist_hover\');"><span class="cometchat_userscontentavatar cometchat_buddy_'+buddy.s+'"><img class="cometchat_userscontentavatarimage" original="'+buddy.a+'"></span><span class="cometchat_userscontentname">'+longname+'</span>'+icon+'</div>';
                    var usercontentstatus = buddy.s;
					if(buddy.d==1){
						usercontentstatus = 'mobile';
						icon = '<div class="cometchat_dot"></div>';
                    }
                    buddylisttemp += group+'<div id="cometchat_userlist_'+buddy.id+'" class="cometchat_userlist" onmouseover="jqcc(this).addClass(\'cometchat_userlist_hover\');" onmouseout="jqcc(this).removeClass(\'cometchat_userlist_hover\');"><span class="cometchat_userscontentname">'+longname+'</span><span class="cometchat_userscontentdot cometchat_'+buddy.s+'">'+icon+'</span></div>';
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
                if(document.getElementById('cometchat_userslist')){
                    if(bltemp!=''){
                        document.getElementById('cometchat_userslist').style.display = 'block';
                        jqcc.cometchat.replaceHtml('cometchat_userslist', '<div>'+bltemp+'</div>');
                    }else{
                        $('#cometchat_userslist').html('<div class="cometchat_nofriends">'+language[14]+'</div>');
                    }
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
                attachPlaceholder('#cometchat_searchbar');
            },
            loggedOut: function(){
                document.title = jqcc.cometchat.getThemeVariable('documentTitle');
                if(settings.ccauth.enabled=="1"){
                    $("#cometchat_optionsbutton").addClass("cometchat_optionsimages_ccauth");
                    $("#cometchat_optionsbutton").attr("title",language[77]);
                }else{
                    $("#cometchat_optionsbutton").addClass("cometchat_optionsimages_exclamation");
                    $("#cometchat_optionsbutton").attr("title",language[8]);
                }
                $("#cometchat_optionsbutton_icon, #cometchat_userstab, #cometchat_chatboxes, #cometchat_chatbox_left, #cometchat_chatbox_right").hide();
                msg_beep = $("#messageBeep").detach();
                option_button = $("#cometchat_optionsbutton_popup").detach();
                user_tab = $("#cometchat_userstab_popup").detach();
                user_tab2 = $("#cometchat_userstab").detach();
                chat_boxes = $("#cometchat_chatboxes").detach();
                $("div.cometchat_tabclick").removeClass("cometchat_tabclick");
                $("span#cometchat_userstab").removeClass("cometchat_tabclick");
                $("div.cometchat_tabopen").removeClass("cometchat_tabopen");
                jqcc.cometchat.setSessionVariable('openChatboxId', '');
                jqcc.cometchat.setThemeVariable('loggedout', 1);
            },
            userStatus: function(item){
                var cometchat_optionsbutton_popup = $('#cometchat_optionsbutton_popup');
                cometchat_optionsbutton_popup.find('.cometchat_statustextarea').val(item.m);
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
                    cometchat_optionsbutton_popup.find("div.cometchat_tabsubtitle").html(language[45]);
                }
                jqcc.cometchat.setThemeVariable('userid', item.id);
                jqcc.cometchat.setThemeArray('buddylistStatus', item.id, item.s);
                jqcc.cometchat.setThemeArray('buddylistMessage', item.id, item.m);
                jqcc.cometchat.setThemeArray('buddylistName', item.id, item.n);
                jqcc.cometchat.setThemeArray('buddylistAvatar', item.id, item.a);
                jqcc.cometchat.setThemeArray('buddylistLink', item.id, item.l);
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
            removeChatbox:function(id){
                $("#cometchat_user_"+id+'_popup').remove();
                $("#cometchat_user_"+id).remove();
                $('#cometchat_chatboxes_wide').width($('#cometchat_chatboxes_wide').width()-chatboxWidth-5);
                $('#cometchat_chatboxes').scrollToCC("-="+chatboxWidth+"px");
                jqcc[settings.theme].windowResize();
                jqcc.cometchat.setThemeArray('chatBoxesOrder', id, null);
                delete(chatboxOpened[id]);
                olddata[id] = 0;
                jqcc.cometchat.orderChatboxes();
                removeOpenChatboxId(id);
            },
            minimizeChatbox:function(id){
                cometchat_user_id = jqcc('#cometchat_user_'+id);
                cometchat_user_id.removeClass("cometchat_tabclick").removeClass("cometchat_usertabclick");
                $("#cometchat_user_"+id+'_popup').removeClass("cometchat_tabopen");
                cometchat_user_id.find("div.cometchat_closebox_bottom").removeClass("cometchat_closebox_bottom_click");
                jqcc.cometchat.setThemeArray('chatBoxesOrder', id, 0);
                chatboxOpened[id] = 0;
                jqcc.cometchat.orderChatboxes();
                removeOpenChatboxId(id);
            },
            createChatboxTab:function(id,name){
                $("<span/>").attr("id", "cometchat_user_"+id).attr("amount", 0).addClass("cometchat_tab").html('<div class="cometchat_icon"></div><div class="cometchat_user_shortname">'+name+'</div><div class="cometchat_closebox_bottom cometchat_tooltip" id="cometchat_closebox_bottom_'+id+'" data-title="'+language[74]+'"></div>').appendTo($("#cometchat_chatboxes_wide"));
                var cometchat_chatboxes = $('#cometchat_chatboxes');
                var cometchat_user_id = $("#cometchat_user_"+id);
                cometchat_user_id.mouseenter(function(){
                    $(this).addClass("cometchat_tabmouseover");
                    cometchat_user_id.find("div.cometchat_user_shortname").addClass("cometchat_tabmouseovertext");
                });
                cometchat_user_id.mouseleave(function(){
                    $(this).removeClass("cometchat_tabmouseover");
                    cometchat_user_id.find("div.cometchat_user_shortname").removeClass("cometchat_tabmouseovertext");
                });
                cometchat_user_id.click(function(){
                    if($(this).hasClass('cometchat_tabclick')){
                        cometchat_user_id.removeClass("cometchat_tabclick").removeClass("cometchat_usertabclick");
                        $("#cometchat_user_"+id+'_popup').removeClass("cometchat_tabopen");
                        cometchat_user_id.find("div.cometchat_closebox_bottom").removeClass("cometchat_closebox_bottom_click");
                        jqcc.cometchat.setThemeArray('chatBoxesOrder', id, 0);
                        chatboxOpened[id] = 0;
                        jqcc.cometchat.orderChatboxes();
                        removeOpenChatboxId(id);
                    }else{
                        var baseLeft = $('#cometchat_base').position().left;
                        if((cometchat_user_id.offset().left<(cometchat_chatboxes.offset().left+cometchat_chatboxes.width()))&&(cometchat_user_id.offset().left-cometchat_chatboxes.offset().left)>=0){
                            $("#cometchat_user_"+id+'_popup').css('left', baseLeft+cometchat_user_id.position().left+1);
                            $(this).addClass("cometchat_tabclick").addClass("cometchat_usertabclick");
                            $("#cometchat_user_"+id+'_popup').addClass("cometchat_tabopen").addClass('cometchat_tabopen_bottom');
                            cometchat_user_id.find("div.cometchat_closebox_bottom").addClass("cometchat_closebox_bottom_click");
                            chatboxOpened[id] = 1;
                            addOpenChatboxId(id);
                            if(olddata[id]!=1||isNaN(id)){
                                jqcc[settings.theme].updateChatbox(id);
                                olddata[id] = 1;
                            }
                        }else{
                            cometchat_user_id.removeClass('cometchat_tabclick').removeClass("cometchat_usertabclick");
                            var newPosition = ((cometchat_user_id.offset().left-$("#cometchat_chatboxes_wide").offset().left))-((Math.floor((cometchat_chatboxes.width()/chatboxWidth))-6)*chatboxWidth+5);
                            cometchat_chatboxes.scrollToCC(newPosition+'px', 0, function(){
                                jqcc[settings.theme].checkPopups();
                                jqcc[settings.theme].scrollBars();
                            });
                            chatboxOpened[id] = 1;
                            addOpenChatboxId(id);
                        }
                        jqcc[settings.theme].scrollDown(id);
                    }
                    if(jqcc.cometchat.getInternalVariable('updatingsession')!=1){
                        $("#cometchat_user_"+id+'_popup').find("textarea.cometchat_textarea").focus();
                    }
                });
            },

            createChatboxData: function(id, name, status, message, avatar, link, isdevice, silent, tryOldMessages){
                var cometchat_chatboxes = $("#cometchat_chatboxes");
                if(chatboxOpened[id]!=null){
                    if(!$("#cometchat_user_"+id).hasClass('cometchat_tabclick')&&silent!=1){
                        if(($("#cometchat_user_"+id).offset().left<(cometchat_chatboxes.offset().left+cometchat_chatboxes.width()))&&($("#cometchat_user_"+id).offset().left-cometchat_chatboxes.offset().left)>=0){
                            $("#cometchat_user_"+id).click();
                        }else{
                            $("#cometchat_chatboxes_wide").find(".cometchat_tabalert").css('display', 'none');
                            $("#cometchat_user_"+id).remove();
                            jqcc.hangout.createChatboxTab(id,name);
                            var ms = settings.scrollTime;
                            if(jqcc.cometchat.getExternalVariable('initialize')==1){
                                ms = 0;
                            }
                            cometchat_chatboxes.scrollToCC(("-="+chatboxWidth+"px"), ms, function(){
                                $("#cometchat_user_"+id).click();
                                jqcc[settings.theme].scrollBars();
                                jqcc[settings.theme].checkPopups();
                                jqcc.hangout.windowResize();
                            });
                        }
                    }
                    jqcc[settings.theme].scrollBars();
                    return;
                }

                $('#cometchat_chatboxes_wide').width($('#cometchat_chatboxes_wide').width()+chatboxWidth+5);

                shortname = name;
                longname = name;
                jqcc.hangout.createChatboxTab(id,shortname);

                var pluginshtml = '';
                var smiliehtml = '';
                var num = parseInt(((chatboxWidth-12)/21));
                if(jqcc.cometchat.getThemeArray('isJabber', id)!=1){
                    var pluginslength = settings.plugins.length;
                    if(pluginslength>0){
                        pluginshtml += '<div class="cometchat_plugins">';
                        for(var i = 0; i<pluginslength; i++){
                            var name = 'cc'+settings.plugins[i];
                            if(typeof ($[name])=='object'){
                                if(settings.plugins[i]!='chattime'&&settings.plugins[i]!='smilies'){
                                    var extraStyle = '';
                                    if(i>num){
                                        extraStyle = " style='margin-top:5px;'"
                                    }
                                    pluginshtml += '<div id="cometchat_'+settings.plugins[i]+'_'+id+'" class="cometchat_pluginsicon cometchat_'+settings.plugins[i]+' cometchat_tooltip" name="'+name+'" to="'+id+'" chatroommode="0" title="'+$[name].getTitle()+'" '+extraStyle+'></div>';
                                } else if(settings.plugins[i]=='smilies') {
                                    num ++;
                                    smiliehtml = '<div class="cometchat_pluginsicon cometchat_smilies" style="margin-top:5px;" name="'+name+'" to="'+id+'" chatroommode="0" ></div>';
                                }
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
                var prepend = '';
                var jabber = jqcc.cometchat.getThemeArray('isJabber', id);

                if(jqcc.cometchat.getThemeVariable('prependLimit') != '0' && jabber != 1){
                    prepend = '<div class=\"cometchat_prependMessages\" onclick\="jqcc.hangout.prependMessagesInit('+id+')\" id = \"cometchat_prependMessages_'+id+'\">'+language[83]+'</div>';
                }
                $("<div/>").attr("id", "cometchat_user_"+id+"_popup").addClass("cometchat_tabpopup").css('display', 'none').html('<div class="cometchat_tabtitle"><div class="cometchat_icon"></div><span id="cometchat_typing_'+id+'" class="cometchat_typing"></span><div class="cometchat_name">'+startlink+longname+endlink+'</div></div><div class="cometchat_message">'+message+'</div><div class="cometchat_tabsubtitle">'+pluginshtml+'<div style="clear:both"></div>'+'</div>'+prepend+'<div class="cometchat_tabcontent"><div class="cometchat_tabcontenttext" id="cometchat_tabcontenttext_'+id+'"></div><div class="cometchat_tabcontentinput">'+smiliehtml+'<textarea class="cometchat_textarea" placeholder="'+language[55]+'"></textarea><div class="cometchat_tabcontentsubmit"></div></div><div style="clear:both"></div></div>').appendTo($("#cometchat"));
                var cometchat_user_popup = $("#cometchat_user_"+id+'_popup');
                if(status=='available'||status=='away'||status=='busy'){
                    cometchat_user_popup.find("div.cometchat_tabtitle").addClass('cometchat_tabtitle_'+status);
                }else{
                    cometchat_user_popup.find("div.cometchat_tabtitle").addClass('cometchat_tabtitle_offline');
                }
                jqcc.cometchat.setThemeArray('chatBoxesOrder', id, 0);
                chatboxOpened[id] = 0;
                jqcc.cometchat.orderChatboxes();
                var cometchat_user_id = $("#cometchat_user_"+id);
                cometchat_user_popup.find('.cometchat_pluginsicon').click(function(){
                    var name = $(this).attr('name');
                    var to = $(this).attr('to');
                    var chatroommode = $(this).attr('chatroommode');
                    var controlparameters = {"to":to, "chatroommode":chatroommode};
                    jqcc[name].init(controlparameters);
                });
                var cometchat_closebox_bottom = cometchat_user_id.find("div.cometchat_closebox_bottom");
                cometchat_closebox_bottom.mouseenter(function(){
                    $(this).addClass("cometchat_closebox_bottomhover");
                });
                cometchat_closebox_bottom.mouseleave(function(){
                    $(this).removeClass("cometchat_closebox_bottomhover");
                });
                cometchat_closebox_bottom.click(function(){
                    jqcc.hangout.removeChatbox(id);
                });
                var cometchat_tabcontenttext = $("#cometchat_user_"+id+'_popup').find("div.cometchat_tabcontenttext");
                if(jqcc().slimScroll){
                    cometchat_tabcontenttext.slimScroll({height: ((chatboxHeight+10))+'px'},function(){
                        jqcc[settings.theme].scrollDown(id);
                    });
                }
                chatbottom[id] = 1;
                cometchat_tabcontenttext.scroll(function(){
                    if(jqcc().slimScroll)
                    if(cometchat_tabcontenttext.height()<($("#cometchat_user_"+id+'_popup').find("div.slimScrollBar").height()+$("#cometchat_user_"+id+'_popup').find("div.slimScrollBar").position().top)){
                        chatbottom[id] = 1;
                        $('#cometchat_tabcontenttext_'+id).find(".cometchat_new_message_unread").remove();
                    }else{
                        chatbottom[id] = 0;
                    }
                });
                var cometchat_textarea = $("#cometchat_user_"+id+'_popup').find(".cometchat_textarea");
                cometchat_textarea.keydown(function(event){
                    return jqcc[settings.theme].chatboxKeydown(event, this, id);
                });
                $("#cometchat_user_"+id+'_popup').find("div.cometchat_tabcontentsubmit").click(function(event){
                    return jqcc[settings.theme].chatboxKeydown(event, cometchat_textarea, id, 1);
                });
                cometchat_textarea.keyup(function(event){
                    return jqcc[settings.theme].chatboxKeyup(event, this, id);
                });
                var cometchat_tabtitle = cometchat_user_popup.find("div.cometchat_tabtitle");
                cometchat_tabtitle.append('<div class="cometchat_closebox cometchat_tooltip" id="cometchat_closebox_'+id+'" title="'+language[74]+'"></div><div class="cometchat_minimizebox cometchat_tooltip" id="cometchat_minimizebox_'+id+'" title="'+language[75]+'"></div><br clear="all"/>');
                var cometchat_closebox = cometchat_tabtitle.find("div.cometchat_closebox");
                var cometchat_minimizebox = cometchat_tabtitle.find("div.cometchat_minimizebox");
                cometchat_closebox.mouseenter(function(){
                    $(this).addClass("cometchat_chatboxmouseoverclose");
                    cometchat_minimizebox.removeClass("cometchat_chatboxtraytitlemouseover");
                });
                cometchat_closebox.mouseleave(function(){
                    $(this).removeClass("cometchat_chatboxmouseoverclose");
                    cometchat_minimizebox.addClass("cometchat_chatboxtraytitlemouseover");
                });
                cometchat_closebox.click(function(){
                    jqcc.hangout.removeChatbox(id);
                });
                cometchat_tabtitle.click(function(){
                    $("#cometchat_user_"+id).click();
                });
                cometchat_tabtitle.mouseenter(function(){
                    cometchat_minimizebox.addClass("cometchat_chatboxtraytitlemouseover");
                });
                cometchat_tabtitle.mouseleave(function(){
                    cometchat_minimizebox.removeClass("cometchat_chatboxtraytitlemouseover");
                });
                if(silent!=1){
                    cometchat_user_id.click();
                }

                var setButton = setInterval(function(){
                    if(jqcc("#cometchat_user_"+id+'_popup').find('div.cometchat_prependMessages').length == 0){
                        jqcc[settings.theme].scrollDown(id);
                    }
                },100);

                setTimeout(function(){
                    clearInterval(setButton);
                },1000);
                jqcc[settings.theme].windowResize();
                attachPlaceholder("#cometchat_user_"+id+'_popup');
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

                    if(message == null){
                        return;
                    }
                    if(typeof(incoming.nopopup) === "undefined" || incoming.nopopup =="") {
                        incoming.nopopup = 0;
                    }
                    if(incoming.self ==1 ){
                         incoming.nopopup = 1;
                    }
					checkfirstmessage = ($("#cometchat_tabcontenttext_"+incoming.from+" .cometchat_chatboxmessage").length) ? 0 : 1;
                    var chatboxopen = 0;
                    var shouldPop = 0;
                    if($('#cometchat_user_'+incoming.from).length == 0){
                            shouldPop = 1;
                    }
                    if(jqcc.cometchat.getThemeArray('trying', incoming.from)===undefined){
                        if(typeof (jqcc[settings.theme].createChatbox)!=='undefined' && incoming.nopopup == 0){
                            var silent = 1;
                            if(chatboxOpened[incoming.from]!=null && (!$("#cometchat_user_"+incoming.from).hasClass('cometchat_tabclick'))){
                                silent = 0;
                            }
                            jqcc[settings.theme].createChatbox(incoming.from, jqcc.cometchat.getThemeArray('buddylistName', incoming.from), jqcc.cometchat.getThemeArray('buddylistStatus', incoming.from), jqcc.cometchat.getThemeArray('buddylistMessage', incoming.from), jqcc.cometchat.getThemeArray('buddylistAvatar', incoming.from), jqcc.cometchat.getThemeArray('buddylistLink', incoming.from), jqcc.cometchat.getThemeArray('buddylistIsDevice', incoming.from), silent, 1);
                            chatboxopen = 0;
                        }
                    }
                    if(chatboxOpened[incoming.from]!=1&&incoming.old!=1){
						if (incoming.self != 1 && settings.messageBeep == 1) {
							if ($.cookie(settings.cookiePrefix + "sound") && $.cookie(settings.cookiePrefix + "sound") == 'true') {
							} else {
								jqcc[settings.theme].playSound();
							}
						}
                        jqcc[settings.theme].addPopup(incoming.from, 1, 1);
                    }
						if(incoming.old!=1){
							if (incoming.self != 1 && settings.messageBeep == 1) {
								if ($.cookie(settings.cookiePrefix + "sound") && $.cookie(settings.cookiePrefix + "sound") == 'true') {
								} else {
									jqcc[settings.theme].playSound();
								}
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
                            var selfstyle = ' cometchat_other';
                            var selfstyleCont = ' cometchat_other_content';
                            var selfstyleAvatar = '';
                            if(parseInt(incoming.self)==1){
                                fromname = language[10];
                                selfstyle = ' cometchat_self';
                                selfstyleCont = ' cometchat_self_content';
                                selfstyleAvatar = ' cometchat_self_avatar';
                            }else{
                                fromname = jqcc.cometchat.getThemeArray('buddylistName', incoming.from);
                            }
                            separator = ':&nbsp;&nbsp;';
                            if($("#cometchat_message_"+incoming.id).length>0){
                                $("#cometchat_message_"+incoming.id).html(message);
                            }else{
                                sentdata = '';
                                if(incoming.sent!=null){
                                    var ts = incoming.sent;
                                    sentdata = jqcc[settings.theme].getTimeDisplay(ts);
                                }
                                if(!settings.fullName){
                                    fromname = fromname.split(" ")[0];
                                }
                                var msg = '';
                                var addMessage = 0;
                                var avatar = baseUrl+"themes/hangout/images/noavatar.png";
                                var cometchat_tabcontenttext_incomingfrom = $('#cometchat_tabcontenttext_'+incoming.from);
                                if(parseInt(incoming.self)==1){
                                    if(jqcc.cometchat.getThemeArray('buddylistAvatar', jqcc.cometchat.getThemeVariable('userid'))!=""){
                                        avatar = jqcc.cometchat.getThemeArray('buddylistAvatar', jqcc.cometchat.getThemeVariable('userid'));
                                    }
                                    if(cometchat_tabcontenttext_incomingfrom.find(".cometchat_chatboxmessage:last").hasClass('self')){
                                        if($("#cometchat_message_"+incoming.id)>0){
                                            $("#cometchat_message_"+incoming.id).html(message);
                                        }else{
                                            if(cometchat_tabcontenttext_incomingfrom.find('.cometchat_chatboxmessage:last.self div.cometchat_chatboxmessagecontent').hasClass('cometchat_self_content')){
                                                cometchat_tabcontenttext_incomingfrom.find('.cometchat_chatboxmessage:last.self .cometchat_self_content span.cometchat_ts').before('<div id="cometchat_message_'+incoming.id+'" class="'+selfstyle+'">'+message+'</div>');
                                            }
                                        }
                                        cometchat_tabcontenttext_incomingfrom.find('.cometchat_chatboxmessage:last .cometchat_self_content span.cometchat_ts').html(sentdata);
                                    }else{
                                        msg = jqcc[settings.theme].processMessage('<div class="cometchat_chatboxmessage self" ><div class="cometchat_chatboxmessagecontent '+selfstyleCont+'"><div id="cometchat_message_'+incoming.id+'" class="'+selfstyle+'">'+message+'</div><span class="cometchat_ts">'+sentdata+'</span></div><div class="cometchat_chatboxmessagefrom '+selfstyleAvatar+'"><a href="'+jqcc.cometchat.getThemeArray('buddylistLink', jqcc.cometchat.getThemeVariable('userid'))+'"><img src="'+avatar+'" title="'+fromname+'"/></a></div></div>', selfstyle);
                                        addMessage = 1;
                                    }
                                }else{
                                    if(jqcc.cometchat.getThemeArray('buddylistAvatar', incoming.from)!=""){
                                        avatar = jqcc.cometchat.getThemeArray('buddylistAvatar', incoming.from);
                                    }
                                    if(cometchat_tabcontenttext_incomingfrom.find('div.cometchat_chatboxmessage:last').hasClass('other')){
                                        if($("#cometchat_message_"+incoming.id)>0){
                                            $("#cometchat_message_"+incoming.id).html(message);
                                        }else{
                                            if(cometchat_tabcontenttext_incomingfrom.find('.cometchat_chatboxmessage:last.other div.cometchat_chatboxmessagecontent  ').hasClass('cometchat_other_content')){
                                                cometchat_tabcontenttext_incomingfrom.find('.cometchat_chatboxmessage:last .cometchat_other_content .cometchat_message_name').before('<div id="cometchat_message_'+incoming.id+'" class="'+selfstyle+' cometchat_other_noarrow">'+message+'</div>');
                                            }
                                        }
                                        cometchat_tabcontenttext_incomingfrom.find('.cometchat_chatboxmessage:last .cometchat_other_content span.cometchat_ts').html(sentdata);
                                    }else{
                                        msg = (jqcc[settings.theme].processMessage('<div class="cometchat_chatboxmessage other"><div class="cometchat_chatboxmessagefrom '+selfstyleAvatar+'"><a href="'+jqcc.cometchat.getThemeArray('buddylistLink', incoming.from)+'"><img src="'+avatar+'" title="'+fromname+'"/></a></div><div class="cometchat_chatboxmessagecontent '+selfstyleCont+'"><div id="cometchat_message_'+incoming.id+'" class="'+selfstyle+'">'+message+'</div><span class="cometchat_message_name">'+fromname+' <strong>.</strong></span><span class="cometchat_ts">'+sentdata+'</span></div></div>', selfstyle));
                                        addMessage = 1;
                                    }
                                }
                                if(addMessage==1&&chatboxopen==0){
                                    $("#cometchat_user_"+incoming.from+"_popup").find("div.cometchat_tabcontenttext").append(msg);
                                }
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
						}
                    var newMessage = 0;
                    var cometchat_tabcontenttext_incomingfrom = $('#cometchat_tabcontenttext_'+incoming.from);
                    var cometchat_message_incomingid = $("#cometchat_message_"+incoming.id);

                    if((jqcc.cometchat.getThemeVariable('isMini')==1||($.inArray( incoming.from+"", jqcc.cometchat.getThemeVariable('openChatboxId')) === -1 ))&&incoming.self!=1&&settings.desktopNotifications==1&&incoming.old==0){
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
                    var totalHeight = 0;
                    cometchat_tabcontenttext_incomingfrom.children().each(function(){
                        totalHeight = totalHeight+$(this).outerHeight(false);
                    });
                    if(newMessage>0){
                        if(cometchat_tabcontenttext_incomingfrom.outerHeight(false)<totalHeight){
                            cometchat_tabcontenttext_incomingfrom.append('<div class="cometchat_new_message_unread"><a herf="javascript:void(0)" onClick="javascript:jqcc.hangout.scrollDown('+incoming.from+');jqcc(\'#cometchat_tabcontenttext_'+incoming.from+' .cometchat_new_message_unread\').remove();">&#9660 '+language[54]+'</a></div>');
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
                $("#cometchat_optionsbutton_popup").find(".busy").css('text-decoration', 'none');
                $("#cometchat_optionsbutton_popup").find(".invisible1").css('text-decoration', 'none');
                $("#cometchat_optionsbutton_popup").find(".offline").css('text-decoration', 'none');
                $("#cometchat_optionsbutton_popup").find(".available").css('text-decoration', 'none');
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
                $("div.cometchat_userstabtitle").attr('class','cometchat_userstabtitle cometchat_userstabtitle_'+status);
            },
            goOffline: function(silent){
                jqcc.cometchat.setThemeVariable('offline', 1);
                jqcc[settings.theme].removeUnderline();
                if(silent!=1){
                    jqcc.cometchat.sendStatus('offline');
                }else{
                    jqcc[settings.theme].updateStatus('offline');
                }
                $('#cometchat_userstab').removeClass('cometchat_userstabclick').removeClass('cometchat_tabclick');
                $('div.cometchat_tabopen').removeClass('cometchat_tabopen');
                $('div.cometchat_tabclick').removeClass('cometchat_tabclick');
                jqcc.cometchat.setSessionVariable('buddylist', '0');
                $('#cometchat_userstab_text').html(language[17]);
                for(var chatbox in jqcc.cometchat.getThemeVariable('chatBoxesOrder')){
                    if(jqcc.cometchat.getThemeVariable('chatBoxesOrder').hasOwnProperty(chatbox)){
                        if(jqcc.cometchat.getThemeVariable('chatBoxesOrder')[chatbox]!==null){
                           jqcc.hangout.removeChatbox(chatbox);
                        }
                    }
                }
                $('.cometchat_container').remove();
                if(typeof window.cometuncall_function=='function'){
                    cometuncall_function(cometid);
                }
                jqcc.cometchat.setSessionVariable('openChatboxId', '');
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
                    if(resynch<1){
                        jqcc[settings.theme].scrollDown(id);

                        resynch = 2;
                    }
                    if(chatbottom[id]==1){
                        $('#cometchat_tabcontenttext_'+id).find(".cometchat_new_message_unread").remove();
                        jqcc[settings.theme].scrollDown(id);

                    }else{
                        $('#cometchat_tabcontenttext_'+id).find(".cometchat_new_message_unread").show();
                    }
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
                    if(resynch<0){
                        resynch = 1;
                    }
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
                                    var chatboxData = value.split(/,/);
                                    var count = 0;
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
                                        jqcc[settings.theme].addPopup(r, parseInt(newActiveChatboxes[r]), 0);
                                        if(parseInt(newActiveChatboxes[r])>0){
                                            jqcc.cometchat.setThemeVariable('newMessages', 1);
                                        }
                                    }
                                }
                                for(y in oldActiveChatboxes){
                                    if(oldActiveChatboxes.hasOwnProperty(y)){
                                        if(newActiveChatboxes[y]==null){
                                            jqcc.hangout.removeChatbox(y);
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
                                jqcc.cometchat.setSessionVariable('openChatboxId', value);
                                if(reload==0){
                                    reload = 1;
                                    var temp = value.split(",");
                                    jqcc.cometchat.setThemeVariable('openChatboxId', temp);
                                    for(i = 0; i<temp.length; i++){
                                        if(!$("#cometchat_user_"+temp[i]+"_popup").hasClass('cometchat_tabopen')){
                                            $("#cometchat_user_"+temp[i]).click();
                                        }
                                    }
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
                    }
                    jqcc.cometchat.setInternalVariable('updatingsession', '0');
                    $('#cometchat_chatboxes').scrollToCC("0px",200,function(){
                        jqcc.hangout.windowResize();
                    });
                    clearTimeout(resynchTimer);
                    resynchTimer = setTimeout(function(){
                        jqcc[settings.theme].resynch();
                    }, 5000);
                }
            },
            setModuleAlert: function(id, number){
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
                    jqcc.cometchat.userDoubleClick(id);
                    var cometchat_user_id = $("#cometchat_user_"+id);
                    amount = parseInt(cometchat_user_id.attr('amount'))+parseInt(amount);
                    if(amount==0){
                        cometchat_user_id.removeClass('cometchat_new_message').attr('amount', 0);
                    }else{
                        cometchat_user_id.addClass('cometchat_new_message').attr('amount', amount);
                    }
                    cometchat_user_id.click(function(){
                        cometchat_user_id.removeClass('cometchat_new_message').attr('amount', 0);
                        jqcc.cometchat.setThemeVariable('newMessages', 0);
                    });
                    jqcc.cometchat.setThemeArray('chatBoxesOrder', id, amount);
                    jqcc.cometchat.orderChatboxes();
                    jqcc[settings.theme].checkPopups();
                }
                if(settings.showSettingsTab==1&&settings.showOnlineTab==0){
                    $("#cometchat_chatboxes_wide span").click(function(){
                        if($('#cometchat_optionsbutton').hasClass('cometchat_tabclick')){
                            $('#cometchat_optionsbutton').removeClass('cometchat_tabclick').removeClass('cometchat_usertabclick');
                            $('#cometchat_optionsbutton_popup').removeClass('cometchat_tabopen');
                        }
                    });
                }
            },
            getTimeDisplay: function(ts){
                ts = parseInt(ts);
                var time = getTimeDisplay(ts);
                if((ts+"").length == 10){
                    ts = ts*1000;
                }
				return ts<jqcc.cometchat.getThemeVariable('todays12am') ? time.month+' '+time.date+', '+time.hour+":"+time.minute+' '+time.ap : time.hour+":"+time.minute+time.ap;
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
                    $("#cometchat_user_"+id+"_popup").find("div.cometchat_tabtitle")
                            .removeClass("cometchat_tabtitle_available")
                            .removeClass("cometchat_tabtitle_busy")
                            .removeClass("cometchat_tabtitle_offline")
                            .removeClass("cometchat_tabtitle_away")
                            .addClass('cometchat_tabtitle_'+status);
                    if($("#cometchat_user_"+id+"_popup").length>0){
                        $("#cometchat_user_"+id+"_popup").find("div.cometchat_message").html(message);
                    }
                }
                jqcc.cometchat.setThemeArray('trying', id, 5);
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
                var width = $('#'+id).outerWidth(true);
                var tooltipWidth = cometchat_tooltip.width();
                if(orientation==1){
                    cometchat_tooltip.css('left', (pos.left+width)).addClass("cometchat_tooltip_left");
                }else{
                    var tooltipWidth = cometchat_tooltip.width();
                    var tooltipHeight = cometchat_tooltip.height();
                    var leftposition = (pos.left+14)-tooltipWidth+11;
                    if(id == 'cometchat_userstab') leftposition = pos.left+3;
                    var topposition = pos.top-tooltipHeight-10;
                    cometchat_tooltip.removeClass("cometchat_tooltip_left").css({'left':leftposition,'top':topposition});
                }
                cometchat_tooltip.css('display', 'block');
            },
            moveBar: function(relativePixels){
                $("#cometchat_chatboxes_wide").find(".cometchat_tabalert").css('display', 'none');
                var ms = settings.scrollTime;
                if(jqcc.cometchat.getExternalVariable('initialize')==1){
                    ms = 0;
                }
                var cometchat_chatboxes = $("#cometchat_chatboxes");
                cometchat_chatboxes.scrollToCC(relativePixels, ms, function(){
                    $.each(chatboxOpened, function(openChatboxId, state){
                        if(state==1){
                            if(($("#cometchat_user_"+openChatboxId).offset().left<(cometchat_chatboxes.offset().left+cometchat_chatboxes.width()))&&($("#cometchat_user_"+openChatboxId).offset().left-cometchat_chatboxes.offset().left)>=0){
                                $("#cometchat_user_"+openChatboxId).click();
                            }
                        }
                    });
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
                        cometchat_userscontent.find('div.cometchat_userlist').hide();
                        cometchat_userscontent.find('.cometchat_subsubtitle').hide();
                        cometchat_userscontent.find('div.cometchat_userlist:icontains('+searchString+')').show();
                        cometchat_search.removeClass('cometchat_search_light');
                    }else{
                        cometchat_userscontent.find('div.cometchat_userlist').show();
                        cometchat_userscontent.find('.cometchat_subsubtitle').show();
                    }
                });
                var cometchat_userstabtitle = $("#cometchat_userstab_popup").find("div.cometchat_userstabtitle");
                var cometchat_userstab = $('#cometchat_userstab');
                cometchat_userstabtitle.click(function(){
                    $('#cometchat_userstab').click();
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
                    if(jqcc.cometchat.getThemeVariable('offline')==1){
                        jqcc.cometchat.setThemeVariable('offline', 0);
                        jqcc.cometchat.setThemeArray('buddylistStatus', jqcc.cometchat.getThemeVariable('userid'), 'available');
                        jqcc[settings.theme].removeUnderline();
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
                        $("span.cometchat_userscontentavatar").find("img").each(function(){
                            if($(this).attr('original')){
                                $(this).attr("src", $(this).attr('original'));
                                $(this).removeAttr('original');
                            }
                        });
                    }
                    var baseLeft = $('#cometchat_base').position().left;
                    var barActualWidth = jqcc('#cometchat_base').width();
                    $('#cometchat_optionsbutton_popup').css('left', baseLeft+barActualWidth-223);
                    $(this).toggleClass("cometchat_tabclick").toggleClass("cometchat_userstabclick");
                    $('#cometchat_userstab_popup').toggleClass("cometchat_tabopen");
                    if(settings.showSettingsTab==0){
                        $('span.cometchat_userstabclick').addClass('cometchat_extra_width');
                    }
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
                cometchat_optionsbutton_popup.find("span.invisible1").click(function(event){
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
                                jqcc[settings.theme].tooltip('cometchat_optionsbutton_icon', language[0]);
                            }
                        }else{
                            if(tooltipPriority==0){
                                jqcc[settings.theme].tooltip('cometchat_optionsbutton', jqcc(this).attr('title'));
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
                    if(jqcc.cometchat.getThemeVariable('loggedout')==0){
                        if(jqcc.cometchat.getThemeVariable('offline')==1){
                            jqcc.cometchat.setThemeVariable('offline', 0);
                            $('#cometchat_userstab_text').html(language[9]+' ('+jqcc.cometchat.getThemeVariable('lastOnlineNumber')+')');
                            jqcc.cometchat.chatHeartbeat(1);
                            cometchat_optionsbutton_popup.find(".available").click();
                        }
                        $("#cometchat_tooltip").css('display', 'none');
                        var baseLeft = $('#cometchat_base').position().left;
                        var barActualWidth = $('#cometchat_base').width();
                        cometchat_optionsbutton_popup.css('left', baseLeft+barActualWidth-223);
                        $(this).toggleClass("cometchat_tabclick");
                        cometchat_optionsbutton_popup.toggleClass("cometchat_tabopen");
                        cometchat_optionsbutton.toggleClass("cometchat_optionsimages_click");
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
                        if(settings.showSettingsTab==1&&settings.showOnlineTab==0){
                            jqcc("#cometchat_chatboxes_wide").find("span").each(function(index){
                                if($('#'+$(this).attr('id')).hasClass('cometchat_tabclick')){
                                    $('#'+$(this).attr('id')).removeClass('cometchat_tabclick').removeClass('cometchat_usertabclick');
                                    $('#'+$(this).attr('id')+'_popup').removeClass('cometchat_tabopen');
                                }
                            });
                        }
                    }else{
                        if(settings.ccauth.enabled == "1"){
                            $("#cometchat_tooltip").css('display', 'none');
                            var baseLeft = $('#cometchat_base').position().left;
                            var barActualWidth = $('#cometchat_base').outerWidth(false);
                            var cometchat_auth_popup_width = cometchat_auth_popup.outerWidth(false);
                            cometchat_auth_popup.css('left', baseLeft+barActualWidth-cometchat_auth_popup_width+1);
                            $(this).toggleClass("cometchat_tabclick");
                            cometchat_auth_popup.toggleClass("cometchat_tabopen");
                            cometchat_optionsbutton.toggleClass("cometchat_optionsimages_click");
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

                var cometchat_userstabtitle = cometchat_optionsbutton_popup.find("div.cometchat_userstabtitle");
                var cometchat_minimize = cometchat_userstabtitle.find("div.cometchat_minimizebox");
                var auth_logout = cometchat_userstabtitle.find("div#cometchat_authlogout");
                cometchat_userstabtitle.click(function(){
                    cometchat_optionsbutton.click();
                });
                cometchat_minimize.mouseenter(function(){
                    cometchat_minimize.addClass("cometchat_chatboxtraytitlemouseover");
                });
                cometchat_minimize.mouseleave(function(){
                    cometchat_minimize.removeClass("cometchat_chatboxtraytitlemouseover");
                });
                auth_logout.mouseenter(function(){
                    auth_logout.css('opacity','1');
                });
                auth_logout.mouseleave(function(){
                    auth_logout.css('opacity','0.5');
                });
                logout_click();
                function logout_click(){
                    auth_logout.click(function(event){
                        auth_logout.unbind('click');
                        event.stopPropagation();
                        auth_logout.css('background','url('+baseUrl+'themes/hangout/images/loading.gif) no-repeat top left');
                        jqcc.ajax({
                            url: baseUrl+'functions/login/logout.php',
                            dataType: 'jsonp',
                            success: function(){
                                if(typeof(cometuncall_function)==="function"){
                                    cometuncall_function(jqcc.cometchat.getThemeVariable('cometid'));
                                    jqcc.cometchat.setThemeVariable('cometid','');
                                }
                                auth_logout.css('background','url('+baseUrl+'themes/hangout/images/logout.png) no-repeat top left');
                                logout_click();
                                jqcc.hangout.removeChatbox(jqcc.cometchat.getThemeVariable('openChatboxId'));
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
                        $("#cometchat_user_"+id+"_popup").find("div.cometchat_tabcontenttext").css('height', ((chatboxHeight)-(adjustedHeight-2)+32)+'px');
                        $("#cometchat_user_"+id+"_popup").find("div.slimScrollDiv").css('height', ((chatboxHeight)-(adjustedHeight-2)+32)+'px');
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
                    $(chatboxtextarea).css('height', '28px');
                    $("#cometchat_user_"+id+"_popup").find("div.slimScrollDiv").css('height', ((chatboxHeight)+10)+'px');
                    $("#cometchat_user_"+id+"_popup").find("div.cometchat_tabcontenttext").css('height', ((chatboxHeight)-1)+'px');
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
            scrollBars: function(silent){
                var hidden = 0;
                var change = 0;
                var change2 = 0;
                var cometchat_chatbox_right = $('#cometchat_chatbox_right');
                var cometchat_chatbox_left = $('#cometchat_chatbox_left');
                var cometchat_chatboxes = $("#cometchat_chatboxes");
                if(cometchat_chatbox_right.hasClass('cometchat_chatbox_right_last')){
                    change = 1;
                }
                if(cometchat_chatbox_right.hasClass('cometchat_chatbox_lr')){
                    change2 = 1;
                }
                if(cometchat_chatboxes.scrollLeft()==0){
                    cometchat_chatbox_left.find('.cometchat_tabtext').html('0');
                    cometchat_chatbox_left.addClass('cometchat_chatbox_left_last');
                    hidden++;
                }else{
                    var number = Math.floor(cometchat_chatboxes.scrollLeft()/chatboxWidth);
                    cometchat_chatbox_left.find('.cometchat_tabtext').html(number);
                    cometchat_chatbox_left.removeClass('cometchat_chatbox_left_last');
                }
                if((cometchat_chatboxes.scrollLeft()+cometchat_chatboxes.width())==$("#cometchat_chatboxes_wide").width()){
                    cometchat_chatbox_right.find('.cometchat_tabtext').html('0');
                    cometchat_chatbox_right.addClass('cometchat_chatbox_right_last');
                    hidden++;
                }else{
                    var number = Math.floor(($("#cometchat_chatboxes_wide").width()-(cometchat_chatboxes.scrollLeft()+cometchat_chatboxes.width()))/chatboxWidth);
                    cometchat_chatbox_right.find('.cometchat_tabtext').html(number);
                    cometchat_chatbox_right.removeClass('cometchat_chatbox_right_last');
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
            },
            windowResize: function(silent){
                var extraWidth = 80;
                var cometchat_base = $('#cometchat_base');
                var cometchat_chatboxes = $('#cometchat_chatboxes');
                var cometchat_auth_popup = $("#cometchat_auth_popup");
                cometchat_base.css('left', settings.barPadding);
                if(cometchat_base.length){
                    var baseLeft = cometchat_base.position().left;
                    var barActualWidth = cometchat_base.width();
                    var cometchat_auth_popup_width = cometchat_auth_popup.outerWidth(false);
                    $('#cometchat_userstab_popup').css('right', settings.barPadding);
                    $('#cometchat_optionsbutton_popup').css('left', baseLeft+barActualWidth-223);
                    cometchat_auth_popup.css('left', baseLeft+barActualWidth-cometchat_auth_popup_width+1);
                    cometchat_base.css({'left': 'auto', 'right': settings.barPadding+'px'});
                }

                if($('#cometchat_chatboxes_wide').width()<=($(window).width()-26-178-44-extraWidth)){
                    cometchat_chatboxes.css('width', $('#cometchat_chatboxes_wide').width());
                    cometchat_chatboxes.scrollToCC("0px", 0);
                }else{
                    var change = cometchat_chatboxes.width();
                    var correction = ((Math.floor(change/chatboxWidth))*5);
                    cometchat_chatboxes.css('width', Math.floor(($(window).width()-26-178-44-extraWidth)/chatboxWidth)*chatboxWidth+correction);
                    var newChange = cometchat_chatboxes.width();
                    if(change!=newChange){
                        cometchat_chatboxes.scrollToCC("-="+chatboxWidth+"px", 0);
                    }
                }

                $.each(chatboxOpened, function(openChatboxId, state){
                    if(state==1&&silent!=1&&$("#cometchat_user_"+openChatboxId).length>0){
                        var cometchat_user_openChatboxId = $("#cometchat_user_"+openChatboxId);
                        if((cometchat_user_openChatboxId.offset().left<(cometchat_chatboxes.offset().left+cometchat_chatboxes.width()))&&(cometchat_user_openChatboxId.offset().left-cometchat_chatboxes.offset().left)>=0){
                            var left = baseLeft+cometchat_user_openChatboxId.offset().left-20;//20 = padding adjustment
                            cometchat_user_openChatboxId.css('left', left);
                            $("#cometchat_user_"+openChatboxId+'_popup').css('left', left);
                            $("#cometchat_user_"+openChatboxId+'_popup').css('bottom', '0px');
                            $("#cometchat_user_"+openChatboxId+'_popup').addClass("cometchat_tabopen");
                        }else{
                            cometchat_user_openChatboxId.removeClass('cometchat_tabclick').removeClass("cometchat_usertabclick");
                            $("#cometchat_user_"+openChatboxId+'_popup').removeClass("cometchat_tabopen");
                        }
                    }
                });
                jqcc[settings.theme].checkPopups(silent);
                jqcc[settings.theme].scrollBars(silent);
                $.hangout.closeTooltip();
            },
            chatWith: function(id){
                if(jqcc.cometchat.getThemeVariable('loggedout')==0 && jqcc.cometchat.getUserID() != id){
                    if(jqcc.cometchat.getThemeVariable('offline')==1){
                        jqcc.cometchat.setThemeVariable('offline', 0);
                        $('#cometchat_userstab_text').html(language[9]+' ('+jqcc.cometchat.getThemeVariable('lastOnlineNumber')+')');
                        jqcc.cometchat.chatHeartbeat(1);
                        $("#cometchat_optionsbutton_popup").find("span.available").click();
                    }
                    jqcc[settings.theme].createChatbox(id, jqcc.cometchat.getThemeArray('buddylistName', id), jqcc.cometchat.getThemeArray('buddylistStatus', id), jqcc.cometchat.getThemeArray('buddylistMessage', id), jqcc.cometchat.getThemeArray('buddylistAvatar', id), jqcc.cometchat.getThemeArray('buddylistLink', id), jqcc.cometchat.getThemeArray('buddylistIsDevice', id));
                }
            },
            scrollFix: function(){
                var elements = ['cometchat_base', 'cometchat_userstab_popup', 'cometchat_optionsbutton_popup', 'cometchat_tooltip'];

                $.each(chatboxOpened, function(openChatboxId, state){
                    if(state==1){
                        elements.push('cometchat_user_'+openChatboxId+'_popup');
                    }
                });
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
                var cometchat_chatboxes = $("#cometchat_chatboxes");
                $("#cometchat_chatboxes_wide").find(".cometchat_tabalert").each(function(){
                    if(($(this).parent().offset().left<(cometchat_chatboxes.offset().left+cometchat_chatboxes.width()))&&($(this).parent().offset().left-cometchat_chatboxes.offset().left)>=0){
                        $(this).css('display', 'block');
                    }else{
                        $(this).css('display', 'none');
                        if(($(this).parent().offset().left-cometchat_chatboxes.offset().left)>=0){
                            $("#cometchat_chatbox_right").find(".cometchat_tabalertlr").html(parseInt($("#cometchat_chatbox_right").find(".cometchat_tabalertlr").html())+parseInt($(this).html())).css('display', 'block');
                        }
                    }
                });
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
                $("#cometchat_trayicon_chatrooms").click();
                $('#cometchat_trayicon_chatrooms_iframe,.cometchat_embed_chatrooms').attr('src', baseUrl+'modules/chatrooms/index.php?roomid='+roomid+'&inviteid='+inviteid+'&roomname='+roomname+'&basedata='+jqcc.cometchat.getThemeVariable('baseData'));
                jqcc.cometchat.setThemeVariable('openChatboxId', '');
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
                    $("#cometchat_optionsbutton").removeClass("cometchat_optionsimages_exclamation");
                    $("#cometchat_optionsbutton").removeClass("cometchat_optionsimages_ccauth");
                    $("#cometchat_optionsbutton").removeClass("cometchat_tooltip");
                    $("#cometchat_optionsbutton_icon").css('display', 'block');
                    $("body").append(msg_beep);
                    $("#cometchat").append(option_button);
                    $("#cometchat").append(user_tab);
                    $("#cometchat_base").append(user_tab2);
                    $("#cometchat_base").append(chat_boxes);
                    $("#cometchat_userstab").show();
                    $("#cometchat_chatboxes").show();
                    jqcc.cometchat.setThemeVariable('loggedout', 0);
                    jqcc.cometchat.setExternalVariable('initialize', '1');
                    jqcc.cometchat.chatHeartbeat();
                    $("#cometchat_userstab").click();
                }
            },
            updateHtml: function(id, temp){
                if($("#cometchat_user_"+id+"_popup").length>0){
                    document.getElementById("cometchat_tabcontenttext_"+id).innerHTML = ''+temp+'';
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
                jqcc[settings.theme].moveBar("-="+chatboxWidth+"px");
            },
            moveRight: function(){
                jqcc[settings.theme].moveBar("+="+chatboxWidth+"px");
            },
            processMessage: function(message, self){
                return message;
            },
            minimizeAll: function(){
                $("div.cometchat_tabpopup").each(function(index){
                    if($(this).hasClass('cometchat_tabopen')){
                        $('#'+$(this).attr('id')).find('.cometchat_minimizebox').click();
                    }
                });
            },
            iconNotFound: function(image, name){
                $('.'+name+'icon').attr({'src': baseUrl+'modules/'+name+'/icon.png', 'width': '16px'});
            },
            prependMessagesInit: function(id){
                var messages = jqcc('#cometchat_tabcontenttext_'+id).find('.cometchat_chatboxmessagecontent div');
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
                var oldSelf = -1;
                var main = '';
                var lastIncomingSelf = 0;
                var selfClose = '';
                var otherClose = '';
                $.each(data, function(type, item){
                    if(type=="messages"){
                        $.each(item, function(i, incoming){
                            var message = jqcc.cometchat.processcontrolmessage(incoming);

                            if(message == null){
                                return;
                            }
                            sentdata = '';
                             var selfstyle = ' cometchat_other';
                                    var selfstyleCont = ' cometchat_other_content';
                                    var selfstyleAvatar = '';
                                    if(parseInt(incoming.self)==1){
                                        fromname = language[10];
                                        selfstyle = ' cometchat_self';
                                        selfstyleCont = ' cometchat_self_content';
                                        selfstyleAvatar = ' cometchat_self_avatar';
                                    }else{
                                        fromname = jqcc.cometchat.getThemeArray('buddylistName', incoming.from);
                                    }
                                    separator = ':&nbsp;&nbsp;';
                            if(incoming.sent!=null){
                                var ts = incoming.sent;
                                sentdata = jqcc[settings.theme].getTimeDisplay(ts);
                            }
                            if(!settings.fullName){
                                fromname = fromname.split(" ")[0];
                            }
                            var msg = '';
                            var addMessage = 0;
                            var avatar = baseUrl+"themes/hangout/images/noavatar.png";
                            var cometchat_tabcontenttext_incomingfrom = $('#cometchat_tabcontenttext_'+incoming.from);

                            if(parseInt(incoming.self)==1){
                                if(jqcc.cometchat.getThemeArray('buddylistAvatar', jqcc.cometchat.getThemeVariable('userid'))!=""){
                                    avatar = jqcc.cometchat.getThemeArray('buddylistAvatar', jqcc.cometchat.getThemeVariable('userid'));
                                }
                                msg1 = '<div id="cometchat_message_'+incoming.id+'" class="'+selfstyle+'">'+message+'</div>';
                                selfClose = '<span class="cometchat_ts">'+sentdata+'</span></div><div class="cometchat_chatboxmessagefrom '+selfstyleAvatar+'"><a href="'+jqcc.cometchat.getThemeArray('buddylistLink', jqcc.cometchat.getThemeVariable('userid'))+'"><img src="'+avatar+'" title="'+fromname+'"/></a></div></div>';

                            }else{
                                if(jqcc.cometchat.getThemeArray('buddylistAvatar', incoming.from)!=""){
                                    avatar = jqcc.cometchat.getThemeArray('buddylistAvatar', incoming.from);
                                }
                                msg1 = '<div id="cometchat_message_'+incoming.id+'" class="'+selfstyle+'">'+message+'</div>';
                                otherClose = '<span class="cometchat_message_name">'+fromname+' <strong>.</strong></span><span class="cometchat_ts">'+sentdata+'</span></div></div>';

                            }

                            if(count == 0){
                                oldSelf = incoming.self;
                                if(oldSelf == 0){
                                    main+='<div class="cometchat_chatboxmessage other"><div class="cometchat_chatboxmessagefrom '+selfstyleAvatar+'"><a href="'+jqcc.cometchat.getThemeArray('buddylistLink', incoming.from)+'"><img src="'+avatar+'" title="'+fromname+'"/></a></div><div class="cometchat_chatboxmessagecontent '+selfstyleCont+'">';
                                }else{
                                    main+='<div class="cometchat_chatboxmessage self" ><div class="cometchat_chatboxmessagecontent '+selfstyleCont+'">';
                                }
                                main+=msg1;
                            }else{
                                if(oldSelf == incoming.self){
                                    main+=msg1;
                                }else{

                                    if(incoming.self == 0){
                                        main+=selfClose;
                                        main+='<div class="cometchat_chatboxmessage other"><div class="cometchat_chatboxmessagefrom '+selfstyleAvatar+'"><a href="'+jqcc.cometchat.getThemeArray('buddylistLink', incoming.from)+'"><img src="'+avatar+'" title="'+fromname+'"/></a></div><div class="cometchat_chatboxmessagecontent '+selfstyleCont+'">';
                                    }else{
                                        main+=otherClose;
                                        main+='<div class="cometchat_chatboxmessage self" ><div class="cometchat_chatboxmessagecontent '+selfstyleCont+'">';
                                    }
                                    oldSelf = incoming.self;
                                    main+=msg1;
                                }
                            }
                            count = count+1;
                            lastIncomingSelf = incoming.self;
                        });
                    }
                });
                if(lastIncomingSelf == 1){
                    main+=selfClose;
                }else {
                    main+=otherClose;
                }
                oldMessages+=main;
                jqcc('#cometchat_tabcontenttext_'+id).prepend(oldMessages);
                $('#cometchat_prependMessages_'+id).text(language[83]);
                if((count - parseInt(jqcc.cometchat.getThemeVariable('prependLimit')) < 0)){
                    $('#cometchat_prependMessages_'+id).text(language[84]);
                    jqcc('#cometchat_prependMessages_'+id).attr('onclick','');
                    jqcc('#cometchat_prependMessages_'+id).css('cursor','default');
                }else{
                    jqcc('#cometchat_prependMessages_'+id).attr('onclick','jqcc.hangout.prependMessagesInit('+id+')');
                }
            }
        };
    })();
})(jqcc);

if(typeof(jqcc.hangout) === "undefined"){
    jqcc.hangout=function(){};
}

jqcc.extend(jqcc.hangout, jqcc.cchangout);

jqcc(window).resize(function(){
    jqcc.hangout.windowResize(1);
});
