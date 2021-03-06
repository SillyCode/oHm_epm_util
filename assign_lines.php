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

class assign_lines {

	public static function render() {
		if (is_postback()) {
			db::begin_transaction();
			$brand_id = post('brand_id');
			if($brand_id > 0) {
				//NOTE: We don't have any index. So ignore the last posted line
				db::query('delete
						`model_lines`
					from `xepm_model_lines` as `model_lines`
					left join `xepm_models` as `models` on (
						`models`.`model_id` = `model_lines`.`model_id`)
					left join `xepm_brands` as `brands` on (
						`brands`.`brand_id` = `models`.`brand_id`)
					where `brands`.`brand_id` = ?',
					$brand_id);
			}
			if (is_array(($model_id_array = util::array_get('model_id', $_POST))) &&
				is_array(($line_id_array = util::array_get('line_name_id', $_POST)))) {
				foreach ($model_id_array as $i => &$model_id) {
					$line_id = intval(trim(util::array_get($i, $line_id_array)));
					if ($model_id > 0) {
						$model_id = db::query('insert ignore into `xepm_model_lines` (
								`model_id`, `line_id`, `index`)
							select
								?,
								?,
								MAX(`index`)+1
							from `xepm_model_lines`',
							$model_id,
							$line_id
						)->insert_id;
					}
				}
			}
			db::commit();
			util::redirect();
		}
		$tpl = new template('util_assign_lines.tpl');
		$brands = xepmdb::brands();
		$brand_id = (count($brands) > 0) ? current($brands)->brand_id : 0;
		$brand_id = intval(trim(util::array_get('brand_id', $_GET, $brand_id)));
		$tpl->brand_id = $brand_id;
		$tpl->brands = $brands;
		$tpl->lines = xepmdb::lines();
		$tpl->models = xepmdb::model_by_brand($brand_id);
		uasort($tpl->models, function($a, $b) {
			return strnatcasecmp($a->model_name, $b->model_name);
		});
		$tpl->assigned_lines = xepmdb::model_lines_by_brand($brand_id);
		uasort($tpl->assigned_lines, function($a, $b) {
			if (($result = strnatcasecmp($a->model_id, $b->model_id)) == 0) {
				$result = strnatcasecmp($a->line_name, $b->line_name);
			}
			return $result;
		});
		$tpl->render();
	}
}

?>
