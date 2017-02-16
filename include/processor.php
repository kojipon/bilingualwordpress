<?php

if (! class_exists('Bilingual_Processor')) {

    /**
     * BilingualWordPress Main Class
     *
     * It is the main class for running BiingualWordPress.
     * This class will read and write settings and
     * execute translations from this class.
     *
     * @access public
     * @author KOJIPON <kojipon@gmail.com>
     * @copyright 2017 KOJIPON All Rights Reserved
     * @category WordpressPlugin
     */

    require_once('settings.php');
    require_once('translator.php');
    require_once('history.php');
    require_once('all_settings_page.php');
    require_once('detail_settings_page.php');
    require_once('translate_page.php');
    require_once('history_page.php');

    class Bilingual_Processor
    {
        private function before_action($input_post_id)
        {
            remove_action('publish_post', array($this, 'run'));
            remove_action('private_post', array($this, 'run'));
            remove_action('draft_post', array($this, 'run'));
        }

        public function run($input_post_id)
        {
            $this->before_action($input_post_id);

            $active_settings = Bilingual_Settings::get_active();
            foreach ($active_settings as $settings) {
                if ($this->is_target_status($input_post_id, $settings)) {
                    if ($this->is_target_category($input_post_id, $settings)) {
                        $this->translate($input_post_id, $settings);
                    }
                }
            }

            $this->after_action($input_post_id);
        }

        private function after_action($input_post_id)
        {
            add_action('publish_post', array($this, 'run'));
            add_action('private_post', array($this, 'run'));
            add_action('draft_post', array($this, 'run'));
        }

        public function translate($input_post_id, $settings)
        {
            $output_post_data = null;
            $output_post_id   = null;
            $error = null;
            try {
                $output_post_data = $this->gen_post_data($input_post_id, $settings);
                $output_post_id   = $this->save_post_data($output_post_data);
            } catch (Exception $e) {
                $error = $e->getMessage();
            }

            Bilingual_History::append(
                $output_post_data,
                $settings,
                array(
                    'input_post_id'  => $input_post_id,
                    'output_post_id' => $output_post_id,
                    'status'         => !isset($error),
                    'error'          => $error,
                    'created_at'     => time()
                )
            );
        }

        public function gen_post_data($input_post_id, $settings)
        {
            $translator = new Bilingual_Translator($settings);

            $input_post_data = get_post($input_post_id);
            $tags    = get_the_tags($input_post_id);
            $author  = $input_post_data->post_author;
            $title   = $translator->translate($input_post_data->post_title);
            $content = $translator->translate($input_post_data->post_content);
            $tags    = $tags;

            return array(
                'post_author'   => $author,
                'post_title'    => $title,
                'post_content'  => $content,
                'post_category' => array($settings->output_category), // TODO MULTI CATEGORIES
                'tags_input'    => $tags,
                'post_status'   => $settings->output_status
            );
        }

        private function save_post_data($post_data)
        {
            return wp_insert_post($post_data);
        }

        public function is_target_category($input_post_id, $settings)
        {
            $categories         = get_the_category($input_post_id);
            $is_target_category = false;

            foreach ($categories as $category) {
                if ($category->{'term_id'} == $settings->input_category) {
                    $is_target_category = true;
                }
            }

            return $is_target_category;
        }

        public function is_target_status($input_post_id, $settings)
        {
            $input_post_data = get_post($input_post_id);
            return ($settings->input_status == $input_post_data->post_status);
        }

        public function set_menu_pages()
        {
            $my_plugin_slug = "bilingual_all_settings";

            add_menu_page(
                'BilingalWordpress',
                'Bilingual',
                'read',
                $my_plugin_slug,
                array($this, 'all_settings_page')
            );

            add_submenu_page(
                $my_plugin_slug,
                'All Settings',
                'All Settings',
                'read',
                $my_plugin_slug,
                array($this, 'all_settings_page')
            );

            add_submenu_page(
                $my_plugin_slug,
                'History',
                'History',
                'moderate_comments',
                'bilingual_history',
                array($this, 'history_page')
            );

            add_submenu_page(
                null,
                null,
                null,
                'manage_options',
                'bilingual_detail_settings',
                array($this, 'detail_settings_page')
            );

            add_submenu_page(
                null,
                null,
                null,
                'manage_options',
                'bilingual_translate',
                array($this, 'translate_page')
            );
        }

        public function all_settings_page()
        {
            Bilingual_All_Settings_Page::output();
        }

        public function detail_settings_page()
        {
            Bilingual_Detail_Settings_Page::output();
        }

        public function history_page()
        {
            Bilingual_History_Page::output();
        }

        public function translate_page()
        {
            Bilingual_Translate_Page::output();
        }
    }
}
