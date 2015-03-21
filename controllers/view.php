<?php

require_once 'app/controllers/plugin_controller.php';

class ViewController extends PluginController {

    function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);
        Navigation::activateItem("/admin/config/delorean");
        $this->internal_limit = 50;

        $deleting = get_config("DELOREAN_SAVING_TIME");
        if ($deleting) {
            $old_versions = Sormversion::findBySQL("mkdate < ?", array(time() - $deleting));
            foreach ($old_versions as $version) {
                $version->delete();
            }
        }
    }

    public function all_action() {
        if (!$GLOBALS['perm']->have_perm("root")) {
            throw new AccessDeniedException("Kein Zugriff");
        }
        if (Request::isPost() && Request::submitted("undo_all")) {
            $versions = SormVersion::findMany(Request::getArray("v"), "ORDER BY version_id DESC");
            foreach ($versions as $v) {
                $v->undo();
            }
            PageLayout::postMessage(MessageBox::success(sprintf(_("%s Versionen r�ckg�ngig gemacht."), count($versions))));
            $this->redirect("delorean/view/all");
            return;
        }
        $params = array(
            'offset' => Request::int("offset", 0),
            'limit' => Request::int("limit", $this->internal_limit) + 1
        );
        if (Request::get("searchfor")) {
            $params['searchfor'] = Request::get("searchfor");
            $this->searchfor = Request::get("searchfor");
        }
        $this->versions = $this->getVersions($params);
        if (count($this->versions) > $this->internal_limit) {
            array_pop($this->versions);
            $this->more = true;
        }
        $this->render_template("view/versions.php", $this->layout);
    }

    public function more_action() {
        if (!$GLOBALS['perm']->have_perm("root")) {
            throw new AccessDeniedException("Kein Zugriff");
        }
        $this->versions = $this->getVersions(array(
            'offset' => Request::int("offset", 0),
            'limit' => Request::int("limit", $this->internal_limit) + 1,
            'item_id' => Request::option("item_id"),
            'searchfor' => Request::get("searchfor"),
            'mkdate' => Request::int("mkdate"),
            'type' => Request::get("type")
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
        $this->render_template("view/versions.php", $this->layout);
    }

    public function second_action($timestamp) {
        $this->versions = $this->getVersions(array(
            'offset' => Request::int("offset", 0),
            'limit' => Request::int("limit", $this->internal_limit) + 1,
            'mkdate' => $timestamp
        ));
        $this->mkdate = $timestamp;
        if (count($this->versions) > $this->internal_limit) {
            array_pop($this->versions);
            $this->more = true;
        }
        $this->render_template("view/versions.php", $this->layout);
    }

    public function type_action($class) {
        $this->versions = $this->getVersions(array(
            'offset' => Request::int("offset", 0),
            'limit' => Request::int("limit", $this->internal_limit) + 1,
            'type' => $class
        ));
        $this->type = $class;
        if (count($this->versions) > $this->internal_limit) {
            array_pop($this->versions);
            $this->more = true;
        }
        $this->render_template("view/versions.php", $this->layout);
    }

    public function undo_action($id) {
        $this->version = new SormVersion($id);
        if (!$GLOBALS['perm']->have_perm("root") && ($this->version['user_id'] !== $GLOBALS['user']->id)) {
            throw new AccessDeniedException("Kein Zugriff");
        }
        $success = $this->version->undo();
        if ($success === "deleted") {
            PageLayout::postMessage(MessageBox::success(_("�nderung wurde r�ckg�ngig gemacht, Objekt wurde gel�scht.")));
        }
        if ($success === "nothing") {
            PageLayout::postMessage(MessageBox::info(_("Objekt h�tte gel�scht werden m�sse, war aber ohnehin nicht mehr da.")));
        }
        if ($success === "changed") {
            PageLayout::postMessage(MessageBox::success(_("�nderung an Objekt r�ckg�ngig gemacht.")));
        }
        $this->redirect("view/all");
    }

    public function file_action($id) {
        $this->version = new SormVersion($id);
        if (!$GLOBALS['perm']->have_perm("root") && ($this->version['user_id'] !== $GLOBALS['user']->id)) {
            throw new AccessDeniedException("Kein Zugriff");
        }
        if ($this->version['original_file_path']) {
            header("Content-Type: ".mime_content_type($this->version->getFilePath()));
            echo file_get_contents($this->version->getFilePath());
            die();
        }
        throw new Exception("No file.");
    }



    protected function getVersions($params) {
        $constraints = array();
        $parameter = array();
        if ($params['limit'] === null) {
            $params['limit'] = 30;
        }

        if ($params['item_id']) {
            $constraints[] = "item_id = :item_id";
            $parameter['item_id'] = $params['item_id'];
        }
        if ($params['mkdate']) {
            $constraints[] = "mkdate = :mkdate";
            $parameter['mkdate'] = $params['mkdate'];
        }
        if ($params['since']) {
            $constraints[] = "mkdate <= :since";
            $parameter['since'] = $params['since'];
        }
        if ($params['searchfor']) {
            $constraints[] = "json_data LIKE :searchfor";
            $parameter['searchfor'] = '%'.$params['searchfor'].'%';
        }
        if ($params['type']) {
            $constraints[] = "sorm_class = :sorm_class";
            $parameter['sorm_class'] = $params['type'];
        }


        if (count($constraints) === 0) {
            $constraints[] = "1=1";
        }
        return SormVersion::findBySQL(
            implode(" AND ", $constraints)." ORDER BY version_id DESC LIMIT ".(int) $params['offset'].", ".(int) $params['limit'],
            $parameter
        );
    }


}