<?php
class PHP_Email_Form {
  public $to;
  public $from_name;
  public $from_email;
  public $subject;
  public $messages = [];
  public $ajax = false;
  public $recaptcha_secret_key = '6LfB_PUpAAAAAOQUcuNDtBt0E3fBqalyoXF-1mUA';

  public function add_message($content, $label, $priority = 0) {
    $this->messages[] = ['content' => $content, 'label' => $label, 'priority' => $priority];
  }

  public function send() {
    if ($this->recaptcha_secret_key) {
      $recaptcha_response = $_POST['g-recaptcha-response'];
      $response = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=6LfB_PUpAAAAAOQUcuNDtBt0E3fBqalyoXF-1mUA" . $this->recaptcha_secret_key . "&response=" . $recaptcha_response);
      $response_keys = json_decode($response, true);
      if (intval($response_keys["success"]) !== 1) {
        return 'Recaptcha verification failed.';
      }
    }

    $email_content = "You have received a new message:\n\n";

    foreach ($this->messages as $message) {
      $email_content .= $message['label'] . ": " . $message['content'] . "\n";
    }

    $headers = "From: " . $this->from_name . " <" . $this->from_email . ">\r\n" .
               "Reply-To: " . $this->from_email . "\r\n" .
               "X-Mailer: PHP/" . phpversion();

    if (mail($this->to, $this->subject, $email_content, $headers)) {
      return 'Your message has been sent successfully.';
    } else {
      return 'Unable to send your message. Please try again later.';
    }
  }
}
?>
