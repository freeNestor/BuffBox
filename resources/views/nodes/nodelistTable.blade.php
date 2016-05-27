<script>
$(function(){
                
            $("#nodepagepre").click(function(){
                //alert($(this).attr('disabled'));
                if ($(this).attr('disabled')!="disabled" && $(this).attr('value')!='') {
                    $url = $(this).attr('value');
                    $perpage = $("input[name='perpagelist']:checked").val();
                    $.ajax({
                        url:$url,
                        type:'get',
                        data:{'filter':$("#nodelistfilter").val(),'perpage':$perpage},
                        success:function(data,status){
                            $("#nodetable").html(data);
                            $("#nodelistfilter").attr('value',$filter);
                            $newperpage = $("input[name='perpagelist']:checked").val();
                            if ($perpage != $newperpage){
                                $.each($("input[name='perpagelist']"),function(){
                                    if (this.value == $perpage){
                                        $(this).attr('checked','true');
                                    }
                                });
                            }
                        }
                    });
                }
            });
            $("#nodepagenext").click(function(){
                if ($(this).attr('disabled')!="disabled" && $(this).attr('value')!='') {
                    $url = $(this).attr('value');
                    $perpage = $("input[name='perpagelist']:checked").val();
                    $.ajax({
                        url:$url,
                        type:'get',
                        data:{'filter':$("#nodelistfilter").val(),'perpage':$perpage},
                        success:function(data,status){
                            $("#nodetable").html(data);
                            $("#nodelistfilter").attr('value',$filter);
                            $newperpage = $("input[name='perpagelist']:checked").val();
                            if ($perpage != $newperpage){
                                $.each($("input[name='perpagelist']"),function(){
                                    if (this.value == $perpage){
                                        $(this).attr('checked','true');
                                    }
                                });
                            }
                        }
                    });
                }
            });
            $("input[name='selnodeall']").click(function(){
                if ($(this).prop('checked') == false) {
                    $("input[name='selnode']").prop('checked',false);
                } else {
                    $("input[name='selnode']").prop('checked',true);
                }
            });
});
</script>
<div class="col-md-12">
<table class="table table-condensed table-hover" style="margin-top: 0;border: none;">
   <tbody>
      <tr>
        <th>Hostname</th>
        <th>osArch</th>
        <th>osVersion</th>
        <th>IP Address</th>
        <th>Created At</th>
        <th style="text-align: center;">Select All <input type="checkbox" name="selnodeall"/></th>
      </tr>
      @foreach ( $noderes as $node )
      <tr class="" >
         
         <td><span class="glyphicon glyphicon-chevron-right"></span>
         <button id="nodeid{{ $node->id }}" class="btn btn-xs btn-info" value="{{ $node->id }}"
          data-toggle="modal" data-target="#nodeproperty" onclick="javascript:getNodeInfo('/getNodeById?nodeid={{ $node->id }}');">
         {{ $node->hostname }}</button></td>
         <td>{{ $node->platform }}</td>
         <td>{{ $node->osversion }}</td>
         <td>{{ $node->ipaddr }}</td>
         <td>{{ $node->created_at }}</td>
         <td style="text-align: center;"><input type="checkbox" value="{{ $node->id }}" name="selnode"/></td>
      </tr>
      @endforeach
   </tbody>
</table></div>
<div style="text-align: center;">
<ul class="pagination">

    <li class=""><a href="#" id="nodepagepre" value="{{ $noderes->previousPageUrl() }}">&laquo;</a></li>
    <li class="active"><a href="#" id="nodepagecur" value="{{ $noderes->url($noderes->currentPage()) }}">{{ $noderes->currentPage() }}</a></li>
    <li class=""><a href="#" id="nodepagenext" value="{{ $noderes->nextPageUrl() }}">&raquo;</a></li>
    <li class="disabled"><a href="#">{{ $noderes->currentPage() }}/{{ $totalpage }} Total:{{ $noderes->total() }}</a></li>
</ul>
</div>

<div class="modal fade" id="nodeModal" tabindex="-1" role="dialog" 
   aria-labelledby="myModalLabel" aria-hidden="true" >
   <div class="modal-dialog">
      <div class="modal-content">
         <div class="modal-header">
            <button type="button" class="close" 
               data-dismiss="modal" aria-hidden="true">&times;</button>
            <h4 class="modal-title" id="myModalLabel">
               Add Node
            </h4>
         </div>
         <div class="modal-body">
            <form class="form-inline" role="form" style="width: 100%;" method="post" action="/discoverd" id="formdis">
               <div class="form-group" >                
                  <input type="text" class="form-control" id="ip" name="ip" style="width: 200px;" 
                   data-toggle="tooltip" data-placement="top" title="Required,eg 192.168.1.200"  placeholder="IP" />
               </div>
               <div class="form-group" >                
                  <input type="text" class="form-control" id="port" name="port" style="width: 100px;" 
                   data-toggle="tooltip" data-placement="top" title="Optional,default is 22"  placeholder="SSH Port" />
               </div>
               <div class="form-group" >                
                  <input type="password" class="form-control" id="rootpass" name="rootpass" style="width: 150px;" 
                   data-toggle="tooltip" data-placement="top" title="Optional,default use Public Key"  placeholder="root password" />
               </div>
                <input type="hidden" id="token" name="_token" value="{{ csrf_token() }}" />
               
              <button id="ipbutton" type="button" class="btn btn-default" >Discover</button>    
            </form>
            <div id="span">&nbsp;</div>
            <div id="disout"></div>
            <div id="process" class="progress progress-striped active" hidden="">
                <div class="progress-bar progress-bar-success" role="progressbar" aria-valuenow="60" aria-valuemin="0" aria-valuemax="100" 
                    style="width: 100%;">
                    <span class="" id="proctext">Discovering...</span>
                </div>
            </div>
         </div>
         <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal" id="closeadddiag">Close</button>
            <button id="butaddnode" type="button" class="btn btn-primary" disabled="disabled">Save</button>
         </div>
      </div><!-- /.modal-content -->
</div><!-- /.modal -->
</div>