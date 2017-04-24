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

class xepmdb {

	// Returns a list of timezones for a brand
	public static function brand_timezones($brand_id) {
		static $timezones = array();
		if (!array_key_exists($brand_id, $timezones)) {
			$brand_timezones = array();
			if ($brand_id > 0) {
				foreach (db::query('select
						`timezone_id`,
						`offset`,
						`name`,
						`value`
					from `xepm_timezones`
					where `brand_id` = ?', $brand_id) as $timezone) {
					$timezone->display_offset = sprintf(
							'%+d:%02d',
							floor($timezone->offset / 3600),
							floor(60 * ((abs($timezone->offset) % 3600) / 3600)));
					$brand_timezones[intval($timezone->timezone_id)] = $timezone;
				}
				uasort($brand_timezones, function($a, $b) {
					if ($a->offset == $b->offset) {
						return strnatcasecmp($a->name, $b->name);
					}
					return $a->offset - $b->offset;
				});
			}
			$timezones[$brand_id] = $brand_timezones;
		}
		return $timezones[$brand_id];
	}

	// Returns a list of all brands
	public static function brands() {
		static $brands = null;
		if ($brands === null) {
			$brands = array();
			foreach (db::query('select
					`brand_id`,
					`name`
				from `xepm_brands`') as $brand) {
				$brands[intval($brand->brand_id)] = $brand;
			}
			uasort($brands, function($a, $b) {
				return strnatcasecmp($a->name, $b->name);
			});
		}
		return $brands;
	}

	// Returns a list of all models
	public static function models() {
		static $models = null;
		if ($models === null) {
			$models = array();
			foreach (db::query('select
					`models`.`model_id`,
					`models`.`brand_id`,
					`models`.`sip_lines`,
					`models`.`max_modules` as `exp_modules`,
					`models`.`name`,
					`brands`.`name` as `brand_name`
				from `xepm_models` as `models`
				left join `xepm_brands` as `brands` on (
					`brands`.`brand_id` = `models`.`brand_id`)') as $model) {
				$models[intval($model->model_id)] = $model;
			}
			uasort($models, function($a, $b) {
				if (($result = strnatcasecmp($a->brand_name, $b->brand_name)) == 0) {
					$result = strnatcasecmp($a->name, $b->name);
				}
				return $result;
			});
		}
		return $models;
	}

	// Returns a list of all models with brand information
	public static function models_with_brand_with_configuration() {
		static $models = null;
		if ($models === null) {
			$models = array();
			foreach (db::query('select
					`models`.`model_id`,
					`brands`.`name` as `brand_name`,
					`models`.`name` as `model_name`,
					`types`.`configuration_type_id`,
					`types`.`name` as `configuration_name`
				from `xepm_models` as `models`
				left join `xepm_brands` as `brands` on (
					`brands`.`brand_id` = `models`.`brand_id`)
				left join `xepm_configuration_types` as `types` on (
					`types`.`model_id` = `models`.`model_id`)') as $model) {
				$models[] = $model;
			}
			uasort($models, function($a, $b) {
				if (($result = strnatcasecmp($a->brand_name, $b->brand_name)) == 0) {
					$result = strnatcasecmp($a->model_name, $b->model_name);
				}
				return $result;
			});
		}
		return $models;
	}

	// Returns a list of ouis for a brand
	public static function brand_ouis($brand_id) {
		static $ouis = array();
		if (!array_key_exists($brand_id, $ouis)) {
			$brand_ouis = array();
			if ($brand_id > 0) {
				foreach (db::query('select
						lower(hex(`value`)) as `oui`
					from `xepm_ouis`
					where `brand_id` = ?', $brand_id) as $oui) {
					$brand_ouis[] = $oui->oui;
				}
			}
			$ouis[$brand_id] = $brand_ouis;
		}
		return $ouis[$brand_id];
	}

	public static function settings_by_sections_model($configuration_type_id) {
		$configuration_settings = array();
		if($configuration_type_id > 0) {
			foreach(db::query('select
					`settings`.`setting_id`,
					`settings`.`name`,
					`settings`.`default` as `value`,
					`types`.`name` as `group_name`,
					`types`.`setting_type_id` as `setting_group_id`,
					`settings`.`parent_id`
				from `xepm_settings` as `settings`
				left join `xepm_setting_types` as `types` on (
					`types`.`setting_type_id` = `settings`.`setting_type_id`)
				where `settings`.`configuration_type_id` = ?', $configuration_type_id) as $settings) {
					$configuration_settings[intval($settings->setting_id)] = $settings;
			}
			// Retrieve parent name
			foreach($configuration_settings as $setting_id => $setting) {
				if(!empty($setting->parent_id)) {
					foreach(db::query('select
							`name` as `parent_name`
						from `xepm_settings`
						where `setting_id` = ?', $setting->parent_id) as $parent_name) {
						$configuration_settings[$setting_id]->parent_name = $parent_name->parent_name;
					}
				}
			}

			uasort($configuration_settings, function ($a, $b) {
				return strnatcasecmp($a->name, $b->name);
			});
		}
		return $configuration_settings;
	}

	// Returns a list of groups allowing to sort by group name or group id
	public static function provision_groups() {
		$setting_groups = array();
		foreach(db::query('select
				`setting_type_id` as `setting_group_id`,
				`name` as `group_name`,
				`ident`
			from `xepm_setting_types`') as $group) {
			$setting_groups[intval($group->setting_group_id)] = $group;
		}
		uasort($setting_groups, function($a, $b) {
			return strnatcasecmp($a->group_name, $b->group_name);;
		});
		return $setting_groups;
	}

	// Removes the parent from the settings in settings tab in provisioning
	public static function provision_organize_settings($parents, $settings) {
		foreach($parents as $parent_id => $parent) {
			unset($settings[$parent_id]);
		}
		return $settings;
	}

	// Returns a list of all perents
	public static function provision_parents($configuration_id) {
		$parents = array();
		foreach(db::query('select
				`setting_id` as `parent_id`,
				`name` as `parent_name`
			from `xepm_settings`
			where `configuration_type_id` = ? and
				`parent_id` is null', $configuration_id) as $parent) {
			$parents[intval($parent->parent_id)] = $parent;
		}
		return $parents;
	}

	// Returns a list of all modules
	public static function modules() {
		static $modules = null;
		if ($modules === null) {
			$modules = array();
			foreach (db::query('select
					`module_id`,
					`button_count`,
					`name`
				from `xepm_modules`') as $module) {
				$modules[intval($module->module_id)] = $module;
			}
			uasort($modules, function($a, $b) {
				if (($result = strnatcasecmp($a->name, $b->name)) == 0) {
					$result = strnatcasecmp($a->name, $b->name);
				}
				return $result;
			});
		}
		return $modules;
	}

	// Returns a list of all lines
	public static function lines() {
		static $lines = null;
		if ($lines === null) {
			$lines = array();
			foreach (db::query('select
					`line_id` as `line_name_id`,
					`name`,
					`ident`
				from `xepm_lines`') as $line) {
				$lines[intval($line->line_name_id)] = $line;
			}
			uasort($lines, function($a, $b) {
				return strnatcasecmp($a->name, $b->name);
			});
		}
		return $lines;
	}

	public static function configuration_types() {
		static $configuration_types = null;
		if ($configuration_types === null) {
			$configuration_types = array();
			foreach(db::query('select
					`types`.`configuration_type_id`,
					`types`.`model_id`,
					`types`.`name`,
					`models`.`name` as `model_name`,
					`brands`.`name` as `brand_name`
				from `xepm_configuration_types` as `types`
				left join `xepm_models` as `models` on (
					`models`.`model_id` = `types`.`model_id`)
				left join `xepm_brands` as `brands` on (
					`brands`.`brand_id` = `models`.`brand_id`)') as $configuration_type) {
				$configuration_types[intval($configuration_type->configuration_type_id)] = $configuration_type;
			}
			uasort($configuration_types, function($a, $b) {
				if (($result = strnatcasecmp($a->brand_name, $b->brand_name)) == 0) {
					if (($result = strnatcasecmp($a->model_name, $b->model_name)) == 0) {
						$result = strnatcasecmp($a->name, $b->name);
					}
				}
				return $result;
			});
		}
		return $configuration_types;
	}

	// Returns list of brand models with their configuration name
	public static function model_with_configuration($brand_id = null) {
		if($brand_id != null) {
			foreach(db::query('select
					`types`.`configuration_type_id` as `type_id`,
					`types`.`name`,
					`types`.`ident`,
					`models`.`model_id`,
					`models`.`name` as `model_name`,
					`brands`.`brand_id`,
					`brands`.`name` as `brand_name`
				from `xepm_configuration_types` as `types`
				left join `xepm_models` as `models` on (
					`models`.`model_id` = `types`.`model_id`)
				left join `xepm_brands` as `brands` on (
					`brands`.`brand_id` = `models`.`brand_id`)
				where `brands`.`brand_id` = ?',
				$brand_id) as $model_configuration_type) {
				$model_configuration_types[intval($model_configuration_type->type_id)] = $model_configuration_type;
			}
		} else {
			foreach(db::query('select
					`types`.`configuration_type_id` as `type_id`,
					`types`.`name`,
					`types`.`ident`,
					`models`.`model_id`,
					`models`.`name` as `model_name`,
					`brands`.`brand_id`,
					`brands`.`name` as `brand_name`
				from `xepm_configuration_types` as `types`
				left join `xepm_models` as `models` on (
					`models`.`model_id` = `types`.`model_id`)
				left join `xepm_brands` as `brands` on (
					`brands`.`brand_id` = `models`.`brand_id`)') as $model_configuration_type) {
				$model_configuration_types[intval($model_configuration_type->type_id)] = $model_configuration_type;
			}
		}
		uasort($model_configuration_types, function($a, $b) {
			if (($result = strnatcasecmp($a->brand_name, $b->brand_name)) == 0) {
				if (($result = strnatcasecmp($a->model_name, $b->model_name)) == 0) {
					$result = strnatcasecmp($a->name, $b->name);
				}
			}
			return $result;
		});
		return $model_configuration_types;
	}

	// Retuns list of brands along with their models
	public static function brands_with_models() {
		foreach(db::query('select
				`brands`.`name` as `brand_name`,
				`brands`.`brand_id`,
				`models`.`name` as `model_name`,
				`models`.`model_id`
			from `xepm_models` as `models`
			left join `xepm_brands` as `brands` on (
			`brands`.`brand_id` = `models`.`brand_id`)') as $devices) {
				$device_array[$devices->model_id] = $devices;
			}
			uasort($device_array, function($a, $b) {
				if (($result = strnatcasecmp($a->brand_name, $b->brand_name)) == 0) {
						$result = strnatcasecmp($a->model_name, $b->model_name);
				}
				return $result;
			});
		return $device_array;
	}

	public static function model_lines_by_brand($brand_id) {
		$model_lines = array();
		foreach(db::query('select
				`model_lines`.`model_id`,
				`model_lines`.`line_id` as `line_name_id`,
				`model_lines`.`index`,
				`lines`.`name` as `line_name`
			from `xepm_model_lines` as `model_lines`
			left join `xepm_models` as `models` on (
				`models`.`model_id` = `model_lines`.`model_id`)
			left join `xepm_brands` as `brands` on (
				`brands`.`brand_id` = `models`.`brand_id`)
			left join `xepm_lines` as `lines` on (
				`lines`.`line_id` = `model_lines`.`line_id`)
			where `brands`.`brand_id` = ?', $brand_id) as $lines) {
				$model_lines[] = $lines;
			}
		return $model_lines;
	}

	public static function model_by_brand($brand_id) {
		$models = array();
		foreach(db::query('select
				`models`.`model_id`,
				`models`.`brand_id`,
				`models`.`sip_lines`,
				`models`.`max_modules` as `exp_modules`,
				`models`.`name` as `model_name`,
				`brands`.`name` as `brand_name`
			from `xepm_models` as `models`
			left join `xepm_brands` as `brands` on (
				`brands`.`brand_id` = `models`.`brand_id`)
			where `brands`.`brand_id` = ?', $brand_id) as $rows) {
				$models[] = $rows;
			}
		return $models;
	}

	public static function categories() {
		$categories = array();
		foreach(db::query('select
				`category_id`,
				`ident`,
				`name`
			from `xepm_button_categories`') as $row) {
				$categories[] = $row;
			}
		return $categories;
	}

	public static function model_button_type($model_id) {
		$model_button_types = array();
		foreach(db::query('select
				`model_button_type`.`model_id`,
				`button_types`.`name`,
				`button_types`.`ident`,
				`categories`.`name` as `category`,
				`categories`.`category_id`
			from `xepm_model_button_types` as `model_button_type`
			left join `xepm_button_categories` as `categories` on (
				`categories`.`category_id` = `model_button_type`.`category_id`)
			left join `xepm_button_types` as `button_types` on (
				`button_types`.`button_type_id` = `model_button_type`.`button_type_id`)
			where `model_id` = ?',
			$model_id
			) as $row) {
				$model_button_types[] = $row;
			}
		return $model_button_types;
	}
}

?>
