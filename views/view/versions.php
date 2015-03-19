<form method="get" action="?">

</form>

<input type="hidden" id="offset" value="<?= Request::int("offset", 0) ?>">
<input type="hidden" id="limit" value="<?= $internal_limit ?>">
<input type="hidden" id="since" value="<?= time() ?>">
<? if ($item_id) : ?>
    <input type="hidden" id="item_id" value="<?= htmlReady($item_id) ?>">
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

<table class="default" id="sormversions">
    <thead>
        <tr>
            <th></th>
            <th><?= _("Typ") ?></th>
            <th><?= _("Veränderer") ?></th>
            <th><?= _("Datum") ?></th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        <? foreach ($versions as $version) : ?>
            <?= $this->render_partial("view/_version.php", array('version' => $version)) ?>
        <? endforeach ?>
    </tbody>
    <tfoot>
    <? if ($more) : ?>
        <tr class="more">
            <td colspan="5" style="text-align: center">
                <?= Assets::img("ajax-indicator-black.svg") ?>
            </td>
        </tr>
    <? endif ?>
    </tfoot>
</table>

<script>
    //Infinity-scroll:
    jQuery(window.document).bind('scroll', _.throttle(function (event) {
        if ((jQuery(window).scrollTop() + jQuery(window).height() > jQuery(window.document).height() - 500)
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
                    'searchfor': jQuery("#searchfor").val(),
                    'mkdate': jQuery("#mkdate").val(),
                    'type': jQuery("#type").val()
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