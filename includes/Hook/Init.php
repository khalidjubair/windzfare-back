<?php

namespace Windzfare\Hook;

class Init{
    function __construct(){

        //Admin hook
        if( is_admin() ){
            new \Windzfare\Admin\Init;  
        }

        //Cptui
        new \Windzfare\Cptui\Init;

        new \Windzfare\Frontend\Init;
        new \Windzfare\Helpers\Shortcodes;

    }
}