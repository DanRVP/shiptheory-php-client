<?php declare(strict_types=1);

namespace ShiptheoryClient;

use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

final class HttpMessageTranslator
{
    /**
     * Transforms Message Interfaces (Request and Response objects) into HTTP strings
     *
     * @param \Psr\Http\Message\MessageInterface $message The message to stringify.
     * @return string
     * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Messages
     */
    final public static function toHttpMessageString(MessageInterface $message): string
    {
        $string = '';
        if ($message instanceof RequestInterface) {
            $string .= $message->getMethod() . ' ' . $message->getRequestTarget();
        }

        $string .= ' HTTP/' . $message->getProtocolVersion();
        if ($message instanceof ResponseInterface) {
            $string .= ' ' . $message->getStatusCode() . ' ' . $message->getReasonPhrase();
        }

        $string .= "\r\n";
        foreach ($message->getHeaders() as $key => $value) {
            if (strtolower($key) === 'authorization') {
                $string .= $key . ': REDACTED';
            } else {
                $string .= $key . ': ' . implode(', ', $value);
            }

            $string .= "\r\n";
        }

        $string .= "\r\n";
        $stream = $message->getBody();
        $body = $stream->getContents();
        $stream->rewind(); // Rewind so stream can be read again via client and user
        $string .= $body;

        return trim($string);
    }
}
