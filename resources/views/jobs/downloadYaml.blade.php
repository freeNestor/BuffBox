<form class="form-inline" id="downjobyamlform" action="/downjobexpfile" method="post">
   <div class="form-group">
    <span class="form-group">Click Right Button to Download</span>
    <input type="button" class="btn btn-info btn-xs" 
        value="{{ $filename }}" onclick="downJobYaml();"/></div> 
<input type="hidden" name="exprealname" value="{{ $realname }}"/>
<input type="hidden" name="filename" value="{{ $filename }}"/>
<input type="hidden" name="_token" value="{{ csrf_token() }}"/>
</form>