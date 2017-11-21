<?php

/*
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
*/

class LogglyException extends Exception {}

class Loggly {
    public $domain = 'loggly.com';
    public $proxy = 'logs.loggly.com';
    public $subdomain = '';
    public $username = '';
    public $password  = '';
    public $apiToken  = '';
    public $inputs = array();

    public function __construct() {}

    public function makeRequest($path, $params = null, $method = 'GET') {
        # maintain compatibility with Python Hoover library
        if ($path[0] !== '/') {
            $path = '/' . $path;
        }

        $method = strtoupper($method);
        $url = sprintf('https://%s.%s%s', $this->subdomain, $this->domain, $path);
        $curl = curl_init();

        if ($method === 'POST') {
            curl_setopt($curl, CURLOPT_POST, 1);
        }

        if ($method === 'PUT' || $method === 'DELETE') {
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
        }

        # set HTTP headers
        $headers = [];
        if($this->username || $this->password){
            curl_setopt($curl, CURLOPT_USERPWD, $this->username . ':' . $this->password);
        }
        if($this->apiToken){
            $headers[] = 'Authorization: bearer ' . $this->apiToken;
        }
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
    
        # handle URL params
        if ($params) {
            $segments = array();
            foreach ($params as $k => $v) {
                $segments[] .= $k . '=' . urlencode($v);
            }

            $qs = join($segments, '&');

            if ($method === 'POST' || $method === 'PUT') {
                curl_setopt($curl, CURLOPT_POSTFIELDS, $qs);
            } else {
                $url .= '?' . $qs;
            }

        }

        curl_setopt($curl, CURLOPT_URL, $url);

        # satisfy content length header requirement for PUT
        if ($method === 'PUT') {
            $headers[] = 'Content-Length: ' . strlen($qs);
        }

        if(!empty($headers)){
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        }

        $result = curl_exec($curl);

        if (!$result) {
          throw new LogglyException(curl_error($curl));
        }

        $json = json_decode($result, true);
        if (!$json) {
            # API is inconsistent
            $status = curl_getinfo($curl, CURLINFO_HTTP_CODE); 
            if ($status >= 200 && $status <= 299) {
                return null;
            }
            curl_close($curl);
            throw new LogglyException($result);
        }

        curl_close($curl);
        return $json;
    }

    public function getInputs() {
        return $this->makeRequest('/api/inputs');
    }

    public function getDevices() {
        return $this->makeRequest('/api/devices');
    }

    # input-related methods

    public function addDevice($inputId) {
        return $this->makeRequest('/api/inputs/' . $inputId . '/adddevice', null, 'POST');
    }

    public function removeDevice($inputId) {
        return $this->makeRequest('/api/inputs/' . $inputId . '/removedevice', null, 'POST');
    }

    public function enableDiscovery($inputId) {
        return $this->makeRequest('/api/inputs/' . $inputId . '/discover', null, 'POST');
    }

    public function disableDiscovery($inputId) {
        return $this->makeRequest('/api/inputs/' . $inputId . '/discover', null, 'DELETE');
    }

    # search-related methods

    public function search($q, $params = null) {
        $params['q'] = $q;
        return $this->makeRequest('/api/search', $params);
    }

    public function facet($q, $facet = 'date', $params = null) {
        $params['q'] = $q;
        return $this->makeRequest('/api/facets/' . $facet, $params);
    }

    public function getSavedSearches() {
        return $this->makeRequest('/api/savedsearches/');
    }

    # $params is an array with keys 'foo' and 'context'
    # context is a JSON blob, e.g.
    # $params = array('name' => 'foo',
    #                 'context' => '{"search_type":"search", "terms":"error AND 500", "from":"NOW-1HOUR", "until":"NOW", "inputs":["app","staging"]}');
    public function createSavedSearch($params) {
        return $this->makeRequest('/api/savedsearches', $params, 'POST');
    }

    # $params must contain a key 'id'
    public function updateSavedSearch($params) {
        return $this->makeRequest('/api/savedsearches', $params, 'PUT');
    }

    public function deleteSavedSearch($id) {
        return $this->makeRequest('/api/savedsearches/' . $id, null, 'DELETE');
    }

    public function runSavedSearch($id, $facet = false, $facetBy = 'date') {
        if (!$facet) {
            return $this->makeRequest('/api/savedsearches/' . $id . '/results');
        } else {
            return $this->makeRequest('/api/savedsearches/' . $id . '/facets/' . $facetBy);
        }
    }


    # account-related methods

    # always returns current Loggly account
    public function getCustomer() {
        return $this->makeRequest('/api/customers/');
    }

    # always returns current Loggly account
    public function getCustomerStats() {
        return $this->makeRequest('/api/customers/stats');
    }
}

?>
