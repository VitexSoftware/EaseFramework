<?php
/**
 * Class to Log messages to Console.
 *
 * @author    Vitex <vitex@hippy.cz>
 * @copyright 2016 Vitex@hippy.cz (G)
 */

namespace Ease\Logger;

/**
 * Description of ToConsole
 *
 * @author vitex
 */
class ToConsole extends ToMemory
{
    /**
     * Saves obejct instace (singleton...).
     */
    private static $_instance = null;

    /**
     * Standard Output handle
     * @var resource
     */
    public $stdout = null;

    /**
     * Standard error handle
     * @var resource
     */
    public $stderr = null;

    /**
     * Ansi Codes
     * @var array
     */
    protected static $ANSI_CODES = array(
        "off" => 0,
        "bold" => 1,
        "italic" => 3,
        "underline" => 4,
        "blink" => 5,
        "inverse" => 7,
        "hidden" => 8,
        "black" => 30,
        "red" => 31,
        "green" => 32,
        "yellow" => 33,
        "blue" => 34,
        "magenta" => 35,
        "cyan" => 36,
        "white" => 37,
        "black_bg" => 40,
        "red_bg" => 41,
        "green_bg" => 42,
        "yellow_bg" => 43,
        "blue_bg" => 44,
        "magenta_bg" => 45,
        "cyan_bg" => 46,
        "white_bg" => 47
    );

    /**
     * Log Status messages to console
     */
    public function __construct()
    {
        parent::__construct();
        $this->stdout = fopen('php://stdout', 'w');
        $this->stderr = fopen('php://stderr', 'w');
    }

    /**
     * Set Ansi Color
     * 
     * @param string $str
     * @param string $color
     * @return string
     */
    public static function set($str, $color)
    {
        $color_attrs = explode("+", $color);
        $ansi_str    = "";
        foreach ($color_attrs as $attr) {
            $ansi_str .= "\033[".self::$ANSI_CODES[$attr]."m";
        }
        $ansi_str .= $str."\033[".self::$ANSI_CODES["off"]."m";
        return $ansi_str;
    }

    /**
     * Zapise zapravu do logu.
     *
     * @param string $caller  název volajícího objektu
     * @param string $message zpráva
     * @param string $type    typ zprávy (success|info|error|warning|*)
     *
     * @return boolean|null byl report zapsán ?
     */
    public function addToLog($caller, $message, $type = 'message')
    {
        $message = $this->set(' '.Message::getTypeUnicodeSymbol($type).' '.strip_tags($message),
            self::getTypeColor($type));
        $logLine = strftime("%D %T").' `'.$caller.'` '.$message;

        switch ($type) {
            case 'error':
                fputs($this->stderr, $logLine."\n");
                break;
            default:
                fputs($this->stdout, $logLine."\n");
                break;
        }
    }

    /**
     * Get color code for given message 
     * 
     * @param string $type
     */
    public static function getTypeColor($type)
    {
        switch ($type) {
            case 'mail':                       // Envelope
                $color = 'blue';
                break;
            case 'warning':                    // Vykřičník v trojůhelníku
                $color = 'yellow';
                break;
            case 'error':                      // Lebka
                $color = 'red';
                break;
            case 'debug':                      // Kytička
                $color = 'magenta';
                break;
            case 'success':                    // Kytička
                $color = 'green';
                break;
            default:                           // i v kroužku
                $color = 'white';
                break;
        }
        return $color;
    }

    /**
     * Pri vytvareni objektu pomoci funkce singleton (ma stejne parametry, jako
     * konstruktor) se bude v ramci behu programu pouzivat pouze jedna jeho
     * instance (ta prvni).
     *
     * @link http://docs.php.net/en/language.oop5.patterns.html Dokumentace a
     * priklad
     */
    public static function singleton()
    {
        if (!isset(self::$_instance)) {
            $class           = __CLASS__;
            self::$_instance = new $class();
        }

        return self::$_instance;
    }
}
