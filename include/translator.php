<?php

if (! class_exists('Bilingual_Translator')) {

    /**
     * Translator
     *
     * It is a base class for
     * performing translation processing corresponding to every API.
     *
     * @access public
     * @author KOJIPON <kojipon@gmail.com>
     * @copyright 2017 KOJIPON All Rights Reserved
     * @category WordpressPlugin
     */

    define('BILINGUAL_GOOGLE', 'google');
    define('BILINGUAL_GOOGLE_CONTENT_MAX_LENGTH', 1800);
    define('BILINGUAL_GOOGLE_API_URL', 'https://www.googleapis.com/language/translate/v2');

    class Bilingual_Translator
    {
        private $settings = null;

        public function __construct($settings)
        {
            $this->settings = $settings;
        }

        public function translate($text)
        {
            switch ($this->settings->api_type) {
                case BILINGUAL_GOOGLE:
                    $text = $this->translate_by_google($text);
                    break;
                default:
                    error_log('Unknown API Type:' . $this->settings->api_type);
            }

            return $text;
        }

        private function translate_by_google($text)
        {
            if ($this->valid_google_spec($text)) {
                $text = substr($text, 0, BILINGUAL_GOOGLE_CONTENT_MAX_LENGTH);
                error_log('Too long content length: ' . $text);
            }

            $text_list = str_split($text, BILINGUAL_GOOGLE_CONTENT_MAX_LENGTH);
            $translated_text = '';

            foreach ($text_list as $text) {
                $translated_text .= $this->_translate_by_google($text);
            }

            return $translated_text;
        }

        private function _translate_by_google($text)
        {
            $api_url  = $this->gen_google_translate_api_url() . urlencode($text);
            $response = wp_remote_get($api_url);
            $translated_text = null;

            if ($response['response']['code'] == 200) {
                $translated_text = json_decode($response['body'])->{'data'}->{'translations'}[0]->{'translatedText'};
                if (gettype($translated_text) != 'string') {
                    throw new Exception('Failed to translate: ' . $text);
                }
            } else {
                $msg = json_decode($response['body'])->{'error'}->{'message'};
                if (gettype($msg) == 'string') {
                    throw new Exception('Failed to send http request [' . $msg . ']:' . $text);
                } else {
                    throw new Exception('Failed to send http request:' . $text);
                }
            }

            return $translated_text;
        }

        private function valid_google_spec($text)
        {
            return strlen($text) > BILINGUAL_GOOGLE_CONTENT_MAX_LENGTH;
        }

        private function gen_google_translate_api_url()
        {
            return urlencode(BILINGUAL_GOOGLE_API_URL .
                '?key=' .
                $this->settings->api_token .
                '&source=' .
                $this->settings->input_language .
                '&target=' .
                $this->settings->output_language .
                '&format=html&prettyprint=false&q=');
        }
    }
}
