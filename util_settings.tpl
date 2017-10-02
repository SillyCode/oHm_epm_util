<h1>Settings</h1>
<div class="import">
	<form method="post" enctype="multipart/form-data">
		<label for="model_id">Import configuration for model</label>
		<select name="model_id_with_configuration_id"><option></option>{loop models}<option value="{model_id}-{configuration_type_id}">{brand_name} {model_name} - {configuration_name}</option>{/loop models}</select>
		<label for="file">using file:</label>
		<input type="file" name="file"/><br/>
		<input type="radio" name="filetype" value="xml"/>
		<label for="filetype">File is formatted as XML<br/>
			<input type="checkbox" name="indexed_collections" checked/>
			<label for="collection_attribute">Collections are indicated by attribute</label>
			<input type="text" name="collection_attribute" value="idx"/><br/>
			<label for="attribute_separator">Suffix attributes to setting names using separator</label>
			<input type="text" name="attribute_separator" value="."/>
		</label><br/>
		<input type="radio" name="filetype" value="ini" checked/>
		<label for="filetype">File is formatted as INI<br/>
			<input type="radio" name="has_parents" value="0"/>
			<label for="has_parents">Contains no sections<br/>
			<input type="checkbox" name="derive_parents"/>
			<label for="derive_parents">Derive section name from setting name prefix, using prefix separator
					<input type="text" name="prefix_separator" value="."/>
				</label></label><br/>
			<input type="radio" name="has_parents" value="1" checked/>
			<label for="has_parents">Has sections. Sections names are enclosed by
				<input type="text" name="parent_markers" value="[]"/>
			</label></label><br/>
			<label for="setting_markers">Setting names and default values are separated by</label>
			<input type="text" name="setting_markers" value="="/><br/>
			<label for="comment_markers">Comments start with</label>
			<input type="text" name="comment_markers" value="#">
		</label><br/>
		<input type="submit" value="Import"/>
	</form>
</div>
<form method="post">
	<p>Select Configuration Type:
		<select name="configuration_type_id" onchange="goto_configuration(this)"><option></option>{loop configurations}<option value="{configuration_type_id}"{selectedif configuration_type_id configuration_type_id}>{brand_name} {model_name} - {name}</option>{/loop configurations}</select>
	</p>
	<table class="settings">
		<thead>
			<tr>
				<th>Setting</th>
				<th>Default value</th>
				<th>Parent</th>
				<th>Group</th>
			</tr>
		</thead>
		<tbody>
			{loop settings}<tr>
				<td><input type="text" name="name[]" value="{name}"/></td>
				<td><input type="text" name="value[]" value='{value}'/></td>
				<td><span name="parent_id[]">{parent_name}</span></td>
				<td><span name="setting_group_id[]">{group_name}</span></td>
			</tr>{/loop settings}
		</tbody>
	</table>
</form>
