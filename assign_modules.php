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

class assign_modules {

	private static function assigned_modules() {
		$assigned_modules = array();
		foreach(db::query('select
				`model_modules`.`module_id`,
				`model_modules`.`model_id`,
				`modules`.`name` as `module_name`,
				`models`.`name` as `model_name`
			from `xepm_model_modules` as `model_modules`
			left join `xepm_modules` as `modules` on (
				`modules`.`module_id` = `model_modules`.`module_id`)
			left join `xepm_models` as `models` on (
				`models`.`model_id` = `model_modules`.`model_id`)') as $assigned_module) {
			$assigned_modules[] = $assigned_module;
		}
		uasort($assigned_modules, function($a, $b) {
			if (($result = strnatcasecmp($a->model_name, $b->model_name)) == 0) {
				$result = strnatcasecmp($a->module_name, $b->module_name);
			}
			return $result;
		});
		return $assigned_modules;
	}

	public static function render() {
		if (is_postback()) {
			db::begin_transaction();
			$models = array();
			if (is_array(($model_id_array = util::array_get('model_id', $_POST))) &&
				is_array(($module_id_array = util::array_get('module_id', $_POST)))) {
				db::query('delete from `xepm_model_modules`');
				foreach ($model_id_array as $i => &$model_id) {
					$model_id = intval(trim($model_id));
					$module_id = intval(trim(util::array_get($i, $module_id_array)));
					//NOTE: We don't have any index. So ignore the last posted line
					$model_id = db::query('insert ignore into `xepm_model_modules` (
							`model_id`,
							`module_id`
						) values (?, ?)',
						$model_id,
						$module_id
					)->insert_id;
				}
			} else {
				db::query('truncate table `xepm_model_modules`');
			}
			db::commit();
			util::redirect();
		}
		$tpl = new template('util_assign_modules.tpl');
		$tpl->models = xepmdb::models_with_brand_with_configuration();
		$tpl->modules = xepmdb::modules();
		$tpl->assigned_modules = self::assigned_modules();
		$tpl->render();
	}
}

?>
