<? $previous = $version->previousVersion() ?>

<? if ($version['create']) : ?>
    <?= MessageBox::info(_("Das Objekt wurde in dieser Aktion neu erstellt. Wenn Sie diese Aktion rückgängig machen, wird das Objekt gelöscht werden.")) ?>
<? endif ?>
<? if ($version['delete']) : ?>
    <?= MessageBox::info(_("Das Objekt wurde in dieser Aktion gelöscht.")) ?>
<? endif ?>
<div class="sorm_version_info">
    <table class="default">
        <tbody>
        <? foreach ($version['json_data']->getArrayCopy() as $key => $value) : ?>
            <tr class="<?= isset($previous['json_data'][$key]) && $previous['json_data'][$key] === $value ? "unchanged" : "changed" ?>">
                <td><?= htmlReady($key) ?></td>
                <td>
                    <div>
                        <? if ($value) : ?>
                            <? switch (strtolower($key)) {
                                case "mkdate":
                                case "chdate":
                                    echo '<a href="'.PluginEngine::getLink($plugin, array(), "view/second/".$value).'" title="'.date("d.m.Y H:i:s", $value).'">';
                                    break;
                                case "user_id":
                                    echo '<a href="'.URLHelper::getLink("dispatch.php/profile", array('username' => get_username($value))).'" title="'.htmlReady(get_fullname($value)).'">';
                                    break;
                                case "seminar_id":
                                    $course = Course::find($value);
                                    if ($course) {
                                        echo '<a href="' . URLHelper::getLink("dispatch.php/course/details", array('sem_id' => $value)) . '" title="' . htmlReady($course->getFullName()) . '">';
                                    }
                                    break;
                                case "institut_id":
                                    $institute = Institute::find($value);
                                    if ($institute) {
                                        echo '<a href="' . URLHelper::getLink("dispatch.php/institute/overview", array('cid' => $value)) . '" title="' . htmlReady($institute->getFullName()) . '">';
                                    }
                                    break;
                                case "semester_id":
                                    $semester = Semester::find($value);
                                    if ($semester) {
                                        echo '<a title="' . htmlReady($semester->name) . '">';
                                    }
                                    break;
                            } ?>
                        <? endif ?>
                        <?= htmlReady($value) ?>
                        <? if ($value && in_array($key, array("chdate", "mkdate", "user_id", "seminar_id", "institut_id"))) : ?>
                            </a>
                        <? endif ?>
                    </div>
                </td>
            </tr>
            <? endforeach ?>
        </tbody>
    </table>
    <? if ($version['original_file_path'] && !$version['create']) : ?>
        <div class="<?= $previous['file_id'] === $version['file_id'] ? "unchanged" : "changed" ?>" style="text-align: center;">
            <?
            $mime_type = function_exists("mime_content_type")
                ? mime_content_type($version->getFilePath())
                : $version['json_data']['mime_type'];
            $mime_type = explode("/", $mime_type) ?>
            <? if ($mime_type[0] === "image") : ?>
                <img src="<?= PluginEngine::getLink($plugin, array(), "view/file/".$version->getId()) ?>" style="max-width: 100%;">
            <? else : ?>
                <a href="<?= PluginEngine::getLink($plugin, array(), "view/file/".$version->getId()) ?>">
                    <?= Icon::create("download", "clickable")->asImg(20, array('class' => "text-bottom")) ?>
                    <?= _("Herunterladen") ?>
                </a>
            <? endif ?>
        </div>
    <? endif ?>
</div>
<style>
    .sorm_version_info .unchanged {
        opacity: 0.5;
    }
</style>
