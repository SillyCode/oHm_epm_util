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

class configuration_types {
	public static function render() {
		if (is_postback()) {
			db::begin_transaction();
			if (is_array(($name_array = util::array_get('name', $_POST))) &&
			is_array(($model_id_array = util::array_get('model_id', $_POST))) &&
			is_array(($type_id_array = util::array_get('type_id', $_POST))) &&
			is_array(($ident_id_array = util::array_get('ident', $_POST)))) {
				foreach ($type_id_array as $i => &$type_id) {
					$type_id = intval(trim($type_id));
					$model_id = intval(trim(util::array_get($i, $model_id_array)));
					$name = trim(util::array_get($i, $name_array));
					$value = trim(util::array_get($i, $ident_id_array));
					db::query('replace into `xepm_configuration_types`
						(`configuration_type_id`, `model_id`, `ident`, `name`) values (?, ?, ?, ?)',
						$type_id,
						$model_id,
						strtolower($value),
						ucfirst($name)
					);
				}
			}
			db::commit();
			util::redirect();
		}
		$brand_id = intval(trim(util::array_get('brand_id', $_GET)));
		$tpl = new template('util_configuration_types.tpl');
		$tpl->brands = xepmdb::brands();
		$tpl->brand_id = $brand_id;
		$tpl->devices = xepmdb::model_by_brand($brand_id);
		if($brand_id > 0 ) {
			$tpl->configurations = xepmdb::model_with_configuration($brand_id);
		}
		$tpl->render();
	}
}

?>
