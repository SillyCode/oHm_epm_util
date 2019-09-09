<h1>Assign Lines</h1>
<form method="post">
	<p>Select Brand:
		<select name="brand_id" onchange="goto_brand(this)">{loop brands}<option value="{brand_id}"{selectedif brand_id brand_id}>{name}</option>{/loop brands}</select>
	</p>

	<table class="assign_lines">
		<thead>
			<tr>
				<th>Model</th>
				<th>Line and Ident</th>
				<th></th>
			</tr>
		</thead>
		<tbody>
			{loop assigned_lines}<tr>
				<td><select name="model_id[]">{loop models}<option value="{model_id}"{selectedif model_id model_id}>{model_name}</option>{/loop models}</select></td>
				<td><select name="line_name_id[]">{loop lines}<option value="{line_name_id}"{selectedif line_name_id line_name_id}>{name} (ident: {ident})</option>{/loop lines}</select></td>
				<td>
					<input type="button" value="Delete" onclick="delete_row(this)"/>
				</td>
			</tr>{/loop assigned_lines}
		</tbody>
		<tfoot>
			<tr>
				<td><select id="model_id[]">{loop models}<option value="{model_id}"{selectedif model_id model_id}>{model_name}</option>{/loop models}</select></td>
				<td><select id="line_name_id[]">{loop lines}<option value="{line_name_id}">{name} (ident: {ident})</option>{/loop lines}</select></td>
				<td>
					<input type="button" value="Assign" onclick="create_line_row(this)"/>
				</td>
			</tr>
			<tr>
				<td colspan="3"><input type="submit" value="Apply"/></td>
			</tr>
		</tfoot>
	</table>
</form>
