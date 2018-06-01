<?php
/**
 * Ease Exeption
 *
 * @author    Vitex <vitex@hippy.cz>
 * @copyright 2009-2018 Vitex@hippy.cz (G)
 */

namespace Ease;

/**
 * Exeption use EaseLogger to keep message
 *
 * @author vitex
 */
class Exception extends \Exception
{

    /**
     * Ease Framework Exception
     * 
     * @param string $message of exeption
     * @param int    $code    error code
     * @param \Ease\Exception $previous
     */
    public function __construct($message, $code = 0, Exception $previous = null)
    {
        $trace = $this->getTrace();
        \Ease\Shared::logger()->addStatusObject(new Logger\Message($message,
                'error',
                $trace[0]['class'].'::'.$trace[0]['function'].
                ( isset($trace[0]['line']) ? ':'.$trace[0]['line'] : '' )));
        parent::__construct($message, $code, $previous);
    }
}
