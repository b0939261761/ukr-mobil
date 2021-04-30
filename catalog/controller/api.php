<?
class ControllerApi extends Controller {

  public function feedback() {
    $requestData = json_decode(file_get_contents('php://input'), true);

    $subject = 'Помилка на сайті';
    $emailTo = 'ukrmobil1@gmail.com';
    // $emailTo = 'b360124@gmail.com';
    if ($requestData['type'] == 'manager') {
      $subject = 'Лист директору';
      $emailTo = 'director.ukrmobil@gmail.com';
      // $emailTo = 'b360124@gmail.com';
    }

    (new \Ego\Providers\MailProvider())
      ->setTo('b360124@gmail.com')
      ->setTo($emailTo)
      ->setFrom('robot@ukr-mobil.com', 'UKR Mobil')
      ->setSubject($subject)
      ->setView('mails.feedback')
      ->setBodyData([
        'name'        => $requestData['name'],
        'phone'       => $requestData['phone'],
        'email'       => $requestData['email'],
        'description' => $requestData['description']
      ])
      ->sendMail();

    exit();
  }
}


// У листів має бути різна тема і йти вони мають на різні емейли, по кліку на "написати директору" - тема листа: Лист директору. Надсилати на емейл director.ukrmobil@gmail.com
