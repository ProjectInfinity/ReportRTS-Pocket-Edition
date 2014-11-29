CREATE TABLE `reportrts_users` (
`uid`  int(10) UNSIGNED NOT NULL AUTO_INCREMENT ,
`name`  varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL ,
`banned`  tinyint(1) UNSIGNED NULL DEFAULT 0 ,
PRIMARY KEY (`uid`)
);