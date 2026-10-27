<?php

class AddTresorDataPath extends Migration {

	public function up() {
        Config::get()->create("TRESOR_DATA_PATH", array(
            'value' => "",
            'type' => "string",
            'range' => "global",
            'section' => "TRESOR",
            'description' => "Wenn der Tresor seine Daten nicht im SAtud.IP-Data-Verzeichnis ablegen soll, sondern an einem alternativen Ort, dann wird der hier eingetragen."
        ));
    }

	public function down() {
        Config::get()->delete("TRESOR_DATA_PATH");
    }
}
