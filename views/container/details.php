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

    <h2><?= htmlReady($container['name']) ?></h2>

    <input type="hidden" name="encrypted_content" id="encrypted_content" value="<?= htmlReady($container['encrypted_content']) ?>">

    <input type="hidden" name="mime_type" value="<?= htmlReady($container['mime_type']) ?>">

    <textarea id="content"
              class="onlytext"
              style="width: calc(100% - 20px); height: calc(70vh);"
              placeholder="<?= $container['encrypted_content'] ? _("Es wird entschlüsselt ...") : _("Text eingeben ...") ?>"></textarea>


    <div class="onlyfile">

        <iframe src="<?= !Config::get()->TRESOR_ALLOW_DOWNLOAD && $container['mime_type'] === "application/pdf" ? PluginEngine::getLink($plugin, array('file' => ""), "container/pdfviewer") : "" ?>"
                id="tresor_decrypted_preview"
                class="<?= !Config::get()->TRESOR_ALLOW_DOWNLOAD ? "prevent_download" : "" ?>"></iframe>

        <? if (Config::get()->TRESOR_ALLOW_DOWNLOAD) : ?>
        <div style="margin-top: 20px;">
            <a href="#" onClick="STUDIP.Tresor.downloadFile(); return false;">
                <?= Icon::create("download", "clickable")->asImg("30px", ['class' => "text-bottom"]) ?>
                <?= _("Datei herunterladen") ?>
            </a>
        </div>
        <? endif ?>

        <?php /* PDFViewerApplication.open(new Uint8Array(xhr.response)) */ ?>
    </div>


    <script>
        jQuery(STUDIP.Tresor.decryptContainer);
    </script>

</form>

