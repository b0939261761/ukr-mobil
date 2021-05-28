<?
class Mail {
	public function send($email, $subject, $viewName, $data = []) {
    $blade = new \Philo\Blade\Blade(DIR_APPLICATION . 'view/theme/default/mails', rtrim(DIR_CACHE, '/'));
    $view = $blade->view()->make($viewName, $data)->render();

    $mail = new \PHPMailer\PHPMailer\PHPMailer();
    $mail->isSMTP();
    $mail->Host = 'smtp-pulse.com';
    $mail->SMTPAuth = true;
    $mail->Username = MAIL_USERNAME;
    $mail->Password = MAIL_PASSWORD;
    $mail->SMTPSecure = 'ssl';
    $mail->Port = 465;

    $mail->CharSet = 'UTF-8';
    $mail->From = 'robot@ukr-mobil.com';
    $mail->FromName = 'UKR Mobil';
    $mail->addAddress($email);
    $mail->isHTML(true);
    $mail->Subject = $subject;
    $mail->msgHTML($view);
    $mail->send();
  }
}
