<?php

namespace Ego\Providers;

use Ego\Services\ConfigService;
use Philo\Blade\Blade;
use Swift_Mailer;
use Swift_Message;
use Swift_SmtpTransport;

class MailProvider {

	/** @var string[] */
	private $toList = [];

	/** @var string[] */
	private $fromList = [];

	/**
	 * @var string
	 */
	private $subject;

	/** @var string */
	private $view;

	/**
	 * @var array
	 */
	private $bodyData = [];

	/**
	 * Transport type for send emails
	 *
	 * @var string
	 */
	private $transportType = 'smtp';

	/**
	 * @return array
	 */
	public function getTo(): array {
		return $this->toList;
	}

	/**
	 * @param string $to
	 * @param string|null $name
	 * @return MailProvider
	 */
	public function setTo($to, string $name = null): self {
		if (is_array($to)) {
			$this->toList = $to;
		} else {
			$this->toList[$to] = $name;
		}

		return $this;
	}

	/**
	 * @return array
	 */
	public function getFrom(): array {
		return $this->fromList;
	}

	/**
	 * @param string $from
	 * @param string|null $name
	 * @return MailProvider
	 */
	public function setFrom($from, string $name = null): self {
		if (is_array($from)) {
			$this->fromList = $from;
		} else {
			$this->fromList[$from] = $name;
		}

		return $this;
	}

	/**
	 * @return string
	 */
	public function getSubject(): string {
		return $this->subject;
	}

	/**
	 * @param string $subject
	 * @return MailProvider
	 */
	public function setSubject(string $subject): self {
		$this->subject = $subject;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getView(): string {
		return $this->view;
	}

	/**
	 * @param string $view
	 * @return MailProvider
	 */
	public function setView(string $view): self {
		$this->view = $view;

		return $this;
	}

	/**
	 * @return array
	 */
	public function getBodyData(): array {
		return $this->bodyData;
	}

	/**
	 * @param array $bodyData
	 * @return MailProvider
	 */
	public function setBodyData(array $bodyData): self {
		$this->bodyData = $bodyData;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getTransportType() {
		return $this->transportType;
	}

	/**
	 * @param string $transportType
	 * @return MailProvider
	 */
	public function setTransportType(string $transportType): self {
		$this->transportType = $transportType;

		return $this;
	}

	/**
	 * Send Mail
	 *
	 * @return bool
	 * @throws \Exception
	 */
	public function sendMail() {
		if (empty($this->getTo())) {
			throw new \Exception('`Mail` Provider - `to` parameter is empty.');
		}

		if (empty($this->getSubject())) {
			throw new \Exception('`Mail` Provider - `subject` parameter is empty.');
		}

		if (empty($this->getView())) {
			throw new \Exception('`Mail` Provider - `view` parameter is empty.');
		}

		//region Define Services
		$configService = new ConfigService();
		//endregion

		// Create the Transport
		$transport = (new Swift_SmtpTransport(
			_env('MAIL_HOST'),
			_env('MAIL_PORT'),
			_env('MAIL_ENCRYPTION')
		))
			->setUsername(_env('MAIL_USERNAME'))
			->setPassword(_env('MAIL_PASSWORD'));

		switch ($this->getTransportType()) {
			case 'sendmail':
				$transport = (new \Swift_SendmailTransport());

				break;
		}

		// Create the Mailer using your created Transport
		$mailer = new Swift_Mailer($transport);

		//region Create a message
		//	Email
		$bodyDataEmail = new \stdClass();
		$bodyDataEmail->fontSize = 'font-size: 16px;';
		$bodyDataEmail->egoLinkButton = 'border-radius: 3px;border: 2px solid #274b89; padding: 15px; text-align: center;';
		$bodyDataEmail->egoLinkButtonLink = 'text-decoration: none; color: #274b89; font-weight: 600;';
		//	Info
		$bodyDataInfo = new \stdClass();
		$bodyDataInfo->siteTitle = $configService->getSiteTitle();
		$bodyDataInfo->siteUrl = $configService->getSiteUrl();

		$bodyData = [
			'email' => $bodyDataEmail,
			'info' => $bodyDataInfo,
			'data' => $this->getBodyData()
		];

		$message = (new Swift_Message($this->getSubject()))
			->setContentType('text/html')
			->setFrom($this->getFrom())
			->setTo($this->getTo())
			->setBody(_view($this->getView(), $bodyData));
		//endregion

		//echo _view($this->getView(), $bodyData);
		//die();

		// Send the message
		return $mailer->send($message) > 0;
	}

}
