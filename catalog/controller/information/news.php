<?php

use Ego\Providers\Util;

class ControllerInformationNews extends Controller {

	public function index() {
		$postModel = new \Ego\Models\EgoPost();
		$postContentModel = new \Ego\Models\EgoPostContent();

		$data['mytemplate'] = $this->config->get('theme_default_directory');

		$data['news'] = [];
		$newsList = $postModel->getByCategory('news', true) ?? [];

		foreach ($newsList as $news) {
			$newsContents = $postContentModel->getByPost($news->getId(), 2, false, true);
			if (empty($newsContents) || empty($newsContents[0])) contimue;
			
			$data['news'][] = [
				'preview_image' => "/image/{$news->getPreviewImage()}",
				'title' => $newsContents[0]->getTitle(),
				'description' => substr(strip_tags($newsContents[0]->getContent()), 0, 50),
				'url' => $this->url->link('information/news/read', ['news_id' => $news->getId()])
			];
		}

		$this->document->setTitle("Новости от UKRMobil");
		$this->document->setDescription("Новости от UKRMobil ✅ Актуально ✅ Полезно");
		$this->document->setKeywords('Новости');
		$data['heading_title'] = 'Новости';
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');
		$this->response->setOutput($this->load->view('information/news', $data));
	}

	public function read() {
		$postModel = new \Ego\Models\EgoPost();
		$postContentModel = new \Ego\Models\EgoPostContent();

		$data['news'] = [];
		$title = '';
		$news = $postModel->get($this->request->get['news_id'], true);

		if (!empty($news)) {
			$newsContents = $postContentModel->getByPost($news->getId(), 2, false, true);

			if (!empty($newsContents) && !empty($newsContents[0])) {
				$title = $newsContents[0]->getTitle();
				$data['news'] = [
					'preview_image' => "/image/{$news->getPreviewImage()}",
					'title' => $title,
					'description' => $newsContents[0]->getContent()
				];
			}
		}
	
		$this->document->setTitle("{$title} - новости от UKRMobil");
		$this->document->setDescription("{$title} ✅ Новости от UKRMobil ✅ Актуально ✅ Полезно");
		$this->document->setKeywords('Новости');
		$data['heading_title'] = $title;
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');
		$this->response->setOutput($this->load->view('information/news_read', $data));
	}

}
