<?php declare(strict_types=1);

namespace App\Controller;

use App\Event\SessionTimeoutEvent;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class SessionTimeoutController extends AbstractController
{
	private EventDispatcherInterface $eventDispatcher;

	public function __construct(EventDispatcherInterface $eventDispatcher)
	{
		$this->eventDispatcher = $eventDispatcher;
	}

	#[Route(path: '/dispatch-session-timeout-event', name: 'dispatch_session_timeout_event', methods: ['POST'])]
	public function dispatchSessionTimeoutEvent(Request $request): JsonResponse
	{
		if ($request->isXmlHttpRequest()) {
			$event = new SessionTimeoutEvent();
			$this->eventDispatcher->dispatch($event);
			return new JsonResponse(['status' => 'success']);
		}
		return new JsonResponse(['status' => 'error']);
	}
}
