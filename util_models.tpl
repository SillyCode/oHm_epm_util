<h1>Models</h1>
<form method="post">
	<table class="models">
		<thead>
			<tr>
				<th>Brand</th>
				<th>Model</th>
				<th>SIP Lines</th>
				<th>Modules</th>
				<th></th>
			</tr>
		</thead>
		<tbody>
			{loop models}<tr>
				<td><select name="brand_id[]">{loop brands}<option value="{brand_id}"{selectedif brand_id brand_id}>{name}</option>{/loop brands}</select></td>
				<td><input type="text" name="name[]" value="{name}"/></td>
				<td><input type="text" name="sip_lines[]" value="{sip_lines}"/></td>
				<td><input type="text" name="exp_modules[]" value="{exp_modules}"/></td>
				<td><input type="hidden" name="model_id[]" value="{model_id}"/><input type="button" value="Delete" onclick="delete_row(this)"/></td>
			</tr>{/loop models}
		</tbody>
		<tfoot>
			<tr>
				<td><select id="brand_id[]">{loop brands}<option value="{brand_id}">{name}</option>{/loop brands}</select></td>
				<td><input type="text" id="name[]"/></td>
				<td><input type="text" id="sip_lines[]"/></td>
				<td><input type="text" id="exp_modules[]"/></td>
				<td><input type="hidden" id="model_id[]" value=""/><input type="button" value="Create" onclick="create_row(this)"/></td>
			</tr>
			<tr>
				<td colspan="6"><input type="submit" value="Apply"/></td>
				<td></td>
			</tr>
		</tfoot>
	</table>
</form>
