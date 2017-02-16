<?php

//    bilingual_save_transaction_log(
//        $output_post_data,
//        $settings,
//        array(
//            'input_post_id'  => $input_post_id,
//            'output_post_id' => $output_post_id,
//            'status'         => !isset($error),
//            'error'          => $error,
//            'created_at'     => time()
//        )
//    );

define('BILINGUAL_SUCCESS_HTML', '<b style="color: white; background-color: green">&nbsp;Success&nbsp;</b>');
define('BILINGUAL_FAILURE_HTML', '<b style="color: white; background-color: #d54e21">&nbsp;Failure&nbsp;</b>');
define('BILINGUAL_TITLE_LENGTH', 20);

function bilingual_transaction_logs_page()
{
    $log_num = BILINGUAL_TRANSACTION_LOG_NUM;
    $logs = bilingual_get_transaction_logs();
    if (!is_array($logs)) {
        $logs = array();
    }
    $logs = array_reverse($logs);

    $log_rows = (count($logs) == 0) ? '<tr><td colspan="5"><br><center><b><font color=red>NO DATA</font></b></center><br></td></tr>' : '';
    foreach ($logs as $log) {
        $post_data  = get_post($log['result']['input_post_id']);
        $title      = sprintf('<a href="%s">%s</a>', get_permalink($log['result']['input_post_id']), isset($post_data) ? mb_strimwidth(htmlspecialchars($post_data->post_title), 0, BILINGUAL_TITLE_LENGTH, '...') : '');
        $status     = $log['result']['status'] ? BILINGUAL_SUCCESS_HTML : BILINGUAL_FAILURE_HTML;
        $translate_url = esc_url(admin_url('admin.php?page=bilingual_translate'));
        $button     = $log['result']['status'] ? sprintf('<a href="%s" class="button button-small right">Show translated</a>', get_permalink($log['result']['output_post_id'])) : "<form method='post' action='{$translate_url}'><input type='hidden' name='bilingual_setting_id' value='{$log['setting_id']}'><input type='hidden' name='page' value='bilingual_translate'><input type='submit' class='button button-primary button-small right' value='Retry translation'></form>";
        $created_at = date(DATE_ISO8601, $log['result']['created_at']);
        $input_language  = array_search($log['settings']['bilingual_input_language'], BILINGUAL_LANGUAGES);
        $output_language = array_search($log['settings']['bilingual_output_language'], BILINGUAL_LANGUAGES);
        $error       = mb_strimwidth(htmlspecialchars($log['result']['error']), 0, BILINGUAL_TITLE_LENGTH, '...');
        $error_title = htmlspecialchars($log['result']['error']);
        $log_rows .= <<<EOD
<tr>
    <th>{$status}</th>
    <th><span title="{$post_data->post_title}">{$title}</span></th>
    <th>{$created_at}</th>
    <th>{$input_language} -> {$output_language}</th>
    <th><span title="{$error_title}">{$error}</span></th>
    <th>{$button}</th>
</tr>
EOD;
    }

    echo <<<EOD
<div class="wrap">
    <h1>Bilingual Transaction Logs</h1>
    <form action="" method="post">
        <table class="wp-list-table widefat">
            <thead>
                <tr>
                    <th colspan="6"><b>Transaction Logs</b>&nbsp;<small>(Display upto ${log_num} settings)</small></th>
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
                {$log_rows}
            </tbody>
        </table>
    </form>
</div>
EOD;
}

define('BILINGUAL_TRANSACTION_LOG_NUM', 500);
define('BILINGUAL_TRANSACTION_LOG_KEY', 'bilingual_transaction_log');

function bilingual_save_transaction_log($post_data, $settings, $result)
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
    $logs = bilingual_get_transaction_logs();
    if (!is_array($logs)) {
        $logs = array();
    }

    array_push(
        $logs,
        array(
            'setting_id'       => md5(uniqid(rand(), 1)),
            'post_data' => $content,
            'settings'  => $settings,
            'result'    => $result
        )
    );

    if (count($logs) > BILINGUAL_TRANSACTION_LOG_NUM) {
        array_splice($logs, count($logs) - BILINGUAL_TRANSACTION_LOG_NUM, BILINGUAL_TRANSACTION_LOG_NUM);
    }

    bilingual_update_transaction_logs($logs);

    return true;
}

function bilingual_get_transaction_logs()
{
    return maybe_unserialize(get_option(BILINGUAL_TRANSACTION_LOG_KEY));
}

function bilingual_get_transaction_log($setting_id)
{
    $logs = bilingual_get_transaction_logs();
    $setting_id_log  = null;

    foreach ($logs as $log) {
        if ($log['setting_id'] == $setting_id) {
            $setting_id_log = $log;
        }
    }

    return $setting_id_log;
}

function bilingual_update_transaction_logs($logs)
{
    update_option(BILINGUAL_TRANSACTION_LOG_KEY, $logs);
}

function bilingual_translate_page()
{
    $setting_id = null;
    if (isset($_POST['bilingual_setting_id'])) {
        $setting_id = $_POST['bilingual_setting_id'];
        $log = bilingual_get_transaction_log($setting_id);
        if (isset($log)) {
            bilingual_run(array($log['result']['input_post_id'], $log['settings']));
        }
    }
    bilingual_transaction_logs_page();
}
