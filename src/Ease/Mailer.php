<?php
/**
 * Třídy pro odesílání Mailu ✉.
 *
 * @author    Vitex <vitex@hippy.cz>
 * @copyright 2009-2012 Vitex@hippy.cz (G)
 */

namespace Ease;

/**
 * Sestaví a odešle mail.
 *
 * @author Vitex <vitex@hippy.cz>
 */
class Mailer extends Page
{
    /**
     * Objekt pro odesílání pošty.
     *
     * @var
     */
    public $mailer          = null;
    public $mimer           = null;
    public $textBody        = null;
    public $mailHeaders     = [];
    public $mailHeadersDone = null;
    public $crLf            = "\n";
    public $mailBody        = null;
    public $finalized       = false;

    /**
     * Již vzrendrované HTML.
     *
     * @var string
     */
    public $htmlBodyRendered = null;

    /**
     * Adresa odesilatele zprávy.
     *
     * @var string
     */
    public $emailAddress = 'postmaster@localhost';
    public $emailSubject = null;

    /**
     * Emailová adresa odesilatele.
     *
     * @var string
     */
    public $fromEmailAddress = null;

    /**
     * Zobrazovat uživateli informaci o odeslání zprávy ?
     *
     * @var bool
     */
    public $notify = true;

    /**
     * Byla již zpráva odeslána ?
     *
     * @var bool
     */
    public $sendResult = false;

    /**
     * Objekt stránky pro rendrování do mailu.
     *
     * @var Html\HtmlTag
     */
    public $htmlDocument = null;

    /**
     * Ukazatel na BODY html dokumentu.
     *
     * @var Html\BodyTag
     */
    public $htmlBody = null;

    /**
     * Parametry odchozí pošty.
     *
     * @var array
     */
    public $parameters = [];

    /**
     * Ease Mail - sestaví a odešle.
     *
     * @param string $emailAddress  adresa
     * @param string $mailSubject   předmět
     * @param mixed  $emailContents tělo - libovolný mix textu a EaseObjektů
     */
    public function __construct($emailAddress, $mailSubject,
                                $emailContents = null)
    {
        if (defined('EASE_SMTP')) {
            $this->parameters = (array) json_decode(constant('EASE_SMTP'));
        }

        if (is_array($emailAddress)) {
            $emailAddress = current($emailAddress).' <'.key($emailAddress).'>';
        }

        $this->setMailHeaders(
            [
                'To' => $emailAddress,
                'From' => $this->fromEmailAddress,
                'Reply-To' => $this->fromEmailAddress,
                'Subject' => $mailSubject,
                'Content-Type' => 'text/plain; charset=utf-8',
                'Content-Transfer-Encoding' => '8bit',
            ]
        );

        $this->mimer                                = new \Mail_mime($this->crLf);
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
     * Vrací obsah poštovní hlavičky.
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
     * Nastaví hlavičky mailu.
     *
     * @param mixed $mailHeaders asociativní pole hlaviček
     *
     * @return bool true pokud byly hlavičky nastaveny
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
                $this->emailSubject           = $this->mailHeaders['Subject'];
                $this->mailHeaders['Subject'] = '=?UTF-8?B?'.base64_encode($this->mailHeaders['Subject']).'?=';
            }
        }
        $this->finalized = false;

        return true;
    }

    /**
     * Přidá položku do těla mailu.
     *
     * @param mixed $item EaseObjekt nebo cokoliv s metodou draw();
     *
     * @return mixed ukazatel na vložený obsah
     */
    public function &addItem($item, $pageItemName = null)
    {
        $mailBody = '';
        if (is_object($item)) {
            if (is_object($this->htmlDocument)) {
                $mailBody = $this->htmlBody->addItem($item, $pageItemName);
            } else {
                $this->htmlDocument = new Html\HtmlTag(new Html\SimpleHeadTag(new Html\TitleTag($this->emailSubject)));
                $this->htmlDocument->setOutputFormat($this->getOutputFormat());
                $this->htmlBody     = $this->htmlDocument->addItem(new Html\BodyTag('Mail',
                    $item));
                $mailBody           = $this->htmlDocument;
            }
        } else {
            $this->textBody .= $item;
            $this->mimer->setTXTBody($this->textBody);
        }

        return $mailBody;
    }

    /**
     * Obtain item count
     *
     * @param Container $object
     * @return int
     */
    public function getItemsCount($object = null)
    {
        if (is_null($object)) {
            $object = $this->htmlBody;
        }
        return parent::getItemsCount($object);
    }

    /**
     * Is object empty ?
     *
     * @param Container $element
     * @return boolean
     */
    public function isEmpty($element = null)
    {
        if (is_null($element)) {
            $element = $this->htmlBody;
        }
        return parent::isEmpty($element);
    }

    /**
     * Vyprázní obsah objektu.
     * Empty container contents
     */
    public function emptyContents()
    {
        $this->htmlBody = null;
    }

    /**
     * Připojí k mailu přílohu ze souboru.
     *
     * @param string $filename cesta/název souboru k přiložení
     * @param string $mimeType MIME typ přílohy
     */
    public function addFile($filename, $mimeType = 'text/plain')
    {
        $this->mimer->addAttachment($filename, $mimeType);
    }

    /**
     * Sestavení těla mailu.
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
            $this->setMailHeaders(['From' => $this->fromEmailAddress]);
        }

        $this->setMailHeaders(['Date' => date('r')]);
        $this->mailBody        = $this->mimer->get();
        $this->mailHeadersDone = $this->mimer->headers($this->mailHeaders);
        $this->finalized       = true;
    }

    /**
     * Mail vložený do stránky se nevykresluje.
     */
    public function draw()
    {
        return;
    }

    /**
     * Odešle mail.
     */
    public function send()
    {
        if (!$this->finalized) {
            $this->finalize();
        }

        $oMail = new \Mail();
        if (count($this->parameters)) {
            $this->mailer = $oMail->factory('smtp', $this->parameters);
        } else {
            $this->mailer = $oMail->factory('mail');
        }
        $this->sendResult = $this->mailer->send($this->emailAddress,
            $this->mailHeadersDone, $this->mailBody);
        if ($this->sendResult) {
            if ($this->notify) {
                $this->addStatusMessage(sprintf(_('Zpráva %s byla odeslána na adresu %s'),
                        $this->emailSubject, $this->emailAddress), 'success');
            }
        } else {
            $this->addStatusMessage(sprintf(_('Zpráva %s, pro %s nebyla odeslána z důvodu %s'),
                    $this->emailSubject, $this->emailAddress,
                    $this->sendResult->message), 'warning');
        }

        return $this->sendResult;
    }

    /**
     * Nastaví návěští uživatelské notifikace.
     *
     * @param bool $notify požadovaný stav notifikace
     */
    public function setUserNotification($notify)
    {
        $this->notify = (bool) $notify;
    }
}
