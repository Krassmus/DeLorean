<form action="<?= PluginEngine::getLink($plugin, array(), "recover/mass_undo") ?>" method="post">

    <table class="default">
        <caption>
            <?= _("Gelöschte Ordner und Dateien") ?>
        </caption>
        <thead>
            <tr>
                <th>
                    <input type="checkbox" data-proxyfor="table.default tbody :checkbox">
                </th>
                <th>
                    <?= _("Typ") ?>
                </th>
                <th><?= _("Name") ?></th>
                <th><?= _("Gelöscht von") ?></th>
                <th><?= _("Löschdatum") ?></th>
                <th class="actions"><?= _("Aktionen") ?></th>
            </tr>
        </thead>
        <tbody>
            <? if (count($folder_versions) + count($file_versions)) : ?>
                <? foreach ($folder_versions as $version) : ?>
                    <tr>
                        <td>
                            <input type="checkbox" name="v[]" value="<?= htmlReady($version->getId()) ?>">
                        </td>
                        <td>
                            <?= Icon::create("folder-empty", "info")->asImg(26) ?>
                        </td>
                        <td>
                            <?= htmlReady($version['json_data']['name']) ?>
                        </td>
                        <td>
                            <?= htmlReady($version['user_id'] ? get_fullname($version['user_id']) : _("unbekannt")) ?>
                        </td>
                        <td>
                            <?= date("d.m.Y H:i:s", $version['mkdate']) ?>
                        </td>
                        <td class="actions">
                            <button formaction="<?= PluginEngine::getLink($plugin, array(), "recover/undo/".$version->getId()) ?>"
                                    style="border: none; background: none; cursor: pointer;"
                                    title="<?= _("Objekt wiederherstellen") ?>">
                                <?= Icon::create("archive2", "clickable")->asImg(20, array('class' => "text-bottom")) ?>
                            </button>
                        </td>
                    </tr>
                <? endforeach ?>
                <? foreach ($file_versions as $version) : ?>
                    <tr>
                        <td>
                            <input type="checkbox" name="v[]" value="<?= htmlReady($version->getId()) ?>">
                        </td>
                        <td>
                            <a href="<?= PluginEngine::getLink($plugin, array(), "recover/download_file/".$version->getId()) ?>">
                                <?= Icon::create(FileManager::getIconNameForMimeType(get_mime_type($version['json_data']['name'])), Icon::ROLE_CLICKABLE)->asImg(24) ?>
                            </a>
                        </td>
                        <td>
                            <a href="<?= PluginEngine::getLink($plugin, array(), "recover/download_file/".$version->getId()) ?>">
                                <?= htmlReady($version['json_data']['name']) ?>
                            </a>
                        </td>
                        <td>
                            <?= htmlReady($version['user_id'] ? get_fullname($version['user_id']) : _("unbekannt")) ?>
                        </td>
                        <td>
                            <?= date("d.m.Y H:i:s", $version['mkdate']) ?>
                        </td>
                        <td class="actions">
                            <button formaction="<?= PluginEngine::getLink($plugin, array(), "recover/undo/".$version->getId()) ?>"
                                    style="border: none; background: none; cursor: pointer;"
                                    title="<?= _("Objekt wiederherstellen") ?>">
                                <?= Icon::create("archive2", "clickable")->asImg(20, array('class' => "text-bottom")) ?>
                            </button>
                        </td>
                    </tr>
                <? endforeach ?>
            <? else : ?>
                <tr>
                    <td colspan="100">
                        <?= _("Keine Objekte, die man wiederherstellen könnte.") ?>
                    </td>
                </tr>
            <? endif ?>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="100">
                    <?= \Studip\Button::create(_("Auswahl wiederherstellen")) ?>
                </td>
            </tr>
        </tfoot>
    </table>
</form>

<?
Sidebar::Get()->setImage('sidebar/files-sidebar.png');

$views = new ViewsWidget();
$views->addLink(
    _('Ordneransicht'),
    URLHelper::getURL('dispatch.php/course/files/index'),
    null,
    [],
    'index'
);
$views->addLink(
    _('Alle Dateien'),
    URLHelper::getURL('dispatch.php/course/files/flat'),
    null,
    [],
    'flat'
);

Sidebar::Get()->addWidget($views);