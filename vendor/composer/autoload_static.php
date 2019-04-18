<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInitf60b29b38a19ee96da3a8a3f7233ddce
{
    public static $prefixLengthsPsr4 = array (
        'P' => 
        array (
            'P4EN\\Views\\' => 11,
            'P4EN\\Models\\' => 12,
            'P4EN\\Controllers\\Menu\\' => 22,
            'P4EN\\Controllers\\Blocks\\' => 24,
            'P4EN\\Controllers\\' => 17,
            'P4EN\\' => 5,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'P4EN\\Views\\' => 
        array (
            0 => __DIR__ . '/../..' . '/classes/view',
        ),
        'P4EN\\Models\\' => 
        array (
            0 => __DIR__ . '/../..' . '/classes/model',
        ),
        'P4EN\\Controllers\\Menu\\' => 
        array (
            0 => __DIR__ . '/../..' . '/classes/controller/menu',
        ),
        'P4EN\\Controllers\\Blocks\\' => 
        array (
            0 => __DIR__ . '/../..' . '/classes/controller/blocks',
        ),
        'P4EN\\Controllers\\' => 
        array (
            0 => __DIR__ . '/../..' . '/classes/controller',
        ),
        'P4EN\\' => 
        array (
            0 => __DIR__ . '/../..' . '/classes',
        ),
    );

    public static $classMap = array (
        'P4EN\\Controllers\\Api\\Fields_Controller' => __DIR__ . '/../..' . '/classes/controller/api/class-fields-controller.php',
        'P4EN\\Controllers\\Api\\Questions_Controller' => __DIR__ . '/../..' . '/classes/controller/api/class-questions-controller.php',
        'P4EN\\Controllers\\Api\\Rest_Controller' => __DIR__ . '/../..' . '/classes/controller/api/class-rest-controller.php',
        'P4EN\\Controllers\\Blocks\\Controller' => __DIR__ . '/../..' . '/classes/controller/blocks/class-controller.php',
        'P4EN\\Controllers\\Blocks\\ENBlock_Controller' => __DIR__ . '/../..' . '/classes/controller/blocks/class-enblock-controller.php',
        'P4EN\\Controllers\\Blocks\\ENForm_Controller' => __DIR__ . '/../..' . '/classes/controller/blocks/class-enform-controller.php',
        'P4EN\\Controllers\\Enform_Fields_List_Table' => __DIR__ . '/../..' . '/classes/controller/class-enform-fields-list-table.php',
        'P4EN\\Controllers\\Enform_Questions_List_Table' => __DIR__ . '/../..' . '/classes/controller/class-enform-questions-list-table.php',
        'P4EN\\Controllers\\Ensapi_Controller' => __DIR__ . '/../..' . '/classes/controller/class-ensapi-controller.php',
        'P4EN\\Controllers\\Menu\\Controller' => __DIR__ . '/../..' . '/classes/controller/menu/class-controller.php',
        'P4EN\\Controllers\\Menu\\Enform_Post_Controller' => __DIR__ . '/../..' . '/classes/controller/menu/class-enform-post-controller.php',
        'P4EN\\Controllers\\Menu\\Fields_Settings_Controller' => __DIR__ . '/../..' . '/classes/controller/menu/class-fields-settings-controller.php',
        'P4EN\\Controllers\\Menu\\Pages_Datatable_Controller' => __DIR__ . '/../..' . '/classes/controller/menu/class-pages-datatable-controller.php',
        'P4EN\\Controllers\\Menu\\Questions_Settings_Controller' => __DIR__ . '/../..' . '/classes/controller/menu/class-questions-settings-controller.php',
        'P4EN\\Controllers\\Menu\\Settings_Controller' => __DIR__ . '/../..' . '/classes/controller/menu/class-settings-controller.php',
        'P4EN\\Controllers\\Uninstall_Controller' => __DIR__ . '/../..' . '/classes/controller/class-uninstall-controller.php',
        'P4EN\\Loader' => __DIR__ . '/../..' . '/classes/class-loader.php',
        'P4EN\\Models\\Fields_Model' => __DIR__ . '/../..' . '/classes/model/class-fields-model.php',
        'P4EN\\Models\\Model' => __DIR__ . '/../..' . '/classes/model/class-model.php',
        'P4EN\\Models\\Questions_Model' => __DIR__ . '/../..' . '/classes/model/class-questions-model.php',
        'P4EN\\Views\\View' => __DIR__ . '/../..' . '/classes/view/class-view.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInitf60b29b38a19ee96da3a8a3f7233ddce::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInitf60b29b38a19ee96da3a8a3f7233ddce::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInitf60b29b38a19ee96da3a8a3f7233ddce::$classMap;

        }, null, ClassLoader::class);
    }
}
