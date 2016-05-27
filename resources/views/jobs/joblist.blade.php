<script>
    $(function(){
        $("#jobrefresh").click(function(){
            //alert();
            $.get('/joblist',function(data,status){
                $("#pad").html(data);
            });
        });
        $("#btncrtjob").click(function(){
            $.get('/crtjobviw',function(data,status){
                $("#pad").html(data);
            });
        });
        
        $("a[name='runjob']").click(function(){
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
        
        $("a[name='deletejob']").click(function(){
            $jobid = $(this).next("input").val();
            $.ajax({
                url:'/removejob',
                type:'get',
                data:{'runview':2,'jobid':$jobid},
                success:function(data,status){
                    $("#pad").html(data);
                }
            });
        });

        $("a[name='editjob']").click(function(){
            $jobid = $(this).next("input").val();
            $.ajax({
                url:'/editjob',
                type:'get',
                data:{'runview':3,'jobid':$jobid},
                success:function(data,status){
                    $("#pad").html(data);
                }
            });
        });
        
        $("a[name='expjob']").click(function(){
            $jobid = $(this).next("input").val();
            $.ajax({
                url:'/expjobviw',
                type:'get',
                data:{'jobid':$jobid},
                success:function(data,status){
                    $("#downjobyaml").html(data);
                }
            });
        });
    });
</script>
<div class="row">
    <div class="col-sm-12">
        <ol class="breadcrumb">
            <li><span class="glyphicon glyphicon-plus"></span>
            <a class="" id="btncrtjob" href="#">New Job</a></li>
            <li><span class="glyphicon glyphicon-refresh"></span>
            <a class="" id="jobrefresh" href="#">Refresh</a></li>
        </ol>
    </div>
</div>

@if ( count($jobres) == 0 )
<table class="table table-condensed table-hover" style="margin-top: 0;">
   <tbody>

      <tr class="">
         <td><h3 class="text-danger"><em>You current haven't defined any jobs</em></h3></td>
      </tr>
      </tbody>
    </table>
     @else
      @foreach ($jobres as $job)
      <div class="row">
      <div class="col-sm-10" style="margin-left: 30px;">
        <span class="glyphicon glyphicon-ok" style="margin-right: 0;"></span> 
        <div class="btn-group">
        <a class="btn btn-link btn-lg dropdown-toggle" style="margin-left:0;border: 0;" id="jobmenu{{ $job->id }}" 
            data-toggle="dropdown">{{ $job->name }}
            <span class="caret" style=""></span>
        </a>
        <ul class="dropdown-menu" role="menu" aria-labelledby="jobmenu{{ $job->id }}">
            <li role="presentation">
                <a role="menuitem" tabindex="-1" name="runjob">
                <span class="glyphicon glyphicon-play" style="color: green;"></span> View and Run Job...</a>
                <input type="hidden" value="{{ $job->id }}"/>
            </li>
            <li role="presentation">
                <a role="menuitem" tabindex="-1" name="editjob">
                <span class="glyphicon glyphicon-edit" style="color: green;"></span> Edit Job...</a>
                <input type="hidden" value="{{ $job->id }}"/>
            </li>
            <li role="presentation">
                <a role="menuitem" tabindex="-1" name="expjob" data-toggle="modal" data-target="#djyaml">
                <span class="glyphicon glyphicon-download" style="color: green;"></span> Export Job...</a>
                <input type="hidden" value="{{ $job->id }}"/>
            </li>
            <li class="divider"></li>
             <li role="presentation">
                <a role="menuitem" tabindex="-1" name="deletejob">
                <span class="glyphicon glyphicon-trash" style="color: red;"></span> Delete</a>
                <input type="hidden" value="{{ $job->id }}"/>
            </li>
        </ul>
        </div>
        <div class="btn-group">
            <h5><small>{{ $job->description }}</small></h5>
        </div>
      </div>
      </div>
      @endforeach
     @endif

<div style="text-align: center;">
<ul class="pagination">
  <li class=""><a href="#" value="{{ $jobres->previousPageUrl() }}">&laquo;</a></li>
    <li class="active"><a href="#" id="nodepagecur" value="{{ $jobres->url($jobres->currentPage()) }}">{{ $jobres->currentPage() }}</a></li>
    <li class=""><a href="#" id="nodepagenext" value="{{ $jobres->nextPageUrl() }}">&raquo;</a></li>
    <li class="disabled"><a href="#">{{ $jobres->currentPage() }}/{{ $totalpage }}</a></li>
</ul>
</div>
@include('jobs.downloadYamlDiag')