<h1>OUIs</h1>
<form method="post">
	<p>Select Brand:
		<select name="brand_id" onchange="goto_brand(this)">{loop brands}<option value="{brand_id}"{selectedif brand_id brand_id}>{name}</option>{/loop brands}</select>
	</p>
	<table class="ouis">
		<thead>
			<tr>
				<th>MA-L</th>
				<th></th>
			</tr>
		</thead>
		<tbody>
			{loop ouis}<tr>
				<td><input type="text" name="oui[]" value="{oui}"/></td>
				<td><input type="button" value="Delete" onclick="delete_row(this)"/></td>
			</tr>{/loop ouis}
		</tbody>
		<tfoot>
			<tr>
				<td><input type="text" id="oui[]"/></td>
				<td><input type="button" value="Create" onclick="create_row(this)"/></td>
			</tr>
			<tr>
				<td><input type="submit" value="Apply"/></td>
				<td></td>
			</tr>
		</tfoot>
	</table>
</form>
