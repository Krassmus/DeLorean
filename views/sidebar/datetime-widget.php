<? if ($reset_search): ?>
    <div style="text-align: right;">
        <?= $reset_search ?>
    </div>
<? endif; ?>
<form action="<?= $url ?>" method="<?= $method ?>" <? if (isset($id)) printf('id="%s"', htmlReady($id)); ?> class="default">
    <label for="needle-<?= $hash = md5($url . '|' . $name) ?>" <? if ($placeholder) echo 'style="display:none;"'; ?>>
        <?= htmlReady($label) ?>
    </label>
    <input type="text" id="needle-<?= $hash ?>"
           name="<?= htmlReady($name) ?>"
           value="<?= htmlReady($value) ?>"
           <?= $value ? "onChange=\"if (!this.value) { jQuery(this).closest('form').submit(); }\"" : "" ?>
        <? if ($placeholder) printf('placeholder="%s"', htmlReady($label)); ?>>

    <?= \Studip\Button::create(_("Ab Datum oder früher anzeigen")) ?>

    <script>
        jQuery(function () {
            jQuery("#needle-<?= $hash ?>").datetimepicker();
        });
    </script>

</form>
