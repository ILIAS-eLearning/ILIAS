var C = ''.split(':');
var ScormApi = null;

var iyes   = new Image (); iyes.src="./images/n_yes.gif";
var ino    = new Image (); ino.src="./images/n_no.gif";
var iyesno = new Image (); iyesno.src="./images/yesno.png";
var ilike  = new Image (); ilike.src="./images/n_selected.png";
var pixel  = new Image (); pixel.src="./images/pixel.png";

var R = new Array(); // right
var S = new Array(); // studentmyd
var E = new Array(); // unchanged
var T = new Array(); // latency begin times

var loaded = false;
var initialized = false;
var lesson_mode = 'browse';
var lesson_status;
var initTime;

for (var i=0;i<C.length; i++) {
        R[i]=C[i].split(',');
        S[i]=new Array(R[i].length);
        E[i]=new Array(R[i].length);
        for (j=0;j<R[i].length;j++) {
                E[i][j] = (R[i][j].indexOf('.')>0) ? j+1 +'.0' : 0;
                S[i][j] = E[i][j]; //XX
                R[i][j] = (R[i][j].indexOf('.')>0) ? R[i][j] : R[i][j]/1;
        }
}

var totpics = 0;
var loadedpics = 0;


function throbdraw () {
        var s = '';
        for (var i=0; i<100; i++) {
                s += '<div '
                  +  'style="float:left;width:7px;height:100%;" '
                  +  'id="throb'+i+'">&nbsp;</div>';
        }
        throbsay (s+'<br clear="all">');
}

function throbsay (s) {
        if (document.layers) {
                with (getObj('throb')) {
                        open();write(s);close();
                }
        } else {
                getObj('throb').innerHTML+=s;
        }
}

function throb () {
        if (totpics == 0) {
                throbdraw ();
        }

        for (totpics=1; ; totpics++) {
                if (getObj('pic'+totpics) == null) {
                        break;
                }
        }

        loadedpics++;
        var s = loadedpics/totpics*100;
        for (var i=0; i<100; i++) {
                if (s >= i) {
                        getObj('throb'+i).style.background='blue';
                }
        }
}

function eat (it) {

        if (!loaded) return throb ();

        if (it.src) {
                switch (it.name.substring(0, it.name.indexOf('.'))) {
                        case 'hint':  return showHint (it);
                        case 'audio': return playAudio (it);
                }
                return dynShowImg (it);
        }

        switch (it.type) {
                case        "radio":      return radio  (it);
                case        "checkbox":   return check  (it);
                case        "select-one": return select (it);
                case        "textarea":   return textarea (it);
                case        "text":       return text (it);
                case        "button":     return button (it);
        }
}

function showHint (it) {
        show (it.name.substring(it.name.indexOf('.')+1, it.name.length));
}

var audioIdx;

function audioIsActive () {
        try {
                return document.AudioPlayer.isActive();
        } catch (e) {
                return false;
        }
}


function playAudio (it) {

        audioIdx = it.name.substring ('audio.'.length, it.name.length);


        if (!audioIsActive()) {
                document.forms.dynDnld.action='./audio/'+audioIdx+'.mp3';
                if (!document.layers) document.forms.dynDnld.target='_blank';
                document.forms.dynDnld.submit();
                return;
        }


        this.tmr = xTimer.set('interval', this, 'interval', 1000);

        this.interval = function() {
                var status = document.AudioPlayer.Status();
                sayAudioStatus(status);
                if (status=='Finished') getObj('button.audio').value = 'Play';
                if (status=='Playing')  getObj('button.audio').value = 'Stop';
        }

        sayAudioStatus("Initializing");
        document.AudioPlayer.setIdx (audioIdx);
        xMoveTo ('dynAudio', M.pageX, M.pageY);
        document.forms.dynDnld.action='./audio/'+audioIdx+'.mp3';
        show('dynAudio');
        var start = function (e, x, y) {
                e.isDragging = true;
        };
        var move  = function (e, dx, dy) {
                if (e.isDragging) xMoveTo (e, xLeft(e)+dx, xTop(e)+dy);
        };
        var drop = function (e, w, h) {
                e.isDragging = false;
                e.isResizing = false;
        };
        xEnableDrag ('dynAudio', start, move, drop);
}

function stopAudio () {
        var a = getObj ('dynAudio');
        if (a == null) return;
        hide (a);
        if (audioIsActive()) document.AudioPlayer.Stop(' ');
}

function sayAudioStatus (s) {
        if (document.layers) {
                with (getObj('dynAudioStatus')) {
                        open();write(s);close();
                }
        } else {
                getObj('dynAudioStatus').innerHTML=s;
        }
}

function doAudio (it) {
        if (!audioIsActive()) return;
        switch (it.value) {
                case 'Stop': it.value = 'Play';
                             sayAudioStatus('&nbsp;');
                             document.AudioPlayer.Stop(' ');
                             return;

                case 'Play': it.value = 'Stop';
                             document.AudioPlayer.setIdx (audioIdx);
                             sayAudioStatus('Initializing');
                             //document.AudioPlayer.Play();
                             return;
        }
}


function button (that) {
        switch (that.id.substring ('button.'.length, that.id.length)) {
                case "start":     hide('initmsg');
                                  hide('backbutton');
                                  //dynDbg();
                 return dynShow();
                 case "continue": if (dynForm==0 ){show('backbutton');}   hide("learningobjecttitle"); hide("throb"); return dynShow();
                case "back":      return history.go(-1);
                case "close":          return window.close();
                case "audio":          return doAudio(that);
                case "save":      if (document.layers)
                                      document.forms.dynDnld.target='_blank';
                                  document.forms.dynDnld.submit();
                                  return;
                case "finish":          window.close();
        }
}

function radio (that) {
        with (that) {
                n = name.substring(name.indexOf("_")+1, name.length)-1;
                l = "cmi.interactions."+n+".learner_response";
                r = value;
        }
        S[n][0]=r;
        setValue(l,r);
}

function check (that) {
        with (that) {
                n = name.substring(name.indexOf("_")+1, name.length)-1;
                S[n][value-1]=checked?1:0;
        }
        l = "cmi.interactions."+n+".learner_response";
        r = S[n];
        t = S[n].slice(0);
        for (i=0; i<t.length; i++) {if(t[i]==0){t[i]="0_"+i;} else {t[i]="1_"+i;}}
        t=t.toString().replace(/,/g,"[,]");
        r="{"+r.toString()+"}";
        setValue(l,t);
//scorm 1.2                setValue(l,r);
}

function select (that) {
        that.xx = that.selectedIndex;
        var a = new Array();
        var c = that.value;
        a = that.name.split('_');
        q = a[1]-1;
        e = a[2];
        S[q][e-1] = e+'.'+c;
        l = "cmi.interactions."+q+".learner_response";
        t = S[q].slice(0);
        t=t.toString().replace(/\./g,"[.]");
        t=t.replace(/,/g,"[,]");
        r = '{'+S[q]+'}';
        setValue(l,t);
//scorm 1.2                setValue(l,r);
}

function textarea (that) {
        with (that) {
                a = name.split('_');
                c = value;
                if (c.length > 255) {
                        return;
                }
        }
        q = a[1]-1;
        S[q][0] = c;
        l = "cmi.interactions."+q+".learner_response";
        r = c;
 r="[.]"+r;
        setValue (l,r);

        pa = getObj("pa_"+that.name.substring(2,that.name.length));
        if (pa) { // possible answer
                dynVisible(pa, true);
        }
}

function text (that) {
        if (!that.value.length) {
                that.focus();
                return;
        }
        q = getObj(that.name.substring(2,that.name.length));
        a = that.name.split('_');
        a[2]++;
        nq = getObj(a.join('_'));
        for (i=0; i<q.length; i++) {
                if (q.options[i].text == that.value)  {
                        a = that.name.split('_');
                        a = R[a[1]-1][a[2]-1].split('.');
                        if (a[1] != i+1) break;

                        that.blur();
                        that.onfocus=function(){this.blur();};
                        q.selectedIndex = i;
                        select (q);
                        dynProc(that.name.split('_')[1], true);
                        if(nq)nq.focus();
                        return;
                }
        }
        hide(that);
        show(q);
        if(nq)nq.focus();
}

function init (n) {

        loaded = true;
        //hide ('throb');
        say('init', 'Trying to connect to SCORM RTE');
        say('dbg', 'Trying to connect to SCORM RTE');
        findScormApi();
        if (!ScormApi) {
                say ('init', 'SCORM RTE not found.');
                say ('init', 'Continuing without LMS support.');
                say ('dbg', 'SCORM RTE not found.');
        } else {
                say ('init', 'SCORM RTE found.');
                say ('dbg', 'SCORM RTE found.');
                var rv = ScormApi.LMSInitialize('').toString();
                if (rv != 'true' && rv != 't' && rv != '1') {
                        say ('init', 'SCORM RTE comminication failed.');
                        say ('dbg', 'SCORM RTE comminication failed.');
                        ScormApi = null;
                } else {
                        say ('init', 'SCORM RTE comminication established.');
                        say ('dbg', 'SCORM RTE comminication established.');
                        window.onunload = finish;
                        window.onclose = finish;
                        //hide ('initmsg');
                }
                lesson_mode   = getValue ('cmi.core.lesson_mode');
                lesson_status = getValue ('cmi.core.lesson_status');
                if (lesson_status == 'not attempted') {
                        setValue (
                                'cmi.core.lesson_status',
                                lesson_mode = 'normal' ? 'incomplete' : 'browsed'
                        );
                }
        }
        initTime = new Date();
        if (window.name && window.name.indexOf('standalone')!=-1) hide('initmsg');
        var count = getValue ('cmi.interactions._count');
        // bug 11103
        pager.Init();
}

var M;
function mWatch (ev) {
        M = new xEvent(ev);
}

var dynSect=0;  // learningstep index of current step
var dynStep=0;  // element index of current element
var dynForm=0;  // interaction index of current/last interaction
var dynDone=0;  // interaction index of last completed interaction
var dynClick=0; // keep correct interaction displayed before switching step
var dynShown = new Array(); // stack visible elements
var arrayOfDivs = document.getElementsByTagName('div');


function dynFree (f) {
        ff = document.forms["fi_"+f];
        for (i=0; i<document.forms["fi_"+f].length; i++) {
                switch (ff[i].type) {
                case        "radio":
                case        "checkbox":
                        ff[i].onclick=function(){return false;};
                        break;
                case        "select-one":
                        ff[i].onchange=function(){
                                this.selectedIndex=this.xx;
                        };
                        break;

                case        "textarea":
                        break;
                }
        }
}

function dynProc (f, ret) {
        if (f<=dynDone) return 'none';
        if (!document.forms["fi_"+f])  return 'none';
        if (!T[f-1]) T[f-1] = new Date();
        if (document.forms["fi_"+f][0].type == 'textarea') {
                if (S[f-1][0].length > 0) {
                        dynDone = f;
                        dynFree(f);
                        return 'fin';
                } else {
                        return 'initial';
                }
        }

        if (S[f-1].join() == E[f-1].join()) {
                return 'initial';
        }

        for (i=0, n=0; n<document.forms["fi_"+f].length; n++) {
                if (document.forms["fi_"+f][n].name.substring(0,2) != 'q_')
                        continue;
                i++;

                var s = S[f-1][i-1]/1; // student's answer
                var e = E[f-1][i-1]/1; // empty answer
                var r = R[f-1][i-1]/1; // correct answer
                var fbi = document.images["fbi_"+f+"_"+i];

                if (R[f-1].toString().length == 1 && R[f-1][0] == 0) { // likert
                        if (S[f-1][0] == 0) {
                                S[f-1][0]=0;
                                return 'initial';
                        }
                        document.images["fbi_"+f+"_"+S[f-1][0]].src = ilike.src;
                        dynDone = f;
                        dynFree(f);
                        return 'fin';
                }

                if (R[f-1].toString().length == 1) { // radio
                        fbi.src = pixel.src;
                        if (i != S[f-1][0]/1) continue;
                        if (i == R[f-1][0]/1) {
                                S[f-1][0]=i;
                                fbi.src = iyes.src;
                        } else {
                                fbi.src = ino.src;
                        }
                        continue;
                }

                // matching or checkbox
                if (s == e ) {
                        fbi.src = pixel.src;
                        if (!T[f-1]) T[f-1] = new Date();
                } else {
                        fbi.src = (s == r) ? iyes.src : ino.src;
                }
        }

        for (i=0; i<R[f-1].toString().length; i++) {
                if (R[f-1][i] != S[f-1][i]) {
                        return 'inc';
                }
        }

        dynDone = f;
        dynFree(f);
        f--;
        setElapsedTime ('cmi.interactions.'+f+'.latency', T[f]);
        //Commit
        return 'fin';
}

function getObj (did) {
        return xGetElementById (did);
}

function dynHideAll () {
        for (i=0; i<dynShown.length; i++) {
                div = getObj (dynShown[i]);
                dynSwitch (div, false);
        }
        dynShown = new Array();
}

function dynVisible (o, on) {
        if (on) xShow (o); else xHide(o);
        o.style.display = on ? "block"   : "none";
}

function dynSwitch (o, on) {
        if (on) dynShown[dynShown.length] = o.getAttribute('id');
        dynVisible (o, on);
}

function show (n) {
        o = typeof(n)=='string'?getObj (n):n;
        if (!o) return;
        if(o.style) o.style.display = '';
        else o.display='';
}

function hide (n) {
        o = typeof(n)=='string'?getObj (n):n;
        if (!o) return;
        if (o.style)o.style.display = 'none';
        else o.display = 'none';
}

function dynShow (back) {
        hide ('throb');
        hide ('start');
        hide ('description');
        hide ('objectives');

        hide ('dynWin');
        stopAudio();

        hide ('sco');

        if (back) {
            mydynBack();
        } else {
                mydynShow();
        }

        show ('sco');
}


function back() {
    dynShow(true);
}


function showAll() {
        //showall
    for (var i=0; i < arrayOfDivs.length; i++) {
        var my_id=arrayOfDivs[i].id;
                if (my_id.indexOf("s")==0) {
                        show(my_id);
                }
    }
        hide ('start');
        hide('initmsg');

}


function mydynBack() {

        var to_hide=dynSect-1;
          var to_show=dynSect-2;
        var last_arr=new Array();
        hide ('throb');
        hide ('start');
        hide ('description');
        hide ('objectives');

        hide ('dynWin');
        stopAudio();

        hide ('sco');

        //hide current section
    for (var i=0; i < arrayOfDivs.length; i++) {
        var my_id=arrayOfDivs[i].id;
                if (my_id.indexOf("s"+to_hide)==0) {
                        hide(my_id);
                }
    }

        dynShown=new Array();
        //show previous section
        for (var i=0; i < arrayOfDivs.length; i++) {
    var my_id=arrayOfDivs[i].id;
                if(my_id) {
                        var my_obj=document.getElementById(my_id);
                        var test=my_id.split("n");

                        if (test[0]=="s"+to_show) {
                                dynSwitch(my_obj,true)
                                last_arr.push(my_obj);
                        }
                }
        }

        var lastelement=last_arr[last_arr.length-1];
        show ('sco');
        //alert(lastelement.id);
        lastelement.id.split
        var last=lastelement.id;
        //alert("Alert"+last)
        var res=last.split("s");
        res2=res[1].split("n");
        var sec=res2[0];
        var res3=res2[1].split("i");
        var step=res3[0];
        var form=res3[1];
        dynSect=eval(sec)+1;
        dynForm=eval(form);
        dynStep=eval(step);
        if (dynSect==2) {
            show("learningobjecttitle");
            hide("backbutton");
        }

}


function mydynShow (cont) {

        if (getObj ("s"+dynSect+"n"+dynStep+"i"+dynForm)) {
                istat = dynProc(dynForm);
                if (istat == 'fin') {
                        mydynShow (true);
                        return;
                }
                if (istat != 'none'){
                        return;
                }
        }

        nn = dynStep+1;
        ns = dynSect+1;
        ni = dynForm+1;

        ndivStep = getObj ("s"+dynSect+"n"+nn+"i"+dynForm);
        ndivSect = getObj ("s"+ns+"n"+nn+"i"+dynForm);
        ndivForm = getObj ("s"+dynSect+"n"+nn+"i"+ni);
        ndivAll  = getObj ("s"+ns+"n"+nn+"i"+ni);

        if (!cont) dynHideAll();

        if (ndivForm) {
                dynForm++;
                dynSwitch (ndivForm, true);
                setLocation();
                mydynShow(true);
                show ('continue');
                hide('backbutton');
                return;
        }
        if (ndivSect) {
                dynSect++;
                setLocation();
                if (!dynShown.length) {
                        dynSwitch (ndivSect, true);
                        mydynShow(true);
                }
                show ('continue');
                return;
        }
        if (ndivStep) {
                if (!cont) window.scrollTo(0,0);
                dynStep++;
                dynSwitch (ndivStep, true);
                mydynShow(true);
                show ('continue');
                return;
        }
        if (ndivAll) {
                dynForm++; dynSect++; dynStep++;
                dynSwitch (ndivAll, true);
                show ('continue');
                return;
        }
        if (!dynShown.length) {
                // end of sco
                lesson_status = lesson_mode == 'browse' ? 'browsed' : 'completed';
                setValue ('cmi.core.lesson_status', lesson_status);
                hide ('continue');
                show("learningobjecttitle");
                finish();

                if (window.name && window.name.indexOf('scowindow') != -1) {
                        show ('finishedwindow');
                        setTimeout ('window.close();', 1000);
                } else {
                        show ('finished');
                }
                return;
        }
        setLocation();
}

function finishSCO () {
	lesson_status = lesson_mode == 'browse' ? 'browsed' : 'completed';
    setValue ('cmi.core.lesson_status', lesson_status);
	
}

function setLocation () {
        setValue ('cmi.core.lesson_location', dynSect+':'+dynStep+':'+dynForm);
}

var sX = 0;
var sY = 0;

function dynShowImg(it){
        var dynImg = getObj("dynImg");
        var dynWin = getObj("dynWin");

        if (!it) {
                hide (dynWin);
                return;
        }

        if (sX && sY) {
                xResizeTo (dynImg, sX, sY);
                xResizeTo (dynWin, sX, sY);
        }

        sX = document.images['im'+it.name.substring (2, it.name.length)].width;
        sY = document.images['im'+it.name.substring (2, it.name.length)].height;

        var p = it.src.split('/');
        dynImg.src='pictures/'+p[p.length-1].substring(10,p[p.length-1].length);

        xMoveTo (dynWin, M.pageX, M.pageY-sY);
            xResizeTo (dynImg, sX, sY);
            xResizeTo (dynWin, sX+4, sY+4);
        show (dynWin);

        var start = function (e, x, y) {
                if (M.pageY < xTop(e)+20 &&
                    M.pageX > xLeft(e)+xWidth(e)-20) {
                        hide (e);
                        return;
                }
                if (M.pageY > xTop(e)+xHeight(e)-20 &&
                    M.pageX > xLeft(e)+xWidth(e)-20) {
                            e.isResizing = true;
                        return;
                }
                e.isDragging = true;
        };
        var move  = function (e, dx, dy) {
                if (e.isDragging) xMoveTo (e, xLeft(e)+dx, xTop(e)+dy);
                if (e.isResizing) {
                    xResizeTo (e, xWidth(e)+dx, xHeight(e)+dy);
                    xResizeTo (dynImg, xWidth(dynImg)+dx, xHeight(dynImg)+dy);
                }
        };
        var drop = function (e, w, h) {
                e.isDragging = false;
                e.isResizing = false;
        };
        xEnableDrag (dynWin, start, move, drop);
}

function say (w, s) {
}

function findScormApi (w) {
        if (w == null) {
                return findScormApi (this.top);
        }
        try {
                if (w.API) { // found it
                        ScormApi = w.API;
						ScormApi.version = "1.2";
                        return;
                }
        } catch (e) {
                return;
        }
        for (var i=0;i<w.length && ScormApi == null; i++) {
                findScormApi (w.frames[i]);
        }
        if (ScormApi == null && this.opener) {
                if (this == this.top) return;
                findScormApi (this.opener.top);
        }
}

function setValue (l, r) {
        say ('dbg', 'LMSSetValue ("'+l+'","'+r+'");');
        if (!ScormApi) return;
        ScormApi.LMSSetValue (l, r);

}
function getValue (l) {
        var r;
        if (ScormApi) {
                r = ScormApi.LMSGetValue (l);
        }
        say ('dbg', 'LMSGetValue ("'+l+'")="'+r+'"');
        return r;
}

function setElapsedTime (l, t) {
        var c= new Date();
        var e= Math.round((c.getTime() - t.getTime())/1000.);
        var hr  = Math.floor(e/3600);
        var min = Math.floor((e-(hr*3600))/60);
        var sec = e-hr*3600 - min*60;
        if (hr<10) hr = "0"+hr;
        if (min<10) min = "0"+min;
        if (sec<10) sec = "0"+sec;
         setValue (l, "PT"+hr+"H"+min+"M"+sec+"S");
    //scorm 1.2    setValue (l, hr+":"+min+":"+sec+".00");
}


function dynDbg () {
        xMoveTo ('dbgmsg', 0, 0);
        show ('dbgmsg');
        var start = function (e, x, y) {
                e.isDragging = true;
        };
        var move  = function (e, dx, dy) {
                if (e.isDragging) xMoveTo (e, xLeft(e)+dx, xTop(e)+dy);
        };
        var drop = function (e, w, h) {
                e.isDragging = false;
                e.isResizing = false;
        };
        xEnableDrag ('dbgmsg', start, move, drop);
}

function finish () {
        window.onunload = function (){return true;};
        window.onclose = function (){return true;};
        if (!ScormApi) {
                return;
        }
        say ('dbg', 'LMSFinish');
        say ('init', 'SCORM RTE communication complete.');
        ScormApi.LMSFinish('');
        ScormApi = null;
}

