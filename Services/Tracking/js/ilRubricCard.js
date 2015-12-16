var RUBRIC = {
    
    tbl : '',
    
    defaultPoints:function(){
        
        var point_ranges=new Array();
        point_ranges[0]=new Array(50,70,'Excellent');
        point_ranges[1]=new Array(40,60,'Good');
        point_ranges[2]=new Array(30,50,'Acceptable');
        point_ranges[3]=new Array(20,40,'Fair');
        point_ranges[4]=new Array(10,30,'Poor');
        point_ranges[5]=new Array(0,20,'Bad');
        
        return(point_ranges);        
    },
    
    
    updateGrade:function(){
        
        var tbody=this.tbl.getElementsByTagName('tbody');
        var tfoot=this.tbl.getElementsByTagName('tfoot');
        
        // get the point values for this card
        var points=this.gatherPointValues();
        
        var grade_mark=0;        
        var total_grade_mark=0;
        var points_per_group=0;
        var total_points_per_group=0;
        
        var max_grade_mark=0;
        for(var a=0;a<points.length;a++){
            if(parseFloat(points[a])>max_grade_mark){
                max_grade_mark=points[a];
            }
        }
        
        var trs=tbody[0].getElementsByTagName('tr');
        for(var a=0;a<trs.length;a++){
            
            var inputs=trs[a].getElementsByTagName('input');
            
            if(inputs.length==0){                
                trs[a].children[1].innerHTML=points_per_group.toFixed(2)+' out of '+total_points_per_group.toFixed(2);
                points_per_group=0;
                total_points_per_group=0;
            }
            
            var radio_count=0;
            for(var b=0;b<inputs.length;b++){
                if(inputs[b].type=='radio'){
                    if(inputs[b].checked===true){
                        grade_mark+=parseFloat(points[radio_count]);
                        points_per_group+=parseFloat(points[radio_count]);                        
                    }                    
                    radio_count++;
                    
                    if(b==0){
                        total_grade_mark+=parseFloat(max_grade_mark);
                        total_points_per_group+=parseFloat(max_grade_mark);                        
                    }                   
                }
            }
        }
        
        // update footer points
        tfoot[0].children[0].children[1].innerHTML=grade_mark.toFixed(2)+' out of '+total_grade_mark.toFixed(2);
        
        // update hidden field
        var tmp_mark=(grade_mark/total_grade_mark)*100;
        document.getElementById('mark').value=tmp_mark.toFixed(2);
        
    },
    
    gatherPointValues:function(){
        
        var thead=this.tbl.getElementsByTagName('thead');
        var ths=thead[0].getElementsByTagName('th');
        var points=new Array();
                
        //skip first 2 (group/criteria) and last (comments)
        var b=0;          
        for(var a=2;a<(ths.length-1);a++){
            var matches=ths[a].innerHTML.match(/\d+\.\d{2}/);
            if(typeof(matches)!=null){
                points[b]=matches;
                b++;                
            }
                        
        }
        return(points);
    },
    
    updatePoints:function(){
        
        var total_behaviors=this.howManyBehaviors();        
        
        var tbody=this.tbl.getElementsByTagName('tbody');
        var tfoot=this.tbl.getElementsByTagName('tfoot');
        
        var trs=tbody[0].getElementsByTagName('tr');
        
        var groups=1;
        var tmp_range='';
        
        var max=min=0;
        var group_max=group_min=0;
        var overall_max=overall_min=0;
        for(var a=0;a<trs.length;a++){
            
            if(this.nodeHasSlider(trs[a])){
                // new range
                
                max=min=0;
                group_min=group_max=0;                
                
                for(var b=0;b<total_behaviors;b++){
                    
                    tmp_range=document.getElementById('Points'+groups+'_'+b+'_value').innerHTML;                                        
                    var broken_range=tmp_range.split(',');
                    
                    if(max==0&&min==0){
                        max=broken_range[1];
                        min=broken_range[0];
                    }else{
                        if(broken_range[1]>max){
                            max=broken_range[1];
                        }
                        if(broken_range[0]<min){
                            min=broken_range[0];
                        }
                    }
                }
                groups++;// update group increment                
                
            }else if(!this.nodeHasPointRange(trs[a])){
                // group and/or criteria
                group_max+=parseInt(max);
                group_min+=parseInt(min);
                
                overall_max+=parseInt(max);
                overall_min+=parseInt(min);
                
            }else{
                // point range
                trs[a].children[1].innerHTML=group_min+' - '+group_max;
            }
            
        }// for var a
        
        //update overall point range
        tfoot[0].children[0].children[1].innerHTML=overall_min+' - '+overall_max;        
        
    },
    
    modBehavior:function(requested_behaviors){
        
        var thead=this.tbl.getElementsByTagName('thead');
        var tbody=this.tbl.getElementsByTagName('tbody');
        var tfoot=this.tbl.getElementsByTagName('tfoot');
        
        //how many behaviors is there?
        var current_behaviors=this.howManyBehaviors();
        
        if(requested_behaviors<current_behaviors){
            
            for(var a=current_behaviors;a>requested_behaviors;a--){
                this.delBehavior(thead[0],tbody[0],tfoot[0],a);                
            }
            
        }else if(requested_behaviors>current_behaviors){
            
            for(var a=(current_behaviors+1);a<=requested_behaviors;a++){
                this.addBehavior(thead[0],tbody[0],tfoot[0],a)                
            }
            
        }
        
    },
    
    delBehavior:function(thead,tbody,tfoot,position){
        
        //remove thead
        thead.children[0].removeChild(thead.children[0].lastElementChild);
        
        //remove tbody        
        var trs=tbody.getElementsByTagName('tr');
        for(var a=0;a<trs.length;a++){            
            if(this.nodeHasPointRange(trs[a])){
                trs[a].children[1].colSpan=trs[a].children[1].colSpan-1;
            }else{
                trs[a].removeChild(trs[a].lastElementChild);                
            }
        }
        
        //remove tfoot
        tfoot.children[0].lastElementChild.colSpan=tfoot.children[0].lastElementChild.colSpan-1;
        
    },  
    
    addBehavior:function(thead,tbody,tfoot,position){
        position--;
        console.log('addBehavior position is '+position);
        var label_value=points_value='';
        
        var txt_label='Label';
        var txt_points='Points';
        
        var point_range=this.defaultPoints();
        
        var label_value=point_range[position][2];
        
        th=document.createElement('th');
        th.setAttribute('scope','col');
        
        // thead changes
        th.appendChild(this.createCardFormHeadInputsDiv(txt_label,label_value,position));
        //th.appendChild(this.createCardFormHeadInputsDiv(txt_points,points_value,position-1));
        thead.children[0].appendChild(th);
        
        var slider_increment=0;
        
        // tbody changes
        var trs=tbody.getElementsByTagName('tr');
        for(var a=0;a<trs.length;a++){
            console.log('working '+a);
            if(this.nodeHasSlider(trs[a])){
                console.log('found a slider');
                slider_increment++;
                trs[a].appendChild(this.addPointSlider(slider_increment,position))
                
                console.log('1APPLYING LISTENER ON Points'+slider_increment+'_'+position+' and Points'+slider_increment+'_'+position+'_value');
                $("#Points"+slider_increment+"_"+position).slider({tooltip: 'hide'});  
                $("#Points"+slider_increment+"_"+position).on("slide", function(slideEvt) {
                    //$("#Points"+a+"_"+position+"_value").text(slideEvt.value);
                    $('#'+this.id+'_value').text(' '+slideEvt.value);
                    recalculate();
                });
            }else if(!this.nodeHasPointRange(trs[a])){
                console.log('found a criteria');
                trs[a].appendChild(this.createCardFormBodyInputs('Behavior Description',a-1,position,false));
            }else{
                // this is a point range
                trs[a].children[1].colSpan=position+1;
                console.log('found a poitn label');
            }
                                  
        }
        
        // tfoot changes
        var trs=tfoot.getElementsByTagName('tr');
        trs[0].children[1].colSpan=position+1;
        
    },
        
    howManyBehaviors:function(){
        //calculate how many behaiors needed
        var thead=this.tbl.getElementsByTagName('thead');        
        var ths=thead[0].getElementsByTagName('th');        
        return(ths.length-1);        
    },
    
    howManyGroups:function(){
        var total_groups=0;
        
        var tbody=this.tbl.getElementsByTagName('tbody');
        var trs=tbody[0].getElementsByTagName('tr');
        for(var a=0;a<trs.length;a++){
            if(this.nodeHasGroupName(trs[a])){
                total_groups++;
            }            
        }
        
        return(total_groups);
    },
    
    nodeHasGroupName:function(tr){
        
        var labels=tr.getElementsByTagName('label');
        for(var a=0;a<labels.length;a++){
            if(labels[a].innerHTML=='Group Name'){
                return(true);
            }
        }
        return(false);
        
    },
    
    nodeHasPointRange:function(tr){
        
        if(tr.children[0].nodeName=='TH'&&tr.children[1].nodeName=='TD'){
            return(true);            
        }else{
            return(false);
        }
        
    },
    
    nodeHasSlider:function(tr){
        if(tr.children[0].nodeName=='TH'&&tr.children[1].nodeName=='TH'){
            return(true);            
        }else{
            return(false);
        }       
    },
   
    addPoints:function(){
        
        var tr=document.createElement('tr');
        tr.setAttribute('class','tblrow1 small');
        
        // add in 2 spaces, one for the group and one for criteria
        for(var a=0;a<2;a++){
            var th=document.createElement('th');
            th.setAttribute('class','col-sm-2');
            th.setAttribute('scope','col');
            th.appendChild(document.createTextNode('\u0020'));
            tr.appendChild(th);            
        }
        
        var total_groups=this.howManyGroups();
        var behaviors_required=this.howManyBehaviors();
                
        // add in the slider bar
        for(var a=0;a<behaviors_required;a++){
            
            tr.appendChild(this.addPointSlider((total_groups+1),a));           
        }
        
        return(tr);
        
    },
    
    addPointSlider:function(group_number,behavior_number){        
        console.log('building point slider, named Points'+group_number+'_'+behavior_number);
        var point_ranges=this.defaultPoints();
        
        var th=document.createElement('th');
        th.setAttribute('scope','col');
        var div=document.createElement('div');
        div.setAttribute('class','form-group');
        var label=document.createElement('label');
        label.setAttribute('class','control-label');
        label.setAttribute('for','Points'+group_number+'_'+behavior_number);
        label.appendChild(document.createTextNode('Points'));
        var span=document.createElement('span');
        span.setAttribute('id','Points'+group_number+'_'+behavior_number+'_value');
        span.appendChild(document.createTextNode('\u0020'+point_ranges[behavior_number][0]+','+point_ranges[behavior_number][1]));
        label.appendChild(span);
        div.appendChild(label);
        th.appendChild(div);
        var span=document.createElement('span');
        span.appendChild(document.createTextNode('0\u0020'));
        th.appendChild(span);
        var input=document.createElement('input');
        input.setAttribute('id','Points'+group_number+'_'+behavior_number);
        input.setAttribute('class','Points'+group_number+'_'+behavior_number);
        input.setAttribute('type','text');
        input.setAttribute('class','form-control');
        input.setAttribute('value','');
        input.setAttribute('data-slider-min','0');
        input.setAttribute('data-slider-max','100');
        input.setAttribute('data-slider-step','1');
        input.setAttribute('data-slider-value','['+point_ranges[behavior_number][0]+','+point_ranges[behavior_number][1]+']');
        th.appendChild(input);
        var span=document.createElement('span');
        span.appendChild(document.createTextNode('\u0020100'));
        th.appendChild(span);
        console.log('finished building pointslider');
        return(th);
    },    
    
    addGroup:function(){
        
        var tbody=this.tbl.getElementsByTagName('tbody');        
        var trs=tbody[0].getElementsByTagName('tr');
        
        // add points to new group        
        tbody[0].appendChild(this.addPoints());
        
        // apply the slider new points
        var total_groups=this.howManyGroups();
        total_groups++;
        var behaviors_required=this.howManyBehaviors();
        
        for(var a=0;a<behaviors_required;a++){
            $("#Points"+total_groups+"_"+a).slider({tooltip: 'hide'});
  
            $("#Points"+total_groups+"_"+a).on("slide", function(slideEvt) {
                $('#'+this.id+'_value').text(' '+slideEvt.value);
                recalculate();
            });
            
        }
        
        var tr=document.createElement('tr');
        tr.setAttribute('class','tblrow1 small');
        
        tr.appendChild(this.createCardFormBodyInputs('Group Name',trs.length-1,0,true));
        tr.appendChild(this.createCardFormBodyInputs('Criteria Label',trs.length-1,0,true));
        
        var behaviors_required=this.howManyBehaviors();
        console.log('behaviors required is '+behaviors_required);
        
        for(var a=0;a<behaviors_required;a++){
            tr.appendChild(this.createCardFormBodyInputs('Behavior Description',trs.length-1,a,false));
        }
        
        tbody[0].appendChild(tr);
        
        //add points
        var tr=document.createElement('tr');
        tr.setAttribute('class','tblrow1 small');
        var th=document.createElement('th');
        th.setAttribute('class','text-right');
        th.setAttribute('scope','rowgroup');
        th.setAttribute('colspan','2');
        th.appendChild(document.createTextNode('Point Range for Group'));
        tr.appendChild(th);
        
        var td=document.createElement('td');
        td.setAttribute('colspan',this.howManyBehaviors());
        td.appendChild(document.createTextNode('0.5 - 1 Points'))
        tr.appendChild(td);
        
        tbody[0].appendChild(tr);
        
        
        
        
    },
    
    delGroup:function(){
        var tbody=this.tbl.getElementsByTagName('tbody');        
        var trs=tbody[0].getElementsByTagName('tr');
        
        var selected_group = this.getOneSelected(trs,'group');
        
        var parent_tr=trs[selected_group];
        
        var total_criteria=parent_tr.children[0].rowSpan;
        
        console.log('deleting '+total_criteria+' rows from group');
        
        //delete group, criteria
        for(var a=0;a<=total_criteria;a++){
            trs[selected_group].parentNode.removeChild(trs[selected_group]);
        }
        
        // delete the point range
        trs[selected_group-1].parentNode.removeChild(trs[selected_group-1]);
        
    },
    
    delCriteria:function(){
        var tbody=this.tbl.getElementsByTagName('tbody');        
        var trs=tbody[0].getElementsByTagName('tr');
        
        var selected_criteria=this.getOneSelected(trs,'crite');
        
        if(this.nodeHasGroupName(trs[selected_criteria])){
            //if it has a group name, does it have other criteria? if so, we have to move the group, if no, delete the row
            //does the next row have a point range
            if(this.nodeHasPointRange(trs[selected_criteria].nextElementSibling)){
                //delete point range
                trs[selected_criteria].nextElementSibling.parentNode.removeChild(trs[selected_criteria].nextElementSibling);
                
                //delete actual row
                trs[selected_criteria].parentNode.removeChild(trs[selected_criteria]);
                                
            }else{
                
                //create the new td
                var td=document.createElement('td');
                td.setAttribute('rowspan',trs[selected_criteria].children[0].rowSpan-1);
                td.innerHTML=trs[selected_criteria].children[0].innerHTML;
                //insert the new td
                trs[selected_criteria+1].insertBefore(td,trs[selected_criteria].nextSibling.firstChild);
                //delete the old row
                trs[selected_criteria].parentNode.removeChild(trs[selected_criteria]);  
            }
        }else{
            //just delete row, no group name, adjust rowspan
            trs[selected_criteria].parentNode.removeChild(trs[selected_criteria]);
            
            for(var a=selected_criteria-1;a>=0;a--){                
                if(this.nodeHasGroupName(trs[a])){
                    trs[a].children[0].rowSpan=trs[a].children[0].rowSpan-1;
                    break;                    
                }
            }
            
        }
        
    },
    
    addCriteria:function(){
        
        var tbody=this.tbl.getElementsByTagName('tbody');        
        var trs=tbody[0].getElementsByTagName('tr');
        var selected_group = this.getOneSelected(trs,'group');
                
        var parent_tr=trs[selected_group];
        
        var current_row_span=parent_tr.children[0].rowSpan
        
        parent_tr.children[0].setAttribute('rowspan',(current_row_span+1));
        
        //adjust group rowspan
        var tr=document.createElement('tr');
        tr.setAttribute('class','tblrow1 small');
        tr.appendChild(this.createCardFormBodyInputs('Criteria Label',trs.length,0,true));
        
        var behaviors_required=this.howManyBehaviors();
        for(var a=0;a<behaviors_required;a++){
            tr.appendChild(this.createCardFormBodyInputs('Behavior Description',trs.length,a,false));
        }
        
        parent_tr.parentNode.insertBefore(tr,parent_tr.nextSibling);
        
    },
    
    getOneSelected:function(trs,looking_for){
        var selected_element=false;
        var selected_element_counter=0;
        for(var a=0;a<trs.length;a++){
            
            //get inputs
            var elems=trs[a].getElementsByTagName('input');
            for(var b=0;b<elems.length;b++){
                
                if(elems[b].type=='checkbox'&&elems[b].id.toLowerCase().substr(0,5)==looking_for&&elems[b].checked==true){                    
                    selected_element_counter++;
                    selected_element=a;
                }
            }           
        }
        
        if(selected_element_counter==0){
            if(looking_for=='group'){
                throw "Select a group.";                
            }else if(looking_for=='crite'){
                throw "Select a criteria.";
            }            
        }else if(selected_element_counter>1){
            if(looking_for=='group'){
                throw "Multiple groups are selected.  Only one group can be selected.";                
            }else if(looking_for=='crite'){
                throw "Multiple criteria are selected.  Only one criteria can be selected.";
            }
            
        }else{
            return(selected_element);
        }
        
    },
    
    createCardFormHeadInputsDiv:function(txt_label,txt_placeholder,position){
        div=document.createElement('div');
        div.setAttribute('class','form-group has-warning has-feedback');
        
        label=document.createElement('label');
        label.setAttribute('class','control-label');
        label.setAttribute('for',txt_label+position);
        label.appendChild(document.createTextNode(txt_label));
        div.appendChild(label);
        
        input=document.createElement('input');
        input.setAttribute('id',txt_label+position);
        input.setAttribute('name',txt_label+position);
        input.setAttribute('type','text');
        input.setAttribute('class','form-control');
        input.setAttribute('placeholder',txt_placeholder);
        input.setAttribute('aria-describedby',txt_label+position+'WarningStatus');
        input.setAttribute('onkeyup','validate(this)');
        input.setAttribute('oninput','validate(this)');
        input.setAttribute('onblur','recalculate()');
        div.appendChild(input);
        
        span=document.createElement('span');
        span.setAttribute('class','glyphicon glyphicon-warning-sign form-control-feedback');
        span.setAttribute('aria-hidden','true');
        div.appendChild(span);
        
        span=document.createElement('span');
        span.setAttribute('id',txt_label+position+'WarningStatus');
        span.setAttribute('class','sr-only');
        span.appendChild(document.createTextNode('(warning)'));
        div.appendChild(span);
        
        return(div);
    },
    
    createCardFormBodyInputs:function (txt_label,int_row_count,int_iteration,input_has_checkbox){
        var fixed_label=txt_label.replace(' ','').toLowerCase()+int_row_count+'_'+int_iteration;
        var tmp_elem=document.getElementById(fixed_label);
        var tmp_count=int_row_count;        
        while(typeof(tmp_elem)!=='undefined'&&tmp_elem!==null){
            tmp_count++;
            fixed_label=txt_label.replace(' ','').toLowerCase()+tmp_count+'_'+int_iteration;
            tmp_elem=document.getElementById(fixed_label);            
        }
        
        var td=document.createElement('td');
        td.setAttribute('scope','rowgroup');
        
        div=document.createElement('div');
        div.setAttribute('class','form-group has-warning has-feedback');
        
        label=document.createElement('label');
        label.setAttribute('class','control-label');
        label.setAttribute('for',fixed_label);
        label.appendChild(document.createTextNode(txt_label));
        div.appendChild(label);
        
        if(input_has_checkbox===true){
            inputdiv=document.createElement('div');
            inputdiv.setAttribute('class','input-group');
            inputspan=document.createElement('span');
            inputspan.setAttribute('class','input-group-addon');
            input=document.createElement('input');
            input.setAttribute('type','checkbox');
            input.setAttribute('id',fixed_label+'_checkbox');
            inputspan.appendChild(input);
            inputdiv.appendChild(inputspan);
        }
        
        input=document.createElement('input');
        input.setAttribute('id',fixed_label);
        input.setAttribute('name',fixed_label);
        input.setAttribute('type','text');
        input.setAttribute('class','form-control');
        input.setAttribute('placeholder',txt_label);
        input.setAttribute('aria-describedby',fixed_label+"WarningStatus");
        input.setAttribute('onkeyup','validate(this)');
        input.setAttribute('oninput','validate(this)');
        
        if(input_has_checkbox===true){            
            inputdiv.appendChild(input);        
            div.appendChild(inputdiv);            
        }else{            
            div.appendChild(input);            
        }
        
        span=document.createElement('span');
        span.setAttribute('class','glyphicon glyphicon-warning-sign form-control-feedback');
        span.setAttribute('aria-hidden','true');
        div.appendChild(span);
        
        span=document.createElement('span');
        span.setAttribute('id',fixed_label+'WarningStatus');
        span.setAttribute('class','sr-only');
        span.appendChild(document.createTextNode('(warning)'));
        div.appendChild(span);
        
        td.appendChild(div);
        
        return(td);
        
    },
    
    verifyForm:function(){
        var inputs=this.tbl.getElementsByTagName('input');
        
        var verified_object=false;
        
        for(var a=0;a<inputs.length;a++){
            
            if(inputs[a].type=='text'){
            
                switch(inputs[a].id.substr(0,5).toLowerCase()){
                    case 'label':
                    case 'behav':
                    case 'point':
                        verified_object=inputs[a].parentNode.children[3];
                    break;
                    case 'group':
                    case 'crite':
                        verified_object=inputs[a].parentNode.parentNode.children[3];
                    break;
                    default:
                        verified_object=false;
                    break;
                }
                
                if(verified_object===false||verified_object.childNodes[0].nodeValue!='(ok)'){
                    throw 'Missing Data for Rubric';                
                }
            
            }
            
        }
        
        // verfiy passing grade value
        verified_object=document.getElementById('passing_grade').parentNode.children[3];
        if(verified_object.childNodes[0].nodeValue!='(ok)'){
            throw 'Missing Passing Grade for Rubric';                
        }
    },
    
    verifyGrade:function(){
        
        this.updateGrade();
        
        var inputs=this.tbl.getElementsByTagName('input');
        var radios=new Array();
        
        var verified_object=true;
        
        for(var a=0;a<inputs.length;a++){            
            if(inputs[a].type=='radio'){
                
                if(!inputs[a].name in radios){
                    radios[inputs[a].name]=false;                    
                }
                
                if(inputs[a].checked){
                    radios[inputs[a].name]=true;
                }
                
            }            
        }
                
        for(var a in radios){
            if(radios[a]==false){
                throw 'All Critiera not Marked';
            }            
        }
        
    },
    
    fixids:function(td,type,g,c,b){
        var tmpname='';
        if(type=='group'||type=='criteria'){
            
            if(type=='group'){
                tmpname='Group_'+g;
            }else{
                tmpname='Criteria_'+g+'_'+c;
            }
                        
            td.children[0].children[0].setAttribute('for',tmpname);// label
            td.children[0].children[1].children[0].children[0].setAttribute('id',tmpname+'_checkbox');// input checkbox
            td.children[0].children[1].children[1].setAttribute('id',tmpname); // input text
            td.children[0].children[1].children[1].setAttribute('name',tmpname); // input text
            td.children[0].children[1].children[1].setAttribute('aria-describedby',tmpname+'_WarningStatus'); // input text
            td.children[0].children[3].setAttribute('id',tmpname+'_WarningStatus');// span        
            
        }else if(type=='behavior'){
            
            tmpname='Behavior_'+g+'_'+c+'_'+b;
            
            td.children[0].children[0].setAttribute('for',tmpname);// label            
            td.children[0].children[1].setAttribute('id',tmpname); // input text
            td.children[0].children[1].setAttribute('name',tmpname); // input text
            td.children[0].children[1].setAttribute('aria-describedby',tmpname+'_WarningStatus'); // input text
            td.children[0].children[3].setAttribute('id',tmpname+'_WarningStatus');// span
            
        }else if(type=='point'){
            
            tmpname='Points'+g+'_'+b;
            console.log('changing point id to '+tmpname);
            
            console.log('1fixing ids, working on '+td.children[0].children[0].nodeName);
            td.children[0].children[0].setAttribute('for',tmpname);// label
            
            console.log('2fixing ids, working on '+td.children[0].children[0].children[0].nodeName);            
            td.children[0].children[0].children[0].setAttribute('id',tmpname+'_value'); // span point value
            
            console.log('3fixing ids, working on '+td.children[2].children[2].nodeName);
            td.children[2].children[2].setAttribute('name',tmpname); // input text
            td.children[2].children[2].setAttribute('id',tmpname); // input text
                                 
        }
        
    },
    
    reorganize:function(){
        console.log('reorganizagin');
        //fix for working in Ilias
        var tbody=this.tbl.getElementsByTagName('tbody');        
        var trs=tbody[0].getElementsByTagName('tr');
        
        var group=0;// group increment
        var criteria=0;// criteria increment
        var behavior=0;// behavior increment
        
        for(var a=0;a<trs.length;a++){
            
            console.log('tr a values is '+a+', group is '+(group+1)+', trs length is '+trs.length);
            
            if(this.nodeHasSlider(trs[a])){
                // if slider row
                
                var ths=trs[a].getElementsByTagName('th');
                
                for(var b=2;b<ths.length;b++){
                    console.log('has sldier, group is '+(group+1));
                    this.fixids(ths[b],'point',(group+1),criteria,(b-2));
                    
                                        
                }
                
                
                
            }else if(!this.nodeHasPointRange(trs[a])){
                // if group row
                
                var tds=trs[a].getElementsByTagName('td');
                
                if(this.nodeHasGroupName(trs[a])){
                    // if row has group name
                    
                    // make changes to group, criteria and behaviors
                    for(var b=0;b<tds.length;b++){
                        
                        if(b==0){
                            group++;
                            this.fixids(tds[b],'group',group,criteria,behavior);                            
                        }else if(b==1){
                            criteria++;
                            this.fixids(tds[b],'criteria',group,criteria,behavior);                            
                        }else{
                            behavior++;
                            this.fixids(tds[b],'behavior',group,criteria,behavior);                            
                        }
                        
                    }
                    
                }else{
                    // no group name, make changes to criteria and behaviors
                    for(var b=0;b<tds.length;b++){
                        
                        if(b==0){
                            criteria++;
                            this.fixids(tds[b],'criteria',group,criteria,behavior);                            
                        }else{
                            behavior++;
                            this.fixids(tds[b],'behavior',group,criteria,behavior);                            
                        }
                        
                    }
                    
                }
                
                behavior=0;                
            }else{
                criteria=0;                
            }
            
        }
        
    }
    
};



function rubric_cmd(o){
    
    document.getElementById('rubric_error_message').innerHTML='';
    
    var cmd=document.getElementById('selected_cmdrubric').value;
    
    RUBRIC.tbl=document.getElementById('jkn_table_rubric');
    
    try{
        
        switch(cmd){
            case 'add_group':
                RUBRIC.addGroup();
            break;
            case 'del_group':
                RUBRIC.delGroup();
            break;
            case 'add_criteria':
                RUBRIC.addCriteria();
            break;
            case 'del_criteria':
                RUBRIC.delCriteria();
            break;
            case 'behavior_2':
            case 'behavior_3':
            case 'behavior_4':
            case 'behavior_5':
            case 'behavior_6':
                RUBRIC.modBehavior(cmd.substring(9,10));
            break;
            default:break;
        }
        
        RUBRIC.updatePoints();
        RUBRIC.reorganize();// temporary, not sure if this belongs here
        
    }catch(err){
        document.getElementById('rubric_error_message').innerHTML=err;
    }       
    
    
}

function recalculate(){
    
    RUBRIC.tbl=document.getElementById('jkn_table_rubric');
    RUBRIC.updatePoints();
    
}

function updateGrade(obj){
    
    RUBRIC.tbl=document.getElementById('jkn_table_rubric');
    RUBRIC.updateGrade();
    
}

function verifyGrade(){
    
    RUBRIC.tbl=document.getElementById('jkn_table_rubric');
        
    try{        
        
        RUBRIC.verifyGrade();       
        return(true);
        
    }catch(err){        
                
        return(false);
        
    }
    
}

function verifyForm(){
    
    document.getElementById('rubric_error_message').innerHTML='';
    
    RUBRIC.tbl=document.getElementById('jkn_table_rubric');
    RUBRIC.reorganize();
    
    try{        
        
        RUBRIC.verifyForm();        
        return(true);
        
    }catch(err){
        
        document.getElementById('rubric_error_message').innerHTML=err;        
        return(false);
    }
}

function validate(obj){    
    
    var validated=false;
    var validated_error_message='';
    
    var modified_object=false;
    
    switch(obj.id.substr(0,5).toLowerCase()){
        case 'label':
            modified_object=obj.parentNode;
            if(obj.value.length>1&&obj.value.length<50&&obj.value!=''){
                validated=true;
            }
        break;
        case 'group':
        case 'crite':
            modified_object=obj.parentNode.parentNode;
            if(obj.value.length>3&&obj.value.length<50&&obj.value!=''){
                validated=true;
            }
        break;
        case 'behav':
            modified_object=obj.parentNode;
            if(obj.value.length>5&&obj.value.length<4000&&obj.value!=''){
                validated=true;
            }
        break;
        case 'point':
            modified_object=obj.parentNode;
            if(obj.value>=0&&obj.value!=''){
                validated=true;
            }
        break;
        case 'passi':
            modified_object=obj.parentNode;
            if(obj.value>=0&&obj.value<=100&&obj.value!=''&&obj.value.indexOf( '.' ) == -1){
                validated=true;
            }
        break;
    }
    
    if(validated){
        modified_object.classList.remove('has-error');
        modified_object.classList.remove('has-warning');
        modified_object.classList.add('has-success');
        modified_object.children[2].setAttribute('class','glyphicon glyphicon-ok form-control-feedback');
        modified_object.children[3].innerHTML='(ok)';    
    }else{        
        modified_object.classList.remove('has-warning');
        modified_object.classList.remove('has-success');
        modified_object.classList.add('has-error');
        modified_object.children[2].setAttribute('class','glyphicon glyphicon-remove form-control-feedback');
        modified_object.children[3].innerHTML='(error)';
    }
    
    
}

// default template sliders
$( document ).ready(function() {

  $("#Points1_0").slider({tooltip: 'hide'});
  $("#Points1_1").slider({tooltip: 'hide'});
  $("#Points1_2").slider({tooltip: 'hide'});
  
  $("#Points1_0").on("slide", function(slideEvt) {    
	$('#'+this.id+'_value').text(slideEvt.value);
    recalculate();
  });
  
  $("#Points1_1").on("slide", function(slideEvt) {    
	$('#'+this.id+'_value').text(slideEvt.value);
    recalculate();
  });
  
  $("#Points1_2").on("slide", function(slideEvt) {    
	$('#'+this.id+'_value').text(slideEvt.value);
    recalculate();
  }); 

});



