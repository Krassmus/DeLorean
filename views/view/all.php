<?php
function stringToColorCode($str) {
    $code = dechex(crc32($str));
    $code = substr($code, 0, 6);
    return $code;
}
?>

<table class="default">
    <thead>
        <tr>
            <th><?= _("Typ") ?></th>
            <th><?= _("Veränderer") ?></th>
            <th><?= _("Datum") ?></th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        <? foreach ($versions as $version) : ?>
            <tr>
                <td>
                    <span style="display: inline-block; width: 10px; height: 10px; background-color: #<?= stringToColorCode($version['item_id']) ?>; border: thin solid black;" title="ID: <?= htmlReady($version['item_id']) ?>"></span>
                    <?= htmlReady($version['sorm_class']) ?>
                </td>
                <td>
                    <a href="<?= URLHelper::getLink("dispatch.php/profile", array('username' => get_username($version['user_id']))) ?>">
                        <?= Avatar::getAvatar($version['user_id'])->getImageTag(Avatar::SMALL) ?>
                        <?= htmlReady(get_fullname($version['user_id'])) ?>
                    </a>
                </td>
                <td><?= date("G:i d.n.Y", $version['mkdate']) ?></td>
                <td>
                    <a href="<?= PluginEngine::getLink($plugin, array(), "view/details/".$version->getId()) ?>" data-dialog>
                        <?= Assets::img("icons/20/blue/info-circle", array('class' => "text-bottom")) ?>
                    </a>
                    <? if (!$version->isCurrentObject()) : ?>
                    <a href="" title="<?= _("Object wiederherstellen.") ?>">
                        <?= Assets::img("icons/20/blue/archive2", array('class' => "text-bottom")) ?>
                    </a>
                    <? endif ?>
                </td>
            </tr>
        <? endforeach ?>
    </tbody>
</table>