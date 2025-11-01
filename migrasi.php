<?php
echo "CREATE TABLE `tbl_data2` (
  `data_id` bigint(20) UNSIGNED NOT NULL,
  `date` date NOT NULL,
  `time` time NOT NULL,
  `line_id` int(11) NOT NULL,
  `model_id` int(11) NOT NULL,
  `file_id` int(11) NOT NULL,
  `header_id` int(11) NOT NULL,";
for ($i = 191; $i <= 380; $i++) {
    echo " data_$i varchar(70) DEFAULT NULL,";
}
echo ");";
// echo "CREATE TABLE tbl_header2 (
//     header_id INT PRIMARY KEY AUTO_INCREMENT,
//     file_id INT,";
// for ($i = 191; $i <= 380; $i++) {
//     echo "column_$i VARCHAR(70),";
// }
// echo ");";
