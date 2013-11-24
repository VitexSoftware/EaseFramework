<?php

/**
 * Třídy pro odesílání Mailu ✉
 * 
 * @package   EaseFrameWork
 * @author    Vitex <vitex@hippy.cz>
 * @copyright 2009-2012 Vitex@hippy.cz (G) 
 */
require_once 'EasePage.php';

require_once 'Mail.php';
require_once 'Mail/mime.php';

/**
 * Sestaví a odešle mail
 * 
 * @author Vitex <vitex@hippy.cz>
 */
class EaseMail extends EasePage
{

    /**
     * Objekt pro odesílání pošty
     * @var
     */
    public $Mailer = null;
    public $Mimer = null;
    public $TextBody = null;
    public $MailHeaders = array();
    public $MailHeadersDone = null;
    public $CrLf = "\n";
    public $MailBody = null;
    public $Finalized = false;

    /**
     * Již vzrendrované HTML
     * @var string
     */
    public $HtmlBodyRendered = null;

    /**
     * Adresa odesilatele zprávy
     * @var string
     */
    public $EmailAddress = 'postmaster@localhost';
    public $EmailSubject = null;

    /**
     * Emailová adresa odesilatele
     * @var string
     */
    public $FromEmailAddress = null;

    /**
     * Zobrazovat uživateli informaci o odeslání zprávy ?
     * @var boolean
     */
    public $Notify = true;

    /**
     * Byla již zpráva odeslána ?
     * @var boolean
     */
    public $SendResult = false;

    /**
     * Objekt stránky pro rendrování do mailu
     * @var EaseHtmlHtmlTag
     */
    public $HtmlDocument = null;

    /**
     * Ukazatel na BODY html dokumentu
     * @var EaseHtmlBodyTag
     */
    public $HtmlBody = null;

    /**
     * Ease Mail - sestaví a odešle
     * 
     * @param string $EmailAddress  adresa
     * @param string $EmailSubject  předmět
     * @param mixed  $EmailContents tělo - libovolný mix textu a EaseObjektů 
     */
    function __construct($EmailAddress, $EmailSubject, $EmailContents = null)
    {
        $this->setMailHeaders(
                array(
                    'To' => $EmailAddress,
                    'From' => $this->FromEmailAddress,
                    'Reply-To' => $this->FromEmailAddress,
                    'Subject' => $EmailSubject,
                    'Content-Type' => 'text/plain; charset=utf-8',
                    'Content-Transfer-Encoding' => '8bit'
                )
        );

        $this->Mimer = new Mail_mime($this->CrLf);
        $this->Mimer->_build_params['text_charset'] = 'UTF-8';
        $this->Mimer->_build_params['html_charset'] = 'UTF-8';
        $this->Mimer->_build_params['head_charset'] = 'UTF-8';

        parent::__construct();
        $this->setOutputFormat('mail');
        if (isset($EmailContents)) {
            $this->addItem($EmailContents);
        }
    }

    /**
     * Vrací obsah poštovní hlavičky
     * 
     * @param string $headername název hlavičky
     * 
     * @return string 
     */
    function getMailHeader($headername)
    {
        if (isset($this->MailHeaders[$headername])) {
            return $this->MailHeaders[$headername];
        }
    }

    /**
     * Nastaví hlavičky mailu
     * 
     * @param mixed $MailHeaders asociativní pole hlaviček
     * 
     * @return boolean true pokud byly hlavičky nastaveny
     */
    function setMailHeaders(array $MailHeaders)
    {
        if (is_array($this->MailHeaders)) {
            $this->MailHeaders = array_merge($this->MailHeaders, $MailHeaders);
        } else {
            $this->MailHeaders = $MailHeaders;
        }
        if (isset($this->MailHeaders['To'])) {
            $this->EmailAddress = $this->MailHeaders['To'];
        }
        if (isset($this->MailHeaders['From'])) {
            $this->FromEmailAddress = $this->MailHeaders['From'];
        }
        if (isset($this->MailHeaders['Subject'])) {
            if (!strstr($this->MailHeaders['Subject'], '=?UTF-8?B?')) {
                $this->EmailSubject = $this->MailHeaders['Subject'];
                $this->MailHeaders['Subject'] = '=?UTF-8?B?' . base64_encode($this->MailHeaders['Subject']) . '?=';
            }
        }
        $this->Finalized = false;
        return true;
    }

    /**
     * Přidá položku do těla mailu
     * 
     * @param mixed $Item EaseObjekt nebo cokoliv s metodou draw();
     * 
     * @return mixed ukazatel na vložený obsah 
     */
    function &addItem($Item,$PageItemName = null)
    {

        if (is_object($Item)) {
            if (is_object($this->HtmlDocument)) {
                $this->HtmlBody->addItem($Item,$PageItemName);
            } else {
                $this->HtmlDocument = new EaseHtmlHtmlTag(new EaseHtmlSimpleHeadTag(new EaseHtmlTitleTag($this->EmailSubject)));
                $this->HtmlDocument->setOutputFormat($this->getOutputFormat());
                $this->HtmlBody = $this->HtmlDocument->addItem(new EaseHtmlBodyTag('Mail', $Item));
            }
        } else {
            $this->TextBody .= $Item;
            $this->Mimer->setTXTBody($this->TextBody);
        }
        return $MailBody;
    }

    /**
     * Připojí k mailu přílohu ze souboru
     * 
     * @param string $Filename cesta/název souboru k přiložení
     * @param string $MimeType MIME typ přílohy
     */
    function addFile($Filename, $MimeType = 'text/plain')
    {
        $this->Mimer->addAttachment($Filename, $MimeType);
    }

    /**
     * Sestavení těla mailu 
     */
    function finalize()
    {
        if (method_exists($this->HtmlDocument, 'GetRendered')) {
            $this->HtmlBodyRendered = $this->HtmlDocument->getRendered();
        } else {
            $this->HtmlBodyRendered = $this->HtmlDocument;
        }
        $this->Mimer->setHTMLBody($this->HtmlBodyRendered);

        if (isset($this->FromEmailAddress)) {
            $this->setMailHeaders(array('From' => $this->FromEmailAddress));
        }

        $this->MailBody = $this->Mimer->get();
        $this->MailHeadersDone = $this->Mimer->headers($this->MailHeaders);
        $this->Finalized = true;
    }

    /**
     * Mail vložený do stránky se nevykresluje
     * 
     * @return null 
     */
    function draw()
    {
        return null;
    }

    /**
     * Odešle mail 
     */
    function send()
    {
        if (!$this->Finalized) {
            $this->finalize();
        }
        $OMail = new Mail();
        $this->Mailer = & $OMail->factory('mail');
        $this->SendResult = $this->Mailer->send($this->EmailAddress, $this->MailHeadersDone, $this->MailBody);
        if ($this->SendResult && $this->Notify) {
            $this->addStatusMessage( sprintf(_('Zpráva %s byla odeslána na adresu %s'),$this->EmailSubject, $this->EmailAddress), 'success');
        }
    }

    /**
     * Nastaví návěští uživatelské notifikace
     * 
     * @param bool $Notify požadovaný stav notifikace
     */
    function setUserNotification($Notify)
    {
        $this->Notify = (bool) $Notify;
    }

}

?>
