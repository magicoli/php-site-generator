<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInite09b7c3d741c63a2bc9c3e6b90bbfc0c
{
    public static $prefixLengthsPsr4 = array (
        'S' => 
        array (
            'ScssPhp\\ScssPhp\\' => 16,
        ),
        'M' => 
        array (
            'Magicoli\\PhpSiteGenerator\\' => 26,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'ScssPhp\\ScssPhp\\' => 
        array (
            0 => __DIR__ . '/..' . '/scssphp/scssphp/src',
        ),
        'Magicoli\\PhpSiteGenerator\\' => 
        array (
            0 => __DIR__ . '/../..' . '/src',
        ),
    );

    public static $prefixesPsr0 = array (
        'P' => 
        array (
            'Parsedown' => 
            array (
                0 => __DIR__ . '/..' . '/erusev/parsedown',
            ),
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInite09b7c3d741c63a2bc9c3e6b90bbfc0c::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInite09b7c3d741c63a2bc9c3e6b90bbfc0c::$prefixDirsPsr4;
            $loader->prefixesPsr0 = ComposerStaticInite09b7c3d741c63a2bc9c3e6b90bbfc0c::$prefixesPsr0;
            $loader->classMap = ComposerStaticInite09b7c3d741c63a2bc9c3e6b90bbfc0c::$classMap;

        }, null, ClassLoader::class);
    }
}
