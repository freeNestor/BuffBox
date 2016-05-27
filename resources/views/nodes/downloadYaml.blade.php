<form class="form-inline table-bordered" id="downyamlform" action="/downexpfile" method="post">
   <div class="form-group">
    <span class="form-group">Click Right Button to Download</span>
    <input type="button" class="btn btn-info btn-sm" 
        value="{{ $filename }}" onclick="downYaml();"/></div> 
<input type="hidden" name="exprealname" value="{{ $realname }}"/>
<input type="hidden" name="_token" value="{{ csrf_token() }}"/>
</form>