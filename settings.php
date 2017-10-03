<?php

/*
 * Copyright 2014, Xorcom Inc.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 675 Mass Ave, Cambridge, MA 02139, USA.
 */

use ombutel\db;

class settings {
	private static function strip_comments($comment_markers, $input) {
		return trim(preg_replace('/' . $comment_markers . '.*$/m', null, $input));
	}

	private static function derive_parents($prefix_separator, $settings) {
		$parents = array();
		foreach ($settings as $name => $value) {
			if (($offset = strpos($name, $prefix_separator)) !== false) {
				$parents[substr($name, 0, $offset)][$name] = $value;
			}
		}
		return $parents;
	}

	private static function parse_settings($setting_markers, $input) {
		$setting_pattern = '/^\s*([^' . $setting_markers . ']+?)\s*[' . $setting_markers . ']\s*([^' . $setting_markers . ']*?)\s*$/m';
		$settings = array();
		if (($count = preg_match_all($setting_pattern, trim($input), $m)) > 0) {
			list(, $names, $values) = $m;
			for ($i = 0; $i < $count; $i++) {
				$settings[$names[$i]] = $values[$i];
			}
		}
		return $settings;
	}

	private static function parse_parents($parent_markers, $setting_markers, $input) {
		$parents = array();
		$parent_pattern = '/^\s*[' . $parent_markers . ']\s*([^' . $parent_markers . ']+?)\s*[' . $parent_markers . ']/m';
		$header = true;
		$parent_name = null;
		foreach (preg_split($parent_pattern, $input, null, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE) as $block) {
			if ($header) {
				$parent_name = $block;
				$parents[$parent_name] = null;
			} else if ($parent_name !== null) {
				$parents[$parent_name] = self::parse_settings($setting_markers, $block);
			}
			$header = !$header;
		}
		return $parents;
	}

	private static function parse_xml($indexed_collections, $collection_attribute, $attribute_separator, $filename) {
		$parents = array();
		foreach (simplexml_load_file($filename) as $parent_name => $parent) {
			$parents[$parent_name] = array();
			foreach ($parent as $setting_name => $setting) {
				$attributes = $setting->attributes();
				if ($indexed_collections && isset($attributes[$collection_attribute])) {
					$index = (string)$attributes[$collection_attribute];
					unset($attributes[$collection_attribute]);
					$parents[$parent_name][$setting_name . $index] = (string) $setting;
					foreach ($attributes as $attribute_name => $attribute_value) {
						$attribute_name = $setting_name . $index . $attribute_separator . $attribute_name;
						$parents[$parent_name][$attribute_name] = (string) $attribute_value;
					}
				} else {
					$parents[$parent_name][$setting_name] = (string) $setting;
					foreach ($attributes as $attribute_name => $attribute_value) {
						$attribute_name = $setting_name . $attribute_separator . $attribute_name;
						$parents[$parent_name][$attribute_name] = (string) $attribute_value;
					}
				}
			}
		}
		return $parents;
	}

	private static function parse_escene_xml($indexed_collections, $collection_attribute, $attribute_separator, $filename) {
		$parents = array();
		foreach (simplexml_load_file($filename) as $parent_name => $parent) {
			foreach ($parent as $setting_name => $setting) {
				$attributes = $setting->attributes();
				if ($indexed_collections && isset($attributes[$collection_attribute])) {
					$index = (string)$attributes[$collection_attribute];
					foreach ($attributes as $attribute_name => $attribute_value) {
						$attribute_name = $setting_name . '/' . $index . '/' . $attribute_separator . $attribute_name;
						$parents[$parent_name . '/' . $setting_name][$attribute_name] = (string) $attribute_value;
					}
				} else {
					foreach ($attributes as $attribute_name => $attribute_value) {
						$attribute_name = $attribute_separator . $attribute_name;
						$parents[$parent_name . '/' . $setting_name][$attribute_name] = (string) $attribute_value;
					}
				}
			}
		}
		return $parents;
	}

	private static function import_xml_node(&$parents, $node, $path) {
		$node_name = $node->getName();
		$value = ($node->count() == 0) ? trim($node->__toString()) : null;
		$path .= empty($path) ? $node_name : '/' . $node_name;
		foreach ($node->attributes() as $attribute_name => $attribute_value) {
			$parents["$path/$attribute_name"] = (string) $attribute_value;
		}
		foreach ($node as $child) {
			self::import_xml_node($parents, $child, $path);
		}
	}

	private static function parse_polycom_xml($attribute_separator, $filename) {
		$xml = simplexml_load_string(file_get_contents($filename), 'SimpleXMLElement', LIBXML_NOERROR | LIBXML_NOWARNING);
		$parents = array();
		self::import_xml_node($parents, $xml, null);
		//Retreive super parent name
		$parents_organized = array();
		foreach($parents as $parent_name => $parent) {
			$parent_names = explode('/',$parent_name, 3);
			$parents_organized[$parent_names[1]][$parent_names[2]] = $parent;
		}
		return $parents_organized;
	}

	public static function get_device_desc($model_id) {
		foreach(db::query('select
				`brands`.`name` as `brand_name`,
				`models`.`name` as `model_name`
			from `xepm_models` as `models`
			left join `xepm_brands` as `brands` on (
				`brands`.`brand_id` = `models`.`brand_id`)
			where `model_id` = ?',
			$model_id) as $device) {
			return $device;
		}
		return null;
	}

	public static function render() {
		if (is_postback()) {
			if ((trim(util::array_get('model_id_with_configuration_id', $_POST))) &&
				is_array($file = util::array_get('file', $_FILES))) {
				$error = intval(trim(util::array_get('error', $file)));
				$filename = trim(util::array_get('tmp_name', $file));
				$filetype = trim(util::array_get('filetype', $_POST));
				$has_parents = intval(trim(util::array_get('has_parents', $_POST)));
				$prefix_separator = trim(util::array_get('prefix_separator', $_POST));
				$derive_parents = isset($_POST['derive_parents']);
				$parent_markers = preg_quote(trim(util::array_get('parent_markers', $_POST)));
				$setting_markers = preg_quote(trim(util::array_get('setting_markers', $_POST)));
				$comment_markers = preg_quote(trim(util::array_get('comment_markers', $_POST)));
				$indexed_collections = isset($_POST['indexed_collections']);
				$collection_attribute = trim(util::array_get('collection_attribute', $_POST));
				$attribute_separator = trim(util::array_get('attribute_separator', $_POST));
				$model_id_with_configuration_id = preg_split('/-/', trim(util::array_get('model_id_with_configuration_id', $_POST)));
				$model_id = $model_id_with_configuration_id[0];
				$configuration_type_id = $model_id_with_configuration_id[1];
				if ($error == 0 && is_uploaded_file($filename)) {
					$parents = array();
					if ($model_id <= 0) { throw new Exception("Model does not exists"); }
					if ($configuration_type_id <= 0) { throw new Exception("Configuration type does not exists"); }
						$device = self::get_device_desc($model_id);
					if ($filetype == "ini") {
						$input = self::strip_comments($comment_markers, file_get_contents($filename));
						unlink($filename);
						if ($has_parents) {
							$parents = self::parse_parents($parent_markers, $setting_markers, $input);
						} else if ($derive_parents) {
							$parents = self::derive_parents($prefix_separator, self::parse_settings($setting_markers, $input));
							$settings = self::parse_settings($setting_markers, $input);
						} else {
							$settings = self::parse_settings($setting_markers, $input);
						}
					} else if ($filetype == "xml") {


						switch(strtolower($device->brand_name)) {
							case "polycom":
								$parents = self::parse_polycom_xml($attribute_separator, $filename);
							break;
							case "escene":
								$parents = self::parse_escene_xml($indexed_collections, $collection_attribute, $attribute_separator, $filename);
							break;
							default:
								$parents = self::parse_xml($indexed_collections, $collection_attribute, $attribute_separator, $filename);
							break;
						}
					}
					// Truncate everything if there is something
					db::query('delete from `xepm_settings` where `configuration_type_id` = ?', $configuration_type_id);

					$groups = array();
					foreach(db::query('select
							`setting_type_id`,
							`ident`
						from `xepm_setting_types`') as $row) {
						$groups[$row->ident] = $row->setting_type_id;
					}

					if (count($parents) > 0) {
						db::begin_transaction();
						// Insert parent name
						foreach($parents as $parent_name => $parent) {
							$group_id = $groups['other']; // generic

							switch(strtolower($device->brand_name)) {
								case "escene": //NOTE: For Escene
									if(preg_match('/programbuttons/i',$parent_name)) { //linekey
										$group_id = $groups['line'];
									} elseif(preg_match('/extension(\d+)?/i', $parent_name)) {
										$group_id = $groups['exp_buttons'];
									} elseif(preg_match('/^hotlines\//i', $parent_name)) { //dss buttons
										$group_id = $groups['dss'];;
									}
								break;
							}

							if(strlen($parent_name) > 0) {
								// Insert parent into settings
								$parent_id = db::query('insert ignore into `xepm_settings` (
										`parent_id`,
										`configuration_type_id`,
										`setting_type_id`,
										`name`,
										`default`
									) values (?,?,?,?,?)
										on duplicate key update
										`default` = values(`default`)',
									null,
									$configuration_type_id,
									$group_id,
									$parent_name,
									null)->insert_id;
								if($parent_id <= 0) { // If insert ignore activated - no parent_id returned
									foreach(query('select
											`setting_id`
										from `xepm_settings` where
											`configuration_type_id` = ? and
											`name` = ? and
											`parent_id` is null',
										$configuration_type_id,
										$parent_name) as $setting) {
										$parent_id = $setting->setting_id;
									}
								}

								foreach ($parent as $setting_name => $setting_value) {
									$group_id = $groups['other'];
									switch(strtolower($device->brand_name)) {
										case "yealink": //NOTE: For Yealink
										case "xorcom";
											if(preg_match('/linekey\.\d+\./', $setting_name)) {
												$group_id = $groups['line'];
											} else if(preg_match('/memorykey\.\d+\./', $setting_name)) {
												$group_id = $groups['dss'];
											} else if(preg_match('/expansion_module\.\d+\./', $setting_name)) {
												$group_id = $groups['exp_buttons'];
											}

											// NOTE: For Yealink T65P
											if(preg_match('/linekey\.\d+\./', $setting_name)) {
												$group_id = $groups['line'];
											} else if(preg_match('/memory\d+\.\w+/', $setting_name)) {
												$group_id = $groups['dss'];
											} else if(preg_match('/key\d+\.\w+/', $setting_name)) {
												$group_id = $groups['exp_buttons'];
											}
										break;
										case "fanvil": //NOTE: For Fanvil
											if(preg_match('/linekey\.\d+\./', $setting_name)) {
												$group_id = $groups['line'];
											} else if(preg_match('/Fkey\d+\s+\w+/', $setting_name)) {
												$group_id = $groups['dss'];
											} else if(preg_match('/expansion_module\.\d+\./', $setting_name) || (preg_match('/exKey\d+\s+\w+/', $setting_name))) {
												$group_id = $groups['exp_buttons'];
											}
										break;
										case "vtech": //NOTE: For Vtech
											if(preg_match('/pfk\.\d+\./', $setting_name)) {
												$group_id = $groups['dss'];
											}
										break;
										case "polycom": //NOTE: For Polycom
// 											var_Dump($setting_name);
											if(preg_match('/^attendant\.resourceList\.(\d+)/', $setting_name, $m)) {
												$group_id = $groups['line']; //Line buttons
												list(, $button_index) = $m;
// 												var_dump($button_index);
												switch($button_index) {
													case $device->model_name == "VVX600":
														if(intval($button_index) > 16) {
															$group_id = $groups['exp_buttons'];
														}
													break;
													case $device->model_name == "VVX400":
													case $device->model_name == "VVX410":
													case $device->model_name == "VVX500":
														if(intval($button_index) > 12) {
															$group_id = $groups['exp_buttons'];
														}
													break;
													case $device->model_name == "VVX1500D":
													case $device->model_name == "VVX1500":
													case $device->model_name == "VVX310":
													case $device->model_name == "VVX300":
													case $device->model_name == "SoundPoint IP670":
													case $device->model_name == "SoundPoint IP650":
														if(intval($button_index) > 6) {
															$group_id = $groups['exp_buttons'];
														}
													break;
													case $device->model_name == "SoundPoint IP560":
													case $device->model_name == "SoundPoint IP550":
														if(intval($button_index) > 4) {
															$group_id = $groups['exp_buttons'];
														}
													break;
													case $device->model_name == "SoundPoint IP450":
														if(intval($button_index) > 3) {
															$group_id = $groups['exp_buttons'];
														}
													break;
													case $device->model_name == "SoundPoint IP320":
													case $device->model_name == "SoundPoint IP321":
													case $device->model_name == "SoundPoint IP330":
													case $device->model_name == "SoundPoint IP331":
													case $device->model_name == "SoundPoint IP335":
														if(intval($button_index) > 2) {
															$group_id = $groups['exp_buttons'];
														}
													break;
												}
											}
										break;
										case "d-link": //NOTE: For D-Link
											if(preg_match('/linekey\.\d+\./', $setting_name)) {
												$group_id = $groups['line'];
											} else if(preg_match('/Fkey\d+\s+\w+/', $setting_name)) {
												$group_id = $groups['dss'];
											} else if(preg_match('/expansion_module\.\d+\./', $setting_name) || (preg_match('/exKey\d+\s+\w+/', $setting_name))) {
												$group_id = $groups['exp_buttons'];
											}
										break;
									}

									db::query('insert ignore into `xepm_settings` (
										`parent_id`,
										`configuration_type_id`,
										`setting_type_id`,
										`name`,
										`default`
									) values (?,?,?,?,?)
										on duplicate key update
										`default` = values(`default`)',
									$parent_id,
									$configuration_type_id,
									$group_id,
									$setting_name,
									trim($setting_value, '"'));
								}
							}
						}
// 						die();
						db::commit();
					} else { // No parents - all parents are sudo parents
						// Insert generic parent into settings
						$parent_id = db::query('insert ignore into `xepm_settings` (
								`parent_id`,
								`configuration_type_id`,
								`setting_type_id`,
								`name`,
								`default`
							) values (?,?,?,?,?)
								on duplicate key update
								`default` = values(`default`)',
							null,
							$configuration_type_id,
							$group_id,
							"Generic",
							null)->insert_id;
						if($parent_id <= 0) { // If insert ignore activated - no parent_id returned
							foreach(db::query('select
								`setting_id`
								from `xepm_settings` as `settings`
								left join `xepm_setting_names` as `names` on (
									`names`.`setting_name_id` = `settings`.`setting_name_id`)
								where `settings`.`configuration_type_id` = ? and
								`names`.`setting_name_id` = ? and
								`settings`.`parent_id` is null',
								$configuration_type_id,
								"Generic") as $setting) {
								$parent_id = $setting->setting_id;
							}
						}
						// Insert all the settings
						foreach($settings as $setting_name => $setting_value) {
							$group_id = $groups['other'];
							switch(strtolower($device->brand_name)) {
								case "grandstream": {
									switch($device->model_name) {
										case "GXP2135":
										case "GXP2170": {
											$buttons = [];
											for($i = 1, $j = 1362; $i < 7; $i++) { // buttons 1-6
												$buttons[] = 'P' . ++$j;
												$buttons[] = 'P' . ++$j;
												$buttons[] = 'P' . ($j + 101);
												$buttons[] = 'P' . ($j + 102);
											}
											for($i = 23800, $j = 7; $j <= 48; $j++) { // buttons 7-48
												$buttons[] = 'P' . $i++;
												$buttons[] = 'P' . $i++;
												$buttons[] = 'P' . $i++;
												$buttons[] = 'P' . $i++;
											}
											if(in_array($setting_name, $buttons)) {
												$group_id = $groups['dss'];
												continue;
											}
											$exp_buttons = [];
											for($i = 23000, $j = 1; $j <= 160; $i += 5, $j++) { // buttons 1-160
												$k = $i;
												$exp_buttons[] = 'P' . $i;
												$exp_buttons[] = 'P' . ++$k;
												$exp_buttons[] = 'P' . ++$k;
												$exp_buttons[] = 'P' . ++$k;
											}
											if(in_array($setting_name, $exp_buttons)) {
												$group_id = $groups['exp_buttons'];
											}
											break;
										}
									}
									break;
								}
							}
							db::query('insert ignore into `xepm_settings` (
								`parent_id`,
								`configuration_type_id`,
								`setting_type_id`,
								`name`,
								`default`
							) values (?,?,?,?,?)
								on duplicate key update
								`default` = values(`default`)',
							$parent_id,
							$configuration_type_id,
							$group_id,
							$setting_name,
							trim($setting_value, '"'))->insert_id;
						}
					}
				}
			}
			util::redirect();
		}
		$tpl = new template('util_settings.tpl');
		$tpl->configurations = xepmdb::configuration_types();
		$tpl->groups = xepmdb::provision_groups();
		$configuration_id = trim(util::array_get('configuration_type_id', $_GET));
		$tpl->parents = xepmdb::provision_parents($configuration_id);
		$tpl->configuration_type_id = $configuration_id;
		$tpl->settings = xepmdb::provision_organize_settings($tpl->parents, xepmdb::settings_by_sections_model(intval($configuration_id)));
		$tpl->models = xepmdb::models_with_brand_with_configuration();
		$tpl->render();
	}
}

?>
