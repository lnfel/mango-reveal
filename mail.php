<?php
/**
 * PHPMailer simple contact form example.
 * If you want to accept and send uploads in your form, look at the send_file_upload example.
 */

session_start();

//Import the PHPMailer class into the global namespace
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

require 'vendor/autoload.php';

if (array_key_exists('message', $_POST)) {
    $err = false;
    $msg = '';
    $email = '';
    //Apply some basic validation and filtering to the subject
    if (array_key_exists('subject', $_POST)) {
        $subject = substr(strip_tags($_POST['subject']), 0, 255);
    } else {
        $subject = 'No subject given';
    }
    //Apply some basic validation and filtering to the message
    if (array_key_exists('message', $_POST)) {
        //Limit length and strip HTML tags
        $message = substr(strip_tags($_POST['message']), 0, 16384);
    } else {
        $message = '';
        $msg = 'No message provided!';
        $err = true;
    }
    //Apply some basic validation and filtering to the name
    if (array_key_exists('name', $_POST)) {
        //Limit length and strip HTML tags
        $name = substr(strip_tags($_POST['name']), 0, 255);
    } else {
        $name = '';
    }
    //Validate to address
    //Never allow arbitrary input for the 'to' address as it will turn your form into a spam gateway!
    //Substitute appropriate addresses from your own domain, or simply use a single, fixed address
    if (array_key_exists('to', $_POST) && in_array($_POST['to'], ['sales', 'support', 'info'], true)) {
        $to = $_POST['to'] . '@mangosystemtech.com';
    } else {
        $to = 'info@mangosystemtech.com';
    }
    //Make sure the address they provided is valid before trying to use it
    if (array_key_exists('email', $_POST) && PHPMailer::validateAddress($_POST['email'])) {
        $email = $_POST['email'];
    } else {
        $msg .= 'Error: invalid email address provided';
        $err = true;
    }
    if (!$err) {
        $mail = new PHPMailer;
        $mail->isSMTP();
        $mail->Host = 'smtp.hostinger.ph';
        $mail->Port = 587;
        $mail->SMTPAuth = true;
        $mail->Username = 'info@mangosystemtech.com';
        $mail->Password = 'g&FvbvFf';
        $mail->CharSet = PHPMailer::CHARSET_UTF8;
        //It's important not to use the submitter's address as the from address as it's forgery,
        //which will cause your messages to fail SPF checks.
        //Use an address in your own domain as the from address, put the submitter's address in a reply-to
        $mail->setFrom($to, (empty($name) ? 'Contact form' : $name));
        $mail->addAddress($to);
        $mail->addCC('mangosystems2018@gmail.com');
        $mail->addReplyTo($email, $name);
        $mail->Subject = 'Contact form: ' . $subject;
        $mail->Body = "Sent from mangosystemtech.com contact form.\n\n" . $message;
        if (!$mail->send()) {
            //$msg .= '<strong>Mailer Error: </strong>'. $mail->ErrorInfo;
            $msg .= 'We track these errors automatically, but if the problem persists feel free to contact us. In the meantime, try refreshing.';
            $status = 'failed';
            $_SESSION["error"] = $msg;
        } else {
            $msg .= 'We will keep in touch with you soon ' . $name . '.';
            $status = 'success';
            $_SESSION["notice"] = $msg;
        }
    }
    $response = array('status' => $status, 'message' => $msg);
    //echo '<input type="hidden" id="status" value="' . $status . '">'
    //echo $msg;
    echo json_encode($response);
    //header('Location: index.php');
} ?>