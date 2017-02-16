<?php

if (! class_exists('Bilingual_All_Settings_Page')) {

    /**
     * All Settings Page
     *
     * List all translation settings.
     *
     *
     * @access public
     * @author KOJIPON <kojipon@gmail.com>
     * @copyright 2017 KOJIPON All Rights Reserved
     * @category WordpressPlugin
     */

    require_once('settings.php');

    define('BILINGUAL_ACTIVE_HTML', '<b style="color: white; background-color: green">&nbsp;Active&nbsp;</b>');
    define('BILINGUAL_INACTIVE_HTML', '<b style="color: white; background-color: #d54e21">&nbsp;Inactive&nbsp;</b>');

    class Bilingual_All_Settings_Page
    {
        private function gen_setting_tr_tags()
        {
            $all_settings = Bilingual_Settings::get_all();
            $setting_tr_tags = '';
            $setting_tr_fmt = <<<EOD
<tr>
    <th>%s</th>
    <td>%s</td>
    <td>-></td>
    <td>%s</td>
    <td>
        <a class="button button-small right" href="%s">Edit</button>
    </td>
</tr>
EOD;
            for ($i = 0; $i < count($all_settings); $i++) {
                $categories = array();
                foreach (get_categories('hide_empty=0') as $category) {
                    $categories[$category->term_id] = $category->name;
                }
                $settings = $all_settings[$i];
                $setting_tr_tags .= sprintf(
                    $setting_tr_fmt,
                    $settings->active_status == 'active' ? BILINGUAL_ACTIVE_HTML : BILINGUAL_INACTIVE_HTML,
                    empty($categories[$settings->input_category]) ? 'Not set' : sprintf(
                        '%s / %s',
                        array_search($settings->input_language, Bilingual_Settings::$all_languages),
                        $categories[$settings->input_category]
                     ),
                    empty($categories[$settings->output_category]) ? 'Not set' : sprintf(
                        '%s / %s',
                        array_search($settings->output_language, Bilingual_Settings::$all_languages),
                        $categories[$settings->output_category]
                    ),
                    esc_url(admin_url('admin.php?page=bilingual_detail_settings&bilingual_setting_id=' . $settings->setting_id))
                );
            }
        
            return $setting_tr_tags;
        }

        public static function gen_data()
        {
            return array(
                'setting_tr_tags' => self::gen_setting_tr_tags()
            );
        }

        public static function output()
        {
            $data = self::gen_data();
            echo self::html($data);
        }

        public static function html($data)
        {
            return <<<EOD
<div class="wrap">
    <h1>Bilingual Wordpress All Settings</h1>
    <table class="wp-list-table widefat">
        <thead>
            <tr>
                <th colspan="5"><b>All Settings of Translation</b></th>
            </tr>
        </thead>
        <tbody>
            {$data['setting_tr_tags']}
        </tbody>
    </table>
</div>
EOD;
        }
    }
}
