<tr>
    <td>
        <a href="<?= PluginEngine::getLink($plugin, array(), "view/object_history/".$version['item_id']) ?>"
           style="display: inline-block; width: 10px; height: 10px; background-color: #<?= $plugin->stringToColorCode($version['item_id']) ?>; border: thin solid black;"
           title="ID: <?= htmlReady($version['item_id']) ?>"></a>
    </td>
    <td>
        <a href="<?= PluginEngine::getLink($plugin, array(), "view/type/".$version['sorm_class']) ?>">
            <?= htmlReady($version['sorm_class']) ?>
        </a>
    </td>
    <? if (!Config::get()->DELOREAN_ANONYMOUS_USERS) : ?>
    <td>
        <? if ($version['user_id']) : ?>
            <a href="<?= PluginEngine::getLink($plugin, array(), "view/by/".$version['user_id']) ?>">
                <?= Avatar::getAvatar($version['user_id'])->getImageTag(Avatar::SMALL) ?>
                <?= htmlReady(get_fullname($version['user_id'])) ?>
            </a>
        <? else : ?>
            <?= _("unbekannt") ?>
        <? endif ?>
    </td>
    <? endif ?>
    <td>
        <a href="<?= PluginEngine::getLink($plugin, array(), "view/second/".$version['mkdate']) ?>">
            <?= date("d.m.Y H:i:s", $version['mkdate']) ?>
        </a>
        <? if ($version['request_id']) : ?>
            <? $counter = SormVersion::countBySql("request_id = ?", [$version['request_id']]) ?>
            <? if ($counter > 1) : ?>
                <a href="<?= PluginEngine::getLink($plugin, array(), "view/request/".$version['request_id']) ?>"
                   title="<?= sprintf(_("Alle %s Änderungen derselben Aktion ansehen."), $counter) ?>">
                    <?= Icon::create("campusnavi", "clickable")->asImg(16, ['class' => "text-bottom"]) ?>
                </a>
            <? endif ?>
        <? endif ?>

    </td>
    <td class="actions">
        <?= Icon::create($version['delete'] ? "trash" : ($version['create'] ? "star" : "edit"), "inactive")
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
