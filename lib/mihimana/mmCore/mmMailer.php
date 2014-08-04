<?php
/**
 * Standardised mailler using PHPMailer
 *
 * @author bruno
 */
class mmMailer extends mmObject {

    private
            $mail,
            $method = 'smtp',
            $server = '127.0.0.1',
            $port = 25;

    public function __construct() {
        $this->mail = new PHPMailer();
        //TODO :  add a way to choose sender, for now sendMail

        $this->mail->isSMTP();
        $this->mail->SMTPDebug = 2;
        $this->mail->Debugoutput = 'html';
        $this->mail->Host = $this->server;
        $this->mail->Port = $this->port;
        $this->mail->SMTPAuth = false;
    }

    public function sendSimpleMail($to, $subject, $body) {
        if (!defined(MAIL_FROM)) throw new mmExceptionConfig('MAIL_FROM is missing in config.php file');
        if (!defined(MAIL_SERVER)) throw new mmExceptionConfig('MAIL_SERVER is missing in config.php file');
        if (!defined(MAIL_PORT)) throw new mmExceptionConfig('MAIL_PORT is missing in config.php file');

        if (defined(MAIL_FROM_NAME)) {
            $fromName = MAIL_FROM_NAME;
        } else {
            $fromName = false;
        }
        $this->sendTextMail(MAIL_FROM, $to, $subject, $body, $fromName);
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
            echo "<h1>".$this->mail->ErrorInfo."</h1>";
            return false;
        }
        // mail successfuly sent
        return true;


    }

}
