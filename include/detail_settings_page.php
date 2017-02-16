<?php

if (! class_exists('Bilingual_Detail_Settings_Page')) {

    /**
     * Detail Settings Page
     *
     * Displays the translation setting details page.
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

    class Bilingual_Detail_Settings_Page
    {
        private function gen_setting_id($setting_id = null)
        {
            if (isset($_POST['bilingual_setting_id'])) {
                $setting_id = $_POST['bilingual_setting_id'];
            } elseif (isset($_GET['bilingual_setting_id'])) {
                $setting_id = $_GET['bilingual_setting_id'];
            } else {
                $setting_id = 1;
            }

            return $setting_id;
        }

        private function gen_active_status_options($settings)
        {
            $active_status_options_fmt = "<label><input name='bilingual_active_status' type='radio' value='%s' %s>%s</label>&nbsp;\n";
            $active_status_options  = sprintf($active_status_options_fmt, 'active', $settings->active_status == 'active' ? 'checked' : '', BILINGUAL_ACTIVE_HTML);
            $active_status_options .= sprintf($active_status_options_fmt, 'inactive', $settings->active_status != 'active' ? 'checked' : '', BILINGUAL_INACTIVE_HTML);

            return $active_status_options;
        }

        private function gen_input_language_options($settings)
        {
            $input_language_options = '';

            foreach (Bilingual_Settings::$all_languages as $key => $value) {
                $input_selected_tag = '';
                if ($settings->input_language == $value) {
                    $input_selected_tag  = "selected='selected'";
                }
                $input_language_options  .= sprintf("<option %s value=%s>%s</option>\n", $input_selected_tag, $value, $key);
            }

            return $input_language_options;
        }

        private function gen_output_language_options($settings)
        {
            $output_language_options = '';

            foreach (Bilingual_Settings::$all_languages as $key => $value) {
                $output_selected_tag = '';
                if ($settings->output_language == $value) {
                    $output_selected_tag = "selected='selected'";
                }
                $output_language_options .= sprintf("<option %s value=%s>%s</option>\n", $output_selected_tag, $value, $key);
            }

            return $output_language_options;
        }

        private function gen_input_status_options($settings)
        {
            $input_status_options = '';
            foreach (Bilingual_Settings::$all_page_status as $key => $value) {
                $input_selected_tag = '';
                if ($settings->input_status == $value) {
                    $input_selected_tag  = "checked='checked'";
                }
                $input_status_fmt      = "<label><input name='bilingual_input_status' type='radio' class='tog' %s value=%s>%s</label>&nbsp;\n";
                $input_status_options .= sprintf($input_status_fmt, $input_selected_tag, $value, $key);
            }

            return $input_status_options;
        }

        private function gen_output_status_options($settings)
        {
            $output_status_options = '';
            foreach (Bilingual_Settings::$all_page_status as $key => $value) {
                $output_selected_tag = '';
                if ($settings->output_status == $value) {
                    $output_selected_tag  = "checked='checked'";
                }
                $output_status_fmt       = "<label><input name='bilingual_output_status' type='radio' class='tog' %s value=%s>%s</label>&nbsp;\n";
                $output_status_options  .= sprintf($output_status_fmt, $output_selected_tag, $value, $key);
            }

            return $output_status_options;
        }

        private function gen_input_category_options($settings)
        {
            $input_category_options = '';
            foreach (get_categories('hide_empty=0') as $category) {
                $input_selected_tag = '';
                if ($settings->input_category == $category->term_id) {
                    $input_selected_tag  = "selected='selected'";
                }
                $input_category_options .= sprintf('<option value="%s" %s>%s</option>', $category->term_id, $input_selected_tag, $category->name);
            }

            return $input_category_options;
        }

        private function gen_output_category_options($settings)
        {
            $output_category_options = '';
            foreach (get_categories('hide_empty=0') as $category) {
                $output_selected_tag = '';
                if ($settings->output_category == $category->term_id) {
                    $output_selected_tag  = "selected='selected'";
                }
                $output_category_options .= sprintf('<option value="%s" %s>%s</option>', $category->term_id, $output_selected_tag, $category->name);
            }

            return $output_category_options;
        }

        private function gen_detail_setting_options($settings)
        {
            $detail_setting_options = '';

            $categories = array();
            foreach (get_categories('hide_empty=0') as $category) {
                $categories[$category->term_id] = $category->name;
            }
       
            foreach (Bilingual_Settings::get_all() as $s) {
                if ($s->setting_id == 0) {
                    continue;
                }
                if (!isset($categories[$s->input_category])) {
                    continue;
                }
                $detail_setting_options .= sprintf(
                    '<option value="%s" %s>%s / %s -> %s / %s</option>',
                    $s->setting_id,
                    $s->setting_id == $setting->setting_id ? 'selected' : '',
                    array_search($s->input_language, Bilingual_Settings::$all_languages),
                    $categories[$s->input_category],
                    array_search($s->output_language, Bilingual_Settings::$all_languages),
                    $categories[$s->output_category]
                );
            }

            if ($detail_setting_options == '') {
                $detail_setting_options = '<option value="1" %s>There is no settings.</option>';
            }

            return $detail_setting_options;
        }

        private static function post_request($settings)
        {
            $is_post = false;
            foreach (Bilingual_Settings::$defaults as $key => $value) {
                if ($settings->$key == null) {
                    $settings->$key = $value;
                }
                if (isset($_POST["bilingual_" . $key])) {
                    $is_post = true;
                    $settings->$key = $_POST["bilingual_" . $key];
                }
            }
            if ($is_post) {
                $settings->save();
            }
        }

        public static function gen_data($setting_id = null)
        {
            $setting_id = self::gen_setting_id($setting_id);
            $settings = new Bilingual_Settings($setting_id);

            self::post_request($settings);
       
            return array(
                'setting_id'                     => $setting_id,
                'active_status_options'   => self::gen_active_status_options($settings),
                'input_language_options'  => self::gen_input_language_options($settings),
                'output_language_options' => self::gen_output_language_options($settings),
                'input_status_options'    => self::gen_input_status_options($settings),
                'output_status_options'   => self::gen_output_status_options($settings),
                'input_category_options'  => self::gen_input_category_options($settings),
                'output_category_options' => self::gen_output_category_options($settings),
                'detail_setting_options'  => self::gen_detail_setting_options($settings),
                'detail_setting_url'      => esc_url(admin_url('admin.php')),
                'edit_category_url'       => esc_url(admin_url('edit-tags.php?taxonomy=category')),
                'api_token'               => $settings->api_token
            );
        }

        public static function output($setting_id = null)
        {
            $data = self::gen_data($setting_id);
            echo self::html($data);
        }

        public static function html($data)
        {
            return <<<EOD
<div class="wrap">
    <h1>Bilingual Wordpress Detail Settings</h1>
    <table class="wp-list-table widefat">
        <thead>
            <tr>
                <th colspan="4"><b>Show Settings</b></th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>
                    <form action="{$data['detail_setting_url']}" method="get">
                        <input type="hidden" name="page" value="bilingual_detail_setting">
                        <select name="bilingual_setting_id">
                            {$data['detail_setting_options']}
                        </select>
                        <button class="button button-primary right">Select</button>
                    </form>
                </td>
            </tr>
        </tbody>
    </table>
    <br />
    <form action="" method="post" enctype="multipart/form-data">
        <input type="hidden" name="bilingual_setting_id" value="{$data['setting_id']}" /></td>
        <table class="wp-list-table widefat">
            <thead>
                <tr>
                    <th colspan="4"><b>Edit Settings</b></th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <th><label for="bilingual_active_status">Active Status</label></th>
                    <td colspan=3>
                        {$data['active_status_options']}
                    </td>
                </tr>
                <tr>
                    <th rowspan=2><label for="bilingual_input_category">Category</label></th>
                    <td>
                        <select name="bilingual_input_category">
                            {$data['input_category_options']}
                        </select>
                    </td>
                    <th><label for="bilingual_output_category">-></label></th>
                    <td>
                        <select name="bilingual_output_category">
                            {$data['output_category_options']}
                        </select>
                    </td>
                </tr>
                <tr>
                    <td colspan=3><a href="{$data['edit_category_url']}">Create a new category / Edit categories.</a></td>
                </tr>
                <tr>
                    <th><label for="bilingual_input_language">Language</label></th>
                    <td>
                        <select name="bilingual_input_language">
                            {$data['input_language_options']}
                        </select>
                    </td>
                    <th><label for="bilingual_output_language">-></label></th>
                    <td>
                        <select name="bilingual_output_language">
                            {$data['output_language_options']}
                        </select>
                    </td>
                </tr>
                <tr>
                    <th><label for="bilingual_input_status">Origin Page</label></th>
                    <td colspan="3">
                        <p>
                            {$data['input_status_options']}
                        </p>
                        <p class="description">You can automatically change the translated original page to Publish, Trash or Private.</p>
                    </td>
                </tr>
                <tr>
                    <th><label for="bilingual_output_status">Page Status</label></th>
                    <td colspan="3">
                        <p>
                            {$data['output_status_options']}
                        </p>
                        <p class="description">You can automatically change the translated page to Draft, Publish or Private.</p>
                    </td>
                </tr>
                <tr>
                    <th><label for="bilingual_api_token">Google API Token</label></th>
                    <td colspan="3">
                        <input type="text" name="bilingual_api_token" size=50 value="{$data['api_token']}" />
                        <p class="description">Authorizaton token using your Google Transrate API account.</p>
                    </td>
                </tr>
                <tr>
                    <td colspan="4">
                        <input type="submit" value="Register" class="button button-primary right" />
                        <br /><br />
                    </td>
                </tr>
            </tbody>
        </table>
    </form>
</div>
EOD;
        }
    }
}
