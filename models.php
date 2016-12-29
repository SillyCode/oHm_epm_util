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

class models {

	public static function render() {
		if (is_postback()) {
			$iax2_lines = 0;
			$dss_buttons = 0;
			db::begin_transaction();
			if (is_array(($brand_id_array = util::array_get('brand_id', $_POST))) &&
				is_array(($name_array = util::array_get('name', $_POST))) &&
				is_array(($sip_lines_array = util::array_get('sip_lines', $_POST))) &&
				is_array(($max_modules_array = util::array_get('exp_modules', $_POST))) &&
				is_array(($model_id_array = util::array_get('model_id', $_POST)))) {
				foreach ($model_id_array as $i => &$model_id) {
					$model_id = intval(trim($model_id));
					$brand_id = intval(trim(util::array_get($i, $brand_id_array)));
					$name = trim(util::array_get($i, $name_array));
					$sip_lines = intval(trim(util::array_get($i, $sip_lines_array)));
					$max_modules = intval(trim(util::array_get($i, $max_modules_array)));
					if ($model_id > 0) {
						db::query('update `xepm_models` set
								`brand_id` = ?,
								`sip_lines` = ?,
								`iax2_lines` = ?,
								`max_modules` = ?,
								`dss_buttons` = ?
							where `model_id` = ?',
							$brand_id,
							$sip_lines,
							$iax2_lines,
							$max_modules,
							$dss_buttons,
							$model_id);
					} else {
						$model_id = db::query('insert into `xepm_models` (
								`brand_id`,
								`sip_lines`,
								`iax2_lines`,
								`max_modules`,
								`dss_buttons`,
								`name`
							) values (?, ?, ?, ?, ?, ?)',
							$brand_id,
							$sip_lines,
							$iax2_lines,
							$max_modules,
							$dss_buttons,
							$name)->insert_id;
					}
				}
				if (count($model_id_array) > 0) {
					db::query('delete from `xepm_models`
						where `model_id` not in(---)',
						$model_id_array);
				} else {
					db::query('truncate table `xepm_models`');
				}
			} else {
				db::query('truncate table `xepm_models`');
			}
			db::commit();
		}
		$tpl = new template('util_models.tpl');
		$tpl->models = xepmdb::models();
		$tpl->brands = xepmdb::brands();
		$tpl->render();
	}
}

?>
