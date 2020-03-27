<?php

namespace Windzfare\Admin;

class Init{
    function __construct(){
        new Enqueue;
        new Menu\Init;
        new Product\Init;
    }
}