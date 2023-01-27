<?php declare(strict_types=1);

namespace App\Event;

use Symfony\Contracts\EventDispatcher\Event;

class SessionTimeoutEvent extends Event
{
	public const NAME = 'session.timeout';
}
