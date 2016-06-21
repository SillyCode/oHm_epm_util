<h1>Timezones</h1>
<form method="post">
	<p>Select Brand:
		<select name="brand_id" onchange="goto_brand(this)">{loop brands}<option value="{brand_id}"{selectedif brand_id brand_id}>{name}</option>{/loop brands}</select>
	</p>
	<table class="timezones">
		<thead>
			<tr>
				<th>Timezone</th>
				<th>Offset (in seconds)</th>
				<th>Value</th>
				<th></th>
			</tr>
		</thead>
		<tbody>
			{loop timezones}<tr>
				<td><input type="text" name="name[]" value="{name}"/></td>
				<td><input type="text" name="offset[]" value="{offset}"/></td>
				<td><input type="text" name="value[]" value="{value}"/></td>
				<td>
					<input type="hidden" name="timezone_id[]" value="{timezone_id}"/>
					<input type="button" value="Delete" onclick="delete_row(this)"/>
				</td>
			</tr>{/loop timezones}
		</tbody>
		<tfoot>
			<tr>
				<td><input type="text" id="name[]"/></td>
				<td><input type="text" id="offset[]"/></td>
				<td><input type="text" id="value[]"/></td>
				<td>
					<input type="hidden" id="timezone_id[]" value=""/>
					<input type="button" value="Create" onclick="create_row(this)"/>
				</td>
			</tr>
			<tr>
				<td colspan="3"><input type="submit" value="Apply"/></td>
				<td></td>
			</tr>
		</tfoot>
	</table>
</form>
