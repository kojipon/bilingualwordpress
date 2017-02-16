<?php

if (! class_exists('Bilingual_Translate_Page')) {

    /**
     * Translate Page
     *
     * It manages the history of translation processing.
     *
     *
     * @access public
     * @author KOJIPON <kojipon@gmail.com>
     * @copyright 2017 KOJIPON All Rights Reserved
     * @category WordpressPlugin
     */

    require_once('history_page.php');
    require_once('history.php');
    require_once('processor.php');

    class Bilingual_Translate_Page
    {
        public static function output()
        {
            $history_id = null;
            if (isset($_POST['bilingual_history_id'])) {
                $history_id = $_POST['bilingual_history_id'];
                $history = new Bilingual_History($history_id);
                if (isset($history)) {
                    $processor = new Bilingual_Processor();
                    $processor->translate($history->result['input_post_id'], $history->settings);
                }
            }

            Bilingual_History_Page::output();
        }
    }
}
