<script>
$(function(){
    $("input[name='expradio']").click(function(){
        $.each($("input[name='expradio']"),function(){
            $(this).next("strong").children().unwrap();
            $(this).next().next().attr('class','');
        });
        $(this).next().wrap("<strong></strong>");
        $(this).next().next().attr('class','strong-red');
    });
    /**
     * export node according to rang
     */
    $("#expnodebut").click(function(){
        $expvalue = $("input[name='expradio']:checked").val();
        $expfilter = '';
        switch ($expvalue) {
            case 'all':$range = 1;break;
            case 'filter':$range = 2;$expfilter = $("input[name='expfilter']").val();break;
            case 'select':$range = 3;break;
            default:break;
        }
        $nodeStr = '';
        $.each($("input[name='selnode']:checked"),function(){
            $nodeStr = $nodeStr + this.value + ",";
        });
        $expfilter = escape($expfilter);
        $nodeStr = escape($nodeStr);
        
        $.ajax({
            url:'/exportnode',
            type:'get',
            data:{'range':$range,'filter':$expfilter,'nodestr':$nodeStr},
            success:function(data,status){
                $("#exportnodemb").html(data);
            },
            error:function(xhr,state,text) {
                $("#exportnodemb").html(text);
            }
        });
    });

});
</script>
<div class="modal fade" id="expnodeDiag" tabindex="-1" role="dialog" 
   aria-labelledby="expnodeDiag" aria-hidden="true" >
   <div class="modal-dialog">
      <div class="modal-content">
         <div class="modal-header">
            <button type="button" class="close" 
               data-dismiss="modal" aria-hidden="true">&times;</button>
            <h4 class="modal-title" id="">
               <span class="glyphicon glyphicon-file"></span> Export to Yaml
            </h4>
         </div>
         <div class="modal-body" id="" style="padding-right: 10px;">
          <div class="row"><div class="col-sm-11" id="exportnodemb" style="text-align: center;"></div></div>
          <div class="row">
          <div class="col-sm-offset-1">
            <form class="form-horizontal" role="form" name="expform">
                <div class="form-group">
                    <input type="radio" checked="true" name="expradio" value="all"/> 
                    <strong><span name="radiotext">All</span></strong> <small class="strong-red">[export all nodes]</small></div>
                <div class="form-group">
                    <input type="radio" name="expradio" value="filter"/> <span name="radiotext">Filter</span> 
                    <small>[type filter below]</small></div>
                <div class="form-group">    
                    <input type="text" class="form-control" name="expfilter" style="width: 500px;"/>
                    <span class="help-block">use column:regex, for example, hostname:ypf*,etc.</span></div>
                <div class="form-group">
                    <input type="radio" name="expradio" value="select"/> <span name="radiotext">Selected</span> 
                    <small>[please select node first]</small></div>
            </form>
          </div>
          </div>
         </div>
         <div class="modal-footer">
            <button type="button" id="expnodebut" class="btn btn-success">Export</button>
            <button type="button" id="closeexpdiag" class="btn btn-default" data-dismiss="modal">Close</button>
         </div>
      </div><!-- /.modal-content -->
</div><!-- /.modal -->
</div>