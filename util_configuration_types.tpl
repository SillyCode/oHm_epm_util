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
			</tr>
		</thead>
		<tbody>
			{loop configurations}
			<tr>
				<td>
					{model_name}
					<input type="hidden" name="type_id[]" value="{type_id}"/>
					<input type='hidden' name="model_id[]" value="{model_id}"/>
				</td>
				<td><input type="text" name="name[]" value="{name}"/></td>
				<td><input type="text" name="itent[]" value="{ident}"/></td>
			</tr>
			{/loop configurations}
		</tbody>
		<tfoot>
			<tr>
				<td colspan="2"><input type="submit" value="Apply"/></td>
				<td></td>
			</tr>
		</tfoot>
	</table>
</form>
