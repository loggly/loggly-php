![Hoover's a Boss](http://loggly.com/assets/4f234edddabe9d5394006e85/promo_round.png) Loggly PHP SDK
==============
==============

This is a library that developers can use to interact with Loggly, without having to deal with the intricacies of cURL.

Input and device IDs can be found with getInputs() and getDevices(), respectively, or by navigating to the page in the Loggly UI and looking for the number in the URL.

Check out Loggly's [PHP logging documentation](https://www.loggly.com/docs/php-logs/) for more. 
<br>



Getting Started
===============

The first step is to instantiate a new instance of the Loggly class:


    $loggly = new Loggly();

    $loggly->subdomain = '<loggly subdomain>';

    $loggly->username = 'demo';

    $loggly->password = '42ftw';


Input and Device Methods
========================



###Retrieving inputs

    $result = $loggly->getInputs();


###Retrieving devices
    
    $result = $loggly->getDevices();
    
    print $result[0]['ip'];


###Authorizing a device
    
    $result = $loggly->addDevice('<device ID>');


###Unauthorizing a device

    $result = $loggly->removeDevice('<device ID>');


###Enabling discovery mode
    
    $result = $loggly->enableDiscovery('<input ID>');


###Disabling discovery mode

    $result = $loggly->disableDiscovery('<input ID>');


Search Methods
==============



###Searching
    
    $result = $loggly->search('unix', array('from' => 'NOW-3HOURS', 'until' => 'NOW-1HOUR'));


###Facet Searching
    
    $result = $loggly->facet('unix', 'ip', array('from' => 'NOW-3HOURS', 'until' => 'NOW-1HOUR'));


###Retrieving saved searches
    
    $result = $loggly->getSavedSearches();


###Creating a saved search
    
    $params = array('name' => 'foo', 
                'context' => '{"search_type":"search", "terms":"error AND 500", "from":"NOW-1HOUR", 
                "until":"NOW", "inputs":["app","staging"]}');
    
    $result = $loggly->createSavedSearch($params);


###Updating a saved search
    
    $params = array('id' => <saved search ID>,
                'name' => 'bar',
                'context' => '{"search_type":"search", "terms":"error AND 500",
                "from":"NOW-1HOUR", "until":"NOW", "inputs":["app","staging"]}');
    
    $result = $loggly->updateSavedSearch($params);


###Deleting a saved search
    
    $result = $loggly->deleteSavedSearch(<saved search ID>);


###Running a saved search
    
    $result = $loggly->runSavedSearch(<saved search ID>);


###Running a faceted saved search
    
    $result = $loggly->runSavedSearch(<saved search ID, true, $facetBy = 'ip');


Customer Methods
================

###Get customer information
    
    $result = $loggly->getCustomer();


###Get customer stats
    
    $result = $loggly->getCustomerStats();


License
=======

Copyright 2012 Loggly Inc.

Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
See the License for the specific language governing permissions and
limitations under the License.


Author
=====

[David Lanstein](https://github.com/lanstein)

Support
=======
Have questions?

Contact support@loggly.com (please include your subdomain)
