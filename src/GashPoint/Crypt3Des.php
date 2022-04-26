<?php

namespace GashPoint;

use InvalidArgumentException;

class Crypt3Des
{
    /** @var string 交易密鑰1 */
    private $key;
    /** @var string 交易密鑰2 */
    private $iv;

    /**
     * @param string $key 交易密鑰1
     * @param string $iv 交易密鑰2
     */
    public function __construct(string $key, string $iv)
    {
        if (empty($key)) {
            throw new InvalidArgumentException('key is not valid');
        }
        if (empty($iv)) {
            throw new InvalidArgumentException('iv is not valid');
        }

        $this->key = $key;
        $this->iv = $iv;
    }

    /**
     * @param string $value
     * @return string
     */
    public function encrypt(string $value): string
    {
        $iv = base64_decode($this->iv);
        $key = base64_decode($this->key);
        $value = $this->PaddingPKCS7($value);
        $ret = openssl_encrypt(
            $value, "DES-EDE3-CBC", $key, OPENSSL_RAW_DATA | OPENSSL_NO_PADDING, $iv
        );

        return base64_encode($ret);
    }

    /**
     * @param string $data
     * @return string
     */
    private function PaddingPKCS7(string $data): string
    {
        $block_size = 8;
        $padding_char = $block_size - (strlen($data) % $block_size);
        $data .= str_repeat(chr($padding_char), $padding_char);

        return $data;
    }
}