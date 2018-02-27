<h1>Configuration Types</h1>
<form method="post">

	<p>Select Brand:
		<select name="brand_id" onchange="goto_brand(this)"><option values=''></option>{loop brands}<option value="{brand_id}"{selectedif brand_id brand_id}>{name}</option>{/loop brands}</select>
	</p>

	<table class="configuration_types">
		<thead>
			<tr>
				<th>Device</th>
				<th>Display Name</th>
				<th>Value</th>
				<th></th>
			</tr>
		</thead>
		<tbody>
			{loop configurations}<tr>
			<td><select name="model_id[]">{loop devices}<option value="{model_id}"{selectedif model_id model_id}>{brand_name} {model_name}</option>{/loop devices}</select></td>
			<td><input type="text" name="name[]" value="{name}"/></td>
			<td><input type="text" name="itent[]" value="{ident}"/></td>
			<td>
				<input type="hidden" name="type_id[]" value="{type_id}"/>
				<input type="button" value="Delete" onclick="delete_row(this)"/>
			</td>
		</tr>{/loop configurations}
		</tbody>
		<tfoot>
			<tr>
				<td><select id="model_id[]">{loop devices}<option value="{model_id}"{selectedif model_id model_id}>{brand_name} {model_name}</option>{/loop devices}</select></td>
				<td><input type="text" id="name[]"/></td>
				<td><input type="text" id="ident[]"/></td>
				<td>
					<input type="hidden" id="type_id[]" value=""/>
					<input type="button" value="Create" onclick="create_row(this)"/>
				</td>
			</tr>
			<tr>
				<td colspan="3"><input type="submit" value="Apply"/></td>
				<td></td>
			</tr>
		</tfoot>
	</table>
</form>
