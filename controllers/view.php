<?php

require_once 'app/controllers/plugin_controller.php';

class ViewController extends PluginController {

    function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);
        Navigation::activateItem("/admin/config/delorean");
        $this->internal_limit = 100;
        if (!$GLOBALS['perm']->have_perm("root")) {
            throw new AccessDeniedException("Kein Zugriff");
        }
    }

    public function all_action() {
        if (Request::isPost() && Request::submitted("undo_all")) {
            $versions = SormVersion::findMany(array_reverse(Request::getArray("v")), "ORDER BY version_id DESC");
            foreach ($versions as $version) {
                $version->undo();
            }
            PageLayout::postMessage(MessageBox::success(sprintf(_("%s Versionen rückgängig gemacht."), count($versions))));
            $this->redirect("view/all");
            return;
        }

        $offset = Request::int("offset", 0);
        if (Request::get("timestamp")) {
            $timestamp = strtotime(Request::get("timestamp"));
            $offset = SormVersion::countBySQL("mkdate > ?", array($timestamp));
        }

        $params = array(
            'offset' => $offset,
            'limit' => Request::int("limit", $this->internal_limit) + 1
        );
        if (Request::get("searchfor")) {
            $params['searchfor'] = Request::get("searchfor");
            $this->searchfor = Request::get("searchfor");
        }
        if (Request::get('type')) {
            $params['type'] = Request::get("type");
            $this->type = Request::get("type");
        }
        if (Request::option('user_id')) {
            $params['user_id'] = Request::get("user_id");
            $this->user_id = Request::get("user_id");
        }
        if (Request::option('timestamp')) {
            $params['since'] = is_numeric(Request::get("timestamp"))
                ? Request::get("timestamp")
                : strtotime(Request::get("timestamp"));
            $this->timestamp = $params['since'];
        }
        $this->versions = $this->getVersions($params);
        if (count($this->versions) > $this->internal_limit) {
            array_pop($this->versions);
            $this->more = true;
        }

        $this->types = $this->getTypes();
        $this->initHelpbar();
        $this->render_template("view/versions.php", $this->layout);
    }

    public function more_action() {
        $this->versions = $this->getVersions(array(
            'offset' => Request::int("offset", 0),
            'limit' => Request::int("limit", $this->internal_limit) + 1,
            'item_id' => Request::option("item_id"),
            'request_id' => Request::option("request_id"),
            'searchfor' => Request::get("searchfor"),
            'mkdate' => Request::int("mkdate"),
            'type' => Request::get("type"),
            'user_id' => Request::get("user_id")
        ));
        if (count($this->versions) > $this->internal_limit) {
            array_pop($this->versions);
            $this->more = true;
        }

        $factory = $this->get_template_factory();


        $output = array('versions' => array());
        foreach ($this->versions as $version) {
            $template = $factory->open("view/_version.php");
            $template->set_attribute('version', $version);
            $template->set_attribute('plugin', $this->plugin);
            $html = $template->render();
            $output['versions'][] = array('html' => $html);
        }
        $output['more'] = $this->more ? 1 : 0;

        $this->render_json($output);
    }

    public function details_action($id) {
        $this->version = new SormVersion($id);
        if (!$GLOBALS['perm']->have_perm("root") && ($this->version['user_id'] !== $GLOBALS['user']->id)) {
            throw new AccessDeniedException("Kein Zugriff");
        }
        PageLayout::setTitle(_("Versionsdetails"));
    }

    public function object_history_action($item_id) {
        $this->versions = $this->getVersions(array(
            'offset' => Request::int("offset", 0),
            'limit' => Request::int("limit", $this->internal_limit) + 1,
            'item_id' => $item_id
        ));
        if (count($this->versions) > $this->internal_limit) {
            array_pop($this->versions);
            $this->more = true;
        }
        $this->item_id = $item_id;
        $this->size = Sormversion::getAllocatedSpace();
        $this->initHelpbar();
        $this->types = $this->getTypes();
        $this->render_template("view/versions.php", $this->layout);
    }

    public function request_action($request_id) {
        $this->versions = $this->getVersions(array(
            'offset' => Request::int("offset", 0),
            'limit' => Request::int("limit", $this->internal_limit) + 1,
            'request_id' => $request_id
        ));
        if (count($this->versions) > $this->internal_limit) {
            array_pop($this->versions);
            $this->more = true;
        }
        $this->request_id = $request_id;
        $this->size = Sormversion::getAllocatedSpace();
        $this->initHelpbar();
        $this->types = $this->getTypes();
        $this->render_template("view/versions.php", $this->layout);
    }

    public function undo_action($id) {
        $this->version = new SormVersion($id);
        if (!$GLOBALS['perm']->have_perm("root") && ($this->version['user_id'] !== $GLOBALS['user']->id)) {
            throw new AccessDeniedException("Kein Zugriff");
        }
        $success = $this->version->undo();
        if ($success === "deleted") {
            PageLayout::postMessage(MessageBox::success(_("Änderung wurde rückgängig gemacht, Objekt wurde gelöscht.")));
        }
        if ($success === "nothing") {
            PageLayout::postMessage(MessageBox::info(_("Objekt hätte gelöscht werden müsse, war aber ohnehin nicht mehr da.")));
        }
        if ($success === "changed") {
            PageLayout::postMessage(MessageBox::success(_("Änderung an Objekt rückgängig gemacht.")));
        }
        $this->redirect("view/all");
    }

    public function file_action($id) {
        $this->version = new SormVersion($id);
        if (!$GLOBALS['perm']->have_perm("root") && ($this->version['user_id'] !== $GLOBALS['user']->id)) {
            throw new AccessDeniedException("Kein Zugriff");
        }
        if ($this->version['original_file_path']) {
            $mime_type = function_exists("mime_content_type")
                ? mime_content_type($this->version->getFilePath())
                : $this->version['json_data']['mime_type'];
            header("Content-Type: ".$mime_type);
            echo file_get_contents($this->version->getFilePath());
            die();
        }
        throw new Exception("No file.");
    }



    protected function getVersions($params) {
        $constraints = array();
        $parameter = array();
        if ($params['limit'] === null) {
            $params['limit'] = $this->internal_limit;
        }

        if (isset($params['item_id'])) {
            $constraints[] = "item_id = :item_id";
            $parameter['item_id'] = $params['item_id'];
        }
        if (isset($params['request_id'])) {
            $constraints[] = "request_id = :request_id";
            $parameter['request_id'] = $params['request_id'];
        }
        if (isset($params['mkdate'])) {
            $constraints[] = "mkdate = :mkdate";
            $parameter['mkdate'] = $params['mkdate'];
        }
        if (isset($params['since'])) {
            $constraints[] = "mkdate <= :since";
            $parameter['since'] = $params['since'];
        }
        if (isset($params['searchfor'])) {
            $constraints[] = "(MATCH (`search_index`) AGAINST (:searchfor IN BOOLEAN MODE) AND `json_data` LIKE :stringsearch)";
            $parameter['searchfor'] = $params['searchfor'];
            $parameter['stringsearch'] = "%".$params['searchfor']."%";
        }
        if (isset($params['type'])) {
            $constraints[] = "sorm_class = :sorm_class";
            $parameter['sorm_class'] = $params['type'];
        }
        if (isset($params['user_id'])) {
            $constraints[] = "user_id = :user_id";
            $parameter['user_id'] = $params['user_id'];
        }


        if (count($constraints) === 0) {
            $constraints[] = "1=1";
        }
        return SormVersion::findBySQL(
            implode(" AND ", $constraints)." ORDER BY version_id DESC LIMIT ".(int) $params['offset'].", ".(int) $params['limit'],
            $parameter
        );
    }

    protected function initHelpbar()
    {
        $this->dbsize = Sormversion::getAllocatedDBSpace();
        $this->filesize = Sormversion::getAllocatedFileSpace();
        $this->lastversion = SormVersion::findOneBySQL("1 ORDER BY version_id ASC LIMIT 1");
        Helpbar::Get()->addPlainText(
            _("Speicherplatz"),
            sprintf(
                _("Die gespeicherten Datenbankeinträge plus Dateien nehmen %s GB und die gespeicherten Dateien %s GB ein."),
                round($this->dbsize / (1024 * 1024 * 1024), 2),
                round($this->filesize / (1024 * 1024 * 1024), 2)
            )." ".($this->lastversion ? sprintf(_("Und die früheste noch existente Version stammt von %s Uhr."), date("j.n.Y G.i", $this->lastversion['mkdate'])) : "")
        );
    }

    protected function getTypes()
    {
        $statement = DBManager::get()->prepare("
            SELECT `sorm_class`
            FROM `sorm_versions`
            GROUP BY `sorm_class`
            ORDER BY `sorm_class` ASC
        ");
        $statement->execute();
        $types = $statement->fetchAll(PDO::FETCH_COLUMN, 0);
        $output = ['' => ''];
        foreach ($types as $type) {
            $output[$type] = $type;
        }
        return $output;
    }


}
