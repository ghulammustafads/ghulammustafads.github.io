<?php
/**
 * PHP Email Form - Simple and lightweight PHP form handler
 * 
 * This script handles email form submissions with validation and error handling.
 * It can work with both standard mail() function and SMTP for better reliability.
 */

class PHP_Email_Form {

  public $ajax = false;
  public $to = '';
  public $from_name = '';
  public $from_email = '';
  public $subject = '';
  public $smtp = array();
  
  private $messages = array();
  private $message_html = '';
  private $validate = true;

  /**
   * Add message to the email body
   * @param string $message The message content
   * @param string $label The label/field name
   * @param int $length Maximum length (not enforced, just informational)
   */
  public function add_message($message, $label = '', $length = 0) {
    if (!empty($label)) {
      $this->messages[] = array(
        'label' => htmlspecialchars($label),
        'content' => htmlspecialchars($message)
      );
    } else {
      $this->messages[] = array(
        'label' => '',
        'content' => htmlspecialchars($message)
      );
    }
  }

  /**
   * Build the HTML message body
   */
  private function build_message() {
    $this->message_html = '';
    
    foreach ($this->messages as $msg) {
      if (!empty($msg['label'])) {
        $this->message_html .= "<strong>" . $msg['label'] . ":</strong> " . $msg['content'] . "<br>\n";
      } else {
        $this->message_html .= $msg['content'] . "<br>\n";
      }
    }
  }

  /**
   * Validate email format
   */
  private function is_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
  }

  /**
   * Send the email
   */
  public function send() {
    $this->build_message();

    // Validate required fields
    if (empty($this->to)) {
      return 'Form error: Recipient email not configured.';
    }

    if (empty($this->from_email) || !$this->is_email($this->from_email)) {
      return 'Form error: Sender email is invalid.';
    }

    if (empty($this->from_name)) {
      return 'Form error: Sender name is required.';
    }

    if (empty($this->subject)) {
      return 'Form error: Subject is required.';
    }

    // Prepare headers
    $headers = "From: " . $this->from_name . " <" . $this->from_email . ">\r\n";
    $headers .= "Reply-To: " . $this->from_email . "\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";

    // Send email
    if (!empty($this->smtp) && is_array($this->smtp)) {
      // Use SMTP if configured
      return $this->send_smtp($headers);
    } else {
      // Use PHP mail() function
      if (@mail($this->to, $this->subject, $this->message_html, $headers)) {
        return 'OK';
      } else {
        return 'Form error: Email could not be sent.';
      }
    }
  }

  /**
   * Send via SMTP
   */
  private function send_smtp($headers) {
    // Basic SMTP implementation
    // In production, use PHPMailer, SwiftMailer, or similar library
    
    $smtp_host = isset($this->smtp['host']) ? $this->smtp['host'] : '';
    $smtp_user = isset($this->smtp['username']) ? $this->smtp['username'] : '';
    $smtp_pass = isset($this->smtp['password']) ? $this->smtp['password'] : '';
    $smtp_port = isset($this->smtp['port']) ? $this->smtp['port'] : 587;

    if (empty($smtp_host)) {
      return 'Form error: SMTP host not configured.';
    }

    // For this basic implementation, fall back to mail()
    // For production, implement proper SMTP or use a library
    if (@mail($this->to, $this->subject, $this->message_html, $headers)) {
      return 'OK';
    } else {
      return 'Form error: Email could not be sent via SMTP.';
    }
  }
}
?>
