<div class="modal fade" id="warnDiag" tabindex="-1" role="dialog" 
   aria-labelledby="warnDiag" aria-hidden="true" >
   <div class="modal-dialog">
      <div class="modal-content">
         <div class="modal-header">
            <button type="button" class="close" 
               data-dismiss="modal" aria-hidden="true">&times;</button>
            <h4 class="modal-title strong-red" id="">
               <span class="glyphicon glyphicon-warning-sign"></span> {{ $title or '' }}
            </h4>
         </div>
         <div class="modal-body" id="">
            <div class="alert alert-danger" id="warntext">
                {{ $warnText or '' }}
            </div>
         </div>
         <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal">Leave</button>
            <button type="button" class="btn btn-danger" data-dismiss="modal" name="deletenodego">Go</button>
         </div>
      </div><!-- /.modal-content -->
</div><!-- /.modal -->
</div>