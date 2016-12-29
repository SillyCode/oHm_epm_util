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

class groups {

	public static function render() {
		if (is_postback()) {
			db::begin_transaction();
			if (is_array(($name_array = util::array_get('name', $_POST))) &&
				is_array(($ident_array = util::array_get('ident', $_POST))) &&
				is_array(($group_id_array = util::array_get('setting_group_id', $_POST)))) {
				foreach ($group_id_array as $i => $group_id) {
					$group_id = intval(trim($group_id));
					$name = trim(util::array_get($i, $name_array));
					$ident = trim(util::array_get($i, $ident_array));
					if ($group_id > 0) {
						db::query('update `xepm_setting_types` set
							`name` = ?, `ident` = ?
						where `setting_type_id` = ?',
						$name,
						$ident,
						$group_id);
					} else {
						$new_group_id = db::query('insert into `xepm_setting_types` (
							`name`, `ident`)
							values (?,?)', $name, $ident)->insert_id;
						array_push($group_id_array, $new_group_id);
					}
				}
				if (count($group_id_array) > 0) {
					db::query('delete from `xepm_setting_types`
						where `setting_type_id` not in (---)',
						$group_id_array);
				} else {
					db::query('truncate table `xepm_setting_types`');
				}
			} else {
				db::query('truncate table `xepm_setting_types`');
			}
			db::commit();
			util::redirect();
		}
		$tpl = new template('util_groups.tpl');
		$tpl->groups = xepmdb::provision_groups();
		$tpl->render();
	}
}

?>
