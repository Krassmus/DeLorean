<?php

require_once 'app/controllers/plugin_controller.php';

class ViewController extends PluginController {

    function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);
        Navigation::activateItem("/admin/config/delorean");
        $this->internal_limit = 50;
    }

    public function all_action() {
        if (!$GLOBALS['perm']->have_perm("root")) {
            throw new AccessDeniedException("Kein Zugriff");
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
        $this->previous = $this->version->previousVersion();
        if (!$this->previous) {
            if ($this->version->invoke()) {
                $this->version->invoke()->delete();
                PageLayout::postMessage(MessageBox::success(_("Änderung wurde rückgängig gemacht, Objekt wurde gelöscht.")));
            } else {
                PageLayout::postMessage(MessageBox::info(_("Objekt hätte gelöscht werden müsse, war aber ohnehin nicht mehr da.")));
            }
        } else { //es gibt einer Vorgängerversion, auf die wir updaten können
            $this->current = $this->version->invoke();

            if (!$this->current) { //es gibt aber keine aktuelle Version mehr. Also bauen wir uns eine.
                $class = $this->version['sorm_class'];
                $this->current = new $class();
            }
            $this->current->setData($this->previous['json_data']);
            $success = $this->current->store();
            if ($success && $this->previous['original_file_path']) {
                @copy($this->previous->getFilePath(), $this->previous['original_file_path']);
            }
            /*var_dump($success);
            echo "\n\n<br><br><br>\n\n\n";
            var_dump($this->current);
            die();*/
            PageLayout::postMessage(MessageBox::success(_("Änderung an Objekt rückgängig gemacht.")));
        }
        $this->redirect("delorean/view/all");
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