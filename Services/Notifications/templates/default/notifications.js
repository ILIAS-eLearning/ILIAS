var OSDNotifications = function(settings) {

    //var initialNotifications = {INITIAL_NOTIFICATIONS};
    /*var options = $({
        initialNotifications: [],
        pollingIntervall: 0
    }).extend(settings);

console.log(settings);*/
    $.extend(
        {
            initialNotifications: [],
            pollingIntervall: 0
        },
        settings
    );
        //console.log(settings);
    return function() {
        return new function() {
            var me = this;

            var lastRequest = 0;

            var items = {};

            $(settings.initialNotifications).each(function() {
                items['osdNotification_' + this.notification_osd_id] = this;
            });

            function closeNotification(notificationElement) {
                $(notificationElement).animate({
                    height: 0,
                    opacity: 0
                }, 1000, "linear", function (){
                    notificationElement.remove();
                });
            }

            this.removeNotification = function (id, callback) {
                $.get(
                    "ilias.php",
                    {
                        baseClass: 'ilObjChatroomGUI',
                        cmd: 'removeOSDNotifications',
                        notification_id: id
                    },
                    function(data){
                        closeNotification($('#osdNotification_' + id));
                        //$('#osdNotification_' + id).remove();
                        if (items['osdNotification_' + id])
                            delete items['osdNotification_' + id];

			if (typeof callback == 'function') {
				callback();
			}
                    }
                    );
            }

	    function getParam(params, ns, defaultValue) {
		if (typeof params == 'undefined')
		    return defaultValue;

		var parts = ns.split('.', 2);
		if (parts.length > 1) {
		    return (!params[parts[0]] || typeof params[parts[0]][parts[1]] == 'undefined') ? defaultValue : params[parts[0]][parts[1]];
		}
		else {
		    return (!params[ns]) ? defaultValue : params[ns];
		}
	    }

	    function renderItems(data, init) {
		    var currentTime = parseInt(new Date().getTime() / 1000);
		    
		    var newItems = false;
		    
		    $(data.notifications).each(function(){
			if (this.type == 'osd_maint') {
			    if (this.data.title == 'deleted') {
				closeNotification($('#osdNotification_' + this.data.shortDescription));
			    }
			}
			else {
			    var id = this.notification_osd_id;
			    if ($('#osdNotification_' + id).length == 0 && (this.valid_until > currentTime || this.valid_until == 0)) {
				newItems = true;
				
				var newElement = $(
				    '<div class="osdNotification" id="osdNotification_'+this.notification_osd_id+'">'
				    + ((getParam(this.data.handlerParams, 'osd.closable', true)) ? ('<div style="float: right" onclick="OSDNotifier.removeNotification('+this.notification_osd_id+')"><img src="templates/default/images/cancel.png" alt="close"/></div>') : '')
				    + '<div class="osdNotificationTitle"><img class="osdNotificationIcon" src="'+this.data.iconPath+'" alt="" />'
				    + (this.data.link ? ('<a class="target_link" href="'+this.data.link+'" target="'+this.data.linktarget+'">'+this.data.title+'</a>') : this.data.title)
				    + '</div>'
				    + '<div class="osdNotificationShortDescription">'+this.data.shortDescription+'</div>'
				    + '</div>'
				    );
				$('.osdNotificationContainer').append(newElement);

				if (getParam(this.data.handlerParams, 'osd.closable', true)) {
					var href = newElement.find('.target_link').attr('href');
					newElement.find('.target_link').click(function() {
						me.removeNotification(id, function() {
							window.location.href = href;
						});

					});
				}
			    }
			    items['osdNotification_' + this.notification_osd_id] = this;
			}
			
			if (!init && settings.playSound && newItems) {
				//console.log('ring');
				ChatInvitationSound.play('Modules/Chatroom/sounds/receive.mp3');
			}
		});

		$.each(items, function() {
		    //console.log(this);
		    if (this.valid_until < data.server_time && this.valid_until != 0) {

			closeNotification($('#osdNotification_' + this.notification_osd_id));
			if (items['osdNotification_' + this.notification_osd_id])
			    delete items['osdNotification_' + this.notification_osd_id];
		    }
		});
	    }

            this.poll = function() {
                $.get(
                    "ilias.php",
                    {
                        baseClass: 'ilObjChatroomGUI',
                        cmd: 'getOSDNotifications',
                        /*
                             * minus 10 seconds for getting really all messages, even if they
                             * arrived while processing
                             */
                        max_age: Math.abs(lastRequest - 10 - (parseInt(new Date().getTime() / 1000)))
                    },
                    function(data){
                        lastRequest = parseInt(new Date().getTime() / 1000);

			renderItems(data);
                        
                        if (settings.pollingIntervall * 1000) {
                            window.setTimeout(me.poll, settings.pollingIntervall * 1000);
                        }

                    },
                    'json'
                    );


            }

	    renderItems({notifications: settings.initialNotifications}, true);

            if (settings.pollingIntervall * 1000) {
                window.setTimeout(me.poll, settings.pollingIntervall * 1000);
            }
        }
    }();
}


Browser = {
    IE: !!(window.attachEvent && !window.opera),
        Opera:  !!window.opera,
        WebKit: navigator.userAgent.indexOf('AppleWebKit/') > -1,
        Gecko:  navigator.userAgent.indexOf('Gecko') > -1 && navigator.userAgent.indexOf('KHTML') == -1,
        MobileSafari: !!navigator.userAgent.match(/Apple.*Mobile.*Safari/),
        Safari: (navigator.userAgent.indexOf('Gecko') > -1 && /Safari/.test(navigator.userAgent) && /KHTML/.test(navigator.userAgent)),
        Chrome: (navigator.userAgent.indexOf('Gecko') > -1 && /Chrome/.test(navigator.userAgent) && /KHTML/.test(navigator.userAgent))
}
        
ChatInvitationSound = {
        _enabled: true,
        enable: function(){
        ChatInvitationSound._enabled = true;
        },
        disable: function(){
                ChatInvitationSound._enabled = false;
        },
        _container: 'embed',
        play: function(url){
                if(!ChatInvitationSound._enabled) return;
           
            if(Browser.IE)
            {
                oBgsound = document.createElement('BGSOUND');
                oBgsound.id = 'sound';
                oBgsound.src = url;
                oBgsound.loop = 0;
                oBgsound.autostart = true;
                document.body.appendChild(oBgsound);
            }
            else
            {
                switch(ChatInvitationSound._container)
                {
                        case 'embed':
                                oBgsound = document.createElement('EMBED');
                                oBgsound.id = 'sound';
                                oBgsound.src = url;
                                oBgsound.loop = false;
                                oBgsound.autostart = true;
                                oBgsound.hidden = true;
                                oBgsound.width = '0';
                                oBgsound.height = '0'
                                document.body.appendChild(oBgsound);
                                break;
                        case 'object':
                                oBgsound = document.createElement('OBJECT');
                                oBgsound.id = 'sound';
                                oBgsound.data = url;
                                oBgsound.type = 'audio/mpeg';
                                oBgsound.width = '0';
                                oBgsound.height = '0'
                                document.body.appendChild(oBgsound);
                                break;  
                }
            }
          }
}

if((Browser.Gecko && navigator.userAgent.indexOf('Win') > 0) ||
        Browser.Safari || 
        Browser.Chrome) {
        if(navigator.plugins)
        {
                qt_found = false;
                for(var i = 0; i < navigator.plugins.length; i++)
                {
                        if(navigator.plugins[i].name.indexOf('QuickTime') != -1)
                        {
                                qt_found = true;                                
                        }
                        
                        if(qt_found == true) break;
                }
                if(qt_found == true)
                {
                        ChatInvitationSound._container = 'object';
                }
        }
        else
        {
                ChatInvitationSound.play = function(){}
        }
}