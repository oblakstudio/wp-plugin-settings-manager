<?php return array(
    'root' => array(
        'name' => '__root__',
        'pretty_version' => '1.0.0+no-version-set',
        'version' => '1.0.0.0',
        'reference' => NULL,
        'type' => 'library',
        'install_path' => __DIR__ . '/../../',
        'aliases' => array(),
        'dev' => true,
    ),
    'versions' => array(
        '__root__' => array(
            'pretty_version' => '1.0.0+no-version-set',
            'version' => '1.0.0.0',
            'reference' => NULL,
            'type' => 'library',
            'install_path' => __DIR__ . '/../../',
            'aliases' => array(),
            'dev_requirement' => false,
        ),
        'dealerdirect/phpcodesniffer-composer-installer' => array(
            'pretty_version' => 'v1.0.0',
            'version' => '1.0.0.0',
            'reference' => '4be43904336affa5c2f70744a348312336afd0da',
            'type' => 'composer-plugin',
            'install_path' => __DIR__ . '/../dealerdirect/phpcodesniffer-composer-installer',
            'aliases' => array(),
            'dev_requirement' => true,
        ),
        'oblak/wordpress-coding-standard' => array(
            'pretty_version' => 'dev-master',
            'version' => 'dev-master',
            'reference' => '5a25c24bd5dcc8efb586dcd63e8eab14417c422b',
            'type' => 'phpcodesniffer-standard',
            'install_path' => __DIR__ . '/../oblak/wordpress-coding-standard',
            'aliases' => array(
                0 => '9999999-dev',
            ),
            'dev_requirement' => true,
        ),
        'phpcompatibility/php-compatibility' => array(
            'pretty_version' => '9.3.5',
            'version' => '9.3.5.0',
            'reference' => '9fb324479acf6f39452e0655d2429cc0d3914243',
            'type' => 'phpcodesniffer-standard',
            'install_path' => __DIR__ . '/../phpcompatibility/php-compatibility',
            'aliases' => array(),
            'dev_requirement' => true,
        ),
        'phpcompatibility/phpcompatibility-paragonie' => array(
            'pretty_version' => '1.3.2',
            'version' => '1.3.2.0',
            'reference' => 'bba5a9dfec7fcfbd679cfaf611d86b4d3759da26',
            'type' => 'phpcodesniffer-standard',
            'install_path' => __DIR__ . '/../phpcompatibility/phpcompatibility-paragonie',
            'aliases' => array(),
            'dev_requirement' => true,
        ),
        'phpcompatibility/phpcompatibility-wp' => array(
            'pretty_version' => '2.1.4',
            'version' => '2.1.4.0',
            'reference' => 'b6c1e3ee1c35de6c41a511d5eb9bd03e447480a5',
            'type' => 'phpcodesniffer-standard',
            'install_path' => __DIR__ . '/../phpcompatibility/phpcompatibility-wp',
            'aliases' => array(),
            'dev_requirement' => true,
        ),
        'phpcsstandards/phpcsextra' => array(
            'pretty_version' => '1.1.0',
            'version' => '1.1.0.0',
            'reference' => '61a9be9f74a53735f7c421d7de8dc64fa80488e6',
            'type' => 'phpcodesniffer-standard',
            'install_path' => __DIR__ . '/../phpcsstandards/phpcsextra',
            'aliases' => array(),
            'dev_requirement' => true,
        ),
        'phpcsstandards/phpcsutils' => array(
            'pretty_version' => '1.0.8',
            'version' => '1.0.8.0',
            'reference' => '69465cab9d12454e5e7767b9041af0cd8cd13be7',
            'type' => 'phpcodesniffer-standard',
            'install_path' => __DIR__ . '/../phpcsstandards/phpcsutils',
            'aliases' => array(),
            'dev_requirement' => true,
        ),
        'squizlabs/php_codesniffer' => array(
            'pretty_version' => '3.7.2',
            'version' => '3.7.2.0',
            'reference' => 'ed8e00df0a83aa96acf703f8c2979ff33341f879',
            'type' => 'library',
            'install_path' => __DIR__ . '/../squizlabs/php_codesniffer',
            'aliases' => array(),
            'dev_requirement' => true,
        ),
        'wp-coding-standards/wpcs' => array(
            'pretty_version' => 'dev-develop',
            'version' => 'dev-develop',
            'reference' => 'e17e05c14c8e63a54595ca9b97247f2fe85e9073',
            'type' => 'phpcodesniffer-standard',
            'install_path' => __DIR__ . '/../wp-coding-standards/wpcs',
            'aliases' => array(
                0 => '9999999-dev',
            ),
            'dev_requirement' => true,
        ),
    ),
);