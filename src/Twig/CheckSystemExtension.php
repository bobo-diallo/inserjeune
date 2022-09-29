<?php
namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class CheckSystemExtension extends AbstractExtension {

   public function getFunctions(): array {
      return [
         new TwigFunction('check_os', [$this, 'checkOs'], ['is_safe' => ['html']]),
         ];
   }

   public function checkOs(string $val): string {
	   return match ($val) {
		   'm' => php_uname('m'),
		   'n' => php_uname('n'),
		   'r' => php_uname('r'),
		   's' => php_uname('s'),
		   default => php_uname('a'),
	   };
   }
}
