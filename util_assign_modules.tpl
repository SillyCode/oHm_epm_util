<h1>Assign Modules</h1>
<form method="post">
	<table class="assign_modules">
		<thead>
			<tr>
				<th>Model</th>
				<th>Module</th>
				<th></th>
			</tr>
		</thead>
		<tbody>
			{loop assigned_modules}<tr>
				<td><select name="model_id[]">{loop models}<option value="{model_id}"{selectedif model_id model_id}>{brand_name} {model_name}</option>{/loop models}</select></td>
				<td><select name="module_id[]">{loop modules}<option value="{module_id}"{selectedif module_id module_id}>{name}</option>{/loop modules}</select></td>
				<td>
					<input type="button" value="Delete" onclick="delete_row(this)"/>
				</td>
			</tr>{/loop assigned_modules}
		</tbody>
		<tfoot>
			<tr>
				<td><select id="model_id[]">{loop models}<option value="{model_id}"{selectedif model_id model_id}>{brand_name} {model_name}</option>{/loop models}</select></td>
				<td><select id="module_id[]">{loop modules}<option value="{module_id}">{name}</option>{/loop modules}</select></td>
				<td>
					<input type="button" value="Assign" onclick="create_row(this)"/>
				</td>
			</tr>
			<tr>
				<td colspan="2"><input type="submit" value="Apply"/></td>
				<td></td>
			</tr>
		</tfoot>
	</table>
</form>
