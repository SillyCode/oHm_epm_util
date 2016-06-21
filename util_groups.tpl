<h1>Groups</h1>
<form method="post">
	<table class="groups">
		<thead>
			<tr>
				<th>Group</th>
				<th>Value</th>
				<th></th>
			</tr>
		</thead>
		<tbody>
			{loop groups}<tr>
				<td><input type="text" name="name[]" value="{group_name}"/></td>
				<td><input type="text" name="ident[]" value="{ident}"/></td>
				<td>
					<input type="hidden" name="setting_group_id[]" value="{setting_group_id}"/>
					<input type="button" value="Delete" onclick="delete_row(this)"/>
				</td>
			</tr>{/loop groups}
		</tbody>
		<tfoot>
			<tr>
				<td><input type="text" id="name[]"/></td>
				<td><input type="text" name="ident[]"/></td>
				<td>
					<input type="hidden" id="setting_group_id[]" value=""/>
					<input type="button" value="Create" onclick="create_row(this)"/>
				</td>
			</tr>
			<tr>
				<td colspan="2"><input type="submit" value="Apply"/></td>
				<td></td>
			</tr>
		</tfoot>
	</table>
</form>
