<?php declare(strict_types=1);

use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\ResponseInterface;

final class HttpMessageTranslator
{
    final public static function toHttpMessageString(MessageInterface $message): string
    {
        $string = 'HTTP/' . $message->getProtocolVersion();
        if ($message instanceof ResponseInterface) {
            $string .= ' ' . $message->getStatusCode() . ' ' . $message->getReasonPhrase();
        }

        $string .= "\r\n";
        $string .= implode("\r\n", $message->getHeaders());
        $string .= "\r\n\r\n";
        $string .= $message->getBody()->getContents();

        return $string;
    }
}
