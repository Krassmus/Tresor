<? if ($donothing) {
    return;
} ?>
<? if (!Request::isDialog()) : ?>
    <?= $this->render_partial("container/_user_key.php") ?>
<?php endif ?>

<div style="display: none;" id="encryption_error">
    <?= MessageBox::error(dgettext("tresor","Das Dokument kann leider nicht entschlüsselt werden. Vermutlich muss es erst von jemand anderem erneut gespeichert werden, damit Sie das lesen können.")) ?>
</div>

<form action="<?= PluginEngine::getLink($plugin, array(), "container/store/".$container->getId()) ?>"
      method="post"
      class="default <?= $container['mime_type'] && $container['mime_type'] !== "text/plain" ? "file" : "text" ?>"
      id="content_form">

    <h2><?= htmlReady($container['name']) ?></h2>

    <input type="hidden" name="encrypted_content" id="encrypted_content" value="<?= htmlReady($container['encrypted_content']) ?>">

    <input type="hidden" name="mime_type" value="<?= htmlReady($container['mime_type']) ?>">
    <input type="hidden" name="name" value="<?= htmlReady($container['name']) ?>">

    <textarea id="content"
              class="onlytext"
              style="width: calc(100% - 20px); height: calc(70vh);"
              readonly
              placeholder="<?= $container['encrypted_content'] ? dgettext("tresor","Es wird entschlüsselt ...") : dgettext("tresor","Text eingeben ...") ?>"></textarea>


    <div class="onlyfile">

        <? if (stripos($container['mime_type'], "video/") === 0) : ?>
            <video id="tresor_decrypted_preview"
                   autoplay
                   controls
                   <?= !Config::get()->TRESOR_ALLOW_DOWNLOAD ? 'controlsList="nodownload"' : "" ?>
                   class="<?= !Config::get()->TRESOR_ALLOW_DOWNLOAD ? "prevent_download" : "" ?>"
                    <?= !Config::get()->TRESOR_ALLOW_DOWNLOAD ? ' oncontextmenu="return false;"' : '' ?>
                   src="">
            </video>
        <? elseif (stripos($container['mime_type'], "audio/") === 0) : ?>
            <audio id="tresor_decrypted_preview"
                   autoplay
                   controls
                   class="<?= !Config::get()->TRESOR_ALLOW_DOWNLOAD ? "prevent_download" : "" ?>"
                    <?= !Config::get()->TRESOR_ALLOW_DOWNLOAD ? ' oncontextmenu="return false;"' : '' ?>
                   src="">
            </audio>
        <? elseif (stripos($container['mime_type'], "image/") === 0) : ?>
            <img id="tresor_decrypted_preview"
                   class="<?= !Config::get()->TRESOR_ALLOW_DOWNLOAD ? "prevent_download" : "" ?>"
                    <?= !Config::get()->TRESOR_ALLOW_DOWNLOAD ? ' oncontextmenu="return false;"' : '' ?>
                   src="">
        <? elseif (Config::get()->TRESOR_ALLOW_DOWNLOAD || $container['mime_type'] === "application/pdf") : ?>
            <iframe src="<?= !Config::get()->TRESOR_ALLOW_DOWNLOAD && $container['mime_type'] === "application/pdf" ? PluginEngine::getLink($plugin, array('file' => ""), "container/pdfviewer") : "" ?>"
                    id="tresor_decrypted_preview"
                    class="<?= !Config::get()->TRESOR_ALLOW_DOWNLOAD ? "prevent_download" : "" ?>"></iframe>
        <? endif ?>
        <? if (Config::get()->TRESOR_ALLOW_DOWNLOAD) : ?>
        <div style="margin-top: 20px;">
            <a href="#" onClick="STUDIP.Tresor.downloadFile(); return false;">
                <?= Icon::create("download", "clickable")->asImg("30px", ['class' => "text-bottom"]) ?>
                <?= dgettext("tresor","Datei herunterladen") ?>
            </a>
        </div>
        <? endif ?>

        <?php /* PDFViewerApplication.open(new Uint8Array(xhr.response)) */ ?>
    </div>


    <script>
        jQuery(STUDIP.Tresor.decryptContainer);
    </script>

</form>

