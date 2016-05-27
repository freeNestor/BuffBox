<script>
            
function getNodeInfo($url){
    $.get($url,function(data,status){
        $("#nodeidres").html(data);
    });
}
function downYaml(){
    $fileName = $("input[name='exprealname']").val();
    $("#downyamlform").submit();
}
$(function () { 
    $("[data-toggle='tooltip']").tooltip();

                        $("#ipbutton").click(function(){
                            $.ajax({
                                type:"post",
                                url:"/discoverd",
                                data:{'ip':$("#ip").val(),'port':$("#port").val(),'_token':$("#token").val(),'rootpass':$("#rootpass").val()},
                                beforeSend:function(){
                                    $("#proctext").text('Discovering...');
                                    $("#process").show();
                                    $("#disout").text("");
                                    $("#butaddnode").attr('disabled','disabled');
                                },
                                success:function(data,status){
                                    $("#disout").html(data);
                                    if ($("#errormsg").text()=="") {
                                        $("#butaddnode").removeAttr('disabled');
                                    }
                                    
                                },
                                complete:function(){
                                    $("#process").hide();
                                },
                                error:function(xhr){
                                    $("#process").hide();
                                    $("#disout").html(xhr.statusText);
                                }
                            }); //ajax end
                        });
                            //$("#formdis").submit();

            $("#butaddnode").click(function(){
                
                $.ajax({
                   type:"post",
                   url:"/addsinglenode",
                   data:{'_token':$("#token").val(),'ip':$("#ip").val(),'host':$("#hostname").val(),
                   'plat':$("#plat").val(),'os':$("#os").val(),'ker':$("#ker").val(),'ven':$("#ven").val(),
                   'tag':$("#tag").val(),'act':'add','port':$("#port").val()},
                   beforeSend:function(){
                        $("#proctext").text('Saving...');
                        $("#process").show();
                        $("#disout").text("");
                        $("#butaddnode").attr('disabled','disabled');
                   },
                   success:function(data,status){
                        $("#disout").html(data);
                        $("#process").hide();
                   },
                   error:function(xhr){
                        $("#process").hide();
                        $("#disout").html(xhr.statusText);
                   }
                });
            });
            
            $("#nodefresh").click(function(){
                $("#delnodemsg").html('');
                $perpage = $("input[name='perpagelist']:checked").val();
                    $.ajax({
                        url:'/nodelist',
                        type:'get',
                        data:{'filter':$("#nodelistfilter").val(),'perpage':$perpage},
                        success:function(data,status){
                            $("#nodetable").html(data);
                            $("#nodelistfilter").attr('value',$filter);
                                $.each($("input[name='perpagelist']"),function(){
                                    if (this.value == $perpage){ 
                                        $(this).attr('checked','true');
                                    }
                                });
                        }
                    });                
            });
            $("input[name='perpagelist']").click(function(){
                $("#nodefresh").click();
            });
            $(".selectpicker").selectpicker();
    $("#nodelistfilter").focusout(function(){
        $filter = $(this).val();
        $perpage = $("input[name='perpagelist']:checked").val();
        $.ajax({
            url:'/nodelist',
            type:'get',
            data:{'filter':$filter,'perpage':$perpage},
            success:function(data,status){
                $("#nodetable").html(data);
                $("#nodelistfilter").attr('value',$filter);
                $newperpage = $("input[name='perpagelist']:checked").val();
                if ($perpage != $newperpage){
                    $.each($("input[name='perpagelist']"),function(){
                        if (this.value == $perpage){
                            $(this).attr('checked','true');
                        }
                    });
                }
            }
        });
    });
    /**
     * Show Warning Diaglog
     */
    $("#deletenode").click(function(){
        $i = 0;
        $.each($("input[name='selnode']:checked"),function(){
            $i = $i + 1;
        });
        $("#warntext").text('You will delete '+$i+' Nodes!');
    });
    /**
     * User confirmed and submit delete request
     */
    $("button[name='deletenodego']").click(function(){
        $nodeidStr = '';
        $.each($("input[name='selnode']:checked"),function(){
            $nodeidStr = $nodeidStr + this.value + ',';
        });
        $.ajax({
            url:'/removenode',
            type:'get',
            data:{'nodeid':$nodeidStr},
            success:function(data,status){
                $("#delnodemsg").html(data);
            }
        });
    });
    $("body").on("hidden.bs.modal",".modal",function(){
        $("#exportnodemb").html('');
        $("#importprogress").html('Waiting Import...');
        $(this).removeData();
    });    
});
</script>
<div class="col-md-12">
    <ol class="breadcrumb">
        <li><span class="glyphicon glyphicon-plus"></span>
        <a class="" href="#" data-toggle="modal" data-target="#nodeModal">Add</a></li>
        <li><span class="glyphicon glyphicon-upload"></span>
        <a class="" href="#" data-toggle="modal" data-target="#nodeimModal" id="nodeimport">Import</a></li>
        <li><span class="glyphicon glyphicon-download"></span>
        <a class="" href="#" data-toggle="modal" data-target="#expnodeDiag" id="nodeexport">Export</a></li>
        <li><span class="glyphicon glyphicon-refresh"></span>
        <a class="" href="#" id="nodefresh">Refresh</a></li>
        <li><span class="glyphicon glyphicon-trash"></span>
        <a class="" href="#" data-toggle="modal" data-target="#warnDiag" id="deletenode">Delete</a></li>
        <li><span class="glyphicon glyphicon-search"></span>
            <input type="text" placeholder="Filter" id="nodelistfilter" value=""
                data-toggle="tooltip" data-placement="top" title="Example, tag:DB2" style="width: 400px;"/></li>
        <li>List Per Page
            <label class="checkbox-inline">
                <input type="radio" name="perpagelist" value="20" />20
            </label>
            <label class="checkbox-inline">
                <input type="radio" name="perpagelist" value="50" />50
            </label>
            <label class="checkbox-inline">
                <input type="radio" name="perpagelist" value="100" />100
            </label>
        </li>
    </ol>
</div>
<div class="col-md-12">
<div id="delnodemsg"></div></div>
<div id="nodetable">@include('nodes.nodelistTable')</div>

@include('nodes.nodeimport')
@include('nodes.nodeproperty')
@include('nodes.removeWarnDiag',['title' => 'Warning'])
@include('nodes.exportDiag')