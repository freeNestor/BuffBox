<script>
    $.each($("span[name='steplog']"),function(){
        $(this).html(unescape($(this).text()));
    });
</script>
<div class="container-fluid">
	<div class="row">
		<div class="col-md-12">
					@for ($i=0;$i < count($data);$i++)
                    @if ($data[$i]['stepstate'] != "success" and !empty($data[$i]['stepstate']) )
                    <div class="row danger table-bordered" style="background: silver;">
                        <div class="col-sm-12" style="color: red;">
							{{ $data[$i]['date'] }}
                            <strong>{{ $data[$i]['host'] }}</strong>
                            {{ $data[$i]['stepstate'] }}
                            <span name="steplog">{{ $data[$i]['msg'] }}</span>
                            <strong>{{ $data[$i]['Elapse'] }}s</strong>
                        </div>
                        <div class="col-sm-offset-1" style="color: red;">
                            <span name="steplog">Output: {!! $data[$i]['out'] !!}</span>
                            
						</div>
					</div>
                    @else
                        @if ( $i % 2 == 0 )
                        <div class="row danger" style="background: #EEEEEE">
                            <div class="col-sm-12" >
    							{{ $data[$i]['date'] }}
                                <strong>{{ $data[$i]['host'] }}</strong>
                                {{ $data[$i]['stepstate'] }}
                                <span name="steplog">{{ $data[$i]['msg'] }}</span>
                                <strong>{{ $data[$i]['Elapse'] }}s</strong>
                            </div>
                            <div class="col-sm-offset-1">
                                <span name="steplog">Output: {!! $data[$i]['out'] !!}</span>
    						</div>
    					</div>
                        @else
                        <div class="row danger">
                            <div class="col-sm-12" >
    							{{ $data[$i]['date'] }}
                                <strong>{{ $data[$i]['host'] }}</strong>
                                {{ $data[$i]['stepstate'] }}
                                <span name="steplog">{{ $data[$i]['msg'] }}</span>
                                <strong>{{ $data[$i]['Elapse'] }}s</strong>
                            </div>
                            <div class="col-sm-offset-1">
                                <span name="steplog">Output: {!! $data[$i]['out'] !!}</span>
    						</div>
    					</div>
                        @endif
                    @endif
					@endfor

		</div>
	</div>
</div>