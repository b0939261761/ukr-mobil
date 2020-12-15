<?
class ControllerAccountLogout extends Controller {
  public function index() {
    if ($this->customer->isLogged()) {
      $this->customer->logout();
      $this->response->redirect($this->url->link('account/logout'));
    }

    $data['message'] = "<p>Вы вышли из Личного Кабинета.</p>
      <p>Ваша корзина покупок была сохранена.
      Она будет восстановлена при следующем входе в Ваш Личный Кабинет.</p>";

    $data['linkContinue'] = $this->url->link('common/home');
    $data['headingH1'] = 'Выход';
    $this->document->setTitle($data['headingH1']);
    $this->document->addMeta(['name' => 'robots', 'content' => 'noindex, nofollow']);
    $this->document->setMicrodataBreadcrumbs();
    $data['footer'] = $this->load->controller('common/footer');
    $data['header'] = $this->load->controller('common/header');
    $this->response->setOutput($this->load->view('account/success', $data));
  }
}
