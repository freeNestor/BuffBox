
<script>
    $(function(){
        $("#savejobmsg").hide();
        $("#stepcontent").hide();
        $("#rejoblist").click(function(){
            $.get('/joblist',function(data,status){
                $("#pad").html(data);
            });
        });
        $("#closejobdef").click(function(){
            $.get('/joblist',function(data,status){
                $("#pad").html(data);
            });
        });
                
        $("#canclestep").click(function(){
            $("#addstep").show();
            $("#stepcoll").attr('class','panel-collapse collapse');
            $("#stepcontent").hide();
            //$("#addstep").bind('click');
        });
        
        $("#stepcoll :radio").click(function(){
            $("#stepcontent input:first").next().attr('type','hidden');
            if (this.value == 0) {
                $("#stepcontent").show();
                $("#stepcontent input:first").val('');
                $("#stepcontent input:first").attr('placeholder','input command');
                $("#stepcontent input:first").attr('type','text');
                $("#stepcontent input:first").next().next().next().text('Save');
                //alert($(":input").height());
            }
            if (this.value == 1) {
                $("#stepcontent").show();
                $("#stepcontent input:first").val('');
                $("#stepcontent input:first").attr('placeholder','input remote script path');
                $("#stepcontent input:first").attr('type','text');
                $("#stepcontent input:first").next().next().next().text('Save');
            }
            if (this.value == 2) {
                $("#stepcontent").show();
                $("#stepcontent input:first").val('');
                $("#stepcontent input:first").attr('type','file');
                $("#stepcontent input:first").attr('name','upshell');
                $("#stepcontent input:first").attr('accept','.sh,.py');
                $("#stepcontent input:first").next().next().next().text('Upload');
            }
            if (this.value == 3) {
                $("#stepcontent").show();
                $("#stepcontent input:first").val('');
                $("#stepcontent input:first").attr('type','file');
                $("#stepcontent input:first").attr('name','upshell');
                $("#stepcontent input:first").attr('accept','*');
                $("#stepcontent input:first").next().next().next().text('Upload');
                $("#stepcontent input:first").next().attr('type','text');
            }
            if (this.value == 4) {
                $("#stepcontent").show();
                $("#stepcontent input:first").val('');
                $("#stepcontent input:first").attr('placeholder','input remote file path');
                $("#stepcontent input:first").attr('type','text');
                $("#stepcontent input:first").next().next().next().text('Save');
                $("#stepcontent input:first").next().attr('type','text');
            }
        });
        
        $("#addstep").click(function(){
            //$(this).unbind('click');
            $(this).hide();
            $("#stepcontent").show();
        });
        /**
         * Add step to queue
         */
        $i = 0;
        $("#savedsteps").sortable({axis:'y'});
        $("#savedsteps").disableSelection();
        $("#savestep").click(function(){
            //$(this).unbind('click');
            if ($("#savedsteps").find("p")) {
                $("#savedsteps").children("p").hide();
            }
            $userinput = escape($("#stepcontent input:first").val());
            $targetPath = $("#stepcontent input:first").next().val();
            $ctype = $("#stepcoll :radio:checked").val();
            
            if (($ctype == 3 || $ctype == 4) && $targetPath == '') {
                $("#stepcontent input:first").next().focus();
                $("#stepcontent").attr('class','form-group has-error');
            }else if ($userinput != '') {
                                
                $("#addstep").show();
                $("#stepcontent").hide();
                $("#stepcoll").attr('class','panel-collapse collapse');
                
                $inputtype = 'text';
                $dis = '';
                switch($ctype) {
                    case "0": $ct = "Command";break;
                    case "1": $ct = "RemoteScript";break;
                    case "2": $ct = "LocalScript";$dis = 'disabled';break;
                    case "3": $ct = "LocalFile";$dis = 'disabled';break;
                    case "4": $ct = "RemoteFile";break;
                    default: break;
                }
                //if user upload a shell,upload it
                if($("#savestep").text() == "Upload") {
                    $("#stepform").ajaxSubmit({
                        url:'/uploadsh',
                        type:'post',
                        async:false,
                        success:function(data,status){
                            $userinput = data;
                        },
                        error:function(x,s,t){
                            $userinput = x.responseText;
                        }
                    });
                }
                
                $i = $i + 1;

                //$title = "<div class='input-group col-sm-offset-2 col-sm-6'><span class='input-group-addon '>StepQueue</span><input type='text' class='form-control' value='Step'/></div>";
                $typeline = "<div class='input-group col-sm-8' id='steps"+$i+"'>";
                $input = "<input type='text' class='form-control' name='dstep' style='background-color: white;' value='"+$userinput+"' "+$dis+"/>\
                    <span class='input-group-addon '>"+$ct+"</span><span class='input-group-addon'>"+$targetPath+"</span>\
                    <span class='input-group-addon'>\
                    <a class='glyphicon glyphicon-trash' href='#' id='"+$i+"' name='dstep'></a>\
                    </span></div>";
                //alert($input);
                $allstring = $typeline + $input;
                $("#savedsteps").append($allstring);
                $.each($("input[name='dstep']"),function(){
                    $(this).val(unescape(this.value));
                });
            } else {
                $("#stepcontent input:first").focus();
                $("#stepcontent").attr('class','form-group has-error');
                //alert();
            }
        });
        //remove step on page and remove upload file backend
        $("#savedsteps").on("click","a[name='dstep']",function(){
            $sel = "#steps"+this.id;
            //alert($($sel).children("input").val());
            $.ajax({
                url:'/removeupsh',
                type:'get',
                data:{'filepath':$($sel).children("input").val()},
                success:function(data,status) {
                    //alert(data);
                }
            });
            $($sel).remove();
        });
        
        $("#getallnode").click(function(){
            $("#nodefilter").val('hostname:*');
            $.ajax({
                url:'/getNodeName',
                type:'get',
                data:{'column':'hostname','filter':'hostname:*'},
                success:function(data,status){
                    $("#loadnodes").html(data);
                }
            });
        });
        //Use filter string to get node
        $("#searchnode").click(function(){
            $filter = $("#nodefilter").val();
            $.ajax({
                url:'/getNodeName',
                type:'get',
                data:{'column':'hostname','filter':$filter},
                success:function(data,status){
                    $("#loadnodes").html(data);
                }
            });
        });
        
        $("#nodefilter").focusout(function(){
            $filter = $(this).val();
            $.ajax({
                url:'/getNodeName',
                type:'get',
                data:{'column':'hostname','filter':$filter},
                success:function(data,status){
                    $("#loadnodes").html(data);
                }
            });
        });
        /**
         * when page loaded,load nodes list immediately
         */
        $.ajax({
            url:'/getNodeName',
            type:'get',
            data:{'filter':$("#nodefilter").val(),
                'column':'hostname',
                'selected':$("#job_selected").val(),
                'filterall':$("#filterall").val()
            },
            success:function(data,status){
                $("#loadnodes").html(data);
            }
        });
        /**
         * checked radio step relation
         */
        var $steprel = $("#steprla").val();
        $.each($("input[name='relastep']"),function(){
            if ($steprel == this.value) {
                $(this).prop('checked',true);
            }
        });
        
        var $stepString = $("#jobsteps").val();
        var $stepObj = JSON.parse($stepString);
        var $i = 0;
        $.each($stepObj,function(key,value){
            $i++;
            $ctext = value['text'];
            $ct = value['type'];
            if (value['targetPath'] == undefined) {
                $targetPath = '';
            } else {
                $targetPath = value['targetPath'];
            }
            $dis = '';
            if ($ct == 'LocalScript' || $ct == 'LocalFile') {
                $dis = 'disabled';
            }
            $typeline = "<div class='input-group col-sm-8' id='steps"+$i+"'>";
            $input = "<input type='text' class='form-control' name='dstep' style='background-color: white;' value='"+$ctext+"' "+$dis+"/>\
                    <span class='input-group-addon '>"+$ct+"</span><span class='input-group-addon'>"+$targetPath+"</span>\
                    <span class='input-group-addon'>\
                    <a class='glyphicon glyphicon-trash' href='#' id='"+$i+"' name='dstep'></a>\
                    </span></div>";
                //alert($input);
            $allstring = $typeline + $input;
            $("#savedsteps").append($allstring);
            $.each($("input[name='dstep']"),function(){
                $(this).val(unescape(this.value));
            });
        });
        
        $("#loadnodes").on('click',"input[name='allnodesel']",function(){
            $checked = $("input[name='allnodesel']").prop('checked');
            $.each($("input[name='filterednodes']"),function(){
                $(this).prop('checked',$checked);
            });
        });
        
        $("#loadnodes").on('click',"input[name='filterednodes']",function(){
            $("input[name='allnodesel']").prop('checked',false);
        });
        /**
         * User click save job button
         */
        $("#savejobdef").click(function(){
            if ($("#savedsteps").children().last().is("p")) {
                $("#savedsteps").children("p").attr('class','help-block alert alert-danger');
                $("#savedsteps").children("p").show();
                $("body").animate({'scrollTop':0});
            } else {
                if ($("#jobname").val() == '') {
                    $("#jobname").focus();
                    $("#jobnameinput").attr('class','form-group has-error');
                    $("body").animate({'scrollTop':0});
                } else {
                    $jobid = $("#jobid").val();
                    $token = $("#token").val();
                    $jobname = $("#jobname").val();
                    $jobdes = $("#jobdes").text();
                    $steprel = $("input[name='relastep']:checked").val();
                    $parallel = $("#stepparallel").val();
                    $steptimeout = $("#steptimeout").val();
                    $jobtimeout = $("#jobtimeout").val();
                    $nodefilter = $("#nodefilter").val();
                    $i = 0;
                    $stepStringTmp = '';
                    $.each($("input[name='dstep']"),function(){
                        $stepT = $(this).next().text();
                        $targetPathT = $(this).next().next().text();
                        $stepStringTmp = $stepStringTmp + '"step'+$i+'":{"text":"'+escape(this.value)+'","targetPath":"'+$targetPathT+'","type":"'+$stepT+'"},';
                        $i = $i + 1;
                    });
                    $stepString = $stepStringTmp;
                    //Check node user selected
                    $nodeSelectedTmp = '';
                    $allChecked = $("input[name='allnodesel']").prop('checked');
                    $.each($("input[name='filterednodes']:checked"),function(){
                           $nodeSelectedTmp = $nodeSelectedTmp + this.value+",";
                    });
                    //User select all nodes, we will store filter
                    if ($allChecked == true) {
                        $nodeSelectedTmp = '';
                        $allChecked = 1;
                    } else {
                        $allChecked = 0;
                    }
                    //Post data to server
                    $.ajax({
                        url:'/saveMyJob',
                        type:'post',
                        data:{
                            'jobid':$jobid,
                            '_token':$token,
                            'jobname':$jobname,
                            'jobdes':$jobdes,
                            'steprela':$steprel,
                            'parallel':$parallel,
                            'steptimeout':$steptimeout,
                            'jobtimeout':$jobtimeout,
                            'filter':$nodefilter,
                            'stepstring':$stepString,
                            'nodeselect':$nodeSelectedTmp,
                            'allselect':$allChecked,
                            'update':true
                        },
                        success:function(data,status){
                            $("#savejobmsg").html(data);
                            $("#savejobmsg").show();
                            //$("#savejobdef").attr('disabled','disabled');
                            //$("#savejobdef").unbind('click');
                            $("body").animate({'scrollTop':0});
                        }
                    });
                }
            }
        });
    });
</script>
<input type="hidden" value="{{ $select or '' }}" id="job_selected"/>
<input type="hidden" value="{{ $filterall or '' }}" id="filterall"/>
<input type="hidden" value="{{ $steprla or '' }}" id="steprla"/>
<input type="hidden" value="{{ $steps or '' }}" id="jobsteps"/>
<input type="hidden" value="{{ $jobid or '' }}" id="jobid"/>
<div class="panel panel-info">
        <div id="savejobmsg"></div>
   <div class="panel-heading">
      <h3 class="panel-title">Edit Job / <a class="" href="#" id="rejoblist">Back</a></h3>
   </div>
   <div class="panel-body" style="padding-left: 0;padding-right: 100px;">
        <form class="form-horizontal" role="form" style="margin-left: 0;">
               <div class="form-group" id="jobnameinput">
                  <label for="" class="col-sm-2 control-label">Name</label>
                  <div class="col-sm-10">
                     <input type="text" class="form-control" id="jobname" 
                        placeholder="" value="{{ $jobname or '' }}"/>
                  </div>
               </div>
               <div class="form-group">
                  <label for="" class="col-sm-2 control-label">Description</label>
                  <div class="col-sm-10">
                     <textarea class="form-control" id="jobdes" 
                        placeholder="">{{ $jobdes or '' }}</textarea>
                  </div>
               </div>
        </form>
        <form class="form-horizontal" role="form" style="margin-bottom: 0;">
               <div class="form-group"  id="steptitle" style="margin-bottom: 0;">
                    <label for="" class="col-sm-2 control-label">steps</label>
                    <div class="col-sm-10"  id="savedsteps">
                        <p class="help-block alert alert-info">Attention:Step cannot be empty</p>
                    </div>
                    
               </div>
        </form>
        <form class="form-horizontal" role="form" style="margin-bottom: 0;">
               <div class="form-group" style="margin-bottom: 0;">
                  <label for="" class="col-sm-2 control-label" ></label>
                  <div class="col-sm-2" style="margin-bottom: 0;">
                     <a class="btn btn-info" id="addstep" 
                        data-toggle="collapse" data-parent="#" 
                        href="#stepcoll">Add Step</a>
                  </div>
               </div>
               <div id="stepcoll" class="panel-collapse collapse" style="margin-bottom: 0;">
                <div class="form-group" style="margin-bottom: 0;">
                  <div class="col-sm-offset-2 col-sm-8" style="margin-bottom: 0;">
                    
                      <div class="panel-body table-bordered" style="margin-bottom: 10px;">
                        <label class="checkbox"><input type="radio" id="" name="optstep" value="0" checked="true"/>Command</label>
                        <label class="checkbox"><input type="radio" id="" name="optstep" value="1"/>Remote Script</label>
                        <label class="checkbox"><input type="radio" id="" name="optstep" value="2"/>Local Script</label>
                        <label class="checkbox"><input type="radio" id="" name="optstep" value="3"/>Local File</label>
                        <label class="checkbox"><input type="radio" id="" name="optstep" value="4"/>Remote File</label>
                      </div>
                      
                  </div>
                </div>
               </div>
            </form>
            <div class="col-sm-offset-2 col-sm-10">
                   <form class="form-inline" role="form" id="stepform" enctype="multipart/form-data">
                    <div class="form-group" id="stepcontent">
                       <input type="text" class="form-control"   style="width: 500px;" value="" placeholder="Input Command here" />
                       <input type="hidden" class="form-control" style="width: 500px;" value="" placeholder="Input Target Path here" />
                       <a class="btn btn-warning" href="#" style="margin:10px;" id="canclestep">Cancle</a>
                       <a class="btn btn-warning" href="#" id="savestep">Save Step</a>
                                   
                    </div>
                    <input type="hidden" name="_token" value="{{ csrf_token() }}"/>
                    </form>
            </div>
            <form class="form-horizontal" role="form" style="margin-left: 0;">
               <div class="form-group">
                  <label for="" class="col-sm-2 control-label">Step Relation</label>
                  <div class="col-sm-10">
                     <label class="checkbox">
                     <input type="radio" id="relateradio0" name="relastep" value="1" checked="false"/>Relate [stop at failed step]</label>
                     <label class="checkbox">
                     <input type="radio" id="relateradio1" name="relastep" value="0" checked="false"/>Non-Relate [continue next step if step failed]</label>
                  </div>
               </div>
               <div class="form-group">
                    <label for="" class="col-sm-2 control-label">Threads</label>
                  <div class="col-sm-5">
                    <input type="text" class="form-control" id="stepparallel" name="" value="{{ $parallel or '' }}" placeholder="5"/>
                    <p class="help-block">Default threads num. is 5. Maximum 50.</p>
                  </div>
               </div>
               <div class="form-group">
                    <label for="" class="col-sm-2 control-label">Step Timeout</label>
                  <div class="col-sm-5">
                    <input type="text" class="form-control" id="steptimeout" name="" value="{{ $steptimeout or '' }}" placeholder="0"/>
                    <p class="help-block">Timeout in seconds.Just impact single step.default 0,not timeout</p>
                  </div>
               </div>
               <div class="form-group">
                    <label for="" class="col-sm-2 control-label">Job Timeout</label>
                  <div class="col-sm-5">
                    <input type="text" class="form-control" id="jobtimeout" name="" value="{{ $jobtimeout or '' }}" placeholder="0"/>
                    <p class="help-block">Timeout in seconds.Just impact job,not steps in job.default 0,not timeout</p>
                  </div>
               </div>
            </form>
            <form class="form-horizontal" role="form" style="margin-left: 0;">
               <div class="form-group">
                    <label for="" class="col-sm-2 control-label">Select Nodes</label>
                  <div class="col-sm-5">
                    <div class="input-group">
                        <a class="input-group-addon btn btn-info" id="getallnode">All Nodes</a>
                        <input type="text" class="form-control" id="nodefilter" value="{{ $filter or '' }}" placeholder=""/>
                        <a class="input-group-addon btn btn-info" id="searchnode">Filter</a>
                        
                    </div>
                    <p class="help-block">filter syntax,example 'hostname:*ypd* ipaddr:11.8.*'
                         space between multipul key:regex. No space between key and regex
                    </p>
                 </div>
               </div>
               <div class="form-group">
                    <label for="" class="col-sm-2 control-label"></label>
                    <div class="col-sm-10">
                        <div id="loadnodes"></div>
                    </div>
               </div>
            </form>
            <div class="col-sm-offset-10 col-sm-2">
                <input type="hidden" id="token" name="_token" value="{{ csrf_token() }}"/>
                <a class="btn btn-primary" id="savejobdef">Save</a>
                <a class="btn btn-danger" id="closejobdef">Close</a>
            </div>
   </div>
</div>