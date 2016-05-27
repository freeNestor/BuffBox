<script>
    $(function(){
        
        $("button").click(function(){
            id = $(this).attr('id');
            obj = "#"+id
            prefix = /^nodetagdel[0-9]*$/;
            if (prefix.test(id)) {
                //$(obj).attr('style','display: normale');
                alert("You will delete it,OK?");
            }
        });
        $("#addnodetag").mouseover(function(){
            //$(this).attr('style','border: 0;display: normal;');
        });
    });
</script>
<div class="panel panel-info">
    @foreach ($nodeidres as $nodeone)
                <div class="panel-heading"><strong>Node: {{ $nodeone->hostname }}</strong></div>
                <div class="panel-body">
                    <p>You can change some properties. Be careful,it will update immediately.

            
                <div class="input-group">
                    <span class="input-group-addon"><strong>HostName</strong></span>
                    <input type="text" class="form-control" value="{{ $nodeone->hostname }}"  style="border: 0;">
                </div>
                <div class="input-group">
                    <span class="input-group-addon"><strong>Platform</strong></span>
                    <input type="text" class="form-control" value="{{ $nodeone->platform }}"  style="border: 0;">
                </div></li>
                <div class="input-group">
                    <span class="input-group-addon"><strong>OS Version</strong></span>
                    <input type="text" class="form-control" value="{{ $nodeone->osversion }}"  style="border: 0;">
                </div>
                <div class="input-group">
                    <span class="input-group-addon"><strong>Kernel</strong></span>
                    <input type="text" class="form-control" value="{{ $nodeone->kernel }}"  style="border: 0;">
                </div>
                <div class="input-group">
                    <span class="input-group-addon"><strong>Type/Model</strong></span>
                    <input type="text" class="form-control" value="{{ $nodeone->vendor }}"  style="border: 0;">
                </div>
                <div class="input-group">
                    <span class="input-group-addon"><strong>IP Address</strong></span>
                    <input type="text" class="form-control" value="{{ $nodeone->ipaddr }}"  style="border: 0;">
                </div>
                <div class="input-group">
                    <span class="input-group-addon"><strong>SSH PORT</strong></span>
                    <input type="text" class="form-control" value="{{ $nodeone->portok }}"  style="border: 0;">
                </div>
                
                <div class="" style="text-align: left;">
                    <span class="input-group-addon"><strong>Group/Tags</strong></span>
                    <ol class="breadcrumb">
                    @foreach ($nodetags as $tag)
                        @if (empty($tag))
                            @break;
                        @endif
                    <li><span class="glyphicon glyphicon-tags"></span>
                    <span class="label label-info" id="nodetag{{ array_search($tag,$nodetags) }}" value="{{ array_search($tag,$nodetags) }}">
                        {{ $tag }}&nbsp;</span>
                    <button class="btn btn-link btn-xs" id="nodetagdel{{ array_search($tag,$nodetags) }}" >&times;</button>
                    </li>
                    @endforeach
                    <li><input type="text" id="addnodetag" placeholder="Add New Tag" 
                        style="border:0;display: normal;"/>
                    </li>
                    </ol>
                 </div>

                <div class="">
                    <span class="input-group-addon"><strong>Update Time</strong></span>
                    <input type="text" class="form-control" disabled value="{{ $nodeone->updated_at }}"  style="border: 0;text-align: center;">
                </div>
             </div>
                </p>
            @endforeach
</div>