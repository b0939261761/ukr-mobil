<?
class ControllerIncomeIncome extends Controller {
  public function index() {
    $data['header'] = $this->load->controller('shared/components/header');
    $data['footer'] = $this->load->controller('common/footer');

    $this->response->setOutput($this->load->view('income', $data));
  }
}
