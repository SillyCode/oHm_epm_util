<h1>Lines</h1>
<form method="post">
	<table class="lines">
		<thead>
			<tr>
				<th>Line</th>
				<th></th>
			</tr>
		</thead>
		<tbody>
			{loop lines}<tr>
				<td><input type="text" name="name[]" value="{name}"/></td>
				<td><input type="text" name="ident[]" value="{ident}"/></td>
				<td>
					<input type="hidden" name="line_name_id[]" value="{line_name_id}"/>
					<input type="button" value="Delete" onclick="delete_row(this)"/>
				</td>
			</tr>{/loop lines}
		</tbody>
		<tfoot>
			<tr>
				<td><input type="text" id="name[]"/></td>
				<td><input type="text" name="ident[]"/></td>
				<td>
					<input type="hidden" id="line_name_id[]" value=""/>
					<input type="button" value="Create" onclick="create_row(this)"/>
				</td>
			</tr>
			<tr>
				<td><input type="submit" value="Apply"/></td>
				<td></td>
			</tr>
		</tfoot>
	</table>
</form>
