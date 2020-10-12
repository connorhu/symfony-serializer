<?php

require('vendor/autoload.php');

use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerAwareInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Encoder\XmlEncoder;

class Message
{
    public $text;
}

class Envelpe
{
    public $message;
}

class EnvelpeNormalizer implements SerializerAwareInterface, NormalizerInterface
{
    public function normalize($envelope, string $format = null, array $context = [])
    {
        $xmlContent = $this->serializer->serialize($envelope->message, 'xml');
        
        $encodedContent = base64_encode($xmlContent);

        return [
            'message' => $encodedContent,
        ];
    }

    public function supportsNormalization($data, string $format = null, array $context = [])
    {
        return $data instanceof Envelpe;
    }
    
    public function setSerializer(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }
}

class MessageNormalizer implements NormalizerInterface
{
    public function normalize($message, string $format = null, array $context = [])
    {
        return [
            'text' => $message->text,
        ];
    }

    public function supportsNormalization($data, string $format = null, array $context = [])
    {
        return $data instanceof Message;
    }
}

$xmlEncoder = new XmlEncoder();
$envelopeNormalizer = new EnvelpeNormalizer();
$messageNormalizer = new MessageNormalizer();

$serializer = new Serializer([
    $envelopeNormalizer,
    $messageNormalizer,
], [
    'xml' => $xmlEncoder,
]);

$enveope = new Envelpe();
$enveope->message = new Message();
$enveope->message->text = 'Hello Word';

$serializer->serialize($enveope, 'xml');