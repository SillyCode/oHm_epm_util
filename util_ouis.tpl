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
			<tr>
				<td><textarea name="oui" rows="{ouis_rows}">{ouis}</textarea>
			</tr>
		</tbody>
		<tfoot>
			<tr>
				<td><input type="submit" value="Apply"/></td>
				<td></td>
			</tr>
		</tfoot>
	</table>
</form>
