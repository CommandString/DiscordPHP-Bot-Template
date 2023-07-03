<?php

namespace Core\Database;


interface DatabaseInterface
{
    public function connect();
    public function query($sql);
    public function close();
}
