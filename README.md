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

To enable them, you need to add the AuthKeyValueAdapter to your **config/generis/auth.conf.php**:

    return array(
        0 => array(
            'driver' => 'oat\\authKeyValue\\AuthKeyValueAdapter',
        ),
        1 => array(
            'driver' => 'oat\\generis\\model\\user\\AuthAdapter'
        ),
    );

Then the login will try to use this library. 

Be sure you have enable  the persistence in **config/generis/persistences.conf.php** by adding:

    'authKeyValue' => array(
	    'driver' => 'phpredis',
            'host' => '127.0.0.1',
            'port' => 6379
	)

