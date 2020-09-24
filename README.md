keyvalue-authentication
=======================

A key-value implementation of the Tao 3.0 user authentication

Requirement
====================
You need to have a redis server installed. You will need to enable redis in the phpconfig, and perhaps to add redis.so library to your system if not already installed


Installation 
======================

This system can be added to a projet as a library. You need to add this parameter to your **composer.json** 

    "minimum-stability" : "dev",
    "require": {
        "oat-sa/generis-auth-keyvalue": "dev-master"
    },

Once it's done, run a composer update. 

------------------------------

Be sure to have a key value persistence registered that will be used for caching. The default persistence identifier is `authKeyValue`, but you will be able to configure which one will be used. Here is an example of registered redis persistence in **config/generis/persistences.conf.php**:

    'authKeyValue' => array(
        'driver' => 'phpredis',
        'host' => '127.0.0.1',
        'port' => 6379
    )

------------------------------


To enable the authentication cache you have to run an install script:

    php index.php 'oat\authKeyValue\action\ActivateKeyValueAuthentication' --persistence authKeyValue


Then the login will try to use this library.

