<script>
$(function(){
    
});

function updateSysConfig() {
    var $jobtimeout = $("#jobtimeout").val();
    var $logdir = $("#syslogdir").val();
    var $phpbin = $("#phpbin").val();
    $.ajax({
        url:'/upsysconf',
        type:'get',
        data:{'jobtimeout':$jobtimeout,'logdir':$logdir,'phpbin':$phpbin},
        success:function(data,status){
            $("#sysconfmsg").html(data);
        }
    });
}
</script>
<div class="container-fluid">
	<div class="row">
		<div class="col-md-12">
            <div id="sysconfmsg"></div>
			<form role="form" class="form-horizontal">
				<div class="form-group">
					<label for="jobtimeout" class="col-sm-3">Default Job Timeout</label>
                    <div class="col-sm-5">
					<input type="text" class="form-control" id="jobtimeout" value="{{ $timeout or 0 }}"/>
                    <span class="help-block">This Timeout Value Only Impact Job,not Steps in Job.
                    0 is not Timeout,Timeout Unit: Second</span>
                    </div>
				</div>
				<div class="form-group">
					<label for="LogPath" class="col-sm-3">Log Directory</label>
                    <div class="col-sm-7">
					<input type="text" class="form-control" id="syslogdir" value="{{ $logdir or '' }}"/>
                    <span class="help-block">Log Directory for Error Log,Step Log,etc.</span>
                    </div>
				</div>
  		        <div class="form-group">
					<label for="phpbin" class="col-sm-3">PHP Binary Path</label>
                    <div class="col-sm-7">
					<input type="text" class="form-control" id="phpbin" value="{{ $phpbin or '' }}"/>
                    <span class="help-block">PHP Binary File Path</span>
                    </div>
				</div>
                <div class="col-sm-offset-11">
				    <button type="button" class="btn btn-warning" onclick="updateSysConfig();">Save</button>
                </div>
			</form>
		</div>
	</div>
</div>