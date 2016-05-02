ALTER TABLE `users` ADD `description` TEXT NULL ;
ALTER TABLE `users` ADD `phone` VARCHAR(20) NULL ;
ALTER TABLE `users` ADD `picture` TEXT NULL ;
ALTER TABLE `collectibles` ADD `picture` TEXT NULL ;
ALTER TABLE `collectibles` CHANGE `category_id` `category_id` INT(10) NULL; 