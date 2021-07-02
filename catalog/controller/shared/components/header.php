<?
class ControllerSharedComponentsHeader extends Controller {
  public function index() {
    header('Cache-Control: no-store');
    $data['base'] = $this->main->getDomain();
    $this->document->addLink($this->main->getCanonical(), 'canonical');

    $data['title'] = $this->document->getTitle();
    $data['description'] = $this->document->getDescription();
    $data['keywords'] = $this->document->getKeywords();
    $data['metaList'] = $this->document->getMetaList();
    $data['microdataList'] = $this->document->getMicrodata();
    $data['dataLayer'] = $this->document->getDataLayer();
    $data['links'] = $this->document->getLinks();
    $data['preloads'] = $this->document->getPreloads();
    $data['libStyles'] = $this->document->getLibStyles();
    $data['customStyles'] = $this->document->getCustomStyles();

    $data['headerBanner'] = $this->load->controller('shared/components/header_banner');
    $data['headerTop'] = $this->load->controller('shared/components/header_top');
    $data['headerBottom'] = $this->load->controller('shared/components/header_bottom');

    return $this->load->view('shared/components/header/header', $data);
  }
}
