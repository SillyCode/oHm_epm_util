<?php

// select * from xepm_lines;
// +---------+--------+---------+
// | line_id | ident  | name    |
// +---------+--------+---------+
// |     569 | 0      | Auto    |
// |     575 | 0      | Line 1  |
// |     570 | 1      | Auto    |
// |     576 | 1      | Line 1  |
// |     585 | 1      | Line 2  |
// |     578 | 10     | Line 10 |
// |     579 | 11     | Line 11 |
// |     580 | 12     | Line 12 |
//
// select * from xepm_model_lines;
// +----------+---------+-------+
// | model_id | line_id | index |
// +----------+---------+-------+
// |        1 |     569 |     1 |
// |        2 |     569 |     4 |

$brand = 'yealink';
$model = 'cp960';
$lines = 1;
$ident = 1;

echo "select `m`.`model_id` into @model_id from `xepm_models` as `m` left join `xepm_brands` as `b` using (`brand_id`) where `b`.`name` = '{$brand}' and `m`.`name` = '{$model}';\n";

for($i=1; $i<=$lines; $i++) {
	echo "select `line_id` into @line_id from `xepm_lines` where `name` = 'line {$i}' and ident = '{$i}';\n";
	echo "insert into `xepm_model_lines` values (@model_id, @line_id, $i);\n";
}
?>
