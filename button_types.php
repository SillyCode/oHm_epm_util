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

class button_types {

	public static function parses_file($filename) {
		$content = file_get_contents($filename);
		$button_types = array();
		foreach(explode("\n", $content) as $line) {
			if(strpos($line,':') != false) {
				list($ident, $name) = explode(":", $line);
				$button_types[trim($ident)] = trim($name);
			}
		}
		return $button_types;
	}

	public static function insert_button_types($button_types) {
		foreach($button_types as $ident => $name) {
			db::begin_transaction();
			db::query('insert ignore into `xepm_button_types` (
				`ident`,
				`name`
			) values (?, ?)',
			$ident,
			$name);
			db::commit();
		}
	}

	public static function render() {
		if (is_postback()) {
			if(is_array($file = util::array_get('file', $_FILES))) {
				$error = intval(trim(util::array_get('error', $file)));
				$filename = trim(util::array_get('tmp_name', $file));
			}
			$brand_id = util::array_get('brand_id', $_POST);
			$model_id = util::array_get('model_id', $_POST);
			if($file['size'] > 0 && $error == 0) { //there is a file uploaded
				$categories = util::array_get('categories', $_POST);
				if($categories == null) {
					util::redirect();
				}

				$button_types = self::parses_file($filename);

				// make sure button types exists
				self::insert_button_types($button_types);

				foreach($categories as $category_id) {
					db::begin_transaction();
					$index = 0;
					foreach($button_types as $ident => $name) {
						db::query('insert into `xepm_model_button_types` (
							`model_id`,
							`category_id`,
							`button_type_id`,
							`index`) select
								?,
								?,
								`button_type_id`,
								?
							from `xepm_button_types`
							where `ident` = ? and
							`name` = ?',
							$model_id,
							$category_id,
							$index,
							$ident,
							$name);
						$index++;
					}
					db::commit();
				}
			} else { //edit
				$button_types = array();
				$names = util::array_get('names', $_POST);
				$values = util::array_get('values', $_POST);
				$categories = util::array_get('categories', $_POST);
				if(is_array($names) && is_array($values) && is_array($categories)) {
					$button_types = array_combine($values, $names);

					// make sure button types exists
					self::insert_button_types($button_types);

					//clean model records
					db::query('delete
						from `xepm_model_button_types`
						where `model_id` = ?',
						$model_id);

					db::begin_transaction();
					foreach($names as $i => $name) {
						$index = 0;
						$name = trim(util::array_get($i, $name_array));
						$ident = intval(trim(util::array_get($i, $values)));
						$category_id = intval(trim(util::array_get($i, $categories)));

						db::query('insert into `xepm_model_button_types` (
							`model_id`,
							`category_id`,
							`button_type_id`,
							`index`) select
								?,
								?,
								`button_type_id`,
								?
							from `xepm_button_types`
							where `ident` = ? and
							`name` = ?',
							$model_id,
							$category_id,
							$index,
							$ident,
							$name);
						$index++;
					}
					db::commit();
				}
			}
			util::redirect();
		}

		$brands = xepmdb::brands();

		$tpl = new template('util_button_types.tpl');
		$tpl->categories = xepmdb::categories();
		$brand_id = (count($brands) > 0) ? current($brands)->brand_id : 0;
		$brand_id = intval(trim(util::array_get('brand_id', $_GET, $brand_id)));
		$tpl->models = xepmdb::model_by_brand($brand_id);
		$tpl->brand_id = $brand_id;
		$tpl->brands = $brands;
		$model_id = intval(trim(util::array_get('model_id', $_GET)));
		$tpl->model_id = $model_id;
		$tpl->model_button_type = xepmdb::model_button_type($model_id);
		$tpl->render();
	}
}

?>
