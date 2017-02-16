<?php

if (! class_exists('Bilingual_Transaction_Log')) {

    /**
     * Transaction Log
     *
     * It manages the history of translation processing.
     *
     * @access public
     * @author KOJIPON <kojipon@gmail.com>
     * @copyright 2017 KOJIPON All Rights Reserved
     * @category WordpressPlugin
     */

    define('BILINGUAL_TRANSACTION_LOG_NUM', 500);
    define('BILINGUAL_TRANSACTION_LOG_KEY', 'bilingual_transaction_log');

    class Bilingual_Transaction_Log
    {
        public function __construct($settings)
        {
        }

        public function append($post_data, $settings, $result)
        {
            //
            // Transaction Logs Format
            //
            // [ {
            //     'post_data' => {
            //       'post_author'   => '',
            //       'post_title'    => '',
            //       'post_content'  => '',
            //       'post_category' => '',
            //       'tags_input'    => '',
            //       'post_status'   => ''
            //     },
            //     'settings' => {
            //       'bilingual_active_status'   => 'inactive',
            //       'bilingual_setting_id'      => '0',
            //       'bilingual_input_category'  => 'not set',
            //       'bilingual_input_language'  => 'not set',
            //       'bilingual_output_category' => 'not set',
            //       'bilingual_output_language' => 'not set',
            //       'bilingual_input_status'    => 'publish',
            //       'bilingual_output_status'   => 'publish',
            //       'bilingual_api_type'        => BILINGUAL_GOOGLE,
            //       'bilingual_api_token'       => ''
            //     },
            //     'result' => {
            //       'input_post_id'  => '',
            //       'output_post_id' => '',
            //       'status'         => false,
            //       'error'          => '',
            //       'created_at'     => ''
            //     }
            // } ]
            $logs = self::get_all();
            if (!is_array($logs)) {
                $logs = array();
            }

            array_push(
                $logs,
                array(
                    'setting_id' => md5(uniqid(rand(), 1)),
                    'post_data'  => $content,
                    'settings'   => $settings,
                    'result'     => $result
                )
            );

            if (count($logs) > BILINGUAL_TRANSACTION_LOG_NUM) {
                array_splice($logs, count($logs) - BILINGUAL_TRANSACTION_LOG_NUM, BILINGUAL_TRANSACTION_LOG_NUM);
            }

            $this->save($logs);

            return true;
        }

        public function save($logs)
        {
            update_option(BILINGUAL_TRANSACTION_LOG_KEY, $logs);
        }

        public static function get_all()
        {
            return maybe_unserialize(get_option(BILINGUAL_TRANSACTION_LOG_KEY));
        }

        public static function get($setting_id)
        {
            $logs = self::get_all();
            $setting_id_log  = null;

            foreach ($logs as $log) {
                if ($log['setting_id'] == $setting_id) {
                    $setting_id_log = $log;
                }
            }

            return $setting_id_log;
        }
    }
}
