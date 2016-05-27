<script>
$(function(){
    $("#resettasklist").click(function(){
        $.ajax({
            url:'/tasklist',
            type:'get',
            success:function(data,status){
                $("#pad").html(data);
            }
        });
    });
    
    $("#taskpre").click(function(){
        var urla = $(this).attr('value');
        if (urla != '') {
            $.ajax({
                url:urla,
                type:'get',
                success:function(data,status){
                    $("#pad").html(data);
                }
            });
        }
    });
    
    $("#tasknext").click(function(){
        var urla = $(this).attr('value');
        if (urla != '') {
            $.ajax({
                url:urla,
                type:'get',
                success:function(data,status){
                    $("#pad").html(data);
                }
            });
        }
    });
    
    $("a[name='viewtask']").click(function(){
        $jobid = $(this).next().val();
        $taskid = $(this).next().next().val();
        $finishedat = $(this).next().next().next().val();
        $.ajax({
            url:'/viewtask',
            type:'get',
            data:{'jobid':$jobid,'runview':1,'taskid':$taskid,'finishedat':$finishedat},
            success:function(data,status) {
                //$("#pad").hide();
                $("#pad").html(data);
            }
        });
    });
    
    $("a[name='killjob']").click(function(){
        $taskid = $(this).next().val();
        $.ajax({
            url:'/killtask',
            type:'get',
            data:{'taskid':$taskid},
            success:function(data,status){
                $("#resettasklist").click();
            }
        });
    });
});
</script>

<div class="container-fluid">
	<div class="row">
		<ol class="breadcrumb">
                    <li id="hiderefresh"><span class="glyphicon glyphicon-refresh"></span>
                    <a class="" href="#" id="resettasklist">Refresh</a>
        </ol>
        <div class="col-md-12">
			<table class="table table-hover table-condensed table-striped table-center">
				<thead>
					<tr>
						<th>
							Task#
						</th>
						<th>
							Updated
						</th>
                        <th>
							Log
						</th>
                        <th>
							Run by
						</th>
						<th>
							Status
						</th>
                        <th>
							Operation
						</th>
						<th>
							Job#
						</th>
					</tr>
				</thead>
				<tbody>
                @foreach ($taskres as $task)
					<tr>
						<td>
							<a href="#" name="viewtask">Task#{{ $task->id }}</a>
                            <input type="hidden" value="{{ $task->jobid }}" name="jobid"/>
                            <input type="hidden" value="{{ $task->id }}" name="taskid"/>
                            <input type="hidden" value="{{ $task->updated_at }}" name="updatedat"/>
						</td>
						<td>
							{{ $task->updated_at }}
                            
						</td>
                        <td>
							{{ $task->exec_log }}
						</td>
                        <td>
							by {{ $task->byuser }}
						</td>
						<td style="width: 100px;text-align: center;">
							@if ($task->state != "Finished" and $task->state != "Canceled" and $task->state != "Timeout" and $task->state != "Error")
                            @include('jobs.progressActive',['status' => $task->state])
                            @else
                                @if ($task->state == "Canceled" or $task->state == "Timeout" or $task->state == "Error")
                                <strong class="strong-red">{{ $task->state }}</strong>
                                @else
                                {{ $task->state }}
                                @endif
                            @endif
						</td>
                        <td>
                        @if ($task->state != "Finished" and $task->state != "Canceled" and $task->state != "Timeout" and $task->state != "Error")
							<a class="btn btn-xs btn-danger" name="killjob">
                            <span class="glyphicon glyphicon-remove"></span>Kill Job</a>
                            <input type="hidden" value="{{ $task->id }}" name="taskid"/>
                        @endif
						</td>
						<td>
							Job#{{ $task->jobid }}
						</td>
					</tr>
                @endforeach
				</tbody>
			</table>
        </div>
        <div class="col-md-12" style="text-align: center;">
            <ul class="pagination" style="text-align: center;">
                <li class=""><a href="#" id="taskpre" value="{{ $taskres->previousPageUrl() }}">&laquo;</a></li>
                <li class="active"><a href="#" id="taskcur" value="{{ $taskres->url($taskres->currentPage()) }}">{{ $taskres->currentPage() }}</a></li>
                <li class=""><a href="#" id="tasknext" value="{{ $taskres->nextPageUrl() }}">&raquo;</a></li>
                <li class="disabled"><a href="#">{{ $taskres->currentPage() }}/{{ $totalpage }}</a></li>
            </ul>
		</div>
	</div>
</div>