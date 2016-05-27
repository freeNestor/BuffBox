<script>
    $(function(){
        $("[data-toggle='tooltip']").tooltip();
    });
    function updateKey(){
        $pubkeypath = $("input[name='pubkeypath']").val();
        $prikeypath = $("input[name='prikeypath']").val();
        $token = $("input[name='_token']").val();
        $.ajax({
            url:'/updatekey',
            type:'post',
            data:{'_token':$token,'pubkeypath':$pubkeypath,'prikeypath':$prikeypath},
            success:function(data,status){
                $("#configmsg").html(data);
            },
            error:function(x,s,t){
                $("#configmsg").html(t);
            }
        });
    }
    
    function uploadkey(){
        $("#uploadkeyform").ajaxSubmit({
            url:'/uploadkey',
            type:'post',
            success:function(data,status){
                $("#configmsg").html(data);
            },
            error:function(x,s,t){
                $("#configmsg").html(t);
            }
        });
    }
</script>
<ul id="keyconfigTab" class="nav nav-tabs">
    <li class="active">
    <a name="sshkey-tab" href="#keyhome" data-toggle="tab">
        Default</a>
    </li>
</ul>
<div id="" class="tab-content">
    <div class="tab-pane fade in active" id="keyhome">
        <ul class="nav nav-list">
            <li class="divider"></li>
        </ul>
        <p><div id="configmsg"></div></p>
        <p><h4><small>Key Files Path</small></h4></p>
        <p>
            <h4><small>Current Public Key File:</small>{{ $key->pubkey_path }}</h4>
        </p>
        <p>
            <h4><small>Current Private Key File:</small>{{ $key->privekey_path }}</h4>
        </p>
        <p><h4><small>Key Type:</small>{{ $key->key_type }}</h4></p>
        <p><h4><small>Change Current Local Server Key Path and File:</small></h4>
            <form class="form-horizontal" role="form">
             <div class="form-group">
                <label class="col-sm-2 control-label">Public Key Path:</label>
                <div class="col-sm-4">
                    <input type="text" class="form-control" data-toggle="tooltip"
                     data-placement="top" title="Example: /<path>/id_rsa.pub" 
                     placeholder="Input Public Key Path" name="pubkeypath"/>
                </div>
             </div>
             <div class="form-group">
                <label class="col-sm-2 control-label">Private Key Path:</label>
              <div class="col-sm-4">
                <input type="text" class="form-control"  data-toggle="tooltip"
                     data-placement="top" title="Example: /<path>/id_rsa"
                     placeholder="Input Private Key Path" name="prikeypath"/>
              </div>
              <div class="col-sm-2">
                <input type="button" class="btn btn-info" onclick="updateKey();" value="Update"/>
                <input type="hidden" name="_token" value="{{ csrf_token() }}"/>
              </div>
             </div>
            </form>
        </p>
        <p><h4><small>Upload Your Key Files:</small></h4>
            <form class="form-inline" id="uploadkeyform" enctype="multipart/form-data">
                <label>Public Key</label>
                <input type="file" class="form-control" name="pubkeyup"/>
                <label>Private Key</label>
                <input type="file" class="form-control" name="prikeyup"/>
                <input type="button" class="btn btn-info" onclick="uploadkey();" value="Upload"/>
                <input type="hidden" name="_token" value="{{ csrf_token() }}"/>
            </form>
        </p>
    </div>
</div>
