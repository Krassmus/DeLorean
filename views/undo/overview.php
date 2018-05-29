<table class="default">
    <thead>
        <tr>
            <th><?= _("Objekt-Typ") ?></th>
            <th><?= _("Name") ?></th>
            <th><?= _("Zeitpunkt") ?></th>
            <th><?= _("Rückgängig machen") ?></th>
        </tr>
    </thead>
    <tbody>
        <? foreach ($versions as $version) : ?>
            <tr>
                <td>
                    <?= htmlReady($version['sorm_class']) ?>
                </td>
                <td>
                    <? if ($version['delete']) : ?>
                        <?= Assets::img("icons/20/grey/trash", array('class' => "text-bottom", 'title' => _("Objekt wurde gelöscht"))) ?>
                    <? else : ?>
                        <? $name = $version['json_data']['description'] ?: $version['json_data']['name'];
                        if (!$name) {
                            $name = $version['json_data']['title'];
                        }
                        if (!$name) {
                            $name = $version['json_data']['content'];
                        }
                        $name = strlen($name) > 30 ? substr($name, 0, 30)."..." : $name;
                        ?>
                        <?= htmlReady($name) ?>
                    <? endif ?>
                </td>
                <td>
                    <?= date("d.m.Y H:i:s", $version['mkdate']) ?>
                </td>
                <td>
                    <form action="<?= PluginEngine::getLink($plugin, array(), "undo/undo/".$version->getId()) ?>" method="post" style="display: inline;">
                        <button style="border: none; background: none; cursor: pointer;" title="<?= _("Änderung rückgängig machen") ?>">
                            <?= Assets::img("icons/20/blue/archive2", array('class' => "text-bottom")) ?>
                        </button>
                    </form>
                </td>
            </tr>
        <? endforeach ?>
    </tbody>
</table>