var RUBRIC = {
    
    tbl : '',
    
    defaultPoints:function(){
        
        var point_ranges=new Array();
        point_ranges[0]=new Array(60,69,'Excellent');
        point_ranges[1]=new Array(50,59,'Good');
        point_ranges[2]=new Array(40,49,'Acceptable');
        point_ranges[3]=new Array(30,39,'Fair');
        point_ranges[4]=new Array(20,29,'Poor');
        point_ranges[5]=new Array(10,19,'Bad');
        
        return(point_ranges);        
    },    
    
    updateGrade:function(){        
        var points=new Array();
        
        var group_total=group_max=0;
        var overall_total=overall_max=0;
        var points_max=0;
        
        var tbody=this.tbl.getElementsByTagName('tbody');
        var tfoot=this.tbl.getElementsByTagName('tfoot');
        for(var i=0;i<tbody.length;i++)
        {
            var trs=tbody[i].getElementsByTagName('tr');
            for(var a=0;a<trs.length;a++){

                if(this.nodeHasGroupRange(trs[a])){
                    // grab the range
                    points=this.gatherPointValues(trs[a]);
                    for(var b=0;b<points.length;b++){
                        if (parseFloat(points[b]['max']) > points_max) {
                            points_max = points[b]['max'];
                        }
                        if (parseFloat(points[b]['min']) > points_max) {

                            points_max = points[b]['min'];
                        }
                    }
                }else if(this.nodeHasPointRange(trs[a])){
                    // update the group total
                    trs[a].children[1].innerHTML=group_total+' out of '+group_max;
                    //reset group values
                    group_max=group_total=points_max=0;
                }else if(this.nodeHasGrade(trs[a])){
                    // get the group grades
                    group_max+=parseFloat(points_max);
                    overall_max+=parseFloat(points_max);

                    var inputs=trs[a].getElementsByTagName('input');
                    if(isNaN(parseFloat(inputs[0].value))===false){
                        group_total+=parseFloat(inputs[0].value);
                        overall_total+=parseFloat(inputs[0].value);
                    }

                }
            }
        }
        // update footer points
        tfoot[0].children[0].children[1].innerHTML=overall_total+' out of '+overall_max;
        
        // update hidden field
        var tmp_mark=(overall_total/overall_max)*100;
        document.getElementById('mark').value=tmp_mark.toFixed(2);
    },
    
    gatherPointValues:function(tr){
        var points=new Array();
        
        var ths=tr.getElementsByTagName('th');
        for(var a=2;a<(ths.length-1);a++){
            var broken_range=ths[a].innerHTML.split('-');
            
            var key=a-2;
            points[key]=new Array();
            points[key]['min']=broken_range[0];
            points[key]['max']=broken_range[1];
        }

        return(points);
    },

    getSingleRange:function(range){
        // what are the min/max ranges in this input
        var broken_range=range.split('-');
        broken_range[0]=parseFloat(broken_range[0]);
        broken_range[1]=parseFloat(broken_range[1]);

        var min=10000000;
        var max=0;
        if(broken_range[0]<min)
            min=broken_range[0];

        if(broken_range[0]>max)
            max=broken_range[0];

        if(broken_range[1]<min)
            min=broken_range[1];

        if(broken_range[1]>max)
            max=broken_range[1];

        var range=new Array();
        range['low']=min;
        range['high']=max;

        return(range);
    },

    checkOverlappingRange:function(input){

        var check=true;

        var current_range=this.getSingleRange(input.value);

        var alert_flag = false;

        // grab the parent tr
        var tr=input.parentNode.parentNode.parentNode;

        if(tr.nodeName=='TR'){
            var inputs=tr.getElementsByTagName('input');
            for(var a=0;a<inputs.length;a++){
                //reset the css of all inputs
                var div=inputs[a].parentNode;
                div.classList.remove('has-warning');
                div.classList.remove('has-error');
                div.classList.add('has-success');
                var span=inputs[a].parentNode.children[2];
                span.setAttribute('class','glyphicon glyphicon-ok form-control-feedback');
                if(alert_flag === false)
                {
                    $('.range').empty();
                }
                var current_range=this.getSingleRange(inputs[a].value);
                for(var b=0;b<inputs.length;b++){
                    if(inputs[b]!==inputs[a]){
                        var tmp_range=this.getSingleRange(inputs[b].value);
                        if(current_range['low']>=tmp_range['low']&&current_range['low']<=tmp_range['high']){
                            var div=inputs[b].parentNode;
                            div.classList.remove('has-warning');
                            div.classList.remove('has-success');
                            div.classList.add('has-error');
                            var span=inputs[b].parentNode.children[2];
                            span.setAttribute('class','glyphicon glyphicon-remove form-control-feedback');
                            check=false;
                            alert_flag=true;
                            $('.range').empty();
                            //give user notice.
                            var span2 = document.createElement('span');
                            span2.setAttribute('style','text-align:center;color:red;display:block;font-size:75%;');
                            span2.setAttribute('class','range');
                            span2.innerHTML = '(out of range)';
                            div.appendChild(span2);
                        }
                        if(current_range['high']>=tmp_range['low']&&current_range['high']<=tmp_range['high']){
                            var div=inputs[b].parentNode;
                            div.classList.remove('has-warning');
                            div.classList.remove('has-success');
                            div.classList.add('has-error');
                            var span=inputs[b].parentNode.children[2];
                            span.setAttribute('class','glyphicon glyphicon-remove form-control-feedback');
                            check=false;
                            alert_flag=true;
                            $('.range').empty();
                            //give user notice.
                            var span2 = document.createElement('span');
                            span2.setAttribute('style','text-align:center;color:red;display:block;font-size:75%;');
                            span2.setAttribute('class','range');
                            span2.innerHTML = '(out of range)';
                            div.appendChild(span2);
                        }
                    }
                }


            }
            
        }

        return(check);
    },
    
    updatePoints:function(){
        
        var total_behaviors=this.howManyBehaviors();

        $(".point-input").tooltip({'trigger':'focus', 'title': 'Please use the following format "##-##"'});
        
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
                    tmp_range=document.getElementById('Points'+groups+'_'+b).value;
                    document.getElementById('Points'+groups+'_'+b).value=tmp_range;//update the input field
                    var broken_range=tmp_range.split('-');
                    if(max==0&&min==0){
                        if(parseFloat(broken_range[0])<parseFloat(broken_range[1]))
                        {
                            max=broken_range[1];
                            min=broken_range[0];
                        }else{
                            max=broken_range[0];
                            min=broken_range[1];
                        }
                    }else{
                        if(parseFloat(broken_range[0])<parseFloat(broken_range[1]))
                        {
                            if(parseFloat(broken_range[1])>max){
                                max=broken_range[1];
                            }
                            if(parseFloat(broken_range[0])<min){
                                min=broken_range[0];
                            }
                        }else{
                            if(parseFloat(broken_range[0])>max){
                                max=broken_range[0];
                            }
                            if(parseFloat(broken_range[1])<min){
                                min=broken_range[1];
                            }
                        }
                    }
                    
                }
                groups++;// update group increment                
                
            }else if(!this.nodeHasPointRange(trs[a])){
                // group and/or criteria
                group_max+=parseFloat(max);
                group_min+=parseFloat(min);
                
                overall_max+=parseFloat(max);
                overall_min+=parseFloat(min);
                
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
        var label_value=points_value='';
        
        var txt_label='Label';
        var txt_points='Points';
        
        var point_range=this.defaultPoints();
        
        var label_value=point_range[position][2];
        
        th=document.createElement('th');
        th.setAttribute('scope','col');
        
        // thead changes
        th.appendChild(this.createCardFormHeadInputsDiv(txt_label,label_value,position));
        thead.children[0].appendChild(th);
        
        var slider_increment=0;
        
        // tbody changes
        var trs=tbody.getElementsByTagName('tr');
        for(var a=0;a<trs.length;a++){
            if(this.nodeHasSlider(trs[a])){
                slider_increment++;
                trs[a].appendChild(this.addPointSlider(slider_increment,position))
            }else if(!this.nodeHasPointRange(trs[a])){
                trs[a].appendChild(this.createCardFormBodyInputs('Behavior Description',a-1,position,false));
            }else{
                // this is a point range
                trs[a].children[1].colSpan=position+1;
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
    
    nodeHasGrade:function(tr){
        var inputs=tr.getElementsByTagName('input');
        var textareas = tr.getElementsByTagName('textarea');
        if(inputs.length==1 && textareas.length ==1){
            return(true);
        }else{
            return(false);
        }
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
    
    nodeHasGroupRange:function(tr){
        if(tr.children[0].nodeName=='TH'&&tr.children[1].nodeName=='TH'&&tr.children[1].innerHTML=='Range'){
            return(true);            
        }else{
            return(false);
        }
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
            th.setAttribute('class','');
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
        var point_ranges=this.defaultPoints();
        
        var th=document.createElement('th');
        th.setAttribute('scope','col');
        var div=document.createElement('div');
        div.setAttribute('class','form-group has-warning has-feedback point-input');
        var label=document.createElement('label');
        label.setAttribute('class','control-label');
        label.setAttribute('for','Points'+group_number+'_'+behavior_number);
        label.appendChild(document.createTextNode('Points'));
        div.appendChild(label);
        var input=document.createElement('input');
        input.setAttribute('id','Points'+group_number+'_'+behavior_number);
        input.setAttribute('class','Points'+group_number+'_'+behavior_number);
        input.setAttribute('type','text');
        input.setAttribute('class','form-control');
        input.setAttribute('onkeyup','validate(this)');
        input.setAttribute('onblur','recalculate(this)');
        input.setAttribute('oninput','validate(this)');
        input.setAttribute('value',''+point_ranges[behavior_number][0]+'-'+point_ranges[behavior_number][1]+'');
        div.appendChild(input);

        var span=document.createElement('span');
        span.setAttribute('class','glyphicon glyphicon-warning-sign form-control-feedback');
        span.setAttribute('aria-hidden','true');
        div.appendChild(span);

        span=document.createElement('span');
        span.setAttribute('id','Points'+group_number+'_'+behavior_number+'WarningStatus');
        span.setAttribute('class','sr-only');
        span.appendChild(document.createTextNode('(warning)'));
        div.appendChild(span);

        th.appendChild(div);
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

        var tr=document.createElement('tr');
        tr.setAttribute('class','tblrow1 small');

        tr.appendChild(this.createCardFormBodyInputs('Group Name',trs.length-1,0,true));
        tr.appendChild(this.createCardFormBodyInputs('Criteria Label',trs.length-1,0,true));

        var behaviors_required=this.howManyBehaviors();

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
        
        // add on the new tr right before the point range
        while(!this.nodeHasPointRange(parent_tr)){            
            parent_tr=parent_tr.nextElementSibling;
        }
        parent_tr.parentNode.insertBefore(tr,parent_tr);
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
        input.setAttribute('onblur','recalculate(this)');
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

        if(fixed_label.indexOf('behaviordescription')> -1)
        {
            input=document.createElement('textarea');
        }
        else{
            input=document.createElement('input');
        }
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
        var requires_verification=true;
        var errors = document.querySelectorAll('.glyphicon-remove,.glyphicon-warning-sign');
        var complete = document.getElementById('complete');
        complete.value = errors.length > 0 ? false : true;

        for(var a=0;a<inputs.length;a++){
            
            requires_verification=true;
            
            if(inputs[a].type=='text'){
                switch(inputs[a].id.substr(0,5).toLowerCase()){
                    case 'label':
                    case 'behav':                    
                        verified_object=inputs[a].parentNode.children[3];
                    break;
                    case 'group':
                    case 'crite':
                        verified_object=inputs[a].parentNode.parentNode.children[3];
                    break;
                    case 'point':
                        requires_verification=false;
                        // assign value of Points to input field
                        inputs[a].value=document.getElementById(inputs[a].id).value;
                    break;
                    default:
                        verified_object=false;
                    break;
                }
                
                if(requires_verification===true){
                    if(verified_object===false||verified_object.childNodes[0].nodeValue=='(error)'){
                        throw 'Missing Data for Rubric';                
                    }                    
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
        
        var verified=true;
        //confirm all the grades are within the proper boundaries
        var inputs=this.tbl.getElementsByTagName('input');
        for(var a=0;a<inputs.length;a++){
            
            if(inputs[a].type=='text'&&inputs[a].id.substr(0,5)=='Grade'){
                
                var tmp=inputs[a].parentNode.parentNode;
                //loop through previous element siblings until we get some ths
                var found_range_row=false;
                var i=0;
                while(found_range_row==false){
                    var range_row=tmp.previousElementSibling;
                    var ths=range_row.getElementsByTagName('th');
                    if(ths.length>0){
                        var min=max=0;
                        for(var b=2;b<ths.length-1;b++){
                            var broken_range=ths[b].innerHTML.split('-');
                            if(b==2){
                                if(parseFloat(broken_range[0])<parseFloat(broken_range[1]))
                                {
                                    max=broken_range[1];
                                    min=broken_range[0];
                                }else{
                                    max=broken_range[0];
                                    min=broken_range[1];
                                }
                            }else{
                                if(parseFloat(broken_range[0])<parseFloat(broken_range[1]))
                                {
                                    if(parseFloat(broken_range[1])>max){
                                        max=broken_range[1];
                                    }
                                    if(parseFloat(broken_range[0])<min){
                                        min=broken_range[0];
                                    }
                                }else{
                                    if(parseFloat(broken_range[0])>max){
                                        max=broken_range[0];
                                    }
                                    if(parseFloat(broken_range[1])<min){
                                        min=broken_range[1];
                                    }
                                }
                            }
                        }

                        found_range_row=true;
                    }
                    tmp=range_row;
                                
                }// while looking for range
               
                //is the value within the range and a number ?                
                var test_value=parseFloat(inputs[a].value);
                if(isNaN(inputs[a].value)||test_value>max||test_value<min){
                    // value is out of range or not a number                  
                    inputs[a].setAttribute('style','border:3px solid red');
                    verified=false;
                }else{
                    //value is all good
                    inputs[a].setAttribute('style','border:3px solid green');
                }
            }            
        }
        if(verified===false){
            throw "Grades are not within valid ranges";            
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

            td.children[0].children[0].setAttribute('for',tmpname);// label

            td.children[0].children[1].setAttribute('name',tmpname); // input text
            td.children[0].children[1].setAttribute('id',tmpname); // input text

        }
        
    },
    
    reorganize:function(){
        //fix for working in Ilias
        var tbody=this.tbl.getElementsByTagName('tbody');
        var trs=tbody[0].getElementsByTagName('tr');

        var group=0;// group increment
        var criteria=0;// criteria increment
        var behavior=0;// behavior increment

        for(var a=0;a<trs.length;a++){

            if(this.nodeHasSlider(trs[a])){
                // if slider row
                var ths=trs[a].getElementsByTagName('th');

                for(var b=2;b<ths.length;b++){
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
    document.getElementById('rubric_error_message').setAttribute('style','');
    
    var cmd=document.getElementById('selected_cmdrubric').value;
    
    RUBRIC.tbl=document.getElementById('jkn_table_rubric');
        
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
            case 'behavior_1':
            case 'behavior_2':
            case 'behavior_3':
            case 'behavior_4':
            case 'behavior_5':
            case 'behavior_6':
                RUBRIC.modBehavior(cmd.substring(9,10));
            break;
            default:break;
        }

        RUBRIC.reorganize();
        RUBRIC.updatePoints();

    
    
}

function recalculate(obj){
    
    RUBRIC.tbl=document.getElementById('jkn_table_rubric');
    RUBRIC.updatePoints();
    RUBRIC.checkOverlappingRange(obj);
    
}

function updateGrade(obj){
    
    RUBRIC.tbl=document.getElementById('jkn_table_rubric');
    RUBRIC.updateGrade();
    
}

function verifyGrade(){
    RUBRIC.tbl=document.getElementById('jkn_table_rubric');
        
    RUBRIC.updateGrade();

    try{
        
        RUBRIC.verifyGrade();
             
        return(true);

    }catch(err){

        return(false);

    }
    
}

function verifyForm(){
    
    document.getElementById('rubric_error_message').innerHTML='';
    document.getElementById('rubric_error_message').setAttribute('style','');
    
    RUBRIC.tbl=document.getElementById('jkn_table_rubric');
    RUBRIC.reorganize();

    try{

        RUBRIC.verifyForm();        
        return(true);
        
    }catch(err){

        document.getElementById('rubric_error_message').innerHTML=err;
        document.getElementById('rubric_error_message').setAttribute('style','background-color:rgb(241,221,221);border-radius: 1px;padding:5px;');
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
            if(obj.value.length>1&&obj.value.length<50){
                validated=true;
            }else if(obj.value==''){
                validated='warning';
            }
        break;
        case 'group':
        case 'crite':
            modified_object=obj.parentNode.parentNode;
            if(obj.value.length>3&&obj.value.length<256){
                validated=true;
            }else if(obj.value==''){
                validated='warning';
            }
        break;
        case 'behav':
            modified_object=obj.parentNode;
            if(obj.value.length>5&&obj.value.length<4000){
                validated=true;
            }else if(obj.value==''){
                validated='warning';
            }
        break;
        case 'point':
            modified_object=obj.parentNode;
            if(obj.value.match(/^\d{1,8}(?:\.\d{0,2})?\-\d{1,8}(?:\.\d{0,2})?$/)){
                validated=true;
            }
            else if(obj.value=='') {
                validated = 'warning';
            }

        break;
        case 'passi':
            modified_object=obj.parentNode;
            if(obj.value>=0&&obj.value<=100&&obj.value.indexOf( '.' ) == -1){
                validated=true;
            }else if(obj.value==''){
                validated='warning';
            }
        break;        
    }
    
    if(validated===true){
        modified_object.classList.remove('has-error');
        modified_object.classList.remove('has-warning');
        modified_object.classList.add('has-success');
        modified_object.children[2].setAttribute('class','glyphicon glyphicon-ok form-control-feedback');
        modified_object.children[3].innerHTML='(ok)';
    }else if(validated===false){
        modified_object.classList.remove('has-warning');
        modified_object.classList.remove('has-success');
        modified_object.classList.add('has-error');
        modified_object.children[2].setAttribute('class','glyphicon glyphicon-remove form-control-feedback');
        modified_object.children[3].innerHTML='(error)';
    }else{
        modified_object.classList.remove('has-error');
        modified_object.classList.remove('has-success');
        modified_object.classList.add('has-warning');
        modified_object.children[2].setAttribute('class','glyphicon glyphicon-warning-sign form-control-feedback');
        modified_object.children[3].innerHTML='(warning)';
    }

}

document.addEventListener("DOMContentLoaded", function(event) {
    var rubric = document.getElementById("jkn_div_rubric").parentNode;
    var rubric_inputs =  rubric.querySelectorAll('input.form-control,textarea.form-control');
    for(var i=0;i<rubric_inputs.length;i++)
    {
        if(rubric_inputs[i].getAttribute("placeholder") !== 'Grade'
            && rubric_inputs[i].getAttribute("placeholder") !== 'Comment'
            && rubric_inputs[i].id.substr(0,5)!=='Point')
        {
            validate(rubric_inputs[i]);
        }
    }
    $(".point-input").tooltip({'trigger':'focus', 'title': 'Please use the following format "##-##"'});
});
