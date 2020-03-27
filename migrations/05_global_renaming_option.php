<?php

class GlobalRenamingOption extends Migration {

	public function up() {
        Config::get()->create("TRESOR_GLOBALS_NAME", array(
            'value' => "Tresor",
            'type' => "string",
            'range' => "global",
            'section' => "TRESOR",
            'description' => "Wie soll der Tresor im Stud.IP heißen?"
        ));
    }

	public function down() {
        Config::get()->delete("TRESOR_GLOBALS_NAME");
    }
}
