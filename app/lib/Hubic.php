<?php

class Hubic {

    // http://docs.openstack.org/juno/config-reference/content/object-storage-tempurl.html
    public static function generateUrlForPath(Path $path) {
        $method = 'GET';
        $expires = strtotime('+1 hour');

        $pathPrefix = Config::get('hubic.path_prefix');
        $relativePath = $path->getRelative();

        $body = implode("\n", array($method, $expires, $pathPrefix.$relativePath));

        $key = Config::get('hubic.secret');
        $hash = hash_hmac('sha1', $body, $key);

        $params = array('temp_url_sig' => $hash, 'temp_url_expires' => $expires);
        $encodedPath = $path->getUrl();
        $url = 'https://'.Config::get('hubic.host').$pathPrefix.$encodedPath.'?'.http_build_query($params);

        return $url;
    }

}