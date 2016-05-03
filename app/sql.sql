ALTER TABLE `users` ADD `description` TEXT NULL ;
ALTER TABLE `users` ADD `phone` VARCHAR(20) NULL ;
ALTER TABLE `users` ADD `picture` TEXT NULL ;
ALTER TABLE `collectibles` ADD `picture` TEXT NULL ;
ALTER TABLE `collectibles` CHANGE `category_id` `category_id` INT(10) NULL;
CREATE TABLE `messages` (
  `message_id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `reciever_id` int(11) NOT NULL,
  `text` text NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Klíče pro exportované tabulky
--

--
-- Klíče pro tabulku `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`message_id`);

--
-- AUTO_INCREMENT pro tabulky
--

--
-- AUTO_INCREMENT pro tabulku `messages`
--
ALTER TABLE `messages`
  MODIFY `message_id` int(11) NOT NULL AUTO_INCREMENT;
  
  ALTER TABLE `messages` ADD INDEX(`reciever_id`);
  ALTER TABLE `messages` ADD INDEX(`sender_id`);