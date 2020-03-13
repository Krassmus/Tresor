<?= $this->render_partial("container/_user_key.php") ?>

<div style="display: none;" id="encryption_error">
    <?= MessageBox::error(_("Das Dokument kann leider nicht entschlüsselt werden. Vermutlich muss es erst von jemand anderem erneut gespeichert werden, damit Sie das lesen können.")) ?>
</div>

<form action="<?= PluginEngine::getLink($plugin, array(), "container/store/".$container->getId()) ?>"
      method="post"
      class="default <?= $container['mime_type'] && $container['mime_type'] !== "text/plain" ? "file" : "text" ?>"
      id="content_form">

    <input type="text" name="name" value="<?= htmlReady($container['name']) ?>">

    <input type="hidden" name="encrypted_content" id="encrypted_content" value="<?= htmlReady($container['encrypted_content']) ?>">

    <input type="hidden" name="mime_type" value="text/plain" value="<?= htmlReady($container['mime_type']) ?>">

    <textarea id="content"
              class="onlytext"
              style="width: calc(100% - 20px); height: calc(70vh);"
              placeholder="<?= $container['encrypted_content'] ? _("Es wird entschlüsselt ...") : _("Text eingeben ...") ?>"></textarea>

    <div class="onlyfile">
        <div style="margin-top: 20px;">
            <a href="#" onClick="STUDIP.Tresor.downloadFile(); return false;">
                <?= Icon::create("download", "clickable")->asImg("30px", ['class' => "text-bottom"]) ?>
                <?= _("Datei herunterladen") ?>
            </a>
        </div>
    </div>

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
    <? else : ?>
        <? if ($container->isNew()) : ?>
            <?= \Studip\LinkButton::create(_("Text eingeben"), "#", array('onClick' => "STUDIP.Tresor.selectText(); return false;")) ?>
        <? endif ?>
    <? endif ?>

    <?= \Studip\LinkButton::create(_("Speichern"), "#", array('onClick' => "STUDIP.Tresor.storeContainer(); return false;")) ?>
</div>

