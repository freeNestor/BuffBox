<div class="container-fluid">
	<div class="row">
		<div class="col-md-12">
        @if ( $sum > 10)
                    <a class="btn btn-xs btn-link">There are {{ $sum or 0 }} Nodes</a>
                    @endif
			<table class="table table-hover table-condensed" style="width: 400px;">
				<tbody>
                <tr>
                    <td>
                    <input type="checkbox" checked="true" name="allnodesel" value="allnode"/>
                    </td>
                    <td>Select All</td><td></td><td></td>
                </tr>
                @foreach ( $noderes as $key => $node )
                    <tr>
					    <td>
							<input type="checkbox" checked="true" name="filterednodes" value="{{ $node->id }}"/>
						</td>
						<td>
							<span class="glyphicon glyphicon-hdd"></span> 
                            <span name="hostname">{{ $node->hostname }}</span>
						</td>
                        <td><span name="userexec">{{ $node->user }}</span></td>
						<td id="sel{{ $node->id }}">{{ $node->ipaddr }}:{{ $node->portok }}</td>
					</tr>
                @endforeach
				</tbody>
			</table>
		</div>
	</div>
</div>
<script>

</script>