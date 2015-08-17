(function($){
    $.ccfacebook = (function(){
        var settings = {};
        var baseUrl;
        var language;
        var trayicon;
        var typingTimer;
        var resynchTimer;
        var notificationTimer;
        var chatboxOpened = {};
        var allChatboxes = {};
        var chatboxDistance = 7;
        var visibleTab = [];
        var blinkInterval;
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
        var side_bar = '';
        var option_button = '';
        var user_tab = '';
        var chat_boxes = '';
        var chat_left = '';
        var unseen_users = '';
        var usertab2 = '';
        var checkfirstmessage;
        var chatboxHeight = parseInt('<?php echo $chatboxHeight; ?>');
        var chatboxWidth = parseInt('<?php echo $chatboxWidth; ?>');
        var barVisiblelimit = (chatboxWidth + chatboxDistance + 14);
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
                            trayData += '<div id="cometchat_trayicon_'+x+'" class="cometchat_trayiconimage cometchat_floatL cometchat_tooltip" title="'+trayicon[x][1]+'" '+onclick+'><img class="'+x+'icon" src="'+baseUrl+'themes/'+settings.theme+'/images/modules/'+x+'.png" onerror="jqcc.'+settings.theme+'.iconNotFound(this,\''+icon[0]+'\')"></div>';
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
                var findUser = '<div id="cometchat_searchbar" class="cometchat_floatL"><div id="cometchat_searchbar_icon"><div class="after"></div></div><input type="text" name="cometchat_search" class="cometchat_search cometchat_search_light textInput" id="cometchat_search" placeholder="'+language[18]+'"></div>';
                if(settings.showSettingsTab==1){
                    optionsbutton = '<div id="cometchat_optionsbutton" class="cometchat_tab cometchat_floatL"><div id="cometchat_optionsbutton_icon" class="cometchat_optionsimages"></div></div>';
                    optionsbuttonpop = '<div id="cometchat_optionsbutton_popup" class="cometchat_tabpopup" style="display:none"><div class="cometchat_userstabtitle"><div class="cometchat_userstabtitletext">'+language[0]+'</div>'+ccauthlogout+'<div class="cometchat_minimizebox cometchat_tooltip" id="cometchat_minimize_optionsbutton_popup" title="'+language[63]+'"></div><br clear="all"/></div><div class="cometchat_tabsubtitle">'+language[1]+'</div><div class="cometchat_tabcontent cometchat_optionstyle"><div id="guestsname"><strong>'+language[43]+'</strong><br/><input type="text" class="cometchat_guestnametextbox"/><div class="cometchat_guestnamebutton">'+language[44]+'</div></div><strong>'+language[2]+'</strong><br/><textarea class="cometchat_statustextarea"></textarea><div class="cometchat_statusbutton">'+language[22]+'</div><div class="cometchat_statusinputs"><strong>'+language[23]+'</strong><br/><span class="cometchat_user_available"></span><span class="cometchat_optionsstatus available">'+language[3]+'</span><span class="cometchat_optionsstatus2 cometchat_user_invisible" ></span><span class="cometchat_optionsstatus invisible">'+language[5]+'</span><div style="clear:both"></div><span class="cometchat_optionsstatus2 cometchat_user_busy"></span><span class="cometchat_optionsstatus busy">'+language[4]+'</span><span class="cometchat_optionsstatus2 cometchat_user_invisible"></span><span class="cometchat_optionsstatus cometchat_gooffline offline">'+language[11]+'</span><br clear="all"/></div><div class="cometchat_options_disable"><div><input type="checkbox" id="cometchat_soundnotifications" style="vertical-align: -2px;">'+language[13]+'</div><div style="clear:both"></div><div><input type="checkbox" id="cometchat_popupnotifications" style="vertical-align: -2px;">'+language[24]+'</div></div></div></div>';
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
                    usertabpop = '<div id="cometchat_userstab_popup" class="cometchat_tabpopup"><div class="cometchat_userstabtitle"><div class="cometchat_userstabtitletext">'+language[12]+'</div><div class="cometchat_minimizebox cometchat_tooltip" id="cometchat_minimize_userstab_popup" title="'+language[62]+'"></div><br clear="all"/></div>'+trayData+'<div class="cometchat_tabcontent cometchat_tabstyle"><div id="cometchat_userscontent"><div id="cometchat_userslist"><div class="cometchat_nofriends">'+language[41]+'</div></div></div></div></div>';
                }
               var baseCode = '<div id="cometchat_base"><div id="loggedout" class="cometchat_tab cometchat_tooltip" title="'+language[8]+'"></div><div id="cometchat_sidebar">'+usertabpop+'<div id="cometchat_bottomBar">'+findUser+optionsbutton+'</div></div>'+optionsbuttonpop+''+ccauthpopup+''+usertab+'<div id="cometchat_chatboxes"><div id="cometchat_chatboxes_wide" class="cometchat_floatR"></div></div><div id="cometchat_chatbox_left" class="cometchat_tab"><div class="cometchat_tabalertlr"></div><div class="cometchat_tabtext">0</div><div id="cometchat_unseenUserCount"></div><div id="cometchat_chatbox_left_border_fix"></div></div><div id="cometchat_unseenUsers"></div></div>';
                document.getElementById('cometchat').innerHTML = baseCode;
                if(settings.showSettingsTab==0){
                    $('#cometchat_userstab').addClass('cometchat_extra_width');
                    $('#cometchat_userstab_popup').find('div.cometchat_tabstyle').addClass('cometchat_border_bottom');
                }

                if(jqcc().slimScroll){
                    $('#cometchat_userscontent').slimScroll({height: '100%'});
                }

                setTimeout(function(){
                    var sidebar = jqcc('#cometchat_sidebar');
                    var calculatedheight = (sidebar.outerHeight(false) - sidebar.find('.cometchat_userstabtitle').outerHeight(true) - sidebar.find('.cometchat_tabsubtitle2').outerHeight(true) - sidebar.find('.cometchat_tabsubtitle').outerHeight(true) - sidebar.find('#cometchat_bottomBar').outerHeight(true))+"px";
                    jqcc('.cometchat_tabcontent.cometchat_tabstyle').css("height",calculatedheight);
                },300);

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
                $('div.cometchat_tab').attr('unselectable', 'on').css('MozUserSelect', 'none').bind('selectstart.ui', function(){
                    return false;
                });
                $('#cometchat_chatbox_left').bind('click', function(){
                    $(this).toggleClass('cometchat_unseenList_open');
                    $('#cometchat_unseenUsers').toggle();
                    $('#cometchat_chatbox_left_border_fix').toggle();
                });
                jqcc[settings.theme].windowResize();
                jqcc[settings.theme].scrollBars();
                $('#cometchat_chatbox_left').mouseover(function(){
                    $(this).addClass("cometchat_chatbox_lr_mouseover");
                });
                $('#cometchat_chatbox_left').mouseout(function(){
                    $(this).removeClass("cometchat_chatbox_lr_mouseover");
                });
                $('#cometchat_unseenUsers .cometchat_unseenClose').live('click',function(e){
                    e.stopImmediatePropagation();
                    var parentElem = $(this).parent();
                    var uid = parentElem.attr('uid');
                    parentElem.remove();
                    $('#cometchat_user_'+uid).find('.cometchat_closebox_bottom').click();
                    $.facebook.rearrange();
                });
                $('#cometchat_unseenUsers .cometchat_unseenUserList').live('click',function(){
                    var uid = $(this).attr('uid');
                    $.facebook.swapTab(uid);
                });
                $('body .cometchat_tooltip').live('mouseover',function(){
                    var currElem = $(this);
                    var tId = currElem.attr('id');
                    var tMsg = currElem.data('title');
                    if(tMsg != null){
                        $.facebook.tooltip(tId, tMsg);
                    }
                });
                $('body .cometchat_tooltip').live('mouseout',function(){
                    $.facebook.closeTooltip();
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
                        var cometchat_user_popup = $("#cometchat_user_"+buddy.id+"_popup");
                        if(buddy.d == 1 && $("#cometchat_user_"+buddy.id+", #cometchat_user_"+buddy.id+"_popup").find('div.cometchat_isMobile').length < 1){
                        cometchat_user_popup.find('.cometchat_tabtitle').prepend('<div class="cometchat_isMobile cometchat_isMobile_'+buddy.s+' cometchat_floatL"><div class="cometchat_mobileDot"></div></div>');
                        }else if(buddy.d == 0){
                            $("#cometchat_user_"+buddy.id+", #cometchat_user_"+buddy.id+"_popup").find('div.cometchat_isMobile').remove();
                        }
                        $("#cometchat_user_"+buddy.id+", #cometchat_user_"+buddy.id+"_popup").find('div.cometchat_isMobile')
                        .removeClass("cometchat_isMobile_available")
                        .removeClass("cometchat_isMobile_busy")
                        .removeClass("cometchat_isMobile_offline")
                        .removeClass("cometchat_isMobile_away")
                        .addClass('cometchat_isMobile_'+buddy.s);
                        $("#cometchat_user_"+buddy.id+", #cometchat_user_"+buddy.id+"_popup").find('div.cometchat_userscontentdot')
                        .removeClass("cometchat_available")
                        .removeClass("cometchat_busy")
                        .removeClass("cometchat_offline")
                        .removeClass("cometchat_away")
                        .addClass('cometchat_'+buddy.s);
                        if(cometchat_user_popup.length>0){
                            cometchat_user_popup.find("div.cometchat_message").html(buddy.m);
                        }
                        if(buddy.s=='offline'){
                             $("#cometchat_user_"+buddy.id+", #cometchat_user_"+buddy.id+"_popup").find('div.cometchat_userscontentdot').hide();
                        }else{
                             $("#cometchat_user_"+buddy.id+", #cometchat_user_"+buddy.id+"_popup").find('div.cometchat_userscontentdot').show();
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
                    var statusIndicator = '';
                    if(buddy.s!='offline'&&buddy.s!='invisible'){
                        var deviceType = language[60];
                        if(buddy.d == 1){
                             deviceType = language[61];
                        }
                        statusIndicator = '<div class="cometchat_deviceType cometchat_floatR cometchat_mobile_'+buddy.s+'">'+deviceType+'</div>';
                    }
                    buddylisttemp += group+'<div id="cometchat_userlist_'+buddy.id+'" class="cometchat_userlist" onmouseover="jqcc(this).addClass(\'cometchat_userlist_hover\');" onmouseout="jqcc(this).removeClass(\'cometchat_userlist_hover\');"><span class="cometchat_userscontentname">'+longname+'</span><span class="cometchat_userscontentdot cometchat_'+buddy.s+'"></span>'+statusIndicator+'</div>';
                    buddylisttempavatar += group+'<div id="cometchat_userlist_'+buddy.id+'" class="cometchat_userlist" onmouseover="jqcc(this).addClass(\'cometchat_userlist_hover\');" onmouseout="jqcc(this).removeClass(\'cometchat_userlist_hover\');"><span class="cometchat_userscontentavatar"><img class="cometchat_userscontentavatarimage" original="'+buddy.a+'"></span><span class="cometchat_userscontentname">'+longname+'</span><span class="cometchat_userscontentdot cometchat_'+buddy.s+'"></span>'+statusIndicator+'</div>';
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
                    if(buddylisttempavatar!=''){
                        document.getElementById('cometchat_userslist').style.display = 'block';
                        jqcc.cometchat.replaceHtml('cometchat_userslist', '<div>'+bltemp+'</div>');
                    }else{
                        $('#cometchat_userslist').html('<div class="cometchat_nofriends">'+language[14]+'</div>');
                    }
                }
                if(jqcc.cometchat.getSessionVariable('buddylist')==1 || $('#cometchat_optionsbutton').hasClass('cometchat_tabclick')){
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
                    $('#cometchat_searchbar').css('visibility', 'visible');
                    $('#cometchat_optionsbutton').removeClass('cometchat_noUser_optionBar');

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
                    $("#loggedout").addClass("cometchat_optionsimages_ccauth");
                    $("#loggedout").attr("title",language[77]);
                }else{
                    $("#loggedout").addClass("cometchat_optionsimages_exclamation");
                    $("#loggedout").attr("title",language[8]);
                }
                $("#loggedout").show();
                msg_beep = $("#messageBeep").detach();
                side_bar = $("#cometchat_sidebar").detach();
                option_button = $("#cometchat_optionsbutton_popup").detach();
                user_tab = $("#cometchat_userstab_popup").detach();
                chat_boxes = $("#cometchat_chatboxes").detach();
                chat_left = $("#cometchat_chatbox_left").detach();
                unseen_users = $("#cometchat_unseenUsers").detach();
                usertab2 = $("#cometchat_userstab").detach();
                $("span.cometchat_tabclick").removeClass("cometchat_tabclick");
                $("div.cometchat_tabopen").removeClass("cometchat_tabopen");
                jqcc.cometchat.setSessionVariable('openChatboxId', '');
                jqcc.cometchat.setThemeVariable('loggedout', 1);
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
            createChatboxData: function(id, name, status, message, avatar, link, isdevice, silent, tryOldMessages){
                var cometchat_chatboxes = $("#cometchat_chatboxes");
                if(chatboxOpened[id]!=null){
                    if(!$("#cometchat_user_"+id).hasClass('cometchat_tabclick')&&silent!=1){
                        if(visibleTab.indexOf(id.toString()) == -1) {
                            $.facebook.swapTab(id);
                        } else {
                            $("#cometchat_user_"+id).click();
                        }
                    }
                    jqcc[settings.theme].scrollBars();
                    return;
                }

                var isMobile = '';
                if(isdevice == 1) {
                     isMobile = '<div class="cometchat_isMobile cometchat_isMobile_'+status+' cometchat_floatL"><div class="cometchat_mobileDot"></div></div>';
                }
                $('#cometchat_chatboxes_wide').width($('#cometchat_chatboxes_wide').width()+chatboxWidth+chatboxDistance);
                shortname = name;
                longname = name;
                $("<span/>").attr("id", "cometchat_user_"+id).attr("amount", 0).addClass("cometchat_tab").html(isMobile+'<div class="cometchat_userscontentdot cometchat_'+status+' cometchat_floatL"></div><div class="cometchat_unreadCount cometchat_floatL">0</div><div class="cometchat_user_shortname">'+shortname+'</div><div class="cometchat_closebox_bottom cometchat_tooltip" title="'+language[74]+'" id="cometchat_closebox_bottom_'+id+'">×</div>').appendTo($("#cometchat_chatboxes_wide"));
                var pluginshtml = '';
                var avchathtml = '';
                var smiliehtml = '';
                var pluginslength = settings.plugins.length;
                if(jqcc.cometchat.getThemeArray('isJabber', id)!=1){
                    if(pluginslength>0){
                        pluginshtml += '<div class="cometchat_plugins cometchat_floatR">';
                        for(var i = 0; i<pluginslength; i++){
                            var name = 'cc'+settings.plugins[i];
                            if(typeof ($[name])=='object'){
                                 if(settings.plugins[i]=='avchat') {
                                    avchathtml = '<div id="cometchat_'+settings.plugins[i]+'_'+id+'" class="cometchat_tooltip cometchat_pluginsicon cometchat_'+settings.plugins[i]+'" name="'+name+'" to="'+id+'" chatroommode="0" title="'+$[name].getTitle()+'"></div>';
                                } else if(settings.plugins[i]=='smilies') {
                                    smiliehtml = '<div class="cometchat_pluginsicon cometchat_smilies" name="'+name+'" to="'+id+'" chatroommode="0" title="'+$[name].getTitle()+'"></div>';
                                } else if(settings.plugins[i]!='chattime'){
                                    pluginshtml += '<div class="cometchat_plugins_dropdownlist" name="'+name+'" to="'+id+'" chatroommode="0" title="'+$[name].getTitle()+'" ><div class="cometchat_pluginsicon cometchat_'+settings.plugins[i]+' cometchat_floatL"></div><div class="cometchat_plugins_name cometchat_floatL">'+$[name].getTitle()+'</div></div>';
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
                    prepend = '<div class=\"cometchat_prependMessages\" onclick\="jqcc.facebook.prependMessagesInit('+id+')\" id = \"cometchat_prependMessages_'+id+'\">'+language[83]+'</div>';
                }
                $("<div/>").attr("id", "cometchat_user_"+id+"_popup").addClass("cometchat_tabpopup").css('display', 'none').html('<div class="cometchat_tabtitle">'+isMobile+'<div class="cometchat_userscontentdot cometchat_'+status+' cometchat_floatL"></div><span id="cometchat_typing_'+id+'" class="cometchat_typing"></span><div class="cometchat_name">'+startlink+longname+endlink+'</div><div id="cometchat_closebox_'+id+'" title="'+language[74]+'" class="cometchat_closebox cometchat_floatR cometchat_tooltip">×</div><div class="cometchat_plugins_dropdown cometchat_floatR"><div class="cometchat_plugins_dropdown_icon cometchat_tooltip" id="cometchat_plugins_dropdown_icon_'+id+'" title="'+language[73]+'"></div><div class="cometchat_popup_plugins">'+pluginshtml+'</div></div>'+avchathtml+'</div><div class="cometchat_tabsubtitle cometchat_tabcontent"><div class="cometchat_message">'+message+'</div>'+prepend+'<div class="cometchat_tabcontenttext" id="cometchat_tabcontenttext_'+id+'"></div><div class="cometchat_tabcontentinput"><textarea class="cometchat_textarea" placeholder="'+language[55]+'"></textarea>'+smiliehtml+'</div><div style="clear:both"></div></div>').appendTo($("#cometchat"));

                var cometchat_user_popup = $("#cometchat_user_"+id+'_popup');
                if(status=='offline'){
                    $("#cometchat_user_"+id+",#cometchat_user_"+id+"_popup").find('div.cometchat_userscontentdot').hide();
                }
                if(cometchat_user_popup.find('div.cometchat_plugins_dropdownlist').length == 0){
					cometchat_user_popup.find('div.cometchat_plugins_dropdown').hide();
				}
                jqcc.cometchat.setThemeArray('chatBoxesOrder', id, 0);
                chatboxOpened[id] = 0;
                allChatboxes[id] = 0;
                jqcc.cometchat.orderChatboxes();
                jqcc[settings.theme].windowResize(1);
                var cometchat_user_id = $("#cometchat_user_"+id);
                cometchat_user_popup.find('.cometchat_plugins_dropdownlist, .cometchat_smilies, .cometchat_avchat').click(function(){
                    var name = $(this).attr('name');
                    var to = $(this).attr('to');
                    var chatroommode = $(this).attr('chatroommode');
                    var controlparameters = {"to":to, "chatroommode":chatroommode};
                    jqcc[name].init(controlparameters);
                });
                cometchat_user_popup.find('.cometchat_smilies').click(function(e){
                    cometchat_user_popup.find('div.cometchat_popup_plugins').css('display','none');
                    cometchat_user_popup.find('div.cometchat_tabtitle').removeClass('cometchat_fullOpacity');
                });
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
                        $(this).removeClass("cometchat_tabclick").removeClass("cometchat_usertabclick");
                        cometchat_user_popup.removeClass("cometchat_tabopen");
                        cometchat_user_id.find("div.cometchat_closebox_bottom").removeClass("cometchat_closebox_bottom_click");
                        jqcc.cometchat.setThemeArray('chatBoxesOrder', id, 0);
                        chatboxOpened[id] = 0;
                        allChatboxes[id] = 0;
                        jqcc.cometchat.orderChatboxes();
                        removeOpenChatboxId(id);
                    }else{
                        if((cometchat_user_id.offset().left<(cometchat_chatboxes.offset().left+cometchat_chatboxes.width()))&&(cometchat_user_id.offset().left-cometchat_chatboxes.offset().left)>=0){
                            $(this).addClass("cometchat_tabclick").addClass("cometchat_usertabclick");
                            cometchat_user_popup.addClass("cometchat_tabopen").addClass('cometchat_tabopen_bottom');
                            cometchat_user_id.find("div.cometchat_closebox_bottom").addClass("cometchat_closebox_bottom_click");
                            chatboxOpened[id] = 1;
                            addOpenChatboxId(id);
                            if(olddata[id]!=1||(isNaN(id))){
                                $("#cometchat_tabcontenttext_"+id).find('div.cometchat_chatboxmessage').remove();
                                jqcc[settings.theme].updateChatbox(id);
                                olddata[id] = 1;
                            }
                        }else{
                            cometchat_user_id.removeClass('cometchat_tabclick').removeClass("cometchat_usertabclick");
                            chatboxOpened[id] = 1;
                            addOpenChatboxId(id);
                        }
                        allChatboxes[id] = 1;
                        jqcc[settings.theme].scrollDown(id);
                    }
                    if(jqcc.cometchat.getInternalVariable('updatingsession')!=1){
                        cometchat_user_popup.find("textarea.cometchat_textarea").focus();
                    }
                    $(this).find('div.cometchat_unreadCount').html('0').hide();
                    $.facebook.rearrange();
                });
                cometchat_user_id.find("div.cometchat_closebox_bottom").mouseenter(function(){
                    $(this).addClass("cometchat_closebox_bottomhover");
                });
                cometchat_user_id.find("div.cometchat_closebox_bottom").mouseleave(function(){
                    $(this).removeClass("cometchat_closebox_bottomhover");
                });
                cometchat_user_id.find("div.cometchat_closebox_bottom").click(function(){
                    cometchat_user_popup.remove();
                    $("#cometchat_user_"+id).remove();
                    $('#cometchat_chatboxes_wide').width($('#cometchat_chatboxes_wide').width()-160-chatboxDistance);
                    jqcc.cometchat.setThemeArray('chatBoxesOrder', id, null);
                    delete(chatboxOpened[id]);
                    delete(allChatboxes[id]);
                    olddata[id] = 0;
                    jqcc.cometchat.orderChatboxes();
                    removeOpenChatboxId(id);
                    jqcc[settings.theme].windowResize();
                });
                if(jqcc().slimScroll){
                    $("#cometchat_tabcontenttext_"+id).slimScroll({height: (chatboxHeight+10)+'px',railAlwaysVisible: true});
                    var currElem = cometchat_user_popup.find('div.cometchat_plugins_dropdownlist');
                    var maxPlugin = (currElem.length>8)?8:currElem.length;
                    var scrollHeight = (maxPlugin * currElem.outerHeight(false))+4+'px';
                    cometchat_user_popup.find('div.cometchat_plugins').slimScroll({'width':'100%','height':scrollHeight});
                }
                chatbottom[id] = 1;
                cometchat_user_popup.find("div.cometchat_tabcontenttext").scroll(function(){
                    if(cometchat_user_popup.find("div.cometchat_tabcontenttext").height()<(cometchat_user_popup.find("div.slimScrollBar").height()+cometchat_user_popup.find("div.slimScrollBar").position().top)){
                        chatbottom[id] = 1;
                        $('#cometchat_tabcontenttext_'+id).find('.cometchat_new_message_unread').remove();
                    }else{
                        chatbottom[id] = 0;
                    }
                });
                var cometchat_textarea = $("#cometchat_user_"+id+'_popup').find("textarea.cometchat_textarea");
                cometchat_textarea.keydown(function(event){
                    return jqcc[settings.theme].chatboxKeydown(event, this, id);
                });
                cometchat_textarea.keyup(function(event){
                    return jqcc[settings.theme].chatboxKeyup(event, this, id);
                });
                cometchat_textarea.focus(function(){
                    clearInterval(blinkInterval);
                    cometchat_user_popup.find('div.cometchat_new_message_titlebar').removeClass('cometchat_new_message_titlebar');
                });
                var cometchat_tabtitle = cometchat_user_popup.find("div.cometchat_tabtitle");
                cometchat_tabtitle.find("div.cometchat_closebox").mouseenter(function(){
                    $(this).addClass("cometchat_chatboxmouseoverclose");
                });
                cometchat_tabtitle.find("div.cometchat_closebox").mouseleave(function(){
                    $(this).removeClass("cometchat_chatboxmouseoverclose");
                });
                cometchat_tabtitle.find(".cometchat_closebox").click(function(){
                    cometchat_user_id.find("div.cometchat_closebox_bottom").click();
                });
                cometchat_tabtitle.click(function(){
                    $(this).removeClass('cometchat_fullOpacity').find('div.cometchat_popup_plugins').slideUp('fast');
                    cometchat_user_id.click();
                });
                $('div.cometchat_plugins_dropdown','#cometchat_user_'+id+'_popup').click(function(e){
                    e.stopImmediatePropagation();
                    $(this).toggleClass('cometchat_plugins_dropdown_active').find('div.cometchat_popup_plugins').slideToggle('fast');
                    cometchat_user_popup.find('div.cometchat_tabtitle').toggleClass('cometchat_fullOpacity');
                });
                $('div.cometchat_pluginsicon','#cometchat_user_'+id+'_popup').click(function(e){
                    e.stopImmediatePropagation();
                    $(this).parents('div.cometchat_popup_plugins').slideUp('fast');
                    cometchat_user_popup.find('div.cometchat_tabtitle').removeClass('cometchat_fullOpacity');
                });
                if(silent!=1){
                    if(visibleTab.indexOf(id.toString()) == -1) {
                        $.facebook.swapTab(id);
                    } else {
                        cometchat_user_id.click();
                    }
                }
                $('div.cometchat_tabcontenttext').click(function(e){
                    if(cometchat_user_popup.find('.cometchat_tabtitle').hasClass('cometchat_fullOpacity')){
                        cometchat_user_popup.find('div.cometchat_plugins_dropdown').removeClass('cometchat_plugins_dropdown_active').find('div.cometchat_popup_plugins').slideToggle('fast');
                        cometchat_user_popup.find('div.cometchat_tabtitle').removeClass('cometchat_fullOpacity');
                    }
                });
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
                            jqcc[settings.theme].createChatbox(incoming.from, jqcc.cometchat.getThemeArray('buddylistName', incoming.from), jqcc.cometchat.getThemeArray('buddylistStatus', incoming.from), jqcc.cometchat.getThemeArray('buddylistMessage', incoming.from), jqcc.cometchat.getThemeArray('buddylistAvatar', incoming.from), jqcc.cometchat.getThemeArray('buddylistLink', incoming.from), jqcc.cometchat.getThemeArray('buddylistIsDevice', incoming.from), 1, 1);
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
					if(incoming.self!=1&&settings.messageBeep==1){
						if($.cookie(settings.cookiePrefix+"sound")&&$.cookie(settings.cookiePrefix+"sound")=='true'){
						}else{
							if(incoming.old!=1){
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
                        var selfstyleAvatar = "";
                        var avatar = baseUrl+"themes/facebook/images/noavatar.png";
                        if(parseInt(incoming.self)==1){
                            fromname = language[10];
                        }else{
                            fromname = jqcc.cometchat.getThemeArray('buddylistName', incoming.from);
                            if(jqcc.cometchat.getThemeArray('buddylistAvatar', incoming.from)!=""){
                                avatar = jqcc.cometchat.getThemeArray('buddylistAvatar', incoming.from);
                            }
                            selfstyleAvatar = '<a class="cometchat_floatL" href="'+jqcc.cometchat.getThemeArray('buddylistLink', incoming.from)+'"><img src="'+avatar+'" title="'+fromname+'"/></a>';
                        }
                        if($("#cometchat_message_"+incoming.id).length>0){
                            $("#cometchat_message_"+incoming.id).find('div.cometchat_chatboxmessagecontent').html(message);
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
                            var avatar = baseUrl+"themes/facebook/images/noavatar.png";
                            if(parseInt(incoming.self)==1){
                                msg = '<div class="cometchat_chatboxmessage" id="cometchat_message_'+incoming.id+'"><div class="cometchat_chatboxmessagecontent cometchat_self cometchat_floatR" title="'+sentdata+'">'+message+'</div><div class="selfMsgArrow"><div class="after"></div></div>';
                                addMessage = 1;

                            }else{
                                msg = '<div class="cometchat_chatboxmessage" id="cometchat_message_'+incoming.id+'">'+selfstyleAvatar+'<div class="cometchat_chatboxmessagecontent cometchat_floatL" title="'+sentdata+'">'+message+'</div><div class="msgArrow"><div class="after"></div></div></div>';
                                addMessage = 1;
                            }
                            if(addMessage==1&&chatboxopen==0){
                                $("#cometchat_tabcontenttext_"+incoming.from).append(msg);
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
                    var isActiveChatBox = $('#cometchat_user_'+incoming.from+'_popup').find('textarea.cometchat_textarea').is(':focus');
                    if((jqcc.cometchat.getThemeVariable('isMini')==1||!isActiveChatBox)&&incoming.self!=1&&incoming.old==0&&settings.desktopNotifications==1){
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
                    var totalHeight = 0;
                    $("#cometchat_tabcontenttext_"+incoming.from).children().each(function(){
                        totalHeight = totalHeight+$(this).outerHeight(false);
                    });
                    if(newMessage>0){
                        if($('#cometchat_tabcontenttext_'+incoming.from).outerHeight(false)<totalHeight){
                            $('#cometchat_tabcontenttext_'+incoming.from).append('<div class="cometchat_new_message_unread"><a herf="javascript:void(0)" onClick="javascript:jqcc.facebook.scrollDown('+incoming.from+');jqcc(\'#cometchat_tabcontenttext_'+incoming.from+' .cometchat_new_message_unread\').remove();">&#9660 '+language[54]+'</a></div>');
                        }
                    }
                    if(incoming.old==0&&!isActiveChatBox){
                        $.facebook.blinkPopupTitle(incoming.from);
                    }
                    if(visibleTab.indexOf(incoming.from) == -1) {
                        var unreadUnseenCount = $('#cometchat_unseenUsers').find('.unread_msg').length;
                        if(unreadUnseenCount > 0) {
                            $('#cometchat_unseenUserCount').html(unreadUnseenCount).show();
                        } else {
                            $('#cometchat_unseenUserCount').hide();
                        }
                    }
                });
            },
            statusSendMessage: function(statustextarea){
                var message = $("#cometchat_optionsbutton_popup").find("textarea.cometchat_statustextarea").val();
                var oldMessage = jqcc.cometchat.getThemeArray('buddylistMessage', jqcc.cometchat.getThemeVariable('userid'));
                if(message!=''&&oldMessage!=message){
                    $('div.cometchat_statusbutton').html('<img src="'+baseUrl+'themes/facebook/images/loader.gif" width="16">');
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
                    $('div.cometchat_guestnamebutton').html('<img src="'+baseUrl+'"themes/facebook/images/loader.gif" width="16">');
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
                $('#cometchat_userstab').removeClass('cometchat_userstabclick cometchat_tabclick');
                $('div.cometchat_tabopen').removeClass('cometchat_tabopen');
                $('span.cometchat_tabclick').removeClass('cometchat_tabclick');
                $('#cometchat_sidebar').hide();
                $('#cometchat_userstab_popup').show();
                $('#cometchat_optionsbutton_popup').hide();
                jqcc.cometchat.setSessionVariable('buddylist', '0');
                $('#cometchat_userstab_text').html(language[17]);
                for(var chatbox in jqcc.cometchat.getThemeVariable('chatBoxesOrder')){
                    if(jqcc.cometchat.getThemeVariable('chatBoxesOrder').hasOwnProperty(chatbox)){
                        if(jqcc.cometchat.getThemeVariable('chatBoxesOrder')[chatbox]!==null){
                            $("#cometchat_user_"+chatbox).find(".cometchat_closebox_bottom").click();
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
                                allChatboxes = newActiveChatboxes;
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
                    amount = parseInt($("#cometchat_user_"+id).attr('amount'))+parseInt(amount);
                    var cometchat_user_id = $("#cometchat_user_"+id);
                    if(amount==0){
                        cometchat_user_id.removeClass('cometchat_new_message').attr('amount', 0).find('div.cometchat_unreadCount').html(amount).hide();
                        $("#unseenUser_"+id).removeClass('unread_msg').find('div.cometchat_unreadCount').html(amount).removeClass('cometchat_unseenUsers_unread');
                    }else{
                        cometchat_user_id.addClass('cometchat_new_message').attr('amount', amount).find('div.cometchat_unreadCount').html(amount).show();
                         $("#unseenUser_"+id).addClass('unread_msg').find('div.cometchat_unreadCount').html(amount).addClass('cometchat_unseenUsers_unread');
                    }

                    cometchat_user_id.click(function(){
                        cometchat_user_id.removeClass('cometchat_new_message').attr('amount', 0);
                        jqcc.cometchat.setThemeVariable('newMessages', 0);
                    });
                    jqcc.cometchat.setThemeArray('chatBoxesOrder', id, amount);
                    jqcc.cometchat.orderChatboxes();
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
                if(orientation==1){
                    cometchat_tooltip.css('left', (pos.left+width)).addClass("cometchat_tooltip_left");
                }else{
                    var tooltipWidth = cometchat_tooltip.width();
                    var tooltipHeight = cometchat_tooltip.height();
                    var leftposition = (pos.left+14)-tooltipWidth+11;
                    if (id== 'cometchat_userstab') leftposition = pos.left-11;
                    if(id == 'loggedout') leftposition += 10;
                    var topposition = pos.top-$(window).scrollTop()-tooltipHeight-7;
                    cometchat_tooltip.removeClass("cometchat_tooltip_left").css({'left':leftposition,'top':topposition});
                }
                cometchat_tooltip.css('display', 'block');
            },
            moveBar: function(relativePixels){
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
                    $('#cometchat_optionsbutton.cometchat_tabclick').click();
                });
                cometchat_search.blur(function(){
                    var searchString = $(this).val();
                    if(searchString==''){
                        cometchat_search.val(language[18]).addClass('cometchat_search_light');
                        $('#cometchat_nousers_found').remove();
                    }
                });
                cometchat_search.keyup(function(event){
                    event.stopImmediatePropagation();
                    if(event.keyCode==27) {
                        $(this).val('').blur();
                    }
                    var searchString = $(this).val();
                    if(searchString.length>0&&searchString!=language[18]){
                        cometchat_userscontent.find("div.cometchat_userlist").hide();
                        cometchat_userscontent.find('.cometchat_subsubtitle').hide();
                        var searchResult = cometchat_userscontent.find('div.cometchat_userlist:icontains('+searchString+')').show();
                        var matchLength = searchResult.length;
                        if(matchLength == 0){
                            if($('#cometchat_nousers_found').length == 0) {
                                $('#cometchat_userslist').prepend('<div id="cometchat_nousers_found">'+language[58]+'</div>');
                            }
                        } else {
                            $('#cometchat_nousers_found').remove();
                        }
                        cometchat_search.removeClass('cometchat_search_light');
                    }else{
                        cometchat_userscontent.find('div.cometchat_userlist').show();
                        cometchat_userscontent.find('.cometchat_subsubtitle').show();
						$('#cometchat_nousers_found').remove();
                    }
                });
                var cometchat_userstab = $('#cometchat_userstab');
                var cometchat_userstab_popup = $("#cometchat_userstab_popup");
                cometchat_userstab_popup.find("div.cometchat_userstabtitle").click(function(){
                    cometchat_userstab.click();
                });
                cometchat_userstab.mouseover(function(){
                    $(this).addClass("cometchat_tabmouseover");
                });
                cometchat_userstab.mouseout(function(){
                    $(this).removeClass("cometchat_tabmouseover");
                });
                var cometchat_loggedoutbutton = $('#loggedout');
                var cometchat_auth_popup = $("#cometchat_auth_popup");
                cometchat_auth_popup.find("div.cometchat_userstabtitle").click(function(){
                    cometchat_loggedoutbutton.click();
                });
                cometchat_loggedoutbutton.mouseover(function(){
                    $(this).addClass("cometchat_tabmouseover");
                });
                cometchat_loggedoutbutton.mouseout(function(){
                    $(this).removeClass("cometchat_tabmouseover");
                });
                var auth_logout = $("div#cometchat_authlogout");
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
                        auth_logout.css('background','url('+baseUrl+'themes/facebook/images/loading.gif) no-repeat top left');
                        jqcc.ajax({
                            url: baseUrl+'functions/login/logout.php',
                            dataType: 'jsonp',
                            success: function(){
                                if(typeof(cometuncall_function)==="function"){
                                    cometuncall_function(jqcc.cometchat.getThemeVariable('cometid'));
                                    jqcc.cometchat.setThemeVariable('cometid','');
                                }
                                auth_logout.css('background','url('+baseUrl+'themes/facebook/images/logout.png) no-repeat top left');
                                logout_click();
                                $("#cometchat_user_"+jqcc.cometchat.getThemeVariable('openChatboxId')).find('.cometchat_closebox_bottom').click();
                                jqcc.cometchat.setSessionVariable('openChatboxId', '');
                                $.cookie(settings.cookiePrefix+"loggedin", null, {path: '/'});
                                $.cookie(settings.cookiePrefix+"state", null, {path: '/'});
                                $.cookie(settings.cookiePrefix+"jabber", null, {path: '/'});
                                $.cookie(settings.cookiePrefix+"jabber_type", null, {path: '/'});
                                $.cookie(settings.cookiePrefix+"hidebar", null, {path: '/'});
                                jqcc[settings.theme].loggedOut();
                                $('#cometchat_optionsbutton_popup .cometchat_userstabtitle').click();
                                clearTimeout(jqcc.cometchat.getCcvariable().heartbeatTimer);
                            },
                            error: function(){
                                logout_click();
                                alert(language[81]);
                            }
                        });
                    });
                }
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
                    $('#cometchat_optionsbutton_popup').removeClass('cometchat_tabopen').hide();
                    $('#cometchat_optionsbutton').removeClass('cometchat_tabclick');
                    if($(this).hasClass("cometchat_tabclick")){
                        jqcc.cometchat.setSessionVariable('buddylist', '0');
                        cometchat_userstab_popup.addClass("cometchat_tabopen");
                    }else{
                        jqcc.cometchat.setSessionVariable('buddylist', '1');
                        cometchat_userstab_popup.removeClass("cometchat_tabopen");
                        $("#cometchat_tooltip").css('display', 'none');
                        $("span.cometchat_userscontentavatar").find("img").each(function(){
                            if($(this).attr('original')){
                                $(this).attr("src", $(this).attr('original'));
                                $(this).removeAttr('original');
                            }
                        });
                    }
                    $(this).toggleClass("cometchat_tabclick cometchat_userstabclick");
                    if(settings.showSettingsTab==0){
                        $('span.cometchat_userstabclick').addClass('cometchat_extra_width');
                    }
                    cometchat_search.removeClass('cometchat_option_active_serach');
                    $('#cometchat_sidebar').toggle();
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
                    if(!cometchat_optionsbutton_popup.hasClass("cometchat_tabopen")){
                        if(jqcc.cometchat.getThemeVariable('loggedout')==0){
                            if(tooltipPriority==0){
                                jqcc[settings.theme].tooltip('cometchat_optionsbutton', language[0]);
                            }
                        }else{
                            if(tooltipPriority==0){
                                jqcc[settings.theme].tooltip('cometchat_optionsbutton', language[8]);
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
                        $(this).toggleClass("cometchat_tabclick");
                        cometchat_optionsbutton_popup.toggle().toggleClass("cometchat_tabopen");
                        $('#cometchat_userstab_popup').toggle();
                        $('#cometchat_search').toggleClass('cometchat_option_active_serach');
                        if($(this).hasClass('cometchat_tabclick')){
                            $('#cometchat_userstab_popup').removeClass("cometchat_tabopen");
                            $('#cometchat_userstab').removeClass('cometchat_tabclick');
                            jqcc.cometchat.setSessionVariable('buddylist', '0');
                        } else {
                            $('#cometchat_userstab_popup').addClass("cometchat_tabopen");
                            $('#cometchat_userstab').addClass('cometchat_tabclick');
                            jqcc.cometchat.setSessionVariable('buddylist', '1');
                        }
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
                            jqcc("#cometchat_chatboxes_wide").find('span').each(function(index){
                                if($('#'+$(this).attr('id')).hasClass('cometchat_tabclick')){
                                    $('#'+$(this).attr('id')).removeClass('cometchat_tabclick').removeClass('cometchat_usertabclick');
                                    $('#'+$(this).attr('id')+'_popup').removeClass('cometchat_tabopen');
                                }
                            });
                        }
                    }else{
                        if(language[16]!=''){
                            location.href = language[16];
                        }
                    }
                });

                if(settings.ccauth.enabled == "1"){
                    var cometchat_loggedoutbutton = $('#loggedout');
                    cometchat_loggedoutbutton.click(function(){
                        $("#cometchat_tooltip").css('display', 'none');
                        var baseLeft = $('#cometchat_base').position().left;
                        var barActualWidth = $('#cometchat_base').width();
                        $(this).toggleClass("cometchat_tabclick");
                        cometchat_auth_popup.toggleClass("cometchat_tabopen");
                        cometchat_loggedoutbutton.toggleClass("cometchat_optionsimages_click");
                    });
                }
                cometchat_optionsbutton_popup.find("div.cometchat_userstabtitle").click(function(){
                    $('#cometchat_userstab_popup').show();
                    $('#cometchat_userstab').addClass('cometchat_tabclick').click();
                });
            },
            chatboxKeyup: function(event, chatboxtextarea, id){
                if(event.keyCode==27){
                    event.stopImmediatePropagation();
                    $(chatboxtextarea).val('');
                     $("#cometchat_user_"+id+"_popup").find('div.cometchat_tabtitle').click();
                }
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
                        $("#cometchat_user_"+id+"_popup").find("div.cometchat_tabcontenttext").css('height', ((chatboxHeight)-(adjustedHeight)+23)+'px');
                        $("#cometchat_user_"+id+"_popup").find("div.cometchat_tabcontent > div.slimScrollDiv").css('height', ((chatboxHeight)-(adjustedHeight)+23)+'px');
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
                    $(chatboxtextarea).css('height', '16px');
                    $("#cometchat_user_"+id+"_popup").find("div.cometchat_tabcontent > div.slimScrollDiv").css('height', ((chatboxHeight)+10)+'px');
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
            },
            scrollDown: function(id){
                if(jqcc().slimScroll){
                    $('#cometchat_tabcontenttext_'+id).slimScroll({scroll: '1',railAlwaysVisible: true});
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
                $("#cometchat_tabcontenttext_"+id).find('div.cometchat_chatboxmessage').remove();
				if(typeof (jqcc[settings.theme].addMessages)!=='undefined'&&data.hasOwnProperty('messages')){
					jqcc[settings.theme].addMessages(data['messages']);
                }
            },
            windowResize: function(silent){
                $('#cometchat_base').css('left', settings.barPadding);
                if($('#cometchat_base').length){
                    var baseRight = (settings.showOnlineTab == 1) ? (settings.barPadding+215) : settings.barPadding;
                    $('#cometchat_base').css({'left': 'auto', 'right': baseRight+'px'});
                }

                jqcc[settings.theme].scrollBars(silent);
                $.facebook.closeTooltip();
                $.facebook.rearrange();
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
                $("#cometchat_chatboxes_wide").find("div.cometchat_tabalert").each(function(){
                    var cometchat_chatboxes = $("#cometchat_chatboxes");
                    if(($(this).parent().offset().left<(cometchat_chatboxes.offset().left+cometchat_chatboxes.width()))&&($(this).parent().offset().left-cometchat_chatboxes.offset().left)>=0){
                        $(this).css('display', 'block');
                    }else{
                        $(this).css('display', 'none');
                        if(($(this).parent().offset().left-cometchat_chatboxes.offset().left)>=0){
                            $("#cometchat_chatbox_right").find("div.cometchat_tabalertlr").html(parseInt($("#cometchat_chatbox_right").find("div.cometchat_tabalertlr").html())+parseInt($(this).html()));
                            $("#cometchat_chatbox_right").find("div.cometchat_tabalertlr").css('display', 'block');
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
                    $('#loggedout').removeClass('cometchat_optionsimages_exclamation');
                    $('#loggedout').removeClass('cometchat_optionsimages_ccauth');
                    $('#loggedout').removeClass('cometchat_tabclick');
                    $('#loggedout').hide();
                    $("body").append(msg_beep);
                    $("#cometchat_base").append(side_bar);
                    $("#cometchat_base").append(option_button);
                    $("#cometchat_base").append(usertab2);
                    $("#cometchat_base").append(user_tab);
                    $("#cometchat_base").append(chat_boxes);
                    $("#cometchat_base").append(chat_left);
                    $("#cometchat_base").append(unseen_users);
                    $("#cometchat_optionsbutton_popup").hide();
                    $("#cometchat_optionsbutton,#cometchat_sidebar").show();
                    $("#cometchat_userstab").addClass('cometchat_userstabclick');
                    $("#cometchat_userstab").show();
                    jqcc.cometchat.setThemeVariable('loggedout', 0);
                    jqcc.cometchat.setExternalVariable('initialize', '1');
                    jqcc.cometchat.chatHeartbeat();
                    $('#cometchat_optionsbutton.cometchat_tabclick').click();
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
                jqcc[settings.theme].moveBar("-=230px");
            },
            moveRight: function(){
                jqcc[settings.theme].moveBar("+=230px");
            },
            processMessage: function(message, self){
                return message;
            },
            minimizeAll: function(){
                $("div.cometchat_tabpopup").each(function(index){
                    if($(this).hasClass('cometchat_tabopen')){
                        $(this).find('div.cometchat_tabtitle').click();
                    }
                });
            },
            iconNotFound: function(image, name){
                $('.'+name+'icon').attr({'src': baseUrl+'modules/'+name+'/icon.png', 'width': '16px'});
            },
            rearrange: function(){
                var ttlWidth = 0;
                visibleTab = [];
                var currUnreadCount = 0;
                var ttlLength = $('#cometchat_chatboxes_wide').children().length;
                $('#cometchat_chatboxes_wide').children().each(function(index){
                    var thisElem = $(this);
                    var left = thisElem.offset().left;
                    var id = thisElem.attr('id').split('_')[2];
                    $('#cometchat_user_'+id+'_popup').css('left',left);
                    if($('#cometchat_user_'+id).outerWidth(false)+1 == chatboxWidth) {
                        left += 100;
                    }
                    if (left < barVisiblelimit) {
                        var currElem = $('#cometchat_user_'+id+'_popup.cometchat_tabopen');
                        currElem.find('div.cometchat_tabtitle').click();
                        currElem.nextAll('div.cometchat_tabopen').find('div.cometchat_tabtitle').click();
                        var unseenUserCount = ttlLength-index;
                        if(unseenUserCount > 0){
                            $('#cometchat_chatbox_left').find('div.cometchat_tabtext').html(ttlLength-index);
                            currUnreadCount = ttlLength-index;
                        }
                        return false;
                    }
                    visibleTab.push(id);
                    ttlWidth += (thisElem.hasClass('cometchat_tabclick')) ? (chatboxWidth+chatboxDistance) : (160+chatboxDistance);
                });
                $('#cometchat_chatboxes').css('width', ttlWidth+'px');
                $.facebook.createUnseenUser();
                if(!currUnreadCount){
                    $('#cometchat_chatbox_left').removeClass('cometchat_unseenList_open').hide();
                    $('#cometchat_unseenUsers').hide();
                    $('#cometchat_chatbox_left_border_fix').hide();
                } else {
                    $('#cometchat_chatbox_left').show();
                    var unreadUnseenCount = $('#cometchat_unseenUsers').find('.unread_msg').length;
                    if(unreadUnseenCount > 0) {
                        $('#cometchat_unseenUserCount').html(unreadUnseenCount).show();
                    } else {
                        $('#cometchat_unseenUserCount').hide();
                    }
                }
            },
            createUnseenUser: function() {
                var unseenUserHtml = ''
                $.each(allChatboxes,function(id){
                   if(visibleTab.indexOf(id) == -1) {
                       var amount = parseInt($('#cometchat_user_'+id).attr('amount'));
                       var countVisible = '';
                       var unreadMsg = '';
                        if(amount > 0) {
                            countVisible = 'style="visibility: visible;" ';
                            unreadMsg = 'unread_msg';
                        }
                       unseenUserHtml += '<div id="unseenUser_'+id+'" class="cometchat_unseenUserList '+unreadMsg+'" uid="'+id+'"><div class="cometchat_unreadCount cometchat_floatL" '+countVisible+'>'+amount+'</div><div class="cometchat_userName cometchat_floatL">'+jqcc.cometchat.getThemeArray('buddylistName', id)+'</div><div class="cometchat_unseenClose cometchat_floatR" uid="'+id+'" >x</div></div>';
                   }
                });
                if(unseenUserHtml == ''){
                    $('#cometchat_chatbox_left').find(".cometchat_unseenList_open").click();
                } else {
                    $('#cometchat_unseenUsers').html(unseenUserHtml);
                }

            },
            swapTab: function(sourceId) {
                var destinationId = visibleTab[visibleTab.length-1] || sourceId;
                var tempElem = $('#cometchat_user_'+sourceId+'_popup').detach();
                $('#cometchat_user_'+destinationId+'_popup').before(tempElem);
                var tempElem = $('#cometchat_user_'+sourceId).detach();
                $('#cometchat_user_'+destinationId).before(tempElem);
                tempElem.click();
                visibleTab.pop();
                visibleTab.push(sourceId);
                $.facebook.createUnseenUser();
                $('#cometchat_chatbox_left').find(".cometchat_unseenList_open").click();
            },
            blinkPopupTitle: function(id){
                clearInterval(blinkInterval);
                blinkInterval= setInterval(function(){
                    $('#cometchat_user_'+id+'_popup').find('div.cometchat_tabtitle').toggleClass('cometchat_new_message_titlebar');
                },1000);
            },
            prependMessagesInit: function(id){
                var messages = jqcc('#cometchat_tabcontenttext_'+id).find('.cometchat_chatboxmessage');
                $('#cometchat_prependMessages_'+id).text(language[41]);
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
                            var avatar = baseUrl+"themes/facebook/images/noavatar.png";
                            if(jqcc.cometchat.getThemeArray('buddylistAvatar', incoming.from)!=""){
                                avatar = jqcc.cometchat.getThemeArray('buddylistAvatar', incoming.from);
                            }
                            fromname = jqcc.cometchat.getThemeArray('buddylistName', incoming.from);
                            selfstyleAvatar = '<a class="cometchat_floatL" href="'+jqcc.cometchat.getThemeArray('buddylistLink', incoming.from)+'"><img src="'+avatar+'" title="'+fromname+'"/></a>';
                            var message = jqcc.cometchat.processcontrolmessage(incoming);

                            if(message == null){
                                return;
                            }

                            if(incoming.sent!=null){
                                var ts = incoming.sent;
                                sentdata = jqcc[settings.theme].getTimeDisplay(ts);
                            }

                            var msg = '';
                            if(parseInt(incoming.self)==1){
                                msg = '<div class="cometchat_chatboxmessage" id="cometchat_message_'+incoming.id+'"><div class="cometchat_chatboxmessagecontent cometchat_self cometchat_floatR" title="'+sentdata+'">'+message+'</div><div class="selfMsgArrow"><div class="after"></div></div></div>';
                            }else{
                                msg = '<div class="cometchat_chatboxmessage" id="cometchat_message_'+incoming.id+'">'+selfstyleAvatar+'<div class="cometchat_chatboxmessagecontent cometchat_floatL" title="'+sentdata+'">'+message+'</div><div class="msgArrow"><div class="after"></div></div></div></div>';
                            }
                            oldMessages+=msg;
                        });
                    }
                });

                jqcc('#cometchat_user_'+id+'_popup').find('.cometchat_tabcontenttext').prepend(oldMessages);
                var prependLimit = jqcc.cometchat.getThemeVariable('prependLimit');
                $('#cometchat_prependMessages_'+id).text(language[83]);
                if((count - parseInt(jqcc.cometchat.getThemeVariable('prependLimit')) < 0)){
                    $('#cometchat_prependMessages_'+id).text(language[84]);
                    jqcc('#cometchat_prependMessages_'+id).attr('onclick','');
                    jqcc('#cometchat_prependMessages_'+id).css('cursor','default');
                }
            }
        };
    })();
})(jqcc);

if(typeof(jqcc.facebook) === "undefined"){
    jqcc.facebook=function(){};
}

jqcc.extend(jqcc.facebook, jqcc.ccfacebook);

jqcc(window).resize(function(){
    jqcc.facebook.windowResize(1);
});

/* for IE8 */
if(!Array.prototype.indexOf){
    Array.prototype.indexOf = function(obj, start){
        for(var i = (start||0), j = this.length; i<j; i++){
            if(this[i]===obj){
                return i;
            }
        }
        return -1;
    }
}

if(!Array.prototype.forEach){
    Array.prototype.forEach = function(fun)
    {
        var len = this.length;
        if(typeof fun!="function")
            throw new TypeError();
        var thisp = arguments[1];
        for(var i = 0; i<len; i++)
        {
            if(i in this)
                fun.call(thisp, this[i], i, this);
        }
    };
}