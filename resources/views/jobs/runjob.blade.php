<script>
    $(function(){
        $("#resetrunjob").click(function(){
            $jobid = $(this).next("input").val();
            $.ajax({
                url:'/prerunjob',
                type:'get',
                data:{'runview':1,'jobid':$jobid},
                success:function(data,status){
                    $("#pad").html(data);
                }
            });
        });
        /**
         * when page loaded,load nodes list immediately
         */
        $.ajax({
            url:'/getNodeName',
            type:'get',
            data:{'filter':$("#runjob_filter").val(),
                'column':'hostname',
                'selected':$("#runjob_selected").val(),
                'filterall':$("#filterall").val()
            },
            success:function(data,status){
                $("#runjobnoderes").html(data);
            }
        });
        /**
         * If click view last task button,hide run job button
         */
        $("#lastjobtask").click(function(){
            $("#hiderun").hide();
            $jobid = $("#jobid").val();
            $taskid = $("#taskid").val();
            $finishedat = $("#taskfinish").val();
            $.ajax({
                url:'/viewtask',
                type:'get',
                data:{'jobid':$jobid,'taskid':$taskid,'finishedat':$finishedat},
                success:function(data,status){
                    $("#row1").html(data);
                    $("#row2").hide();
                }
            });
        });
        $("#runjobnoderes").on('click',"input[name='allnodesel']",function(){
            $checked = $("input[name='allnodesel']").prop('checked');
            $.each($("input[name='filterednodes']"),function(){
                $(this).prop('checked',$checked);
            });
        });        
        $("#runjob_back").click(function(){
            $("#joblist").click();
            //history.back();
        });
        
        /*
        * Send data to server
        * but this function not execute job immediately
        */
        $("#nowrunjob").click(function(){
            $nodenull = $("#runjobnoderes").text();
            $nodestring = "";
            $.each($("input[name='filterednodes']:checked"),function(){
                    $seltd = "#sel"+this.value;
                    $hostname = $(this).parent().next().children("span[name='hostname']").text().replace(/[\s\r\n]/g,"");
                    $userexec = $(this).parent().next().next().children("span[name='userexec']").text().replace(/[\s\r\n]/g,"");
                    $nodestring = $nodestring + $hostname+":"+$($seltd).text().replace(/[\s\r\n]/g,"")+":"+$userexec+",";
            });
            if ($nodenull == '' || $nodestring == '') {
                $("#rowmsg").html("<div class='col-sm-12'><div class='alert alert-warning'>No Nodes to be executed.</div></div>");
            } else {
                $token = $("#token").val();
                $jobid = $("#jobid").val();
                $parallel = $("#parallel").text().replace(/[\s\r\n]/g,"");
                $timeout = $("#timeout").text().replace(/[\s\r\n]/g,"");
                $rela = $("#rela").text().replace(/[\s\r\n]/g,"");
                if ($rela == "Relation[StopatFailedStep]") {
                    $rela = 1;
                } else {
                    $rela = 0;
                }
                
                $debug = 0;
                $debug = $("input[name='debug']").prop('checked');
                if ($debug == true) {
                    $debug = 1;
                }
                $logpth = $("#logpath").text().replace(/[\s\r\n]/g,"");
                $allflag = 0;
                $allselect = $("input[name='allnodesel']").prop('checked');
                if ($allselect == true){
                    $allflag = 1;
                }
                
                $.ajax({
                    url:'/jobexec',
                    type:'post',
                    data:{
                        'jobid':$jobid,
                        'parallel':$parallel,
                        'timeout':$timeout,
                        'rela':$rela,
                        'nodestring':escape($nodestring),
                        'logpath':$logpth,
                        'debug':$debug,
                        '_token':$token,
                        'allflag':$allflag
                    },
                    beforeSend:function(xhr) {
                        $("#hiderun").children("a").text("Run Again");
                        $("#row1").hide();
                        $("#row2").hide();
                        $("#hiderefresh").hide();
                    },
                    success:function(data,status){
                        $("#row1").show();
                        $("#row1").html(data);
                    }
                });
            }
        });
    });
</script>
<input type="hidden" id="token" value="{{ csrf_token() }}"/>
<input type="hidden" id="taskid" value="{{ $taskid or '' }}"/>
<input type="hidden" id="taskfinish" value="{{ $finishedat or '' }}"/>
<input type="hidden" id="filterall" value="{{ $job->filterall }}"/>
<div class="container-fluid">
	<div class="row table-bordered">
		<div class="col-md-12">
			
            <div class="row">
            <div class="col-md-12">
				<h2>
					{{ $job->name }} <small>{{ $job->description }} 
                    </small>
				</h2>
            </div>
            <div class="col-md-offset-0 col-md-12">
                <ol class="breadcrumb">
                    @if (empty($taskid))
                    <li><span class="glyphicon glyphicon-circle-arrow-left"></span>
                    <a class="" href="#" id="runjob_back">Back</a></li>
                    <li id="hiderefresh"><span class="glyphicon glyphicon-refresh"></span>
                    <a class="" href="#" id="resetrunjob">Refresh</a>
                    <input type="hidden" id="jobid" value="{{ $job->id }}"/></li>
                    
                    <li id="hiderun"><span class="glyphicon glyphicon-play"></span>
                    <a class="" href="#" id="nowrunjob">Run Now</a></li>
                    @endif
                    <li id=""><span class="glyphicon glyphicon-th-list"></span>
                    <a class="" href="#" id="lastjobtask">Last Task</a></li>
                </ol>
			</div>
            </div>
            <div class="row" id="rowmsg"></div>
            <div class="row" id="row1">
			  <div class="col-sm-6" id="row1msg">
                <h3>
    				Steps
    			</h3>
                @for ($i=0;$i < count($jobstep);$i++)
    			<p>
                    Queue: 
                    
                    {{$i+1}}. {{ $jobstep['step'.$i]['text'] }} [type: {{$jobstep['step'.$i]['type']}}]
                    
    			</p>
                @endfor
                <input type="checkbox" value="1" name="debug"/> <strong style="color: blue;">Debug</strong>
              </div>
            <div class="col-sm-3">
                <h3>
    				Timeout(s)
    			</h3>
    			<p id="timeout">{{ $job->stepTimeout }}</p>
            </div>
            <div class="col-sm-3">
                <h3>
    				Step Relation
    			</h3>
    			<p id="rela">
                    @if ( $job->stepRelation == 1 )
                    Relation [ Stop at Failed Step ]
                    @else
                    Non-relation [ Continue next step if failed ]
                    @endif
    			</p>
            </div>
            </div>
            
            <div class="row" id="row2">
			  <div class="col-sm-6">
                <h3>
    				Nodes
    			</h3>
    			<p>
                    @if ( ($job->filterall == 0 and empty($job->nodeSelected)) or empty($job->nodeFilter) )
                        Node List Empty. No nodes selected.
                    @else
                        <div class="form-group">
                        <div class="input-group">
                            <p>You have selected nodes below:</p>
                            <div><strong>Filter stored:</strong> {{ $job->nodeFilter }}</div>
                            <input class="form-control" type="hidden" value="{{ $job->nodeFilter }}"
                              id="runjob_filter"  style="width: 300px;"/>
                              <input class="form-control" type="hidden" value="{{ $job->nodeSelected }}"
                              id="runjob_selected"  style="width: 300px;"/>
                        </div>
                        </div>
                        <div id="runjobnoderes"></div>
                    @endif
    			</p>
              </div>
            <div class="col-sm-3">
                <h3>
    				Log Path
    			</h3>
    			<p id="logpath">
                    {{ $job->logpath }}
                </p>
            </div>
            <div class="col-sm-3">
                <h3>
    				Parallel(Threads)
    			</h3>
    			<p id="parallel">{{ $job->parallel }}</p>
            </div>

		</div>
	</div>
</div>