<?php

namespace forz13\redisCompressedConnection;

interface CompressorInterface
{
    public function compress($data);

    public function isCompressed($data);

    public function decompress($data);
}
