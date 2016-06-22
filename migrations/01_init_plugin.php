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
              `name` varchar(64) NOT NULL,
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
        DBManager::get()->exec("
            CREATE TABLE `tresor_group_keys` (
              `key_id` varchar(32) NOT NULL,
              `user_id` varchar(32) NOT NULL,
              `seminar_id` varchar(32) NOT NULL,
              `statusgruppe_id` varchar(32) DEFAULT NULL,
              `editor_user_id` varchar(32) NOT NULL,
              `encrypted_key` text NOT NULL,
              `chdate` int(11) NOT NULL,
              `mkdate` int(11) NOT NULL,
              PRIMARY KEY (`key_id`),
              KEY `statusgruppe_id` (`statusgruppe_id`),
              KEY `user_id` (`user_id`),
              KEY `seminar_id` (`seminar_id`),
              KEY `editor_user_id` (`editor_user_id`)
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
        DBManager::get()->exec("
            DROP TABLE IF EXISTS `tresor_group_keys`
        ");
    }
}