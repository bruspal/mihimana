<?php
/**
 * Standardised mailler using PHPMailer
 *
 * @author bruno
 */
class mmMailer extends mmObject {
    public
            $method = 'smtp',
            $server = '127.0.0.1',
            $port = 25,
            $from = 'mail@example.com',
            $fromName = '',
            $format = MM_MAIL_PLAINTEXT,
            $secureMode = MM_MAIL_SMTP_SECURE_NONE,
            $mail;
    protected
            $secure = array(
                MM_MAIL_SMTP_SECURE_TLS => 'tls',
                MM_MAIL_SMTP_SECURE_SSL => 'ssl'
            );

    public function __construct() {
        $this->mail = new PHPMailer();

        if (defined('MM_MAIL_SERVER')) {
            $this->server = MM_MAIL_SERVER;
        }
        if (defined('MM_MAIL_PORT')) {
            $this->port = MM_MAIL_PORT;
        }
        if (defined('MM_MAIL_FROM')) {
            $this->from = MM_MAIL_FROM;
        }
        if (defined('MM_MAIL_FROM_NAME')) {
            $this->fromName = MM_MAIL_FROM_NAME;
        }
        if (defined('MM_MAIL_FORMAT')) {
            $this->format = MM_MAIL_FORMAT;
        }
        if (defined('MM_MAIL_SECURE')) {
            $this->secureMode = MM_MAIL_SECURE;
        }
        //TODO :  add a way to choose sender, for now sendMail

        $this->mail->isSMTP();
        if (DEBUG && false) {
            $this->mail->SMTPDebug = 2;
        }
        $this->mail->Debugoutput = 'html';
        $this->mail->Host = $this->server;
        $this->mail->Port = $this->port;
        $this->mail->SMTPAuth = false;
        if ($this->secureMode > MM_MAIL_SMTP_SECURE_NONE) {
            $this->mail->SMTPSecure = $this->secure[$this->secureMode];
        }
        if (defined('MM_MAIL_SMTP_USER')) {
            $this->mail->SMTPAuth = true;
            $this->mail->Username = MM_MAIL_SMTP_USER;
            $this->mail->Password = MM_MAIL_SMTP_PWD;
        }
    }

    /**
     * Send a mail with default server values
     * @param type $to
     * @param type $subject
     * @param type $body
     */
    public function sendSimpleMail($to, $subject, $body) {
        if (empty($this->fromName)) {
            $fromName = false;
        } else {
            $fromName = $this->fromName;
        }
        if ($this->format === MM_MAIL_PLAINTEXT) {
            $this->sendTextMail($this->from, $to, $subject, $body, $fromName);
        }
        elseif ($this->format === MM_MAIL_HTML) {
            $this->sendHtmlMail($this->from, $to, $subject, $body, $fromName);
        } else {
            throw new mmExceptionConfig('MM_MAIL_FORMAT value not recognized, it should be MM_MAIL_PLAINTEXT or MM_MAIL_HTML');
        }
    }

    /**
     * send text mail
     * @param type $from adresse
     * @param type $to
     * @param type $subject
     * @param type $body
     * @param type $fromName (optionel) Nom de
     * @return boolean
     */
    public function sendTextMail($from, $to, $subject, $body, $fromName = false) {
        //mail setup
        if ($fromName === false) {
            $this->mail->setFrom($from);
        } else {
            $this->mail->setFrom($from, $fromName);
        }
        $this->mail->addAddress($to);
        $this->mail->Subject = $subject;
        $this->mail->Body = $body;

        //send
        if ( ! $this->mail->send()) {
            return array('succes'=>false, 'errorCode'=>500, 'errorMessage', $this->mail->ErrorInfo);
        }
        // mail successfuly sent
        return true;
    }

    /**
     * send html mail
     * @param type $from adresse
     * @param type $to
     * @param type $subject
     * @param type $body
     * @param type $fromName (optionel) Nom de
     * @return boolean
     */
    public function sendHtmlMail($from, $to, $subject, $body, $fromName = false) {
        //mail setup
        if ($fromName === false) {
            $this->mail->setFrom($from);
        } else {
            $this->mail->setFrom($from, $fromName);
        }
        $this->mail->addAddress($to);
        $this->mail->Subject = $subject;
        $this->mail->msgHTML($body);

        //send
        if ( ! $this->mail->send()) {
            return array('succes'=>false, 'errorCode'=>500, 'errorMessage', $this->mail->ErrorInfo);
        }
        // mail successfuly sent
        return true;
    }



}
