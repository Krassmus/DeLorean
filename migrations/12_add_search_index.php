<?php

class AddSearchIndex extends Migration {

    public function up()
    {
        /*$statement = DBManager::get()->prepare("
            SELECT *
            FROM `sorm_versions`
            WHERE `search_index` IS NULL
        ");
        $statement->execute();

        $update = DBManager::get()->prepare("
            UPDATE `sorm_versions`
            SET `search_index` = :search_index
            WHERE `version_id` = :id
            ORDER BY `version_id` DESC
        ");
        while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
            $json = json_decode($row['json_data'], true);
            $search_index = implode(" ", array_values((array) $json));
            $update->execute([
                'id' => $row['version_id'],
                'search_index' => $search_index
            ]);
        }*/
    }

    public function down()
    {

    }

}
