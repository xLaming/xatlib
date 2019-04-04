<?php

namespace Core;

use Core\Settings;

class Utils {    
    /* caching only */
    static $powers = [];
    static $pow2 = [];
    
    /**
    * Helper for loading web pages
    **/
    static function loadSiteFromUrl(String $url, $post = array()) {
        $opts = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER         => false,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_POST           => (empty($post) ? false : true),
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_TIMEOUT        => 5,
            CURLOPT_MAXREDIRS      => 2,
            CURLOPT_POSTFIELDS     => http_build_query($post)
        ];
        $ch = curl_init($url);
        curl_setopt_array($ch, $opts);
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }

    /**
    * Helper for loading static pow2
    **/
    static function setPow2() {
        if (empty(Utils::$pow2)) {
            Utils::$pow2 = self::loadSiteFromUrl(Settings::XAT_LINKS['p2']);
        }
        
        if (empty(Utils::$pow2)) {
            return false;
        }        
        
        return true;
    }
    
    /**
    * Helper for loading static powers
    **/
    static function setPowers() {
        if (empty(Utils::$powers)) {
            Utils::$powers = self::loadSiteFromUrl(Settings::XAT_LINKS['pw']);
        }
        
        if (empty(Utils::$powers)) {
            return false;
        }        
        
        return true;
    }
    
    /**
    * Convert language code to full language
    **/
    static function twoToLang(String $code) {
        if (array_key_exists($code, Settings::XAT_LANGUAGES)) {
            return Settings::XAT_LANGUAGES[$code];
        }
        
        return 'unknown';
    }

    /**
    * Make everything be JSON, thanks Jedi big change!!!
    **/
    static function makeJson($text, $error = false) {
        $final = new \stdClass();

        $final->status = $error ? 'fail' : 'success';

        if (is_array($text)) {
            $final->data = $text;
        } 
        else 
        {
            $final->result = $text;
        }

        return json_encode($final);
    }

}