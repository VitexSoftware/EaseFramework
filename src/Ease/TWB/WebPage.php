<?php

namespace Ease\TWB;

/**
 * Stránka TwitterBootstrap.
 *
 * @author     Vítězslav Dvořák <vitex@hippy.cz>
 * @copyright  2012 Vitex@vitexsoftware.cz (G)
 *
 * @link       http://twitter.github.com/bootstrap/index.html
 */
class WebPage extends \Ease\WebPage
{
    /**
     * Boostrap URL Strart path with ./ to use local one.
     *
     * @var string relative path/url
     */
    public $mainStyle = 'twitter-bootstrap/css/bootstrap.css';

    /**
     * Stránka s podporou pro twitter bootstrap.
     *
     * @param string   $pageTitle
     */
    public function __construct($pageTitle = null)
    {
        parent::__construct($pageTitle);
        $this->includeCss($this->mainStyle, ($this->mainStyle[0] != '.'));
        $this->head->addItem(
            '<meta name="viewport" content="width=device-width,initial-scale=1.0">'
        );
    }

    /**
     * Vrací zprávy uživatele.
     *
     * @param string $what info|warning|error|success
     *
     * @return string
     */
    public function getStatusMessagesAsHtml($what = null)
    {
        /*
         * Session Singleton Problem hack
         */
        if (!count($this->easeShared->statusMessages)) {
            return '';
        }
        $htmlFargment = '';

        $allMessages = [];
        foreach ($this->easeShared->statusMessages as $quee => $messages) {
            foreach ($messages as $msgID => $message) {
                $allMessages[$msgID][$quee] = $message;
            }
        }
        ksort($allMessages);
        foreach ($allMessages as $messages) {
            $messageType = key($messages);

            if (is_array($what)) {
                if (!in_array($messageType, $what)) {
                    continue;
                }
            }

            foreach ($messages as $message) {
                if (is_object($this->logger)) {
                    if (!isset($this->logger->logStyles[$messageType])) {
                        $messageType = 'notice';
                    }
                    if ($messageType == 'error') {
                        $messageType = 'danger';
                    }
                    $htmlFargment .= '<div class="alert alert-'.$messageType.'" >'.$message.'</div>'."\n";
                } else {
                    $htmlFargment .= '<div class="alert">'.$message.'</div>'."\n";
                }
            }
        }

        return $htmlFargment;
    }
}
