<?php

namespace App\EventSubscriber;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class ResponseSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::RESPONSE => [
                    ['securityResponse', 0],
            ],
        ];
    }

    public function securityResponse(ResponseEvent $event)
    {
         $responseHeaders = $event->getResponse()->headers;
         $responseHeaders->set('x-frame-options', 'deny');
         $responseHeaders->set('Strict-Transport-Security', 'max-age=86400');
         $responseHeaders->set('X-Content-Type-Options', 'nosniff');
         $responseHeaders->set('X-XSS-Protection', '1');
         //$responseHeaders->set('Content-Security-Policy', $this->getContentSecurityPolicy());
    }

    public function getContentSecurityPolicy()
    {
        $appUrl    = 'http' . (isset($_SERVER['HTTPS']) ? 's' : '') . '://' . "{$_SERVER['HTTP_HOST']}";
		$staticUrl = $appUrl . '/build/';  // /build
		$externalJsUrl = [
		];
		$externalCssUrl = [
		];
		$externalFontUrl = [
		];
		$externalImgUrl = [
		];
		$externalFrameUrl = [
		];

		$defaultPolicy = 'default-src ' . $appUrl . ' \'unsafe-eval\' \'unsafe-inline\'';
		$scriptPolicy  = 'script-src '  . $appUrl;
		$connectPolicy = 'connect-src ' . $appUrl;
		$stylePolicy   = 'style-src '   . $appUrl;
		$fontPolicy    = 'font-src '    . $appUrl;
		$imgPolicy     = 'img-src '     . $appUrl;
		$framePolicy   = 'frame-src '   . $appUrl;

		if ($staticUrl != $appUrl && !str_contains($staticUrl, $appUrl)) {
			$defaultPolicy .= ' ' . $staticUrl;
			$scriptPolicy  .= ' ' . $staticUrl;
			$connectPolicy .= ' ' . $staticUrl;
			$stylePolicy   .= ' ' . $staticUrl;
			$fontPolicy    .= ' ' . $staticUrl;
			$imgPolicy     .= ' ' . $staticUrl;
			$framePolicy   .= ' ' . $staticUrl;
		}

		$scriptPolicy  .= ' ' . implode(' ', $externalJsUrl) . ' \'unsafe-eval\' \'unsafe-inline\'';
		$connectPolicy .= ' ' . implode(' ', $externalJsUrl) . ' \'unsafe-eval\' \'unsafe-inline\'';
		$stylePolicy   .= ' ' . implode(' ', $externalCssUrl) . ' \'unsafe-inline\'';
		$fontPolicy    .= ' ' . implode(' ', $externalFontUrl);
		$imgPolicy     .= ' ' . implode(' ', $externalImgUrl) . ' data:';
		$framePolicy   .= ' ' . implode(' ', $externalFrameUrl);

		return $defaultPolicy . '; '
			. $scriptPolicy . '; '
			. $connectPolicy . '; '
			. $stylePolicy . '; '
			. $imgPolicy . '; '
			. $framePolicy . '; '
			. $fontPolicy;
    }
}
