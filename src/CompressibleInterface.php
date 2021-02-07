<?php


namespace forz13\redisCompressedConnection;


interface CompressibleInterface
{

    public function getCompressor();

    public function setCompressor(array $data);

    public function getCommandsWithCompression();

    public function setCompressionOnWrite($flag);

    public function getCompressionOnWrite();

}
