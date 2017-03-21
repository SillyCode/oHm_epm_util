<h1>Models</h1>
<form method="post">
	<p>Select Brand:
		<select name="brand_id" onchange="goto_brand(this)">{loop brands}<option value="{brand_id}"{selectedif brand_id brand_id}>{name}</option>{/loop brands}</select>
	</p>
	<table class="models">
		<thead>
			<tr>
				<th>Model</th>
				<th>SIP Lines</th>
				<th>Modules</th>
				<th></th>
			</tr>
		</thead>
		<tbody>
			{loop models}<tr>
				<td><input type="text" name="name[]" value="{model_name}"/></td>
				<td><input type="text" name="sip_lines[]" value="{sip_lines}"/></td>
				<td><input type="text" name="exp_modules[]" value="{exp_modules}"/></td>
				<td><input type="hidden" name="model_id[]" value="{model_id}"/><input type="button" value="Delete" onclick="delete_row(this)"/></td>
			</tr>{/loop models}
		</tbody>
		<tfoot>
			<tr>
				<td><input type="text" id="name[]"/></td>
				<td><input type="text" id="sip_lines[]"/></td>
				<td><input type="text" id="exp_modules[]"/></td>
				<td><input type="hidden" id="model_id[]" value=""/><input type="button" value="Create" onclick="create_row(this)"/></td>
			</tr>
			<tr>
				<td colspan="4"><input type="submit" value="Apply"/></td>
			</tr>
		</tfoot>
	</table>
</form>
