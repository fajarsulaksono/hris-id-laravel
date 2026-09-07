<?php

namespace App\Domain\Encryptor;

class Encryptor
{
    private ?string $key = null;

    public function __construct(private KeyLoader $keyLoader)
    {
    }

    public function encrypt($plain): string
    {
        if (!$this->key) {
            $this->key = $this->generateKey();
        }

        $result = openssl_public_encrypt(
            sprintf('%s#%s', $this->key, $plain),
            $encryptedData,
            $this->keyLoader->getPublicKey()
        );

        if (!$result) {
            return (string) $plain;
        }

        return base64_encode(sprintf('%s#%s', $encryptedData, $this->key));
    }

    public function decrypt(string $encrypted, string $key)
    {
        $result = openssl_private_decrypt(
            str_replace(sprintf('#%s', $key), '', base64_decode($encrypted)),
            $decryptedData,
            $this->keyLoader->getPrivateKey()
        );

        if (!$result) {
            return $encrypted;
        }

        return str_replace(sprintf('%s#', $key), '', $decryptedData);
    }

    public function getKey(): string
    {
        return (string) $this->key;
    }

    private function generateKey(): string
    {
        return sha1(uniqid());
    }
}