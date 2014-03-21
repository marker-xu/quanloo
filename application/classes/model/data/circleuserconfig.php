<?php
class Model_Data_CircleUserConfig extends Model_Data_MongoCollection {   
    public function __construct() {
        parent::__construct('circle', 'video_search', 'circle_user_config');
    }
}