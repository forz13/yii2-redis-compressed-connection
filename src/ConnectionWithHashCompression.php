<?php

namespace forz13\redisCompressedConnection;

use Yii;
use yii\base\InvalidConfigException;
use yii\db\Exception;
use yii\redis\Connection;

/**
 * Class ConnectionWithHashCompression
 * @property CompressorInterface $compressor
 * @property bool $compressionOnWrite
 */
class ConnectionWithHashCompression extends Connection implements CompressibleInterface
{
    const HSET = 'hset';
    const HGET = 'hget';
    const HVALS = 'hvals';
    const HGETALL = 'hgetall';
    const HMGET = 'hmget';

    public function getCommandsWithCompression()
    {
        return [self::HSET, self::HGET, self::HVALS, self::HGETALL, self::HMGET];
    }

    private $enableCompressionOnWrite;


    public function setCompressionOnWrite($flag)
    {
        $this->enableCompressionOnWrite = (bool) (int) $flag;
    }

    public function getCompressionOnWrite()
    {
        return $this->enableCompressionOnWrite;
    }

    private $compressorInstance;

    /**
     * @return CompressorInterface|object
     * @throws InvalidConfigException
     */
    public function getCompressor()
    {
        if (!$this->compressorInstance instanceof CompressorInterface) {
            $this->compressorInstance = Yii::createObject($this->compressorInstance);
        }

        return $this->compressorInstance;
    }

    /**
     * @param  array  $data
     */
    public function setCompressor(array $data)
    {
        $this->compressorInstance = $data;
    }


    /**
     * @param  string  $name
     * @param  array  $params
     * @return array|bool|string|null
     */
    public function executeCommand($name, $params = [])
    {
        try {
            $name = trim(strtolower($name));
            if (!in_array($name, $this->getCommandsWithCompression())) {
                return parent::executeCommand($name, $params);
            }
            return $this->executeCommandWithCompression($name, $params);
        } catch (Exception $ignore) {
            return false;
        }
    }

    /**
     * @param $name
     * @param  array  $params
     * @return array|bool|string|null
     * @throws Exception
     */
    private function executeCommandWithCompression($name, $params = [])
    {
        switch ($name) {
            case self::HVALS:
                return $this->hvals($params[0]);
            case self::HGET:
                return $this->hget($params[0], $params[1]);
            case self::HSET:
                return $this->hset($params[0], $params[1], $params[2]);
            case self::HGETALL:
                return $this->hgetall($params[0]);
            case self::HMGET:
                $key = array_shift($params);
                return $this->hmget($key, ...$params);
            default:
                return parent::executeCommand($name, $params);
        }
    }


    /**
     * @param $key
     * @param $field
     * @param $value
     * @return array|bool|mixed|string|null
     * @throws Exception
     */
    public function hset($key, $field, $value)
    {
        if ($this->enableCompressionOnWrite) {
            $value = $this->compressor->compress($value);
        }
        return parent::executeCommand(self::HSET, [$key, $field, $value]);
    }


    /**
     * @param $key
     * @param $field
     * @return array|bool|mixed|string|null
     * @throws Exception
     */
    public function hget($key, $field)
    {
        $rawValue = parent::executeCommand(self::HGET, [$key, $field]);
        if ($this->compressor->isCompressed($rawValue)) {
            return $this->compressor->decompress($rawValue);
        } else {
            return $rawValue;
        }
    }

    /**
     * @param $key
     * @return array|mixed
     * @throws Exception
     */
    public function hvals($key)
    {
        $rawValues          = parent::executeCommand(self::HVALS, [$key]);
        $decompressedValues = [];
        foreach ($rawValues as $rawValue) {
            if ($this->compressor->isCompressed($rawValue)) {
                try {
                    $decompressedValues[] = $this->compressor->decompress($rawValue);
                } catch (\Exception $ignore) {
                }
            } else {
                $decompressedValues[] = $rawValue;
            }
        }
        return $decompressedValues;
    }


    /**
     * @param $key
     * @return array|mixed
     * @throws Exception
     */
    public function hgetall($key)
    {
        $rawValues          = parent::executeCommand(self::HGETALL, [$key]);
        $decompressedValues = [];
        foreach ($rawValues as $rawValue) {
            if ($this->compressor->isCompressed($rawValue)) {
                try {
                    $decompressedValues[] = $this->compressor->decompress($rawValue);
                } catch (\Exception $ignore) {
                }
            } else {
                $decompressedValues[] = $rawValue;
            }
        }
        return $decompressedValues;
    }

    /**
     * @param $key
     * @param  mixed  ...$fields
     * @return array|mixed
     * @throws Exception
     */
    public function hmget($key, ...$fields)
    {
        $rawValues          = parent::executeCommand(self::HMGET,  array_merge([$key], $fields));
        $decompressedValues = [];
        foreach ($rawValues as $rawValue) {
            if ($this->compressor->isCompressed($rawValue)) {
                try {
                    $decompressedValues[] = $this->compressor->decompress($rawValue);
                } catch (\Exception $ignore) {
                }
            } else {
                $decompressedValues[] = $rawValue;
            }
        }
        return $decompressedValues;
    }
}
