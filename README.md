keyvalue-authentication
=======================

A key-value implementation of the Tao user authentication

Requirement
====================
You need to have a redis server installed. You will need to enable redis in the phpconfig, and perhaps to add redis.so library to your system if not already installed


Installation 
======================

This system can be added to a projet as a library. You need to add this parameter to your composer.json 

    "minimum-stability" : "dev",
    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/oat-sa/keyvalue-authentication"
        }
    ],
    "require": {
        "oat-sa/keyvalue-authentication": "dev-library"
    },

Once it's done, run a composer update. 

------------------------------

To enable them, you need to go to generis/common/conf/auth.conf.php and add these lines 

    array(
        'driver' => 'core_kernel_users_AuthAdapter',
    ),

Then the login will try to use this library. 

Be sure you have enable 

    'authKeyValue' => array(
	    'driver' => 'phpredis',
            'host' => '127.0.0.1',
            'port' => 6379
	),

in the generis/common/conf/persistences.conf.php