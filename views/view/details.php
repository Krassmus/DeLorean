<? $previous = $version->previousVersion() ?>
<? if ($version['json_data'] == null) : ?>
    <?= MessageBox::info(_("Das Objekt wurde in dieser Aktion gelöscht.")) ?>
<? else : ?>
    <? if (!$previous) : ?>
        <?= MessageBox::info(_("Es wurde zu dieser Version keine Vorgängerversion gefunden. Vermutlich wurde das Objekt in dieser Aktion neu erstellt. Wenn Sie diese Aktion rückgängig machen, wird das Objekt gelöscht werden.")) ?>
    <? endif ?>
    <div class="sorm_version_info">
        <table class="default">
            <tbody>
            <? foreach ($version['json_data'] as $key => $value) : ?>
                <? if ($key !== "id") : ?>
                <tr class="<?= isset($previous['json_data'][$key]) && $previous['json_data'][$key] === $value ? "unchanged" : "changed" ?>">
                    <td><?= htmlReady($key) ?></td>
                    <td>
                        <div><?= htmlReady($value) ?></div>
                        <?
                        switch ($key) {
                            case "user_id":
                                echo '<a href="'.URLHelper::getLink("dispatch.php/profile", array('username' => get_username($value))).'">'.Avatar::getAvatar($value)->getImageTag(Avatar::SMALL).htmlReady(get_fullname($value)).'</a>';
                                break;
                        }
                        ?>
                    </td>
                </tr>
                <? endif ?>
                <? endforeach ?>
            </tbody>
        </table>
        <? if ($version['original_file_path']) : ?>
            <div class="<?= $previous['file_id'] === $version['file_id'] ? "unchanged" : "changed" ?>" style="text-align: center;">
                <? $mimetype = explode("/", mime_content_type($version->getFilePath())) ?>
                <? if ($mimetype[0] === "image") : ?>
                    <img src="<?= PluginEngine::getLink($plugin, array(), "view/file/".$version->getId()) ?>" style="max-width: 100%;">
                <? else : ?>
                    <a href="<?= PluginEngine::getLink($plugin, array(), "view/file/".$version->getId()) ?>">
                        <?= Assets::img("icons/20/blue/download", array('class' => "text-bottom")) ?>
                        <?= _("Herunterladen") ?>
                    </a>
                <? endif ?>
            </div>
        <? endif ?>
    </div>
<? endif ?>
<style>
    .sorm_version_info .unchanged {
        opacity: 0.5;
    }
</style>