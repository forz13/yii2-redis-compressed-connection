<?php

namespace forz13\redisCompressedConnection;

use function gzdecode;
use function gzencode;
use function is_string;
use function mb_strpos;

/**
 * Class GzipCompressor
 * @package redisCompressedConnection
 * @property int $compressionLevel
 */
class GzipCompressor implements CompressorInterface
{
    const DEFAULT_COMPRESSION_LEVEL = 6;
    const BYTE_CHARSET = 'US-ASCII';

    private $compression;

    /**
     * @return mixed
     */
    public function getCompressionLevel()
    {
        return $this->compression ? $this->compression : self::DEFAULT_COMPRESSION_LEVEL;
    }

    /**
     * @param $level
     * @throws CompressorException
     */
    public function setCompressionLevel($level)
    {
        if (!in_array($level, range(1, 9))) {
            throw new CompressorException('level must be >=1 and <=9');
        }
        $this->compression = $level;
    }

    /**
     * @param string $data
     * @return string
     * @throws CompressorException
     */
    public function compress($data)
    {
        $compressed = @gzencode($data, $this->compressionLevel);
        if ($compressed === false) {
            throw new CompressorException('Compression failed');
        }

        return $compressed;
    }

    /**
     * @param $data
     * @return bool
     */
    public function isCompressed($data)
    {
        if (!is_string($data)) {
            return false;
        }

        return 0 === mb_strpos($data, "\x1f" . "\x8b" . "\x08", 0, self::BYTE_CHARSET);
    }

    /**
     * @param string $data
     * @return string
     * @throws CompressorException
     */
    public function decompress($data)
    {
        $decompressed = @gzdecode($data);
        if ($decompressed === false) {
            throw new CompressorException('Decompression failed');
        }

        return $decompressed;
    }
}
