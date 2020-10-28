<?php

class ControllerInformationAboutUs extends Controller {

	public function index() {
		$this->document->setTitle('О компании - интернет-магазин UKRMobil');
		$this->document->setDescription('О компании ✅ UKRMobil ✅ Фиксированные цены ✅ Гарантия ✅ Доставка по всей Украине');
		$this->document->setKeywords('О компании');
		$data['heading_title'] = 'О компании';

		$postModel = new \Ego\Models\EgoPost();
		$postContentModel = new \Ego\Models\EgoPostContent();

		// ----------------------------------

		$post = $postModel->getByCategory('about_us', true);

		if (!empty($post)) {
			$postContent = $postContentModel->getByPost($post[0]->getId(), 2, false, true);
			$data['about_us'] = empty($postContent) ? '' : $postContent[0]->getContent();
		}
		
		$post = $postModel->getByCategory('delivery', true);
		if (!empty($post)) {
			$postContent = $postContentModel->getByPost($post[0]->getId(), 2, false, true);
			$data['delivery'] = empty($postContent) ? '' : $postContent[0]->getContent();
		}
		
		$post = $postModel->getByCategory('warranty', true);
		if (!empty($post)) {
			$postContent = $postContentModel->getByPost($post[0]->getId(), 2, false, true);
			$data['warrantly'] = empty($postContent) ? '' : $postContent[0]->getContent();
		}

		$post = $postModel->getByCategory('contact', true);
		if (!empty($post)) {
			$postContent = $postContentModel->getByPost($post[0]->getId(), 2, false, true);
			$data['contact'] = empty($postContent) ? '' : $postContent[0]->getContent();
		}


		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');
		$this->response->setOutput($this->load->view('information/about_us', $data));
	}

}
