<? $previous = $version->previousVersion() ?>
<? if (($version['json_data'] == null) OR (empty($this->version['json_data']->getArrayCopy()))) : ?>
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