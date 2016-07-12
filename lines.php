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

class lines {

	public static function render() {
		if (is_postback()) {
			db::begin_transaction();
			if (is_array(($name_array = util::array_get('name', $_POST))) &&
				is_array(($ident_array = util::array_get('ident', $_POST))) &&
				is_array(($line_id_array = util::array_get('line_name_id', $_POST)))) {
				foreach ($line_id_array as $i => &$line_id) {
					$line_id = intval(trim($line_id));
					$name = trim(util::array_get($i, $name_array));
					$ident = trim(util::array_get($i, $ident_array));
					if ($line_id > 0) {
						db::query('update `xepm_lines` set
								`name` = ?, `ident` = ?
							where `line_id` = ?',
							$name,
							$ident,
							$line_id);
					} else {
						$line_id = db::query('insert into `xepm_lines` (
								`name`, `ident`
							) values (?, ?)',
							$name, $ident)->insert_id;
					}
				}
				if (count($line_id_array) > 0) {
					db::delete_in('delete from `xepm_lines`
						where `line_id` not in (---)',
						$line_id_array);
				} else {
					db::query('truncate table `xepm_lines`');
				}
			} else {
				db::query('truncate table `xepm_lines`');
			}
			db::commit();
			util::redirect();
		}

		$tpl = new template('util_lines.tpl');
		$tpl->lines = xepmdb::lines();
		$tpl->render();
	}
}

?>
