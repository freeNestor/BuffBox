<script>
    var newpass;
    var confirmpass;
    var uid;
    var _token;
    $(function(){
        $("input#isadmin").bootstrapSwitch();
        //$("#isadmin").setSizeClass('small');
        
        $("button[name='goresetpass']").click(function(){
            $(this).attr('disabled','disabled');
            $.ajax({
                type:'post',
                url:'/resetpass',
                data:{'_token':_token,'uid':uid,'newpass':newpass,'confirmpass':confirmpass},
                success:function(data,status){
                    $("#resetpassmsg").html(data);
                } 
            });
        });
        $("input#firstclick").click(function(){
            newpass = $(this).closest('form').find("input[name='newpass']").val();
            confirmpass = $(this).closest('form').find("input[name='confirmpass']").val();
            uid = $(this).closest('form').find("input[name='uid']").val();
            _token = $(this).closest('form').find("input[name='token']").val();
            uname = $(this).closest('form').find("input[name='uname']").val();
            $("#resettext").html("Warning!!!You are going to reset <strong>"+uname+"</strong> password,Really go?");
        });
    });
</script>
<ul id="defaultuserTab" class="nav nav-tabs">
    @foreach ($userList as $user)
        @if ($user->name == 'admin')
                    <li class="active"><a name="default-user-tab" href="#home{{ $user->id }}" data-toggle="tab">
                        {{ $user->name }}</a>
                    </li>
        @else
                    <li class=""><a name="default-user-tab" href="#home{{ $user->id }}" data-toggle="tab">
                        {{ $user->name }}</a>
                    </li>
        @endif
    @endforeach
</ul>
<div id="" class="tab-content">
 @foreach ($userList as $user)
  @if ($user->name == 'admin')
    <div class="tab-pane fade in active" id="home{{ $user->id }}">
        <p><h4><small>User Name:</small>{{ $user->name }}</h4></p>
        <p><form class="form-inline">
            <span>Admin :</span>
                <input type="checkbox" id="isadmin" checked data-size="mini" 
                data-off-color="danger" data-on-color="info" disabled="true"/>
                <div class="alert alert-warning">Tip:default user admin cannot be changed</div>
            </form>
        </p>
        <ul class="nav nav-list">
            <li class="divider"></li>
        </ul>
        <p><h4>Reset Password:</h4></p>
        <p>
        <form class="form-inline" style="">
            <input type="hidden" class="form-control" name="token" value="{{ csrf_token() }}"/>
            <input type="hidden" class="form-control" name="uid" value="{{ $user->id }}"/>
            <input type="hidden" class="form-control" name="uname" value="{{ $user->name }}"/>
            <span class="">New Password:</span>
            <input type="password" class="form-control" name="newpass" />
            <span class="">Confirm Password:</span>
            <input type="password" class="form-control" name="confirmpass" />
            <span class="form-control sr-only" >submit</span>
            <input id="firstclick" type="button" class="btn btn-danger" 
                data-toggle="modal" data-target="#resetconfirm" value="Reset"/>
            <input id="resetpassbut" type="button" style="display: none;"/>
        </form>
        </p>
    </div>
  @else
    <div class="tab-pane fade" id="home{{ $user->id }}">
        <p><h4><small>User Name:</small>{{ $user->name }}</h4></p>
        <p><form class="form-inline">
            <span>Admin :</span>
             <input type="checkbox" id="isadmin" data-size="mini" 
             data-off-color="danger" data-on-color="info" disabled="true"/>
                <div class="alert alert-warning">Tip:default user admin cannot be changed</div>
            </form>
        </p>
        <ul class="nav nav-divider">
            <li class="divider"></li>
        </ul>
        <p><h4>Reset Password:</h4></p>
        <p>
        <form class="form-inline" style="">
            <input type="hidden" class="form-control" name="token" value="{{ csrf_token() }}"/>
            <input type="hidden" class="form-control" name="uid" value="{{ $user->id }}"/>
            <input type="hidden" class="form-control" name="uname" value="{{ $user->name }}"/>
            <span class="">New Password:</span>
            <input type="password" class="form-control" name="newpass" />
            <span class="">Confirm Password:</span>
            <input type="password" class="form-control" name="confirmpass" />
            <span class="form-control sr-only" >submit</span>
            <input id="firstclick" type="button" class="btn btn-danger" 
               data-toggle="modal" data-target="#resetconfirm" value="Reset"/>
            <input id="resetpassbut" type="button" style="display: none;"/>
        </form>
        </p>
    </div>
   @endif
 @endforeach
 <div id=""></div>
</div>

<div class="modal fade" id="resetconfirm" tabindex="-1" role="dialog" 
   aria-labelledby="myModalLabel" aria-hidden="true">
   <div class="modal-dialog">
      <div class="modal-content">
         <div class="modal-header">
            <h4 class="modal-title" id="myModalLabel">
              Reset Password Confirm
            </h4>
         </div>
         <div class="modal-body" id="resetpassmsg">
            <div class="alert alert-warning" id="resettext">
                Warning!!!You are going to reset user password,Really go?
            </div>
         </div>
         <div class="modal-footer">
            <button type="button" class="btn btn-default" 
               data-dismiss="modal">No,Close
            </button>
            <button name="goresetpass" type="button" class="btn btn-primary">
                Go
            </button>
         </div>
      </div><!-- /.modal-content -->
</div><!-- /.modal -->
</div>              
                