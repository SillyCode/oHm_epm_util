<h1>Brands</h1>
<form method="post">
	<table class="brands">
		<thead>
			<tr>
				<th>Brand</th>
				<th></th>
			</tr>
		</thead>
		<tbody>
			{loop brands}<tr>
				<td><input type="text" name="name[]" value="{name}"/></td>
				<td>
					<input type="hidden" name="brand_id[]" value="{brand_id}"/>
					<input type="button" value="Delete" onclick="delete_row(this)"/>
				</td>
			</tr>{/loop brands}
		</tbody>
		<tfoot>
			<tr>
				<td><input type="text" id="name[]"/></td>
				<td>
					<input type="hidden" id="brand_id[]" value=""/>
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
