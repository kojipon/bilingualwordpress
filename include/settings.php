<?php

if (! class_exists('Bilingual_Settings')) {

    /**
     * Translation settings
     *
     * It manages the input / output language of each translation sentence
     * and the cloud translation setting.
     *
     * @access public
     * @author KOJIPON <kojipon@gmail.com>
     * @copyright 2017 KOJIPON All Rights Reserved
     * @category WordpressPlugin
     */

    define('BILINGUAL_SETTINGS_NOT_SET', 'not set');
    define('BILINGUAL_SETTINGS_NUM', 10);
    define('BILINGUAL_SETTINGS_DB_TABLE', 'bilingual_settings%03d');

    class Bilingual_Settings
    {
        private $var = array();

        public static $all_languages = array(
            'Afrikaans' => 'af',
            'Albanian' => 'sq',
            'Amharic' => 'am',
            'Arabic' => 'ar',
            'Armenian' => 'hy',
            'Azeerbaijani' => 'az',
            'Basque' => 'eu',
            'Belarusian' => 'be',
            'Bengali' => 'bn',
            'Bosnian' => 'bs',
            'Bulgarian' => 'bg',
            'Catalan' => 'ca',
            'Cebuano' => 'ceb',
            'Chichewa' => 'ny',
            'Chinese (Simplified)' => 'zh-CN',
            'Chinese (Traditional)' => 'zh-TW',
            'Corsican' => 'co',
            'Croatian' => 'hr',
            'Czech' => 'cs',
            'Danish' => 'da',
            'Dutch' => 'nl',
            'English' => 'en',
            'Esperanto' => 'eo',
            'Estonian' => 'et',
            'Filipino' => 'tl',
            'Finnish' => 'fi',
            'French' => 'fr',
            'Frisian' => 'fy',
            'Galician' => 'gl',
            'Georgian' => 'ka',
            'German' => 'de',
            'Greek' => 'el',
            'Gujarati' => 'gu',
            'Haitian Creole' => 'ht',
            'Hausa' => 'ha',
            'Hawaiian' => 'haw',
            'Hebrew' => 'iw',
            'Hindi' => 'hi',
            'Hmong' => 'hmn',
            'Hungarian' => 'hu',
            'Icelandic' => 'is',
            'Igbo' => 'ig',
            'Indonesian' => 'id',
            'Irish' => 'ga',
            'Italian' => 'it',
            'Japanese' => 'ja',
            'Javanese' => 'jw',
            'Kannada' => 'kn',
            'Kazakh' => 'kk',
            'Khmer' => 'km',
            'Korean' => 'ko',
            'Kurdish' => 'ku',
            'Kyrgyz' => 'ky',
            'Lao' => 'lo',
            'Latin' => 'la',
            'Latvian' => 'lv',
            'Lithuanian' => 'lt',
            'Luxembourgish' => 'lb',
            'Macedonian' => 'mk',
            'Malagasy' => 'mg',
            'Malay' => 'ms',
            'Malayalam' => 'ml',
            'Maltese' => 'mt',
            'Maori' => 'mi',
            'Marathi' => 'mr',
            'Mongolian' => 'mn',
            'Burmese' => 'my',
            'Nepali' => 'ne',
            'Norwegian' => 'no',
            'Pashto' => 'ps',
            'Persian' => 'fa',
            'Polish' => 'pl',
            'Portuguese' => 'pt',
            'Punjabi' => 'ma',
            'Romanian' => 'ro',
            'Russian' => 'ru',
            'Samoan' => 'sm',
            'Scots Gaelic' => 'gd',
            'Serbian' => 'sr',
            'Sesotho' => 'st',
            'Shona' => 'sn',
            'Sindhi' => 'sd',
            'Sinhala' => 'si',
            'Slovak' => 'sk',
            'Slovenian' => 'sl',
            'Somali' => 'so',
            'Spanish' => 'es',
            'Sundanese' => 'su',
            'Swahili' => 'sw',
            'Swedish' => 'sv',
            'Tajik' => 'tg',
            'Tamil' => 'ta',
            'Telugu' => 'te',
            'Thai' => 'th',
            'Turkish' => 'tr',
            'Ukrainian' => 'uk',
            'Urdu' => 'ur',
            'Uzbek' => 'uz',
            'Vietnamese' => 'vi',
            'Welsh' => 'cy',
            'Xhosa' => 'xh',
            'Yiddish' => 'yi',
            'Yoruba' => 'yo',
            'Zulu' => 'zu'
        );

        public static $all_page_status = array(
            'Publish' => 'publish',
            'Private' => 'private',
            'Draft'   => 'draft'
        );

        public static $all_active_status= array(
            'Active'   => 'active',
            'Inactive' => 'inactive'
        );

        public static $defaults = array(
            'setting_id'      => 1,
            'active_status'   => 'inactive',
            'input_category'  => BILINGUAL_SETTINGS_NOT_SET,
            'input_language'  => 'ja',
            'input_status'    => 'publish',
            'output_category' => BILINGUAL_SETTINGS_NOT_SET,
            'output_language' => 'en',
            'output_status'   => 'publish',
            'api_type'        => 'google',
            'api_token'       => BILINGUAL_SETTINGS_NOT_SET
        );

        public function __construct($setting_id = null)
        {
            if (is_numeric($setting_id)) {
                $settings = $this->get_settings($setting_id);
                $this->set_settings($settings);
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

        public function save()
        {
            $settings_db_table = $this->get_settings_db_table($this->setting_id);
            update_option($settings_db_table, maybe_serialize($this));
        }

        private function valid_key($key)
        {
            return in_array($key, array_keys(self::$defaults));
        }

        private function get_settings_db_table($setting_id)
        {
            return sprintf(BILINGUAL_SETTINGS_DB_TABLE, $setting_id);
        }

        private function get_settings($setting_id = null)
        {
            $settings_db_table = $this->get_settings_db_table($setting_id);
            $settings = maybe_unserialize(get_option($settings_db_table));
            if ($settings->setting_id == null) {
                $settings = new StdClass();
            }
            $settings->setting_id = $setting_id;

            foreach (self::$defaults as $key => $value) {
                if ($settings->$key == null) {
                    $settings->$key = $value;
                }
            }

            return $settings;
        }

        private function set_settings($settings)
        {
            foreach (self::$defaults as $key => $value) {
                $this->$key = $settings->$key == null ? $value : $settings->$key;
            }
        }

        public static function get_all()
        {
            $all_settings = array();

            for ($setting_id = 1; $setting_id <= BILINGUAL_SETTINGS_NUM; $setting_id++) {
                array_push($all_settings, new Bilingual_Settings($setting_id));
            }

            return $all_settings;
        }

        public static function get_active()
        {
            $all_settings = self::get_all();
            $active_settings = array();

            foreach ($all_settings as $settings) {
                if ($settings->active_status == 'active') {
                    array_push($active_settings, $settings);
                }
            }

            return $active_settings;
        }
    }
}
