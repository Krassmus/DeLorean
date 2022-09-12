<tr>
    <td>
        <a href="<?= PluginEngine::getLink($plugin, array(), "view/object_history/".$version['item_id']) ?>"
           style="display: inline-block; width: 10px; height: 10px; background-color: #<?= $plugin->stringToColorCode($version['item_id']) ?>; border: thin solid black;"
           title="ID: <?= htmlReady($version['item_id']) ?>"></a>
    </td>
    <td>
        <a href="<?= PluginEngine::getLink($plugin, array('type' => $version['sorm_class']), "view/all") ?>">
            <?= htmlReady($version['sorm_class']) ?>
        </a>
    </td>
    <? if (!Config::get()->DELOREAN_ANONYMOUS_USERS) : ?>
    <td>
        <? if ($version['user_id'] && ($version['user_id'] !== 'cli')) : ?>
            <a href="<?= URLHelper::getLink( "dispatch.php/profile", ['username' => get_username($version['user_id'])]) ?>">
                <?= Avatar::getAvatar($version['user_id'])->getImageTag(Avatar::SMALL) ?>
            </a>
            <a href="<?= PluginEngine::getLink($plugin, array('user_id' => $version['user_id']), "view/all") ?>">
                <?= htmlReady(get_fullname($version['user_id'])) ?>
            </a>
        <? elseif ($version['user_id'] && ($version['user_id'] === 'cli')) : ?>
            <?= Icon::create($plugin->getPluginURL().'/assets/terminal.svg', Icon::ROLE_INFO)->asImg(25, ['class' => 'avatar-small']) ?>
            <a href="<?= PluginEngine::getLink($plugin, array('user_id' => $version['user_id']), "view/all") ?>">
                <?= htmlReady(_('Cronjob / CLI')) ?>
            </a>
        <? else : ?>
            <?= _("unbekannt") ?>
        <? endif ?>
    </td>
    <? endif ?>
    <td>
        <? if ($version['request_id']) : ?>
        <a href="<?= PluginEngine::getLink($plugin, array(), "view/request/".$version['request_id']) ?>" title="<?= _("Alle Änderungen derselben Aktion anzeigen.") ?>">
        <? endif ?>
            <?= date("d.m.Y H:i:s", $version['mkdate']) ?>
        <? if ($version['request_id']) : ?>
        </a>
        <? endif ?>
    </td>
    <td class="actions">
        <? if ($version['file_id'] && file_exists($version->getFilePath())) : ?>
            <?= round(filesize($version->getFilePath()) / 1024 / 1024, 2) ?> MB
        <? endif ?>
    </td>
    <td class="actions">
        <?= Icon::create($version['delete'] ? "trash" : ($version['create'] ? "star" : "edit"), "info")
                ->asImg(20, array('class' => "text-bottom", 'title' => $version['delete'] ? _("Objekt wurde gelöscht.") : ($version['create'] ? _("Objekt wurde erzeugt.") : _("Objekt wurde bearbeitet")))) ?>
        <a href="<?= PluginEngine::getLink($plugin, array(), "view/details/".$version->getId()) ?>" data-dialog="true">
            <?= Icon::create("info-circle", "clickable")->asImg(20, array('class' => "text-bottom")) ?>
        </a>
        <a href="<?= PluginEngine::getLink($plugin, array(), "view/undo/".$version->getId()) ?>" title="<?= _("Änderung rückgängig machen") ?>">
            <?= Icon::create("archive2", "clickable")->asImg(20, array('class' => "text-bottom")) ?>
        </a>
        <input type="checkbox" name="v[]" value="<?= htmlReady($version->getId()) ?>">
    </td>
</tr>
