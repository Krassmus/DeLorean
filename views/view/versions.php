<form action="<?= PluginEngine::getLink($plugin, array(), "view/all") ?>" method="post">

    <input type="hidden" id="offset" value="<?= Request::int("offset", 0) ?>">
    <input type="hidden" id="limit" value="<?= $internal_limit ?>">
    <input type="hidden" id="since" value="<?= time() ?>">
    <? if ($item_id) : ?>
        <input type="hidden" id="item_id" value="<?= htmlReady($item_id) ?>">
    <? endif ?>
    <? if ($request_id) : ?>
        <input type="hidden" id="request_id" value="<?= htmlReady($request_id) ?>">
    <? endif ?>
    <? if ($searchfor) : ?>
        <input type="hidden" id="searchfor" value="<?= htmlReady($searchfor) ?>">
    <? endif ?>
    <? if ($mkdate) : ?>
        <input type="hidden" id="mkdate" value="<?= htmlReady($mkdate) ?>">
    <? endif ?>
    <? if ($type) : ?>
        <input type="hidden" id="type" value="<?= htmlReady($type) ?>">
    <? endif ?>
    <? if ($user_id) : ?>
        <input type="hidden" id="user_id" value="<?= htmlReady($user_id) ?>">
    <? endif ?>


    <div style="float: right;">
        <?= \Studip\Button::create(_("Ausgewählte rückgängig machen"), "undo_all") ?>
    </div>
    <div style="clear: both;"></div>

    <table class="default" id="sormversions">
        <? if ($caption) : ?>
        <caption>
            <?= htmlReady($caption) ?>
        </caption>
        <? endif ?>
        <thead>
            <tr>
                <th></th>
                <th><?= _("Typ") ?></th>
                <? if (!Config::get()->DELOREAN_ANONYMOUS_USERS) : ?>
                <th><?= _("Veränderer") ?></th>
                <? endif ?>
                <th><?= _("Datum") ?></th>
                <th class="actions">
                    <input type="checkbox" data-proxyfor=":checkbox[name^=v]" aria-label="<?= _("Alle auswählen/abwählen") ?>" title="<?= _("Alle auswählen/abwählen") ?>">
                </th>
            </tr>
        </thead>
        <tbody>
            <? if (count($versions)) : ?>
                <? foreach ($versions as $version) : ?>
                    <?= $this->render_partial("view/_version.php", array('version' => $version)) ?>
                <? endforeach ?>
            <? else : ?>
            <tr>
                <td colspan="6" style="text-align: center;"><?= _("Noch wurden keine Änderungen am System erkannt.") ?></td>
            </tr>
            <? endif ?>
        </tbody>
        <tfoot>
        <? if ($more) : ?>
            <tr class="more">
                <td colspan="6" style="text-align: center">
                    <?= Assets::img("ajax-indicator-black.svg") ?>
                </td>
            </tr>
        <? endif ?>
        </tfoot>
    </table>

</form>

<script>
    //Infinity-scroll:
    jQuery(window.document).bind('scroll', _.throttle(function (event) {
        if ((jQuery(window).scrollTop() + jQuery(window).height() > jQuery(window.document).height() - 1200)
            && (jQuery("#sormversions .more").length > 0)) {
            //nachladen
            jQuery("#sormversions .more").removeClass("more").addClass("loading");
            jQuery.ajax({
                url: STUDIP.ABSOLUTE_URI_STUDIP + "plugins.php/delorean/view/more",
                data: {
                    'offset': parseInt(jQuery("#offset").val(), 10) + parseInt(jQuery("#limit").val(), 10),
                    'limit': jQuery("#limit").val(),
                    'since': jQuery("#since").val(),
                    'item_id': jQuery("#item_id").val(),
                    'request_id': jQuery("#request_id").val(),
                    'searchfor': jQuery("#searchfor").val(),
                    'mkdate': jQuery("#mkdate").val(),
                    'type': jQuery("#type").val(),
                    'user_id': jQuery("#user_id").val()
                },
                dataType: "json",
                success: function (response) {
                    jQuery.each(response.versions, function (index, version) {
                        jQuery("#sormversions tbody").append(version.html);
                    });
                    jQuery("#offset").val(parseInt(jQuery("#offset").val(), 10) + response.versions.length);
                    if (response.more) {
                        jQuery("#sormversions .loading").removeClass("loading").addClass("more");
                    } else {
                        jQuery("#sormversions .loading").remove();
                    }
                }
            });
        }
    }, 30));
</script>


<?

$search = new SearchWidget(PluginEngine::getURL($plugin, array(), "view/all"));
$search->addNeedle(_("ID, Eigenschaft, Zeitstempel"), "searchfor", true);
Sidebar::Get()->addWidget($search);

$search = new SearchWidget(PluginEngine::getURL($plugin, array(), "view/by"));
$search->setTitle(_("Personen-Filter"));
$search->addNeedle(
        _("Person"),
        "user_id",
        true,
        new StandardSearch("user_id"),
        "function () { jQuery('input[name=user_id]').closest('form').submit(); }",
        $user_id
);
Sidebar::Get()->addWidget($search);


$datepicker = new DatetimeWidget(
    PluginEngine::getURL($plugin, array(), "view/all"),
    "timestamp",
    _("ab ...")
);
if (Request::get("timestamp")) {
    $datepicker->setValue(Request::get("timestamp"));
}
Sidebar::Get()->addWidget($datepicker);
