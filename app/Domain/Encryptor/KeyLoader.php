<?php

namespace App\Domain\Encryptor;

class KeyLoader
{
    public function __construct(
        private string $privateKeyPath,
        private string $publicKeyPath,
        private string $passphrase,
    ) {
    }

    public function getPrivateKey(string $passphrase = null): \OpenSSLAsymmetricKey|false
    {
        $key = openssl_pkey_get_private($this->privateKeyPath, $passphrase ?? $this->passphrase);

        return $key;
    }

    public function getPublicKey(): \OpenSSLAsymmetricKey|false
    {
        return openssl_pkey_get_public($this->publicKeyPath);
    }
}