<?php

class RestrictFiletypesOption extends Migration {

	public function up() {
        Config::get()->create("TRESOR_ACCEPT_FILETYPES", array(
            'value' => "",
            'type' => "string",
            'range' => "global",
            'section' => "TRESOR",
            'description' => "Welche Dateitypen sind nur erlaubt? Leer lassen für alle, 'none' für gar keine oder input[accept] Angaben wie 'image/*,.pdf'."
        ));
    }

	public function down() {
        Config::get()->delete("TRESOR_ACCEPT_FILETYPES");
    }
}
