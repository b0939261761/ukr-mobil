<?
class ControllerErrorNotFound extends Controller {
  public function index() {
    $data['headingH1'] = 'Запрашиваемая страница не найдена!';
    $this->document->setTitle($data['headingH1']);
    $this->document->addMeta(['name' => 'robots', 'content' => 'noindex, nofollow']);
    $data['linkHome'] = $this->url->link('common/home');
    $data['footer'] = $this->load->controller('common/footer');
    $data['header'] = $this->load->controller('common/header');
    $this->response->addHeader($this->request->server['SERVER_PROTOCOL'] . ' 404 Not Found');
    $this->response->setOutput($this->load->view('error/not_found', $data));
  }
}
