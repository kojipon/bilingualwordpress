<?php

if (! class_exists('Bilingual_History_Page')) {

    /**
     * History Page
     *
     * It manages the history of translation processing.
     *
     *
     * @access public
     * @author KOJIPON <kojipon@gmail.com>
     * @copyright 2017 KOJIPON All Rights Reserved
     * @category WordpressPlugin
     */

    require_once('settings.php');
    require_once('history.php');

    define('BILINGUAL_SUCCESS_HTML', '<b style="color: white; background-color: green">&nbsp;Success&nbsp;</b>');
    define('BILINGUAL_FAILURE_HTML', '<b style="color: white; background-color: #d54e21">&nbsp;Failure&nbsp;</b>');
    define('BILINGUAL_TITLE_LENGTH', 20);

    class Bilingual_History_Page
    {
        public static function gen_data()
        {
            $histories = Bilingual_History::get_all();
            if (!is_array($histories)) {
                $histories = array();
            }
            $histories = array_reverse($histories);

            $history_rows = (count($histories) == 0) ? '<tr><td colspan="5"><br><center><b><font color=red>NO DATA</font></b></center><br></td></tr>' : '';
            foreach ($histories as $history) {
                $post_data  = get_post($history['result']['input_post_id']);
                $title      = sprintf('<a href="%s">%s</a>', get_permalink($history['result']['input_post_id']), isset($post_data) ? mb_strimwidth(htmlspecialchars($post_data->post_title), 0, BILINGUAL_TITLE_LENGTH, '...') : '');
                $content = isset($history['result']['output_post_id']) ? htmlspecialchars(get_post($history['result']['output_post_id'])->post_content) : '';
                $trimed_content = mb_strimwidth($content, 0, BILINGUAL_TITLE_LENGTH, '...');
                $status     = $history['result']['status'] ? BILINGUAL_SUCCESS_HTML : BILINGUAL_FAILURE_HTML;
                $translate_url = esc_url(admin_url('admin.php?page=bilingual_translate'));
                $button     = $history['result']['status'] ? sprintf('<a href="%s" class="button button-small right">Show translated</a>', get_permalink($history['result']['output_post_id'])) : "<form method='post' action='{$translate_url}'><input type='hidden' name='bilingual_history_id' value='{$history['history_id']}'><input type='hidden' name='page' value='bilingual_translate'><input type='submit' class='button button-primary button-small right' value='Retry translation'></form>";
                $created_at = date(DATE_ISO8601, $history['result']['created_at']);
                $input_language  = array_search($history['settings']->input_language, Bilingual_Settings::$all_languages);
                $output_language = array_search($history['settings']->output_language, Bilingual_Settings::$all_languages);
                $error = htmlspecialchars($history['result']['error']);
                $trimed_error = mb_strimwidth($error, 0, BILINGUAL_TITLE_LENGTH, '...');
                $note = isset($history['result']['output_post_id']) ? $content : $error;
                $trimed_note = isset($history['result']['output_post_id']) ? $trimed_content : $trimed_error;

                $history_rows .= <<<EOD
<tr>
    <th>{$status}</th>
    <th><span title="{$post_data->post_title}">{$title}</span></th>
    <th>{$created_at}</th>
    <th>{$input_language} -> {$output_language}</th>
    <th><span title="{$note}">{$trimed_note}</span></th>
    <th>{$button}</th>
</tr>
EOD;
            }

            return array(
                'history_num'  => Bilingual_History::$history_num,
                'history_rows' => $history_rows
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
    <h1>Bilingual Transaction Logs</h1>
    <form action="" method="post">
        <table class="wp-list-table widefat">
            <thead>
                <tr>
                    <th colspan="6"><b>Transaction Logs</b>&nbsp;<small>(Display upto {$data['history_num']} settings)</small></th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <th></th>
                    <th>Original Title</th>
                    <th>Date</th>
                    <th>Language</th>
                    <th>Note</th>
                    <th></th>
                </tr>
                {$data['history_rows']}
            </tbody>
        </table>
    </form>
</div>
EOD;
        }
    }
}
