@include('header')
<script type="text/javascript">
   $(document).ready(function(){
    $("#butsub").click(function(){
//        token = $("#_token").val();
//        name = $("#name1").val();
//        pass = $("#pass").val();
//        $.ajax({
//            type:'post',
//            url:'/login',
//            data:{'_token':token,'inputName':name,'inputPassword':pass},
//            success:function(data,status){
//                $("#loginmsg").html(data);
//            }
//        });
        $("#loginf").submit();
    });
   });
</script>
<body class="pannel pannel-default bg">

<div class="container"  style="display: table;">
     <div class="row" style="">
     <div class="col-sm-12 text-center">
      <form id="loginf" class="form-inline" method="post" action="/login" style="margin-top: 20%;">
 
        <input type="hidden" id="_token" name="_token" value="{{ csrf_token() }}" />
        <label class=""><span class="glyphicon glyphicon-user" ></span></label>
        <input id="name1" type="text" class="form-control" name="inputName" placeholder="LoginName" />
            
        <label class=""><span class="glyphicon glyphicon-lock" ></span></label>
        <input id="pass" type="password" name="inputPassword" class="form-control" placeholder="Password" />
        <input id="butsub" class="btn btn-default" type="button" value="Login" />
      </form>
      </div>
     </div>
     <div class="row" style="">
     <div class="col-sm-12 text-center">
      <div id="loginmsg" class="alert alert-danger" style="background: transparent;border: 0;">
        @if (isset($msg))
            <strong>Warning!!! </strong>{{ $msg }}
        @endif
      </div>
      </div>
     </div> 
 </div>

 </body>
</html>
