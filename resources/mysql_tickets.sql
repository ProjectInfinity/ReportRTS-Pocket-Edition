CREATE TABLE IF NOT EXISTS `reportrts_tickets` (
  `id`  int(10) UNSIGNED NOT NULL AUTO_INCREMENT ,
  `userId`  int(10) UNSIGNED NOT NULL DEFAULT 0 ,
  `staffId`  int(10) UNSIGNED NULL DEFAULT 0 ,
  `timestamp`  int(10) UNSIGNED NOT NULL DEFAULT 0 ,
  `staffTime`  int(10) UNSIGNED NULL DEFAULT 0 ,
  `comment`  varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL ,
  `world`  varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' ,
  `x`  int(10) NOT NULL DEFAULT 0 ,
  `y`  int(10) NOT NULL DEFAULT 0 ,
  `z`  int(10) NOT NULL DEFAULT 0 ,
  `yaw`  smallint(6) NOT NULL DEFAULT 0 ,
  `pitch`  smallint(6) NOT NULL DEFAULT 0 ,
  `text`  varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' ,
  `status`  tinyint(1) UNSIGNED NOT NULL DEFAULT 0 ,
  `notified`  tinyint(1) UNSIGNED NOT NULL DEFAULT 0 ,
  PRIMARY KEY (`id`)
);