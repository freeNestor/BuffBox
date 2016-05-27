<form id="nodeinfo" class="form-horizontal" role="form" style="" method="post" action="/addsinglenode" >
               <div class="form-group" >                
                  <label for="hostname" class="col-sm-2 control-label">Hostname:</label>
                  <div class="col-sm-10">
                    <input type="text" class="form-control" 
                        style="background: white;border: 0;padding: 0;"  
                        placeholder="" disabled="" value="{{$hostname}}" id="hostname"/>
                  </div>
               </div>
               <div class="form-group" >
                  <label for="Platform" class="col-sm-2 control-label">Platform:</label>              
                  <div class="col-sm-10">
                    <input type="text" class="form-control" 
                        style="background: white;border: 0;padding: 0;"  
                        placeholder="" disabled="" value="{{$kernel}}" id="plat"/>
                  </div>
               </div>
               <div class="form-group" >
                  <label for="os" class="col-sm-2 control-label">OS Version:</label>              
                  <div class="col-sm-10">
                    <input type="text" class="form-control" 
                        style="background: white;border: 0;padding: 0;"  
                        placeholder="" disabled="" value="{{$os}}" id="os"/>
                  </div>
               </div>
               <div class="form-group" >
                  <label for="ker" class="col-sm-2 control-label">Kernel:</label>              
                  <div class="col-sm-10">
                    <input type="text" class="form-control" 
                        style="background: white;border: 0;padding: 0;"  
                        placeholder="" disabled="" value="{{$kernelv}}" id="ker"/>
                  </div>
               </div>
                <div class="form-group" >
                  <label for="ven" class="col-sm-2 control-label">Vendor:</label>              
                  <div class="col-sm-10">
                    <input type="text" class="form-control" 
                        style="background: white;border: 0;padding: 0;"  
                        placeholder="" disabled="" value="{{$vendor}}" id="ven"/>
                  </div>
               </div>
               
               <div class="form-group" >
                  <label for="tags" class="col-sm-2 control-label">Tags&nbsp;<span class="glyphicon glyphicon-tags"></span></label>              
                  <div class="col-sm-10">
                    <input type="text" class="form-control" 
                        style="background: white;border: 0;padding: 0;"  
                        placeholder="Please input like Linux,DB2 or Other..." value="" id="tag"/>
                  </div>
               </div>
                <input type="hidden" id="token" name="_token" value="{{ csrf_token() }}" />  
                <input type="hidden" id="act" name="act" value="add" /> 
</form>