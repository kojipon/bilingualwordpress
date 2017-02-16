<?php

if (! class_exists('Bilingual_History')) {

    /**
     * Transaction History
     *
     * It manages the history of translation processing.
     *
     * @access public
     * @author KOJIPON <kojipon@gmail.com>
     * @copyright 2017 KOJIPON All Rights Reserved
     * @category WordpressPlugin
     */

    define('BILINGUAL_HISTORY_NUM', 500);
    define('BILINGUAL_HISTORY_DB_TABLE', 'bilingual_history');

    class Bilingual_History
    {
        private $var = array();
        public static $history_num = BILINGUAL_HISTORY_NUM;

        //
        // History Format
        //
        // {
        //     'history_id' => 1,
        //     'post_data' => {
        //       'post_author'   => '',
        //       'post_title'    => '',
        //       'post_content'  => '',
        //       'post_category' => '',
        //       'tags_input'    => '',
        //       'post_status'   => ''
        //     },
        //     'settings' => {
        //       'active_status'   => 'inactive',
        //       'setting_id'      => '0',
        //       'input_category'  => 'not set',
        //       'input_language'  => 'not set',
        //       'output_category' => 'not set',
        //       'output_language' => 'not set',
        //       'input_status'    => 'publish',
        //       'output_status'   => 'publish',
        //       'api_type'        => BILINGUAL_GOOGLE,
        //       'api_token'       => ''
        //     },
        //     'result' => {
        //       'input_post_id'  => '',
        //       'output_post_id' => '',
        //       'status'         => false,
        //       'error'          => '',
        //       'created_at'     => ''
        //     }
        // }
        private $properties = array(
            'history_id',
            'post_data',
            'settings',
            'result'
        );

        public function __construct($history_id)
        {
            $histories = self::get_all();

            foreach ($histories as $history) {
                if ($history['history_id'] == $history_id) {
                    foreach ($this->properties as $key) {
                        $this->$key = $history[$key];
                    }
                }
            }
        }

        public function __get($key)
        {
            return $this->valid_key($key) ? $this->get($key) : null;
        }

        public function __set($key, $value)
        {
            return $this->valid_key($key) ? $this->set($key, $value) : false;
        }

        public function get($key, $default=null)
        {
            if (array_key_exists($key, $this->var)) {
                return $this->var[$key];
            }
            return $default;
        }

        public function set($key, $value)
        {
            $this->var[$key] = $value;
        }

        private function valid_key($key)
        {
            return in_array($key, $this->properties);
        }

        public static function append($post_data, $settings, $result)
        {
            $histories = self::get_all();
            if (!is_array($histories)) {
                $histories = array();
            }

            array_push(
                $histories,
                array(
                    'history_id' => md5(uniqid(rand(), 1)),
                    'post_data'  => $content,
                    'settings'   => $settings,
                    'result'     => $result
                )
            );

            if (count($histories) > BILINGUAL_HISTORY_NUM) {
                array_splice($histories, count($histories) - BILINGUAL_HISTORY_NUM, BILINGUAL_HISTORY_NUM);
            }

            self::save($histories);

            return true;
        }

        public static function save($histories)
        {
            update_option(BILINGUAL_HISTORY_DB_TABLE, $histories);
        }

        public static function get_all()
        {
            return maybe_unserialize(get_option(BILINGUAL_HISTORY_DB_TABLE));
        }
    }
}
