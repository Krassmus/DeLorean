<?php

require_once 'app/controllers/plugin_controller.php';

class ViewController extends PluginController {

    function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);
        Navigation::activateItem("/admin/config/delorean");
    }

    public function all_action() {
        if (!$GLOBALS['perm']->have_perm("root")) {
            throw new AccessDeniedException("Kein Zugriff");
        }
        $this->versions = $this->getVersions(array(
            'offset' => Request::int("offset", 0),
            'limit' => Request::int("limit", 30)
        ));
        $this->render_template("view/versions.php", $this->layout);
    }

    public function more_action() {
        if (!$GLOBALS['perm']->have_perm("root")) {
            throw new AccessDeniedException("Kein Zugriff");
        }
        $this->versions = $this->getVersions(array(
            'offset' => Request::int("offset", 0),
            'limit' => Request::int("limit", 30)
        ));
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
            'limit' => Request::int("limit", 30),
            'item_id' => $item_id
        ));
        $this->render_template("view/versions.php", $this->layout);
    }

    public function second_action($timestamp) {
        $this->versions = $this->getVersions(array(
            'offset' => Request::int("offset", 0),
            'limit' => Request::int("limit", 30),
            'mkdate' => $timestamp
        ));
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

        if (count($constraints) === 0) {
            $constraints[] = "1=1";
        }
        return SormVersion::findBySQL(
            implode(" AND ", $constraints)." ORDER BY version_id DESC LIMIT ".(int) $params['offset'].", ".(int) $params['limit'],
            $parameter
        );
    }


}