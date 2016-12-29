<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Ease;

/**
 * Description of Exce
 *
 * @author vitex
 */
class Exception extends \Exception
{

    public function __construct($message, $code = 0, Exception $previous = null)
    {
        $trace = $this->getTrace();
        \Ease\Shared::logger()->addStatusObject(new Logger\Message($message,
            'error',
            $trace[0]['class'].'::'.$trace[0]['function'].(isset($trace[0]['line']))
                    ? '' : ':'.$trace[0]['line'] ));
        parent::__construct($message, $code, $previous);
    }
}
