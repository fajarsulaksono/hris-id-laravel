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

    public function getPrivateKey(?string $passphrase = null): \OpenSSLAsymmetricKey|false
    {
        return openssl_pkey_get_private($this->getKey($this->privateKeyPath), $passphrase ?? $this->passphrase);
    }

    public function getPublicKey(): \OpenSSLAsymmetricKey|false
    {
        return openssl_pkey_get_public($this->getKey($this->publicKeyPath));
    }

    private function validate(string $path): void
    {
        if (! is_file($path) || ! is_readable($path)) {
            throw new \RuntimeException(sprintf('%s does not exist or is not readable', $path));
        }
    }

    private function getKey(string $path): string
    {
        $this->validate($path);

        $contents = file_get_contents($path);

        if ($contents === false) {
            throw new \RuntimeException(sprintf('%s cannot be read', $path));
        }

        return $contents;
    }
}