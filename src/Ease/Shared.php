<?php
/**
 * Všeobecně sdílený objekt frameworku.
 * Tento objekt je automaticky přez svůj singleton instancován do každého Ease*
 * objektu.
 * Poskytuje kdykoliv přístup k často volaným objektům framworku jako například
 * uživatel, databáze, webstránka nebo logy.
 * Také obsahuje pole obecnych nastavení a funkce pro jeho obluhu.
 *
 * @author    Vitex <vitex@hippy.cz>
 * @copyright 2009-2017 Vitex@hippy.cz (G)
 */

namespace Ease;

/**
 * Všeobecně sdílený objekt frameworku.
 * Tento objekt je automaticky přez svůj singleton instancován do každého Ease*
 * objektu.
 * Poskytuje kdykoliv přístup k často volaným objektům framworku jako například
 * uživatel, databáze, webstránka nebo logy.
 * Také obsahuje pole obecnych nastavení a funkce pro jeho obluhu.
 *
 * @author    Vitex <vitex@hippy.cz>
 * @copyright 2009-2016 Vitex@hippy.cz (G)
 * @author    Vitex <vitex@hippy.cz>
 */
class Shared extends Atom
{
    /**
     * Odkaz na objekt stránky.
     *
     * @var WebPage
     */
    public $webPage = null;

    /**
     * JavaScripts.
     *
     * @var array
     */
    public $javaScripts = null;

    /**
     * Pole kaskádových stylů
     * $var array.
     */
    public $cascadeStyles = null;

    /**
     * Pole konfigurací.
     *
     * @var array
     */
    public $configuration = [];

    /**
     * Informuje zdali je objekt spuštěn v prostředí webové stránky nebo jako script.
     *
     * @var string web|cli
     */
    public $runType = null;

    /**
     * Odkaz na instanci objektu uživatele.
     *
     * @var User|Anonym
     */
    public $user = null;

    /**
     * Odkaz na objekt databáze.
     *
     * @var SQL\PDO
     */
    public $dbLink = null;

    /**
     * Saves obejct instace (singleton...).
     *
     * @var Shared
     */
    private static $_instance = null;

    /**
     * Pole odkazů na všechny vložené objekty.
     *
     * @var array pole odkazů
     */
    public $allItems = [];

    /**
     * Název položky session s objektem uživatele.
     *
     * @var string
     */
    public static $userSessionName = 'User';

    /**
     * Logger live here
     * @var Logger\ToFile|Logger\ToMemory|Logger\ToSyslog
     */
    static public $log = null;

    /**
     * Array of Status Messages
     * @var array of Logger\Message
     */
    public $messages = [];

    /**
     * All Language Codes => languages
     * @var array
     */
    public static $alllngs = [
        "af_NA" => "Afrikaans (Namibia)",
        "af_ZA" => "Afrikaans (South Africa)",
        "af" => "Afrikaans",
        "ak_GH" => "Akan (Ghana)",
        "ak" => "Akan",
        "sq_AL" => "Albanian (Albania)",
        "sq" => "Albanian",
        "am_ET" => "Amharic (Ethiopia)",
        "am" => "Amharic",
        "ar_DZ" => "Arabic (Algeria)",
        "ar_BH" => "Arabic (Bahrain)",
        "ar_EG" => "Arabic (Egypt)",
        "ar_IQ" => "Arabic (Iraq)",
        "ar_JO" => "Arabic (Jordan)",
        "ar_KW" => "Arabic (Kuwait)",
        "ar_LB" => "Arabic (Lebanon)",
        "ar_LY" => "Arabic (Libya)",
        "ar_MA" => "Arabic (Morocco)",
        "ar_OM" => "Arabic (Oman)",
        "ar_QA" => "Arabic (Qatar)",
        "ar_SA" => "Arabic (Saudi Arabia)",
        "ar_SD" => "Arabic (Sudan)",
        "ar_SY" => "Arabic (Syria)",
        "ar_TN" => "Arabic (Tunisia)",
        "ar_AE" => "Arabic (United Arab Emirates)",
        "ar_YE" => "Arabic (Yemen)",
        "ar" => "Arabic",
        "hy_AM" => "Armenian (Armenia)",
        "hy" => "Armenian",
        "as_IN" => "Assamese (India)",
        "as" => "Assamese",
        "asa_TZ" => "Asu (Tanzania)",
        "asa" => "Asu",
        "az_Cyrl" => "Azerbaijani (Cyrillic)",
        "az_Cyrl_AZ" => "Azerbaijani (Cyrillic, Azerbaijan)",
        "az_Latn" => "Azerbaijani (Latin)",
        "az_Latn_AZ" => "Azerbaijani (Latin, Azerbaijan)",
        "az" => "Azerbaijani",
        "bm_ML" => "Bambara (Mali)",
        "bm" => "Bambara",
        "eu_ES" => "Basque (Spain)",
        "eu" => "Basque",
        "be_BY" => "Belarusian (Belarus)",
        "be" => "Belarusian",
        "bem_ZM" => "Bemba (Zambia)",
        "bem" => "Bemba",
        "bez_TZ" => "Bena (Tanzania)",
        "bez" => "Bena",
        "bn_BD" => "Bengali (Bangladesh)",
        "bn_IN" => "Bengali (India)",
        "bn" => "Bengali",
        "bs_BA" => "Bosnian (Bosnia and Herzegovina)",
        "bs" => "Bosnian",
        "bg_BG" => "Bulgarian (Bulgaria)",
        "bg" => "Bulgarian",
        "my_MM" => "Burmese (Myanmar [Burma])",
        "my" => "Burmese",
        "ca_ES" => "Catalan (Spain)",
        "ca" => "Catalan",
        "tzm_Latn" => "Central Morocco Tamazight (Latin)",
        "tzm_Latn_MA" => "Central Morocco Tamazight (Latin, Morocco)",
        "tzm" => "Central Morocco Tamazight",
        "chr_US" => "Cherokee (United States)",
        "chr" => "Cherokee",
        "cgg_UG" => "Chiga (Uganda)",
        "cgg" => "Chiga",
        "zh_Hans" => "Chinese (Simplified Han)",
        "zh_Hans_CN" => "Chinese (Simplified Han, China)",
        "zh_Hans_HK" => "Chinese (Simplified Han, Hong Kong SAR China)",
        "zh_Hans_MO" => "Chinese (Simplified Han, Macau SAR China)",
        "zh_Hans_SG" => "Chinese (Simplified Han, Singapore)",
        "zh_Hant" => "Chinese (Traditional Han)",
        "zh_Hant_HK" => "Chinese (Traditional Han, Hong Kong SAR China)",
        "zh_Hant_MO" => "Chinese (Traditional Han, Macau SAR China)",
        "zh_Hant_TW" => "Chinese (Traditional Han, Taiwan)",
        "zh" => "Chinese",
        "kw_GB" => "Cornish (United Kingdom)",
        "kw" => "Cornish",
        "hr_HR" => "Croatian (Croatia)",
        "hr" => "Croatian",
        "cs_CZ" => "Czech (Czech Republic)",
        "cs" => "Czech",
        "da_DK" => "Danish (Denmark)",
        "da" => "Danish",
        "nl_BE" => "Dutch (Belgium)",
        "nl_NL" => "Dutch (Netherlands)",
        "nl" => "Dutch",
        "ebu_KE" => "Embu (Kenya)",
        "ebu" => "Embu",
        "en_AS" => "English (American Samoa)",
        "en_AU" => "English (Australia)",
        "en_BE" => "English (Belgium)",
        "en_BZ" => "English (Belize)",
        "en_BW" => "English (Botswana)",
        "en_CA" => "English (Canada)",
        "en_GU" => "English (Guam)",
        "en_HK" => "English (Hong Kong SAR China)",
        "en_IN" => "English (India)",
        "en_IE" => "English (Ireland)",
        "en_JM" => "English (Jamaica)",
        "en_MT" => "English (Malta)",
        "en_MH" => "English (Marshall Islands)",
        "en_MU" => "English (Mauritius)",
        "en_NA" => "English (Namibia)",
        "en_NZ" => "English (New Zealand)",
        "en_MP" => "English (Northern Mariana Islands)",
        "en_PK" => "English (Pakistan)",
        "en_PH" => "English (Philippines)",
        "en_SG" => "English (Singapore)",
        "en_ZA" => "English (South Africa)",
        "en_TT" => "English (Trinidad and Tobago)",
        "en_UM" => "English (U.S. Minor Outlying Islands)",
        "en_VI" => "English (U.S. Virgin Islands)",
        "en_GB" => "English (United Kingdom)",
        "en_US" => "English (United States)",
        "en_ZW" => "English (Zimbabwe)",
        "en" => "English",
        "eo" => "Esperanto",
        "et_EE" => "Estonian (Estonia)",
        "et" => "Estonian",
        "ee_GH" => "Ewe (Ghana)",
        "ee_TG" => "Ewe (Togo)",
        "ee" => "Ewe",
        "fo_FO" => "Faroese (Faroe Islands)",
        "fo" => "Faroese",
        "fil_PH" => "Filipino (Philippines)",
        "fil" => "Filipino",
        "fi_FI" => "Finnish (Finland)",
        "fi" => "Finnish",
        "fr_BE" => "French (Belgium)",
        "fr_BJ" => "French (Benin)",
        "fr_BF" => "French (Burkina Faso)",
        "fr_BI" => "French (Burundi)",
        "fr_CM" => "French (Cameroon)",
        "fr_CA" => "French (Canada)",
        "fr_CF" => "French (Central African Republic)",
        "fr_TD" => "French (Chad)",
        "fr_KM" => "French (Comoros)",
        "fr_CG" => "French (Congo - Brazzaville)",
        "fr_CD" => "French (Congo - Kinshasa)",
        "fr_CI" => "French (Côte d’Ivoire)",
        "fr_DJ" => "French (Djibouti)",
        "fr_GQ" => "French (Equatorial Guinea)",
        "fr_FR" => "French (France)",
        "fr_GA" => "French (Gabon)",
        "fr_GP" => "French (Guadeloupe)",
        "fr_GN" => "French (Guinea)",
        "fr_LU" => "French (Luxembourg)",
        "fr_MG" => "French (Madagascar)",
        "fr_ML" => "French (Mali)",
        "fr_MQ" => "French (Martinique)",
        "fr_MC" => "French (Monaco)",
        "fr_NE" => "French (Niger)",
        "fr_RW" => "French (Rwanda)",
        "fr_RE" => "French (Réunion)",
        "fr_BL" => "French (Saint Barthélemy)",
        "fr_MF" => "French (Saint Martin)",
        "fr_SN" => "French (Senegal)",
        "fr_CH" => "French (Switzerland)",
        "fr_TG" => "French (Togo)",
        "fr" => "French",
        "ff_SN" => "Fulah (Senegal)",
        "ff" => "Fulah",
        "gl_ES" => "Galician (Spain)",
        "gl" => "Galician",
        "lg_UG" => "Ganda (Uganda)",
        "lg" => "Ganda",
        "ka_GE" => "Georgian (Georgia)",
        "ka" => "Georgian",
        "de_AT" => "German (Austria)",
        "de_BE" => "German (Belgium)",
        "de_DE" => "German (Germany)",
        "de_LI" => "German (Liechtenstein)",
        "de_LU" => "German (Luxembourg)",
        "de_CH" => "German (Switzerland)",
        "de" => "German",
        "el_CY" => "Greek (Cyprus)",
        "el_GR" => "Greek (Greece)",
        "el" => "Greek",
        "gu_IN" => "Gujarati (India)",
        "gu" => "Gujarati",
        "guz_KE" => "Gusii (Kenya)",
        "guz" => "Gusii",
        "ha_Latn" => "Hausa (Latin)",
        "ha_Latn_GH" => "Hausa (Latin, Ghana)",
        "ha_Latn_NE" => "Hausa (Latin, Niger)",
        "ha_Latn_NG" => "Hausa (Latin, Nigeria)",
        "ha" => "Hausa",
        "haw_US" => "Hawaiian (United States)",
        "haw" => "Hawaiian",
        "he_IL" => "Hebrew (Israel)",
        "he" => "Hebrew",
        "hi_IN" => "Hindi (India)",
        "hi" => "Hindi",
        "hu_HU" => "Hungarian (Hungary)",
        "hu" => "Hungarian",
        "is_IS" => "Icelandic (Iceland)",
        "is" => "Icelandic",
        "ig_NG" => "Igbo (Nigeria)",
        "ig" => "Igbo",
        "id_ID" => "Indonesian (Indonesia)",
        "id" => "Indonesian",
        "ga_IE" => "Irish (Ireland)",
        "ga" => "Irish",
        "it_IT" => "Italian (Italy)",
        "it_CH" => "Italian (Switzerland)",
        "it" => "Italian",
        "ja_JP" => "Japanese (Japan)",
        "ja" => "Japanese",
        "kea_CV" => "Kabuverdianu (Cape Verde)",
        "kea" => "Kabuverdianu",
        "kab_DZ" => "Kabyle (Algeria)",
        "kab" => "Kabyle",
        "kl_GL" => "Kalaallisut (Greenland)",
        "kl" => "Kalaallisut",
        "kln_KE" => "Kalenjin (Kenya)",
        "kln" => "Kalenjin",
        "kam_KE" => "Kamba (Kenya)",
        "kam" => "Kamba",
        "kn_IN" => "Kannada (India)",
        "kn" => "Kannada",
        "kk_Cyrl" => "Kazakh (Cyrillic)",
        "kk_Cyrl_KZ" => "Kazakh (Cyrillic, Kazakhstan)",
        "kk" => "Kazakh",
        "km_KH" => "Khmer (Cambodia)",
        "km" => "Khmer",
        "ki_KE" => "Kikuyu (Kenya)",
        "ki" => "Kikuyu",
        "rw_RW" => "Kinyarwanda (Rwanda)",
        "rw" => "Kinyarwanda",
        "kok_IN" => "Konkani (India)",
        "kok" => "Konkani",
        "ko_KR" => "Korean (South Korea)",
        "ko" => "Korean",
        "khq_ML" => "Koyra Chiini (Mali)",
        "khq" => "Koyra Chiini",
        "ses_ML" => "Koyraboro Senni (Mali)",
        "ses" => "Koyraboro Senni",
        "lag_TZ" => "Langi (Tanzania)",
        "lag" => "Langi",
        "lv_LV" => "Latvian (Latvia)",
        "lv" => "Latvian",
        "lt_LT" => "Lithuanian (Lithuania)",
        "lt" => "Lithuanian",
        "luo_KE" => "Luo (Kenya)",
        "luo" => "Luo",
        "luy_KE" => "Luyia (Kenya)",
        "luy" => "Luyia",
        "mk_MK" => "Macedonian (Macedonia)",
        "mk" => "Macedonian",
        "jmc_TZ" => "Machame (Tanzania)",
        "jmc" => "Machame",
        "kde_TZ" => "Makonde (Tanzania)",
        "kde" => "Makonde",
        "mg_MG" => "Malagasy (Madagascar)",
        "mg" => "Malagasy",
        "ms_BN" => "Malay (Brunei)",
        "ms_MY" => "Malay (Malaysia)",
        "ms" => "Malay",
        "ml_IN" => "Malayalam (India)",
        "ml" => "Malayalam",
        "mt_MT" => "Maltese (Malta)",
        "mt" => "Maltese",
        "gv_GB" => "Manx (United Kingdom)",
        "gv" => "Manx",
        "mr_IN" => "Marathi (India)",
        "mr" => "Marathi",
        "mas_KE" => "Masai (Kenya)",
        "mas_TZ" => "Masai (Tanzania)",
        "mas" => "Masai",
        "mer_KE" => "Meru (Kenya)",
        "mer" => "Meru",
        "mfe_MU" => "Morisyen (Mauritius)",
        "mfe" => "Morisyen",
        "naq_NA" => "Nama (Namibia)",
        "naq" => "Nama",
        "ne_IN" => "Nepali (India)",
        "ne_NP" => "Nepali (Nepal)",
        "ne" => "Nepali",
        "nd_ZW" => "North Ndebele (Zimbabwe)",
        "nd" => "North Ndebele",
        "nb_NO" => "Norwegian Bokmål (Norway)",
        "nb" => "Norwegian Bokmål",
        "nn_NO" => "Norwegian Nynorsk (Norway)",
        "nn" => "Norwegian Nynorsk",
        "nyn_UG" => "Nyankole (Uganda)",
        "nyn" => "Nyankole",
        "or_IN" => "Oriya (India)",
        "or" => "Oriya",
        "om_ET" => "Oromo (Ethiopia)",
        "om_KE" => "Oromo (Kenya)",
        "om" => "Oromo",
        "ps_AF" => "Pashto (Afghanistan)",
        "ps" => "Pashto",
        "fa_AF" => "Persian (Afghanistan)",
        "fa_IR" => "Persian (Iran)",
        "fa" => "Persian",
        "pl_PL" => "Polish (Poland)",
        "pl" => "Polish",
        "pt_BR" => "Portuguese (Brazil)",
        "pt_GW" => "Portuguese (Guinea-Bissau)",
        "pt_MZ" => "Portuguese (Mozambique)",
        "pt_PT" => "Portuguese (Portugal)",
        "pt" => "Portuguese",
        "pa_Arab" => "Punjabi (Arabic)",
        "pa_Arab_PK" => "Punjabi (Arabic, Pakistan)",
        "pa_Guru" => "Punjabi (Gurmukhi)",
        "pa_Guru_IN" => "Punjabi (Gurmukhi, India)",
        "pa" => "Punjabi",
        "ro_MD" => "Romanian (Moldova)",
        "ro_RO" => "Romanian (Romania)",
        "ro" => "Romanian",
        "rm_CH" => "Romansh (Switzerland)",
        "rm" => "Romansh",
        "rof_TZ" => "Rombo (Tanzania)",
        "rof" => "Rombo",
        "ru_MD" => "Russian (Moldova)",
        "ru_RU" => "Russian (Russia)",
        "ru_UA" => "Russian (Ukraine)",
        "ru" => "Russian",
        "rwk_TZ" => "Rwa (Tanzania)",
        "rwk" => "Rwa",
        "saq_KE" => "Samburu (Kenya)",
        "saq" => "Samburu",
        "sg_CF" => "Sango (Central African Republic)",
        "sg" => "Sango",
        "seh_MZ" => "Sena (Mozambique)",
        "seh" => "Sena",
        "sr_Cyrl" => "Serbian (Cyrillic)",
        "sr_Cyrl_BA" => "Serbian (Cyrillic, Bosnia and Herzegovina)",
        "sr_Cyrl_ME" => "Serbian (Cyrillic, Montenegro)",
        "sr_Cyrl_RS" => "Serbian (Cyrillic, Serbia)",
        "sr_Latn" => "Serbian (Latin)",
        "sr_Latn_BA" => "Serbian (Latin, Bosnia and Herzegovina)",
        "sr_Latn_ME" => "Serbian (Latin, Montenegro)",
        "sr_Latn_RS" => "Serbian (Latin, Serbia)",
        "sr" => "Serbian",
        "sn_ZW" => "Shona (Zimbabwe)",
        "sn" => "Shona",
        "ii_CN" => "Sichuan Yi (China)",
        "ii" => "Sichuan Yi",
        "si_LK" => "Sinhala (Sri Lanka)",
        "si" => "Sinhala",
        "sk_SK" => "Slovak (Slovakia)",
        "sk" => "Slovak",
        "sl_SI" => "Slovenian (Slovenia)",
        "sl" => "Slovenian",
        "xog_UG" => "Soga (Uganda)",
        "xog" => "Soga",
        "so_DJ" => "Somali (Djibouti)",
        "so_ET" => "Somali (Ethiopia)",
        "so_KE" => "Somali (Kenya)",
        "so_SO" => "Somali (Somalia)",
        "so" => "Somali",
        "es_AR" => "Spanish (Argentina)",
        "es_BO" => "Spanish (Bolivia)",
        "es_CL" => "Spanish (Chile)",
        "es_CO" => "Spanish (Colombia)",
        "es_CR" => "Spanish (Costa Rica)",
        "es_DO" => "Spanish (Dominican Republic)",
        "es_EC" => "Spanish (Ecuador)",
        "es_SV" => "Spanish (El Salvador)",
        "es_GQ" => "Spanish (Equatorial Guinea)",
        "es_GT" => "Spanish (Guatemala)",
        "es_HN" => "Spanish (Honduras)",
        "es_419" => "Spanish (Latin America)",
        "es_MX" => "Spanish (Mexico)",
        "es_NI" => "Spanish (Nicaragua)",
        "es_PA" => "Spanish (Panama)",
        "es_PY" => "Spanish (Paraguay)",
        "es_PE" => "Spanish (Peru)",
        "es_PR" => "Spanish (Puerto Rico)",
        "es_ES" => "Spanish (Spain)",
        "es_US" => "Spanish (United States)",
        "es_UY" => "Spanish (Uruguay)",
        "es_VE" => "Spanish (Venezuela)",
        "es" => "Spanish",
        "sw_KE" => "Swahili (Kenya)",
        "sw_TZ" => "Swahili (Tanzania)",
        "sw" => "Swahili",
        "sv_FI" => "Swedish (Finland)",
        "sv_SE" => "Swedish (Sweden)",
        "sv" => "Swedish",
        "gsw_CH" => "Swiss German (Switzerland)",
        "gsw" => "Swiss German",
        "shi_Latn" => "Tachelhit (Latin)",
        "shi_Latn_MA" => "Tachelhit (Latin, Morocco)",
        "shi_Tfng" => "Tachelhit (Tifinagh)",
        "shi_Tfng_MA" => "Tachelhit (Tifinagh, Morocco)",
        "shi" => "Tachelhit",
        "dav_KE" => "Taita (Kenya)",
        "dav" => "Taita",
        "ta_IN" => "Tamil (India)",
        "ta_LK" => "Tamil (Sri Lanka)",
        "ta" => "Tamil",
        "te_IN" => "Telugu (India)",
        "te" => "Telugu",
        "teo_KE" => "Teso (Kenya)",
        "teo_UG" => "Teso (Uganda)",
        "teo" => "Teso",
        "th_TH" => "Thai (Thailand)",
        "th" => "Thai",
        "bo_CN" => "Tibetan (China)",
        "bo_IN" => "Tibetan (India)",
        "bo" => "Tibetan",
        "ti_ER" => "Tigrinya (Eritrea)",
        "ti_ET" => "Tigrinya (Ethiopia)",
        "ti" => "Tigrinya",
        "to_TO" => "Tonga (Tonga)",
        "to" => "Tonga",
        "tr_TR" => "Turkish (Turkey)",
        "tr" => "Turkish",
        "uk_UA" => "Ukrainian (Ukraine)",
        "uk" => "Ukrainian",
        "ur_IN" => "Urdu (India)",
        "ur_PK" => "Urdu (Pakistan)",
        "ur" => "Urdu",
        "uz_Arab" => "Uzbek (Arabic)",
        "uz_Arab_AF" => "Uzbek (Arabic, Afghanistan)",
        "uz_Cyrl" => "Uzbek (Cyrillic)",
        "uz_Cyrl_UZ" => "Uzbek (Cyrillic, Uzbekistan)",
        "uz_Latn" => "Uzbek (Latin)",
        "uz_Latn_UZ" => "Uzbek (Latin, Uzbekistan)",
        "uz" => "Uzbek",
        "vi_VN" => "Vietnamese (Vietnam)",
        "vi" => "Vietnamese",
        "vun_TZ" => "Vunjo (Tanzania)",
        "vun" => "Vunjo",
        "cy_GB" => "Welsh (United Kingdom)",
        "cy" => "Welsh",
        "yo_NG" => "Yoruba (Nigeria)",
        "yo" => "Yoruba",
        "zu_ZA" => "Zulu (South Africa)",
        "zu" => "Zulu"
    ];

    /**
     * Inicializace sdílené třídy.
     */
    public function __construct()
    {
        $cgiMessages = [];
        $webMessages = [];
        $prefix      = defined('EASE_APPNAME') ? constant('EASE_APPNAME') : '';
        $msgFile     = sys_get_temp_dir().'/'.$prefix.'EaseStatusMessages.ser';
        if (file_exists($msgFile) && is_readable($msgFile) && filesize($msgFile)
            && is_writable($msgFile)) {
            $cgiMessages = unserialize(file_get_contents($msgFile));
            file_put_contents($msgFile, '');
        }

        if (defined('EASE_APPNAME')) {
            if (isset($_SESSION[constant('EASE_APPNAME')]['EaseMessages'])) {
                $webMessages = $_SESSION[constant('EASE_APPNAME')]['EaseMessages'];
                unset($_SESSION[constant('EASE_APPNAME')]['EaseMessages']);
            }
        } else {
            if (isset($_SESSION['EaseMessages'])) {
                $webMessages = $_SESSION['EaseMessages'];
                unset($_SESSION['EaseMessages']);
            }
        }
        $this->statusMessages = is_array($cgiMessages) ? array_merge($cgiMessages, $webMessages) : $webMessages ;
    }

    /**
     * Pri vytvareni objektu pomoci funkce singleton (ma stejne parametry, jako konstruktor)
     * se bude v ramci behu programu pouzivat pouze jedna jeho Instance (ta prvni).
     *
     * @param string $class název třídy jenž má být zinstancována
     *
     * @link   http://docs.php.net/en/language.oop5.patterns.html Dokumentace a priklad
     *
     * @return \Ease\Shared
     */
    public static function singleton($class = null)
    {
        if (!isset(self::$_instance)) {
            if (is_null($class)) {
                $class = __CLASS__;
            }
            self::$_instance = new $class();
        }

        return self::$_instance;
    }

    /**
     * Vrací se.
     *
     * @return Shared
     */
    public static function &instanced()
    {
        $easeShared = self::singleton();

        return $easeShared;
    }

    /**
     * Nastavuje hodnotu konfiguračního klíče.
     *
     * @param string $configName  klíč
     * @param mixed  $configValue hodnota klíče
     */
    public function setConfigValue($configName, $configValue)
    {
        $this->configuration[$configName] = $configValue;
    }

    /**
     * Vrací konfigurační hodnotu pod klíčem.
     *
     * @param string $configName klíč
     *
     * @return mixed
     */
    public function getConfigValue($configName)
    {
        if (isset($this->configuration[$configName])) {
            return $this->configuration[$configName];
        }

        return;
    }

    /**
     * Returns database object instance.
     *
     * @return SQL\PDO
     */
    public static function &db($pdo = null)
    {
        $shared = self::instanced();
        if (is_object($pdo)) {
            $shared->dbLink = &$pdo;
        }
        if (!is_object($shared->dbLink)) {
            $shared->dbLink = self::db(SQL\PDO::singleton(is_array($pdo) ? $pdo : [
                ]));
        }

        return $shared->dbLink;
    }

    /**
     * Vrací instanci objektu logování.
     *
     * @return Logger
     */
    public static function logger()
    {
        return Logger\Regent::singleton();
    }

    /**
     * Vrací nebo registruje instanci webové stránky.
     *
     * @param EaseWebPage $oPage objekt webstránky k zaregistrování
     *
     * @return EaseWebPage
     */
    public static function &webPage($oPage = null)
    {
        $shared = self::instanced();
        if (is_object($oPage)) {
            $shared->webPage = &$oPage;
        }
        if (!is_object($shared->webPage)) {
            self::webPage(WebPage::singleton());
        }

        return $shared->webPage;
    }

    /**
     * Vrací, případně i založí objekt uživatele.
     *
     * @param User|Anonym|string $user objekt nového uživatele nebo
     *                                 název třídy
     *
     * @return User
     */
    public static function &user($user = null, $userSessionName = 'User')
    {
        $efprefix = defined('EASE_APPNAME') ? constant('EASE_APPNAME') : 'EaseFramework';

        if (is_null($user) && isset($_SESSION[$efprefix][self::$userSessionName])
            && is_object($_SESSION[$efprefix][self::$userSessionName])) {
            return $_SESSION[$efprefix][self::$userSessionName];
        }

        if (!is_null($userSessionName)) {
            self::$userSessionName = $userSessionName;
        }
        if (is_object($user)) {
            $_SESSION[$efprefix][self::$userSessionName] = clone $user;
        } else {
            if (class_exists($user)) {
                $_SESSION[$efprefix][self::$userSessionName] = new $user();
            } elseif (!isset($_SESSION[$efprefix][self::$userSessionName]) || !is_object($_SESSION[$efprefix][self::$userSessionName])) {
                $_SESSION[$efprefix][self::$userSessionName] = new Anonym();
            }
        }

        return $_SESSION[$efprefix][self::$userSessionName];
    }

    /**
     * Běží php v příkazovém řádku ?
     *
     * @return bool
     */
    public static function isCli()
    {
        return PHP_SAPI == 'cli' && empty($_SERVER['REMOTE_ADDR']);
    }

    /**
     * Zaregistruje položku k finalizaci.
     *
     * @param mixed $itemPointer
     */
    public static function registerItem(&$itemPointer)
    {
        $easeShared             = self::singleton();
        $easeShared->allItems[] = $itemPointer;
    }

    /**
     * Take message to print / log
     * @param Logger\Message $message
     */
    public function takeMessage($message)
    {
        $this->messages[] = $message;
        $this->addStatusMessage($message->body, $message->type);
        $this->logger()->addToLog($message->caller, $message->body,
            $message->type);
    }

    /**
     * Write remaining messages to temporary file.
     */
    public function __destruct()
    {
        if (self::isCli()) {
            $prefix       = defined('EASE_APPNAME') ? constant('EASE_APPNAME') : '';
            $messagesFile = sys_get_temp_dir().'/'.$prefix.'EaseStatusMessages.ser';
            file_put_contents($messagesFile, serialize($this->statusMessages));
            chmod($messagesFile, 666);
        } else {
            if (defined('EASE_APPNAME')) {
                $_SESSION[constant('EASE_APPNAME')]['EaseMessages'] = $this->statusMessages;
            } else {
                $_SESSION['EaseMessages'] = $this->statusMessages;
            }
        }
    }

    /**
     * Load Configuration values from json file $this->configFile and define UPPERCASE keys
     *
     * @param string $configFile Path to file with configuration
     *
     * @return array full configuration array
     */
    public function loadConfig($configFile)
    {
        if (!file_exists($configFile)) {
            throw new Exception('Config file '.(realpath($configFile) ? realpath($configFile)
                        : $configFile).' does not exist');
        }
        $this->configuration = json_decode(file_get_contents($configFile), true);
        if (is_null($this->configuration)) {
            $this->addStatusMessage('Empty Config File '.realpath($configFile) ? realpath($configFile)
                        : $configFile, 'debug');
        } else {
            foreach ($this->configuration as $configKey => $configValue) {
                if ((strtoupper($configKey) == $configKey) && (!defined($configKey))) {
                    define($configKey, $configValue);
                } else {
                    $this->setConfigValue($configKey, $configValue);
                }
            }
        }

        if (array_key_exists('debug', $this->configuration)) {
            $this->debug = boolval($this->configuration['debug']);
        }

        return $this->configuration;
    }

    /**
     * Initialise Gettext
     *
     * $i18n/$defaultLocale/LC_MESSAGES/$appname.mo
     *
     * @param string $appname        name for binddomain
     * @param string $defaultLocale  locale of source code localstring
     * @param string $i18n           directory base localisation directory
     *
     * @return
     */
    public static function initializeGetText($appname, $defaultLocale = 'en_US',
                                             $i18n = '../i18n')
    {
        $langs = [];
        foreach (self::$alllngs as $langCode => $language) {
            $langs[$langCode] = [strstr($langCode, '_') ? substr($langCode, 0,
                    strpos($langCode, '_')) : $langCode, $language];
        }

        if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) && function_exists('\locale_accept_from_http')) {
            $defaultLocale = \locale_accept_from_http($_SERVER['HTTP_ACCEPT_LANGUAGE']);
        }

        if (isset($_GET['locale'])) {
            $defaultLocale = preg_replace('/[^a-zA-Z_]/', '',
                substr($_GET['locale'], 0, 10));
        }

        foreach ($langs as $code => $lang) {
            if ($defaultLocale == $lang[0]) {
                $defaultLocale = $code;
                break;
            }
        }
        setlocale(LC_ALL, $defaultLocale);
        bind_textdomain_codeset($appname, 'UTF-8');
        putenv("LC_ALL=$defaultLocale");
        if (file_exists($i18n)) {
            bindtextdomain($appname, $i18n);
        }
        return textdomain($appname);
    }
    
    
    /**
     * Add params to url
     *
     * @param string  $url      originall url
     * @param array   $addParams   value to add
     * @param boolean $override replace already existing values ?
     * 
     * @return string url with parameters added
     */
    public static function addUrlParams($url, $addParams, $override = false)
    {
        $urlParts = parse_url($url);
        $urlFinal = '';
        if (array_key_exists('scheme', $urlParts)) {
            $urlFinal .= $urlParts['scheme'].'://'.$urlParts['host'];
        }
        if (array_key_exists('port', $urlParts)) {
            $urlFinal .= ':'.$urlParts['port'];
        }
        if (array_key_exists('path', $urlParts)) {
            $urlFinal .= $urlParts['path'];
        }
        if (array_key_exists('query', $urlParts)) {
            parse_str($urlParts['query'], $queryUrlParams);
            $urlParams = $override ? array_merge($queryUrlParams, $addParams) : array_merge($addParams,
                    $queryUrlParams);
        } else {
            $urlParams = $addParams;
        }

        if (!empty($urlParams)) {
            $urlFinal .= '?';
            if (is_array($urlParams)) {
                $urlFinal .= http_build_query($urlParams);
            } else {
                $urlFinal .= $urlParams;
            }
        }
        return $urlFinal;
    }    
    
}
