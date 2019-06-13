<?php

namespace Core;

use Core\Utils;

class Xatlib {
    /**
    * Get shortname prices
    **/
    final static function ShortnamePrice(String $value) { 
        $getPage = Utils::loadSiteFromUrl(
            Settings::XAT_LINKS['sn'], 
            [
                'GroupName' => $value, 
                'Quote'     => 1
            ]
        );
        
        if (empty($getPage)) { // page is offline
            return false;
        }
        
        $json = json_decode($getPage, false);
        
        if (empty($json->Err)) {
            return Utils::makeJson($json->Xats);
        }
        
        return Utils::makeJson(
            strip_tags(reset($json->Err)),
            true // result fail
        );
    }
    
    /**
    * Get chat prices
    **/
    final static function ChatPrice(String $value) {
        $getPage = Utils::loadSiteFromUrl(
            Settings::XAT_LINKS['cp'], 
            [
                'GroupName' => $value, 
                'Quote'     => 1
            ]
        );
        
        if (empty($getPage)) { // page is offline
            return false;
        }
        
        $json = json_decode($getPage, false);
        
        if (empty($json->Err)) {
            return Utils::makeJson($json->Xats);
        }
        
        return Utils::makeJson(
            strip_tags(reset($json->Err)),
            true // result fail
        );
    }
    
    /**
    * Get information about chats
    **/
    final static function ChatInformation(String $value) {
        $getPage = Utils::loadSiteFromUrl(
            sprintf(
                Settings::XAT_LINKS['ci'], 
                $value
            )
        );

        $json = json_decode($getPage, false);
        
        if (empty($json)) {
            return false;
        }
        
        $data = explode(';=', $json->a);
        $flag = !empty($json->t) ? $json->t : 0;
        $tabs = !empty($json->tabs) ? $json->tabs : 0;
        $list = array();
        
        foreach ($tabs as $t) {
            $list[] = utf8_decode($t->label);
        }
        
        $result = [
            'id'           => $json->id,
            'name'         => $json->g,
            'desc'         => $json->d,
            'inner'        => $data[0],
            'outer'        => !empty($json->gb) ? $json->gb : 0,
            'tabbedchat'   => $data[1],
            'tabbedchatid' => $data[2],
            'language'     => $data[3],
            'radio'        => $data[4],
            'buttons'      => $data[5],
            'tabs'         => implode(', ', $list),
            'botid'        => !empty($json->bot) ? $json->bot : 0,
            'trusted'      => $flag & 256 ? 1 : 0,
        ];
        
        return Utils::makeJson($result);
    }
    
    /**
    * Convert days to xats
    **/
    final static function DaysToXats(Int $value) {
        $result = round($value * Settings::XAT_CURRENCY);
        return Utils::makeJson($result);
    }
    
    /**
    * Convert xats to days
    **/
    final static function XatsToDays(Int $value) {
        $result = round($value / Settings::XAT_CURRENCY);
        return Utils::makeJson($result);
    }
    
    /**
    * Get information about the latest power
    **/
    final static function LatestPower() {
        if (!Utils::setPowers() || !Utils::setPow2()) {
            return false;
        }
        
        $pow2   = json_decode(Utils::$pow2, true);
        $powers = json_decode(Utils::$powers, true);
        $pawns  = array(); // caching
        
        foreach ($pow2[7][1] as $k => $v) {
            if ($pow2[0][1]['id'] === $v[0]) {
                $pawns[$k] = $v[1];
            }
        }
        
        $result = [
            'id'      => $pow2[0][1]['id'],
            'name'    => array_search($pow2[0][1]['id'], $pow2[6][1]),
            'smilies' => array_keys($pow2[4][1], $pow2[0][1]['id']),
            'pawns'   => $pawns
        ];
         
        if (!empty($powers[$result['id']])) {
            $xatsOrDays = 
                !empty($powers[$result['id']]['x']) 
                    ? $powers[$result['id']]['x'] . ' xats' 
                    : $powers[$result['id']]['d']  . ' days';
            
            $result['is_epic']  = $powers[$result['id']]['f'] & 8 ? 1 : 0;
            $result['is_game']  = $powers[$result['id']]['f'] & 0x80 ? 1 : 0;
            $result['is_allp']  = $powers[$result['id']]['f'] & 0x401 ? 1 : 0;
            $result['is_group'] = $powers[$result['id']]['f'] & 0x800 ? 1 : 0;
            $result['status']   = $powers[$result['id']]['f'] & 0x2000 ? 'limited' : 'unlimited';
            $result['desc']     = $powers[$result['id']]['d1'];
            $result['price']    = $xatsOrDays;
        }
        
        return Utils::makeJson($result);
    }
    
    /**
    * Get chat promotion prices
    **/
    final static function ChatPromotionPrice(Float $value) {
        $getPage = Utils::loadSiteFromUrl(
            Settings::XAT_LINKS['pp'], 
            [
                'GroupName' => 'test',
                'XatsDays'  => 'Xats',
                'Hours'     => (float) $value,
                'Lang'      => 'en',
                'Quote'     => 1
            ]
        );
        
        if (empty($getPage)) { // page is offline
            return false;
        }
        
        $fixed = preg_replace('/[\x00-\x1F\x7F-\xFF]/', '', $getPage); //someone missed invisible chars in xat internal's
        $json = json_decode($fixed, false);
        
        if (empty($json->Err)) {
            return Utils::makeJson([
                'time' => $json->Hours,
                'xats' => $json->Xats,
                'days' => $json->Days
            ]);
        }
        
        return Utils::makeJson(
            strip_tags(reset($json->Err)),
            true
        );
    }
    
    /**
    * Verify if the banner URL is approved
    **/
    final static function IsBannerApproved(String $value) {
        if (false === filter_var($value, FILTER_VALIDATE_URL)) {
            return false;
        }
        
        $getPage = Utils::loadSiteFromUrl(
            Settings::XAT_LINKS['pp'], 
            [
                'GroupName' => 'test',
                'XatsDays'  => 'Xats',
                'Hours'     => 1,
                'Lang'      => 'en',
                'Quote2'    => 1,
                'AdImg'     => $value
            ]
        );

        if (empty($getPage)) { // page is offline
            return false;
        }
        
        $fixed = preg_replace('/[\x00-\x1F\x7F-\xFF]/', '', $getPage); //someone missed invisible chars in xat internal's
        $json = json_decode($fixed, false);
        
        if (empty($json->Err)) {
            return Utils::makeJson(
                1 === $json->NeedApproval ? 0 : 1
            );
        }
        
        return Utils::makeJson(
            strip_tags(reset($json->Err)),
            true
        );
    }
    
    /**
    * Get power information
    **/
    final static function PowerInformation(String $value) {
        if (!Utils::setPowers()) {
            return false;
        }
        
        $powers = json_decode(Utils::$powers, true); // not obj
        
        
        foreach ($powers as $k => $v) {
            if (strtolower($value) === $v['s']) {
                $xatsOrDays = 
                    !empty($powers[$k]['x']) 
                        ? $powers[$k]['x'] . ' xats' 
                        : $powers[$k]['d']  . ' days';
                    
                $result = [
                    'id'       => $k,
                    'name'     => $v['s'],
                    'is_epic'  => $powers[$k]['f'] & 8 ? true : false,
                    'is_game'  => $powers[$k]['f'] & 0x80 ? true : false,
                    'is_allp'  => $powers[$k]['f'] & 0x401 ? true : false,
                    'is_group' => $powers[$k]['f'] & 0x800 ? true : false,
                    'desc'     => $powers[$k]['f'] & 0x2000 ? 'limited' : 'unlimited',
                    'price'    => $xatsOrDays,
                ];
                
                return Utils::makeJson($result);
            }
        }
        
        return false;
    }
    
    /**
    * Get all xat store prices
    **/
    final static function StorePrices() {
        if (!Utils::setPowers()) {
            return false;
        }
        
        $powers = json_decode(Utils::$powers, true); // not obj
        
        $result = array(); // init
        
        foreach ($powers as $k => $v) {
            $xatsOrDays = 
                !empty($powers[$k]['x']) 
                    ? $powers[$k]['x'] . ' xats' 
                    : $powers[$k]['d']  . ' days';
                
                
            if (empty($powers[$k]['f'])) {
                $powers[$k]['f'] = 0;
            }
            
            $result[$k] = [
                'name'     => $v['s'],
                'is_epic'  => $powers[$k]['f'] & 8 ? 1 : 0,
                'is_game'  => $powers[$k]['f'] & 0x80 ? 1 : 0,
                'is_allp'  => $powers[$k]['f'] & 0x401 ? 1 : 0,
                'is_group' => $powers[$k]['f'] & 0x800 ? 1 : 0,
                'desc'     => $powers[$k]['f'] & 0x2000 ? 'limited' : 'unlimited',
                'price'    => $xatsOrDays,
            ];
        }
        
        return Utils::makeJson($result);
    }
    
    /**
    * Get various information about xat powers
    **/
    final static function FairTradePrices(String $value) {
        /* This is now deprecated
         * Please check: https://github.com/xLaming/xat-fairtrade
         * It is the official xattrade.com's api
         */
    }
    
    /**
    * Get chats promoted
    **/
    final static function ChatsPromoted() {
        $getPage = Utils::loadSiteFromUrl(Settings::XAT_LINKS['pm']);
        
        if (empty($getPage)) { // page is offline
            return false;
        }
        
        $json = json_decode($getPage, false);
        
        $result = array(); // caching
        
        foreach ($json as $k => $v) {
            $lang = Utils::twoToLang($k);
            foreach ($v as $x) {
                if (in_array(strtolower($x->n), ['assistance', 'chat'])) {
                    continue;
                }
                
                if (empty($x->t)) {
                    $promo = 'autopromo';
                } else if ($x->t > time()) {
                    $promo = $x->t - time() . ' seconds';
                } else {
                    $promo = 'ended';
                }
                
                $chat = [
                    'name' => $x->n,
                    'desc' => $x->d,
                    'bg'   => $x->a,
                    'ends' => $promo
                ];
                
                $result[$lang][$x->i] = $chat;
            }
        }
        
        return Utils::makeJson($result);
    }
    
    /**
    * Convert ID to register
    **/
    final static function IdToReg(Int $value) {
        if(array_key_exists($value, Settings::XAT_SPECIAL)) {
            return Utils::makeJson(Settings::XAT_SPECIAL[$value]);
        }
        
        $getPage = Utils::loadSiteFromUrl(
            sprintf(
                Settings::XAT_LINKS['rg'], 
                $value
            )
        );

        if (empty($getPage) || strlen($getPage) > 12) {
            return false;
        }
        
        return Utils::makeJson($getPage);
    }
    
    /**
    * Convert register to ID
    **/
    final static function RegToId(String $value) {
        $getPage = Utils::loadSiteFromUrl(
            sprintf(
                Settings::XAT_LINKS['id'], 
                $value
            )
        );
        
        if (empty($getPage)) {
            return false;
        }
        
        return Utils::makeJson($getPage);
    }
    
    /**
    * Verify if the chat is delisted
    **/
    final static function DelistCheck(String $value) {
        $getPage = Utils::loadSiteFromUrl(
            Settings::XAT_LINKS['pp'], 
            [
                'YourEmail' => 'test',
                'password'  => '1234',
                'agree'     => 'ON',
                'XatsDays'  => 'Xats',
                'GroupName' => $value,
                'Lang:'     => 'en',
                'Xats'      => 100,
                'Hours'     => 1,
                'Days'      => 8,
                'Promote'   => 1
            ]
        );
        
        if (empty($getPage)) { // page is offline
            return false;
        }
        
        $fixed = preg_replace('/[\x00-\x1F\x7F-\xFF]/', '', $getPage); //someone missed invisible chars in xat internal's
        $json = json_decode($fixed, false);
        
        $parseError = strip_tags(reset($json->Err));
        
        if (false !== strpos($parseError, '(2)')) { //check for error (2)
            return Utils::makeJson(true);
        }
        
        return Utils::makeJson(false);
    }
    
    /**
    * Search for xat chats
    **/
    final static function ChatSearch(String $value) {
        $getPage = Utils::loadSiteFromUrl(
            sprintf(
                Settings::XAT_LINKS['cs'], 
                $value
            )
        );
        
        if (empty($getPage)) { // page is offline
            return false;
        }

        $json = json_decode($getPage, false);
        
        $result = array();
        
        foreach ($json as $v) {
            $result[$v->g] = [
                'desc' => $v->d,
                'pic'  => $v->a,
            ];
        }
        
        return Utils::makeJson($result);
    }
    
    /**
    * Get countdown from the latest power
    **/
    final static function PowerReleaseCountdown() {
        $getPage = Utils::loadSiteFromUrl(Settings::XAT_LINKS['ad']);

        if (empty($getPage)) {
            return false;
        }
        
        $json = json_decode($getPage, false);
        
        if (1 === $json->t) { // no countdown
            return 'no countdown';
        } else if (time() > $json->t) { // already released
            return 'released';
        }
        
        $result = ($json - time()) . ' seconds';
        
        return Utils::makeJson($result); //will be released in X seconds
    }
    
    /**
    * Load the hug list
    **/
    final static function HugList() {
        if (!Utils::setPowers() || !Utils::setPow2()) {
            return false;
        }
        
        $powers = json_decode(Utils::$powers, true);
        $pow2 = json_decode(Utils::$pow2, false);
        $result = array();
        
        foreach ($pow2[3][1] as $k => $v) {
            if (10000 > $v) {
                $result[$k] = [
                    'usage' => '/hug ' . $k,
                    'power' => $powers[$v]['s']
                ];
            }
        }
        
        return Utils::makeJson($result);
    }
    
    /**
    * Load the jinx list
    **/
    final static function JinxList() {
        if (!Utils::setPowers() || !Utils::setPow2()) {
            return false;
        }
        
        $powers = json_decode(Utils::$powers, true);
        $pow2 = json_decode(Utils::$pow2, false);
        $result = array();
        
        foreach ($pow2[3][1] as $k => $v) {
            if (10000 < $v) {
                $pid = $v % 10000;
                
                $result[$k] = [
                    'usage' => '/jinx ' . $k,
                    'power' => $powers[$pid]['s']
                ];
            }
        }
        
        return Utils::makeJson($result);
    }
    
    /**
    * Get gifts from an xat user
    **/
    final static function SeeUserGifts(Int $value) {
        $getPage = Utils::loadSiteFromUrl(
            sprintf(
                Settings::XAT_LINKS['gt'], 
                $value
            )
        );
        
        if (empty($getPage)) { // page is offline
            return false;
        }
        
        $json = json_decode($getPage, false);
        $result = array();
        
        foreach ($json as $k => $v) {
            if (!is_object($v)) {
                continue;
            }
            
            $result[] = [
                'id'      => $v->id,
                'reg'     => $v->n,
                'gift'    => $v->g,
                'time'    => date('H:i:s, d/m/Y', $k),
                'title'   => $v->s,
                'message' => !empty($v->m) ? $v->m : 'private',
            ];
        }
        
        return Utils::makeJson([
            'total' => count($result),
            'gifts' => $result
        ]);
    }
    
    /**
    * See top five countries where xat is most common
    **/
    final static function PopularCountries() {
        $getPage = Utils::loadSiteFromUrl(Settings::XAT_LINKS['al']);
        
        if (empty($getPage)) {
            return false;
        }
        
        preg_match('/id="visitorPercentage">(.*?)<\//', $getPage, $matches);

        $fixed = preg_replace('/[\x00-\x1F\x7F-\xFF]/', '', $matches[1]); //someone missed invisible chars in alexa internal's
		$json = json_decode($fixed, true);
        
		$results = array();
		
		if (empty($json)) {
			return false;
		}
		
		foreach($json as $k => $v) {

			$results[$k+1] = [
				'country'     => $v['name'],
				'visitors'    => $v['visitors_percent']. '%'
			];
		}
        
        return Utils::makeJson($results);        
    }
}
