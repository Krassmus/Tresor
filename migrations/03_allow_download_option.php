<?php

class AllowDownloadOption extends Migration {

	public function up() {
        Config::get()->create("TRESOR_ALLOW_DOWNLOAD", array(
            'value' => "1",
            'type' => "boolean",
            'range' => "global",
            'section' => "TRESOR",
            'description' => "Soll ein Download-Knopf angezeigt werden für entschlüsselte Dateien?"
        ));
    }

	public function down() {
        Config::get()->delete("TRESOR_ALLOW_DOWNLOAD");
    }
}
