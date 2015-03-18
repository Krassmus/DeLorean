<? $previous = $version->previousVersion() ?>
<? if ($version['json_data'] == null) : ?>
    <?= MessageBox::info(_("Das Objekt wurde in dieser Aktion gel�scht.")) ?>
<? else : ?>
    <? if (!$previous) : ?>
        <?= MessageBox::info(_("Es wurde zu dieser Version keine Vorg�ngerversion gefunden. Vermutlich wurde das Objekt in dieser Aktion neu erstellt. Wenn Sie diese Aktion r�ckg�ngig machen, wird das Objekt gel�scht werden.")) ?>
    <? endif ?>
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
<? endif ?>
<style>
    table.default .unchanged {
        opacity: 0.5;
    }
</style>