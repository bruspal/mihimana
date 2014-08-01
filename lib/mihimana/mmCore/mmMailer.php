<?php
/**
 * Standardised mailler using PHPMailer
 *
 * @author bruno
 */
class mmMailer {

    private
            $mail;

    public function __construct() {
        $this->mail = new PHPMailer();
        //TODO :  add a way to choose sender, for now sendMail
        $this->mail->isSendmail();
    }

    public function sendSimpleMail($from, $to, $subject, $body, $fromName = false) {
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
            return false;
        }
        // mail successfuly sent
        return true;


    }

}
