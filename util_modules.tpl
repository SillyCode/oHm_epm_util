<h1>Modules</h1>
<form method="post">
	<table class="modules">
		<thead>
			<tr>
				<th>Module</th>
				<th>Buttons</th>
				<th></th>
			</tr>
		</thead>
		<tbody>
			{loop modules}<tr>
				<td><input type="text" name="name[]" value="{name}"/></td>
				<td><input type="text" name="button_count[]" value="{button_count}"/></td>
				<td>
					<input type="hidden" name="module_id[]" value="{module_id}"/>
					<input type="button" value="Delete" onclick="delete_row(this)"/>
				</td>
			</tr>{/loop modules}
		</tbody>
		<tfoot>
			<tr>
				<td><input type="text" id="name[]"/></td>
				<td><input type="text" id="button_count[]"/></td>
				<td>
					<input type="hidden" id="module_id[]" value=""/>
					<input type="button" value="Create" onclick="create_row(this)"/>
				</td>
			</tr>
			<tr>
				<td colspan="3"><input type="submit" value="Apply"/></td>
			</tr>
		</tfoot>
	</table>
</form>
