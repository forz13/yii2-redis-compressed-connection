<?php

namespace forz13\redisCompressedConnection;

use Yii;
use yii\base\InvalidConfigException;
use yii\db\Exception;
use yii\redis\Connection;

/**
 * Class ConnectionWithKeyValueCompression
 * @property CompressorInterface $compressor
 * @property bool $compressionOnWrite
 */
class ConnectionWithKeyValueCompression extends Connection implements CompressibleInterface
{
    const SET = 'set';
    const GET = 'get';

    public function getCommandsWithCompression()
    {
        return [self::SET, self::GET];
    }

    private $enableCompressionOnWrite;


    public function setCompressionOnWrite($flag)
    {
        $this->enableCompressionOnWrite = (bool)(int)$flag;
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
     * @param array $data
     */
    public function setCompressor(array $data)
    {
        $this->compressorInstance = $data;
    }


    /**
     * @param string $name
     * @param array $params
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
     * @param array $params
     * @return array|bool|string|null
     * @throws Exception
     */
    private function executeCommandWithCompression($name, $params = [])
    {
        switch ($name) {
            case self::SET:
                $key = $params[0];
                $value = $params[1];
                $params = array_slice($params, 2);
                return $this->set($key, $value, ...$params);
            case self::GET:
                return $this->get($params[0]);
            default:
                return parent::executeCommand($name, $params);
        }
    }


    /**
     * @param $key
     * @param $value
     * @param mixed ...$options
     * @return array|bool|mixed|string|null
     * @throws Exception
     */
    public function set($key, $value, ...$options)
    {
        if ($this->enableCompressionOnWrite) {
            $value = $this->compressor->compress($value);
        }
        $params = [];
        $params[] = $key;
        $params[] = $value;
        foreach ($options as $option) {
            $params [] = $option;
        }
        return parent::executeCommand(self::SET, $params);
    }



    /**
     * @param $key
     * @return array|bool|mixed|string|null
     * @throws Exception
     */
    public function get($key)
    {
        $rawValue = parent::executeCommand(self::GET, [$key]);
        if ($this->compressor->isCompressed($rawValue)) {
            return $this->compressor->decompress($rawValue);
        } else {
            return $rawValue;
        }
    }
}
