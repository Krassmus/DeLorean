<form action="<?= PluginEngine::getLink($plugin, [], 'recover/revive_messages') ?>"
      class="default"
      id="revive_messages"
      method="post">
    <table class="default">
        <thead>
            <tr>
                <th width="20">
                    <input type="checkbox" data-proxyfor="#revive_messages tbody :checkbox">
                </th>
                <th><?= _("Betreff") ?></th>
                <th></th>
                <th><?= _('Inbox/Outbox') ?></th>
                <th><?= _("Zeit") ?></th>
            </tr>
        </thead>
        <tbody>
            <? foreach ($deleted_items as $message_user) : ?>
            <? if (is_a($message_user, SormVersion::class)) : ?>
                <tr>
                    <td>
                        <input type="checkbox" id="" name="m[]" value="<?= htmlReady($message_user->json_data['message_id']) ?>">
                    </td>
                    <td>
                        <?
                        $deleted_message = SormVersion::findOneBySQL("`sorm_class` = 'Message' AND `delete` = '1' AND `item_id` = :message_id AND version_id = (SELECT version_id FROM sorm_versions AS s2 WHERE s2.item_id = sorm_versions.item_id AND s2.sorm_class = sorm_versions.sorm_class ORDER BY version_id DESC LIMIT 1)", [
                            'message_id' => $message_user->json_data['message_id']
                        ]);
                        if ($deleted_message) {
                            echo htmlReady($deleted_message->json_data['subject']);
                        } else {
                            echo _('unbekannt');
                        }
                        ?>
                    </td>
                    <td>
                        <?
                        $deleted_folder = SormVersion::findOneBySQL("`sorm_class` = 'Folder' AND `delete` = '1' AND `search_index` LIKE :message_id AND version_id = (SELECT version_id FROM sorm_versions AS s2 WHERE s2.item_id = sorm_versions.item_id AND s2.sorm_class = sorm_versions.sorm_class ORDER BY version_id DESC LIMIT 1)", [
                            'message_id' => '%'.$message_user->json_data['message_id'].'%'
                        ]);
                        if ($deleted_folder) {
                            echo Icon::create('staple', Icon::ROLE_INFO);
                        }
                        ?>
                    </td>
                    <td>
                        <?= $message_user->json_data['snd_rec'] === 'rec' ? _('Empfangen') : _('Gesendet') ?>
                    </td>
                    <td>
                        <?= strftime('%x %R', $message_user->json_data['mkdate']) ?>
                    </td>
                </tr>
            <? else : ?>
                <tr>
                    <td>
                        <input type="checkbox" id="" name="m[]" value="<?= htmlReady($message_user->message_id) ?>">
                    </td>
                    <td>
                        <?= htmlReady(!empty($message_user->message) ? $message_user->message['subject'] : _('unbekannt')) ?>
                    </td>
                    <td>
                        <?= !empty($message_user->message) && $message_user->message->attachment_folder ? Icon::create('staple', Icon::ROLE_INFO) : '' ?>
                    </td>
                    <td>
                        <?= $message_user['snd_rec'] === 'rec' ? _('Empfangen') : _('Gesendet') ?>
                    </td>
                    <td>
                        <?= strftime('%x %R', $message_user['mkdate']) ?>
                    </td>
                </tr>
            <? endif ?>
            <? endforeach ?>
        </tbody>
    </table>

    <div>
        <?= \Studip\Button::create(_('Ausgewählte wiederherstellen')) ?>
    </div>
</form>
