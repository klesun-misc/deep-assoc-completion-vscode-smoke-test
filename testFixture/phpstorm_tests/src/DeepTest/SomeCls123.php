<?php

class SomeCls123
{
    /**
     * @var $data = (object) [
     *     'database' => (object)[
     *         'server' => '',
     *         'user' => '',
     *         'pass' => '',
     *         'database' => '',
     *         'port' => '5719',
     *         'socket' => '',
     *     ],
     *     'max_occurences' => 10,
     *     'zhopa' => (object)[
     *         'guzno' => 123,
     *         'dzhigurda' => 321,
     *     ],
     * ]
     */
    public static $data;

    public function __get($key)
    {
        if (isset(self::$data->$key)) {
            return self::$data->$key;
        }
        throw new \RuntimeException('Configuration property ' . $key . ' not exists.', 2);
    }

    public static function getProp($name)
    {
        return self::$data->$name;
    }
}


function asdasd($arg)
{
    (new SomeCls123())->database->e;
}

asdasd('qwe');
