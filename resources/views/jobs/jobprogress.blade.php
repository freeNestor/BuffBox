<script>
$(function(){
    $taskid = $("input[name='taskid']").val();
    $token = $("input[name='_token']").val();
    $userop = $("input[name='userop']").val();
    /*
    * This page has two functions
    * if userop equal 'run', run job immediately, do nothing on page
    * else view task info
    */
    if ($userop == "run") {
        $.ajax({
            url:'/jobgonow',
            type:'post',
            data:{'_token':$token,'taskid':$taskid}
        });
    }
    
    //Get Task Complete Time
    function getCompleteTime() {
        $.ajax({
                    url:'/progress',
                    type:'get',
                    data:{'taskid':$taskid,'para':'updated_at'},
                    success:function(data,status){
                        $("div[name='timefinish']").text("Completed at: "+data);
                    }
        });
    }
    //view detail step logs
    $("#viwsteplog").click(function(){
        $.ajax({
            url:'/getsteplog',
            type:'get',
            data:{'taskid':$taskid,'log':'step'},
            beforeSend:function(){
                $("#p2msg").html('Reading and Parsing Log...');
            },
            success:function(data,status) {
                $("#p2msg").html(data);
            }
        });
    });
    //view task logs
    $("#viewerror").click(function(){
        $.ajax({
            url:'/getsteplog',
            type:'get',
            data:{'taskid':$taskid,'log':'task'},
            beforeSend:function(){
                $("#p4msg").html('Reading and Parsing Log...');
            },
            success:function(data,status) {
                $("#p4msg").html(data);
            }
        });
    });

        /**
         * Get task summary per second
         * include total node,finished,failed,elapse time
         * be careful,increase interval may act good performance
         */
        //clearInterval($getjobsum);
        $getjobsum = setInterval(function(){     
                $.ajax({
                    url:'/progress',
                    type:'get',
                    data:{'taskid':$taskid,'para':'jobsum'},
                    success:function(data,status){
                        $objData = JSON.parse(data);
                        $percent = $("#progressrate"+$taskid).text().replace(/[\s\r\n]/g,"");
                        $.each($objData,function(key,value){
                            if (value == null) {
                                value = 0;
                            }
                            
                            $("span[name='"+key+"']").text(value);
                            if (key == "hostfailed" || key == "stepfailed") {
                                if (value > 0) {
                                    $("span[name='"+key+"']").attr('style','background: red;');
                                }
                            }
                            
                            switch (key) {
                                case 'percent':
                                    $("#jobprogress"+$taskid).attr('style','width:'+value+'%;');
                                    $("#progressrate"+$taskid).text(value+"%");
                                    break;
                                case 'state':
                                    $("#taskstate").text("Task "+value);
                                    if (value != "Preparing" && value != "Starting" && value != "Running" && $percent == "100%") {
                                        $("#pswitch").attr('class','progress progress-striped');
                                        $timestring = $("div[name='timefinish']").text().replace(/[\s\r\n]/g,"");
                                        if ($timestring == "Completedat:NotComplete") {
                                            getCompleteTime();
                                        }
                                        clearInterval($getjobsum);
                                    }
                                    break;
                                default: break;
                            }
                        });
                    }
                });
        },1500);

});
</script>
<input type="hidden" value="{{ $taskid or '' }}" name="taskid"/>
<input type="hidden" value="{{ csrf_token() }}" name="_token"/>
<input type="hidden" value="{{ $userop or 'run' }}" name="userop"/>
<div class="container-fluid">
	<div class="row">
		<div class="col-md-12">
			<div class="row">
                <div class="col-md-12"><h4>Task#{{ $taskid or '' }} Status</h4>
                </div>
				<div class="col-md-3">
					<div class="progress progress-striped active" id="pswitch">
						<div class="progress-bar progress-success" role="progressbar" 
      aria-valuenow="60" aria-valuemin="0" aria-valuemax="100" 
      style="width: 100%;" id="taskstate">Retriving Status
						</div>
					</div>
				</div>
				<div class="col-md-9" name="timefinish">Completed at: {{ $finishedat or 'Not Complete' }}
				</div>
			</div>
			<div class="tabbable" id="tabs-80740">
				<ul class="nav nav-tabs">
                    <li class="active">
						<a href="#panel-850152" data-toggle="tab" id="viewprogress">Progress</a>
					</li>
					<li>
						<a href="#panel-836471" data-toggle="tab" id="viwsteplog">Step Log</a>
					</li>
                    <li>
						<a href="#panel-836472" data-toggle="tab" id="viewerror">Task Error Log</a>
					</li>
				</ul>
				<div class="tab-content">
                    <div class="tab-pane active" id="panel-850152" style="padding-top: 20px;">
						@include('jobs.jobsummary')
                        <p id="p3msg">
							<div class="progress progress-striped">
                            <div class="progress-bar progress-bar-warning" role="progressbar" 
                                    aria-valuenow="60" aria-valuemin="0" aria-valuemax="100" 
                            style="width: 0%;" id="jobprogress{{ $taskid or '' }}">
                            <span class="" id="progressrate{{ $taskid or '' }}">0%</span>
                            </div>
                            </div>
						</p>
                        
					</div>
					<div class="tab-pane" id="panel-836471">
						<p id="p2msg" style="padding-top: 20px;">
							Step Log Empty
						</p>
					</div>
                    <div class="tab-pane" id="panel-836472">
						<p id="p4msg">
							Task is normal
						</p>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>