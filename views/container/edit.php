<? if ($donothing) {
    return;
} ?>
<? if (!Request::isDialog()) : ?>
    <?= $this->render_partial("container/_user_key.php") ?>
<?php endif ?>

<div style="display: none;" id="encryption_error">
    <?= MessageBox::error(_("Das Dokument kann leider nicht entschlüsselt werden. Vermutlich muss es erst von jemand anderem erneut gespeichert werden, damit Sie das lesen können.")) ?>
</div>

<form action="<?= PluginEngine::getLink($plugin, array(), "container/store/".$container->getId()) ?>"
      method="post"
      class="default <?= $container['mime_type'] && $container['mime_type'] !== "text/plain" ? "file" : "text" ?>"
      id="content_form">

    <label>
        <?= _("Name") ?>
        <input type="text" name="name" value="<?= htmlReady($container['name']) ?>">
    </label>

    <input type="hidden" name="encrypted_content" id="encrypted_content" value="<?= htmlReady($container['encrypted_content']) ?>">

    <input type="hidden" name="mime_type" value="<?= htmlReady($container['mime_type']) ?>">
    <input type="hidden" name="container_id" value="<?= htmlReady($container->getId()) ?>">

    <textarea id="content"
              class="onlytext"
              style="width: calc(100% - 20px); height: calc(70vh);"
              placeholder="<?= $container['encrypted_content'] ? _("Es wird entschlüsselt ...") : _("Text eingeben ...") ?>"></textarea>


    <script>
        STUDIP.Tresor.keyToEncryptFor = <?= json_encode(array_map(
            function ($key) { return $key['public_key']; },
            $foreign_user_public_keys
        )) ?>;
        jQuery(STUDIP.Tresor.decryptContainer);
    </script>

</form>


<div data-dialog-button>

    <input type="file" id="file_upload" onChange="STUDIP.Tresor.selectFile(event);" style="display: none;">


    <? if ($container['mime_type'] && $container['mime_type'] !== "text/plain") : ?>
        <?= \Studip\LinkButton::create(_("Datei hochladen"), "#", array('onClick' => "jQuery('#file_upload').trigger('click'); return false;")) ?>
    <? endif ?>

    <?= \Studip\LinkButton::create(_("Speichern"), "#", array('onClick' => "STUDIP.Tresor.storeContainer(); return false;")) ?>
</div>

