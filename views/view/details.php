<table class="default">
    <tbody>
    <? foreach ($version['json_data'] as $key => $value) : ?>
        <? if ($key !== "id") : ?>
        <tr>
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