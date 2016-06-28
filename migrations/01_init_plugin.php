<?php

class InitPlugin extends Migration {
    
	function description() {
        return 'initializes the database for this plugin';
    }

    public function up() {
        DBManager::get()->exec("
            CREATE TABLE IF NOT EXISTS `tresor_container` (
              `tresor_id` varchar(32) NOT NULL,
              `seminar_id` varchar(32) NOT NULL,
              `statusgruppe_id` varchar(32) NULL,
              `last_user_id` varchar(32) NOT NULL,
              `name` varchar(128) NOT NULL,
              `mime_type` varchar(64) NOT NULL DEFAULT 'text/plain',
              `encrypted_content` text NOT NULL,
              `chdate` int NOT NULL,
              `mkdate` int NOT NULL,
              PRIMARY KEY (`tresor_id`),
              KEY `seminar_id` (`seminar_id`),
              KEY `statusgruppe_id` (`statusgruppe_id`),
              KEY `last_user_id` (`last_user_id`)
            );
	    ");
        DBManager::get()->exec("
            CREATE TABLE `tresor_user_keys` (
              `user_id` varchar(32) NOT NULL,
              `synchronously_encrypted_private_key` text NOT NULL,
              `public_key` text NOT NULL,
              `mkdate` int(11) NOT NULL,
              `chdate` int(11) NOT NULL,
              PRIMARY KEY (`user_id`)
            );
        ");
    }
	
	public function down() {
        DBManager::get()->exec("
            DROP TABLE IF EXISTS `tresor_container`
        ");
        DBManager::get()->exec("
            DROP TABLE IF EXISTS `tresor_user_keys`
        ");
    }
}