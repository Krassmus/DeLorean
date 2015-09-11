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
    <? if (!get_config("DELOREAN_ANONYMOUS_USERS")) : ?>
    <td>
        <? if ($version['user_id']) : ?>
            <a href="<?= URLHelper::getLink("dispatch.php/profile", array('username' => get_username($version['user_id']))) ?>">
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
    </td>
    <td>
        <a href="<?= PluginEngine::getLink($plugin, array(), "view/details/".$version->getId()) ?>" data-dialog="true">
            <?= Assets::img("icons/20/blue/info-circle", array('class' => "text-bottom")) ?>
        </a>
        <a href="<?= PluginEngine::getLink($plugin, array(), "view/undo/".$version->getId()) ?>" title="<?= _("Änderung rückgängig machen") ?>">
            <?= Assets::img("icons/20/blue/archive2", array('class' => "text-bottom")) ?>
        </a>
    </td>
    <td>
        <input type="checkbox" name="v[]" value="<?= htmlReady($version->getId()) ?>">
    </td>
</tr>