<script>
    $(function(){
        $.get('/configwel',function(data,status){
            $("#config-pad-body").html(data);
        });
        
        $("#config-default-user").click(function(){
            $("#configopt").text("User Config");
            $.get('/configDefUser',function(data,status){
                $("#config-pad-body").html(data);
            });
        });
        
        $("#config-key").click(function(){
            $("#configopt").text("SSH Config");
            $.get('/configSSHKey',function(data,status){
                $("#config-pad-body").html(data);
            });
        });
        
        $("#config-sys").click(function(){
            $("#configopt").text("System Config");
            $.ajax({
                url:'/configSys',
                type:'get',
                success:function(data,status){
                    $("#config-pad-body").html(data);
                }
            });
        });
    });
</script>
<div class="container-fluid" >
   <div class="row">
     <div class="col-sm-12" style="inset -1px 1px 1px #444;">
        <ol class="breadcrumb">
            <li><a href="#">Config Home</a></li>
            <li class="active">User</li>
        </ol>
     </div>
   </div>
   
   <div class="row">
      <div class="col-sm-3" style="padding-right: 0;">
        <div class="panel panel-info">
            <div class="panel-heading">
                <h3 class="panel-title">Configure Options</h3>
            </div>
            <div class="panel-body">
                <div class="panel-group" id="accordion">
                    <div class="panel panel-warning">
                        <div class="panel-heading">
                          <h4 class="panel-title">
                            <a data-toggle="collapse" data-parent="#accordion" 
                              href="#collapseZero">System</a>
                          </h4>
                        </div>
                        <div id="collapseZero" class="panel-collapse collapse in">
                          <div class="panel-body">
                            <ul class="list-group list-unstyled">
                            <li class="list-unstyled">
                                <a class="btn btn-link" id="config-sys">System</a>
                            </li>
                            </ul>
                          </div>
                        </div>
                      </div>
                      <div class="panel panel-warning">
                        <div class="panel-heading">
                          <h4 class="panel-title">
                            <a data-toggle="collapse" data-parent="#accordion" 
                              href="#collapseOne">User</a>
                          </h4>
                        </div>
                        <div id="collapseOne" class="panel-collapse collapse">
                          <div class="panel-body">
                            <ul class="list-group list-unstyled">
                            <li class="list-unstyled">
                                <a class="btn btn-link" id="config-default-user">Default User</a>
                            </li>
                            <li class="list-unstyled">
                                <a class="btn btn-link">New User</a>
                            </li>
                            </ul>
                          </div>
                        </div>
                      </div>
                      <div class="panel panel-warning">
                        <div class="panel-heading">
                          <h4 class="panel-title">
                            <a data-toggle="collapse" data-parent="#accordion" 
                              href="#collapseTwo">SSH</a>
                          </h4>
                        </div>
                        <div id="collapseTwo" class="panel-collapse collapse">
                          <div class="panel-body">
                            <a class="btn btn-link" id="config-key">Public/Private Key</a>
                            
                          </div>
                        </div>
                      </div>
                      <div class="panel panel-warning">
                        <div class="panel-heading">
                          <h4 class="panel-title">
                            <a data-toggle="collapse" data-parent="#accordion" 
                              href="#collapseThree">Inner Shell</a>
                          </h4>
                        </div>
                        <div id="collapseThree" class="panel-collapse collapse">
                          <div class="panel-body">
                            menu
                          </div>
                        </div>
                      </div>
                    </div>
            </div>
        </div>
      </div>
      <div class="col-sm-9" style="padding-left: 0;">
        <div class="panel panel-success">
            <div class="panel-heading">
                <h3 class="panel-title" id="configopt">Configure Panel</h3>
            </div>
            <div class="panel-body" id="config-pad-body">
                
            </div>
        </div>
      </div>      
    </div>
</div>