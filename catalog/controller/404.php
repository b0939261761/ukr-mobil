<?
class Controller404 extends Controller {
  public function index() {
    // $this->document->setTitle('Упс! Похоже, что страница не существует.');
    $this->document->addMeta(['name' => 'robots', 'content' => 'noindex, nofollow']);
    // $data['linkHome'] = $this->url->link('home/home');
    // $data['footer'] = $this->load->controller('common/footer');
    // $data['header'] = $this->load->controller('common/header');
    // $this->response->addHeader($this->request->server['SERVER_PROTOCOL'] . ' 404 Not Found');
    http_response_code(404);

    $data['header'] = $this->load->controller('shared/components/header');
    $data['footer'] = $this->load->controller('shared/components/footer');

    echo $this->load->view('404/404', $data);
  }
}
