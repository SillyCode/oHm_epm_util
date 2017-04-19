<h1>Button Types</h1>
<form method="post" enctype="multipart/form-data">
	<p>Import button types</p>
	<p>Select Brand:
		<select name="brand_id" onchange="goto_brand(this)">{loop brands}<option value="{brand_id}"{selectedif brand_id brand_id}>{name}</option>{/loop brands}</select>
	</p>

	<p>Select Model:
		<select name="model_id[]" onchange="goto_model_button_types(this)"><option></option>{loop models}<option value="{model_id}"{selectedif model_id model_id}>{brand_name} {model_name}</option>{/loop models}</select>
	</p>
	<p>Select file to import. Button types must be in the format [value]:[name]</p>
	<label for="file">Button types file:</label>
	<input type="file" name="file"/><br/>
	<label for="categories">Check the categories if buttons are applicable for them:</label>
	{loop categories}
		</br><input type="checkbox" name="categories[]" value="{category_id}">{name}</input>
	{/loop categories}

	</br>
	</br>
	<input type="submit" value="Apply"/>
	</br>
	</br>
	<table class="button_types">
		<thead>
			<tr>
				<th>Name</th>
				<th>Value</th>
				<th>Category</th>
				<th>Action</th>
			</tr>
		</thead>
		<tbody>
			{loop model_button_type}
			<tr>
				<td><input type="text" name="names[]" value='{name}'/></td>
				<td><input type="text" name="values[]" value='{ident}'/></td>
				<td><select id="categories[]" name="categories[]">
				{loop categories}
				<option value="{category_id}"{selectedif category_id category_id}>{name}</option>
				{/loop categories}
				</select></td>
				<td><input type="button" value="Delete" onclick="delete_row(this)"/></td>
			</tr>
			{/loop model_button_type}
		</tbody>
		<tfoot>
			<tr>
				<td><input type="text" id="names[]" value=''/></td>
				<td><input type="text" id="values[]" value=''/></td>
				<td><select id="categories[]">
				{loop categories}
				<option value="{category_id}">{name}</option>
				{/loop categories}
				</select></td>
				<td>
					<input type="button" value="Assign" onclick="create_row(this)"/>
				</td>
			</tr>
			<tr>
				<td><input type="submit" value="Apply"/></td>
			</tr>
		</tfoot>
	</table>
</form>
