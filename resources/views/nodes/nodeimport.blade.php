<script>
$(function(){
    $("#importyaml").click(function(){
        
        $token = $(this).next("input[name='token']").val();
        $("#importyamlform").ajaxSubmit({
            url:'/importnode',
            type:'post',
            beforeSend:function(xhr){
                $("#importprogress").html('Importing...');
                $("div.progress-striped").attr('class','progress progress-striped active');
            },
            success:function(data,status){
                $("#importprogress").html(data);
                $("div.progress-striped").attr('class','progress progress-striped');
            },
            error:function(xhr,text,status){
                $("#importprogress").html(status);
                $("div.progress-striped").attr('class','progress progress-striped');
            }
        });
    });
});
</script>
<div class="modal fade" id="nodeimModal" tabindex="-1" role="dialog" 
   aria-labelledby="nodeimModal" aria-hidden="true" >
   <div class="modal-dialog">
      <div class="modal-content">
         <div class="modal-header">
            <button type="button" class="close" 
               data-dismiss="modal" aria-hidden="true">&times;</button>
            <h4 class="modal-title" id="">
               Import Node
            </h4>
         </div>
         <div class="modal-body" id="">
            <div class="row"><div class="col-sm-12" id="importmsg">
                <div class="progress progress-striped">
                <div class="progress-bar progress-bar-success" role="progressbar" 
                    aria-valuenow="30" aria-valuemin="0" aria-valuemax="100" 
                    style="width: 100%;">
                <span class="" id="importprogress">Waiting Import...</span>
                </div>
            </div>
            </div></div>
            <form class="form-inline" role="form" id="importyamlform" enctype="multipart/form-data">
                <div class="form-group">
                <label class="sr-only"></label>
                <input type="radio" value="yaml" checked=""/>Yaml</div>
                <div class="form-group">
                <label class="sr-only"></label>
                <input class="form-control" type="file" name="yamlfile"/></div>
                <div class="form-group">
                <label class="sr-only"></label>
                <button type="button" class="btn btn-default" id="importyaml">Import</button>
                <input type="hidden" name="_token" value="{{ csrf_token() }}"/></div>
            </form>
         </div>
         <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
         </div>
      </div><!-- /.modal-content -->
</div><!-- /.modal -->
</div>