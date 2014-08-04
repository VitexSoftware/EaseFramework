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
    public $mailer = null;
    public $mimer = null;
    public $textBody = null;
    public $mailHeaders = array();
    public $mailHeadersDone = null;
    public $crLf = "\n";
    public $mailBody = null;
    public $finalized = false;

    /**
     * Již vzrendrované HTML
     * @var string
     */
    public $htmlBodyRendered = null;

    /**
     * Adresa odesilatele zprávy
     * @var string
     */
    public $emailAddress = 'postmaster@localhost';
    public $emailSubject = null;

    /**
     * Emailová adresa odesilatele
     * @var string
     */
    public $fromEmailAddress = null;

    /**
     * Zobrazovat uživateli informaci o odeslání zprávy ?
     * @var boolean
     */
    public $notify = true;

    /**
     * Byla již zpráva odeslána ?
     * @var boolean
     */
    public $sendResult = false;

    /**
     * Objekt stránky pro rendrování do mailu
     * @var EaseHtmlHtmlTag
     */
    public $htmlDocument = null;

    /**
     * Ukazatel na BODY html dokumentu
     * @var EaseHtmlBodyTag
     */
    public $htmlBody = null;

    /**
     * Ease Mail - sestaví a odešle
     *
     * @param string $emailAddress  adresa
     * @param string $mmailSubject  předmět
     * @param mixed  $emailContents tělo - libovolný mix textu a EaseObjektů
     */
    public function __construct($emailAddress, $mmailSubject, $emailContents = null)
    {
        $this->setMailHeaders(
                array(
                    'To' => $emailAddress,
                    'From' => $this->fromEmailAddress,
                    'Reply-To' => $this->fromEmailAddress,
                    'Subject' => $mmailSubject,
                    'Content-Type' => 'text/plain; charset=utf-8',
                    'Content-Transfer-Encoding' => '8bit'
                )
        );

        $this->mimer = new Mail_mime($this->crLf);
        $this->mimer->_build_params['text_charset'] = 'UTF-8';
        $this->mimer->_build_params['html_charset'] = 'UTF-8';
        $this->mimer->_build_params['head_charset'] = 'UTF-8';

        parent::__construct();
        $this->setOutputFormat('mail');
        if (isset($emailContents)) {
            $this->addItem($emailContents);
        }
    }

    /**
     * Vrací obsah poštovní hlavičky
     *
     * @param string $headername název hlavičky
     *
     * @return string
     */
    public function getMailHeader($headername)
    {
        if (isset($this->mailHeaders[$headername])) {
            return $this->mailHeaders[$headername];
        }
    }

    /**
     * Nastaví hlavičky mailu
     *
     * @param mixed $mailHeaders asociativní pole hlaviček
     *
     * @return boolean true pokud byly hlavičky nastaveny
     */
    public function setMailHeaders(array $mailHeaders)
    {
        if (is_array($this->mailHeaders)) {
            $this->mailHeaders = array_merge($this->mailHeaders, $mailHeaders);
        } else {
            $this->mailHeaders = $mailHeaders;
        }
        if (isset($this->mailHeaders['To'])) {
            $this->emailAddress = $this->mailHeaders['To'];
        }
        if (isset($this->mailHeaders['From'])) {
            $this->fromEmailAddress = $this->mailHeaders['From'];
        }
        if (isset($this->mailHeaders['Subject'])) {
            if (!strstr($this->mailHeaders['Subject'], '=?UTF-8?B?')) {
                $this->emailSubject = $this->mailHeaders['Subject'];
                $this->mailHeaders['Subject'] = '=?UTF-8?B?' . base64_encode($this->mailHeaders['Subject']) . '?=';
            }
        }
        $this->finalized = false;

        return true;
    }

    /**
     * Přidá položku do těla mailu
     *
     * @param mixed $item EaseObjekt nebo cokoliv s metodou draw();
     *
     * @return mixed ukazatel na vložený obsah
     */
    function &addItem($item,$pageItemName = null)
    {

        if (is_object($item)) {
            if (is_object($this->htmlDocument)) {
                $this->htmlBody->addItem($item,$pageItemName);
            } else {
                $this->htmlDocument = new EaseHtmlHtmlTag(new EaseHtmlSimpleHeadTag(new EaseHtmlTitleTag($this->emailSubject)));
                $this->htmlDocument->setOutputFormat($this->getOutputFormat());
                $this->htmlBody = $this->htmlDocument->addItem(new EaseHtmlBodyTag('Mail', $item));
            }
        } else {
            $this->textBody .= $item;
            $this->mimer->setTXTBody($this->textBody);
        }

        return $mailBody;
    }

    /**
     * Připojí k mailu přílohu ze souboru
     *
     * @param string $filename cesta/název souboru k přiložení
     * @param string $mimeType MIME typ přílohy
     */
    public function addFile($filename, $mimeType = 'text/plain')
    {
        $this->mimer->addAttachment($filename, $mimeType);
    }

    /**
     * Sestavení těla mailu
     */
    public function finalize()
    {
        if (method_exists($this->htmlDocument, 'GetRendered')) {
            $this->htmlBodyRendered = $this->htmlDocument->getRendered();
        } else {
            $this->htmlBodyRendered = $this->htmlDocument;
        }
        $this->mimer->setHTMLBody($this->htmlBodyRendered);

        if (isset($this->fromEmailAddress)) {
            $this->setMailHeaders(array('From' => $this->fromEmailAddress));
        }

        $this->mailBody = $this->mimer->get();
        $this->mailHeadersDone = $this->mimer->headers($this->mailHeaders);
        $this->finalized = true;
    }

    /**
     * Mail vložený do stránky se nevykresluje
     *
     * @return null
     */
    public function draw()
    {
        return null;
    }

    /**
     * Odešle mail
     */
    public function send()
    {
        if (!$this->finalized) {
            $this->finalize();
        }
        $oMail = new Mail();
        $this->mailer = & $oMail->factory('mail');
        $this->sendResult = $this->mailer->send($this->emailAddress, $this->mailHeadersDone, $this->mailBody);
        if ($this->sendResult && $this->notify) {
            $this->addStatusMessage( sprintf(_('Zpráva %s byla odeslána na adresu %s'),$this->emailSubject, $this->emailAddress), 'success');
        }
    }

    /**
     * Nastaví návěští uživatelské notifikace
     *
     * @param bool $notify požadovaný stav notifikace
     */
    public function setUserNotification($notify)
    {
        $this->notify = (bool) $notify;
    }

}
