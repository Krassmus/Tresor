<?php

class RenameTab extends Migration {
    
	public function up() {
        DBManager::get()->exec("
            CREATE TABLE IF NOT EXISTS `tresor_settings` (
              `seminar_id` varchar(32) NOT NULL,
              `tabname` varchar(128) NULL,
              `chdate` int NOT NULL,
              `mkdate` int NOT NULL,
              PRIMARY KEY (`seminar_id`)
            );
	    ");
    }
	
	public function down() {
        DBManager::get()->exec("
            DROP TABLE IF EXISTS `tresor_settings`
        ");
    }
}