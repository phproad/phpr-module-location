<?php

/**
 * Class autoloader.
 */
class Geocoder_Autoloader
{

    private $base_dir;

    /**
     * Autoloader constructor.
     *
     * @param string $base_dir Geocoder library base directory (default: dirname(__FILE__))
     */
    public function __construct($base_dir = null)
    {
        if ($base_dir === null) {
            $this->base_dir = dirname(__FILE__);
        } else {
            $this->base_dir = rtrim($base_dir, '/');
        }
    }

    /**
     * Register a new instance as an SPL autoloader.
     *
     * @param string $base_dir Geocoder library base directory (default: dirname(__FILE__).'/..')
     *
     * @return Geocoder_Autoloader Registered Autoloader instance
     */
    public static function register($base_dir = null)
    {
        $loader = new self($base_dir);
        spl_autoload_register(array($loader, 'autoload'));
        return $loader;
    }

    /**
     * Autoload Geocoder classes.
     *
     * @param string $class
     */
    public function autoload($class)
    {
        if ($class[0] === '\\') {
            $class = substr($class, 1);
        }

        // if (strpos($class, 'Geocoder') !== 0) {
        //     return;
        // }
        
        $file = sprintf('%s/%s.php', $this->base_dir, str_replace('_', '/', $class));
        if (is_file($file)) {
            require $file;
        }
    }
}



Geocoder_Autoloader::register();