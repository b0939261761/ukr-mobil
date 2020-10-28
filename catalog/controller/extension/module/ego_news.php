<?php

class ControllerExtensionModuleEgoNews extends \Ego\Controllers\BaseController {
	public function index($setting) {	
		$egoPostModel = new \Ego\Models\EgoPost();
		$egoPostContentModel = new \Ego\Models\EgoPostContent();

		$data['postList'] = [];
		$postList = $egoPostModel->getByCategory('news', true) ?? [];

		foreach ($postList as $post) {
			if (count($data['postList']) >= 10) break;
			$postContent = $egoPostContentModel->getByPost($post->getId(), 2, false, true);

			if (empty($postContent)) continue;

			$data['postList'][] = [
				'preview_image' => '/image/' . $post->getPreviewImage(),
				'title' => $postContent[0]->getTitle(),
				'url' => $this->url->link('information/news/read', ['news_id' => $post->getId()]),
				'date' => date('d.m.Y', strtotime($post->getDateUpdate()))
			];
		}

		$data['mytemplate'] = $this->config->get('theme_default_directory');
		return $this->load->view('extension/module/ego_news', $data);
	}
}
