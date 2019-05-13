<?php

namespace Core;

class Settings {
    /* Days to xat currency */
    const XAT_CURRENCY = 13.5;
        
    /* Language short-codes */
    const XAT_LANGUAGES = [
        'ro' => 'romanian',
        'es' => 'spanish',
        'pt' => 'portuguese',
        'it' => 'italian',
        'de' => 'german',
        'th' => 'thai',
        'ar' => 'arabic',
        'tr' => 'turkish',
        'pl' => 'polish',
        'nl' => 'dutch',
        'hr' => 'croatian',
        'sr' => 'serbian',
        'fr' => 'french',
        'bs' => 'bosnian',
        'n0' => 'international',
        'en' => 'english',
    ];
        
    /* Links settings - touch only when xat changes */
    const XAT_LINKS = [
        'ad' => 'http://xat.com/json/ad.php',
        'sn' => 'https://xat.com/web_gear/chat/BuyShortName2.php',
        'cp' => 'https://xat.com/web_gear/chat/TransferGroup2.php',
        'p2' => 'https://xat.com/web_gear/chat/pow2.php',
        'pw' => 'http://xat.com/json/powers.php', //domain=.xat.com; HttpOnly
        'cs' => 'http://xat.com/json/GroupSearch.php?s=%s', //domain=.xat.com; HttpOnly
        'ci' => 'https://xat.com/api/roomid.php?d=%s',
        'pp' => 'https://xat.com/web_gear/chat/promotion2.php',
        'pm' => 'https://xat.com/json/promo.php',
        'id' => 'https://xat.com/web_gear/chat/profile.php?name=%s',
        'rg' => 'https://xat.com/web_gear/chat/profile.php?id=%d',
        'gt' => 'https://xat.com/web_gear/chat/gifts.php?id=%d',
        'al' => 'http://www.alexa.com/siteinfo/xat.com?ver=classic'
    ];
        
    /* Special xat IDs - touch only when xat changes */
    const XAT_SPECIAL = [
        7   => 'Darren',
        42  => 'xat',
        100 => 'Sam',
        101 => 'Chris',
        200 => 'Ajuda',
        201 => 'Ayuda',
        804 => 'Bot',
        911 => 'Guy'
    ];
}
