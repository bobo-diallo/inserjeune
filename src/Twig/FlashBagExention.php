<?php

namespace App\Twig;

use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use App\Tools\Utils;

class FlashBagExention extends AbstractExtension {

	private RequestStack $requestStack;

	public function __construct(RequestStack $requestStack) {
		$this->requestStack = $requestStack;
	}

	public function getFunctions(): array {
		return [
			new TwigFunction('falsh_bag', [$this, 'showFlashBag'], ['is_safe' => ['html']])
		];
	}

	/**
	 * @return string
	 */
	public function showFlashBag(): string {
		$flashBags = $this->requestStack->getSession()->getFlashBag()->all();

		foreach ($flashBags as $type => $messages) {
			foreach ($messages as $message) {
				if (in_array($type, [Utils::OFB_DANGER, Utils::OFB_SUCCESS, Utils::OFB_WARNING])) {
					$html = "<p class=\"$type\" style=\"padding: 5px; margin: 15px; color: #fff; background-color: #90be2e; border-color: #90be2e; font-size: 14px\">$message</p>";
				} else {
					$html = "<p class=\"btn-$type kz-flashbag\" style=\"padding: 5px; margin: 15px;\">$message</p>";
				}
				return $html;
			}
		}
		return '';
	}

}
