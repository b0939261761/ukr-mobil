<?
class ControllerSharedComponentsHeaderBottom extends Controller {
  public function index() {
    $data['linkLogin'] = $this->url->link('account/login');

    return $this->load->view('shared/components/header_bottom/header_bottom', $data);
  }
}
