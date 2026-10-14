<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) 2002 - 2008 Nick Jones
| http://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Title: CxM Radiostatus Panel for PHP-Fusion
| Filename: cxm_radiostatus_class.php
| Author: Alexaxander Schilinski
| Homepage: https://schilinskialexander.com
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
if (!defined('IN_FUSION')) { die('Access Denied'); }

class LautFmAPI {
    private $base_url;
    private $station;
    public $error = '';

    public function __construct($station_slug, $api_base = 'https://api.laut.fm') {
        $this->station  = $station_slug;
        $this->base_url = rtrim($api_base, '/');
    }

    private function request($path) {
        $url = $this->base_url.$path;
        $ctx = stream_context_create(array(
            'http' => array(
                'timeout'        => 5,
                'ignore_errors'  => true,
                'user_agent'     => 'cxm_radiostatus_panel/1.0',
            )
        ));
        $raw = @file_get_contents($url, false, $ctx);
        if ($raw === false) {
            $this->error = 'HTTP-Fehler beim Abruf von '.$path;
            return false;
        }
        if (trim($raw) === '') {
            return null;
        }
        $data = json_decode($raw, true);
        if (!is_array($data) && !is_numeric($data) && !is_bool($data)) {
            $this->error = 'Ungültige JSON-Antwort von '.$path;
            return false;
        }
        return $data;
    }

    public function getStation() {
        return $this->request('/station/'.urlencode($this->station));
    }

    public function getCurrentSong() {
        return $this->request('/station/'.urlencode($this->station).'/current_song');
    }

    public function getLastSongs() {
        $data = $this->request('/station/'.urlencode($this->station).'/last_songs');
        return is_array($data) ? $data : array();
    }

    public function getListeners() {
        $data = $this->request('/station/'.urlencode($this->station).'/listeners');
        return is_numeric($data) ? (int)$data : (is_array($data) ? 0 : 0);
    }

    public function getNextArtists() {
        $data = $this->request('/station/'.urlencode($this->station).'/next_artists');
        return is_array($data) ? $data : array();
    }

    public function isLive() {
        $data = $this->request('/stations/live');
        if (!is_array($data)) {
            return false;
        }
        foreach ($data as $station) {
            if (isset($station['name']) && $station['name'] === $this->station) {
                return true;
            }
        }
        return false;
    }

    protected function sanitizeForCache($str) {
        $str = (string)$str;
        $str = str_replace("\\", "\\\\", $str);
        $str = str_replace("'", "\\'", $str);
        return $str;
    }

    protected function toPublicPath($path) {
        global $settings;

        $path = (string)$path;
        if ($path === '') {
            return '';
        }

        if (preg_match('#^https?://#i', $path) || strpos($path, '//') === 0) {
            return $path;
        }

        $normalized = ltrim(preg_replace('#^(?:\.\./)+#', '', $path), '/');
        if (!empty($settings['siteurl'])) {
            return rtrim($settings['siteurl'], '/').'/'.$normalized;
        }

        return $path;
    }

    protected function getDefaultDisplayImage() {
        return $this->toPublicPath(
            (defined('CXM_RADIOSTATUS') ? CXM_RADIOSTATUS : INFUSIONS.'cxm_radiostatus_panel/').'images/autodj.gif'
        );
    }

    protected function loadExistingCache($rs_id, $cache_id) {
        $cache = array();
        $cache_file = (defined('CXM_RADIOSTATUS') ? CXM_RADIOSTATUS : INFUSIONS.'cxm_radiostatus_panel/')
            .'cache/stream_'.(int)$rs_id.'_'.preg_replace('/[^a-f0-9]/i', '', (string)$cache_id).'.php';

        if (file_exists($cache_file)) {
            include $cache_file;
        }

        return is_array($cache) ? $cache : array();
    }

    private function extractModeratorName($title) {
        $title = trim(htmlspecialchars_decode((string)$title, ENT_QUOTES));
        if ($title === '') {
            return '';
        }

        if (preg_match('/\|\s*([^\s|]+)/u', $title, $matches)) {
            return trim($matches[1]);
        }

        return '';
    }

    protected function resolveModerator($moderator_name) {
        $fallback = array(
            'name'  => $moderator_name !== '' ? $moderator_name : 'AutoDJ',
            'image' => $this->getDefaultDisplayImage(),
        );

        $moderator_name = trim($moderator_name);
        if ($moderator_name === '') {
            return $fallback;
        }

        $result = dbquery(
            "SELECT user_name, user_avatar FROM ".DB_USERS." WHERE user_name='".stripinput($moderator_name)."' LIMIT 1"
        );
        if ($result && dbrows($result)) {
            $user = dbarray($result);
            $fallback['name'] = $user['user_name'];
            if (!empty($user['user_avatar']) && file_exists(IMAGES.'avatars/'.$user['user_avatar'])) {
                $fallback['image'] = $this->toPublicPath(IMAGES.'avatars/'.$user['user_avatar']);
            }
        }

        return $fallback;
    }

    private function normalizeSongValue($value) {
        return trim(htmlspecialchars_decode((string)$value, ENT_QUOTES));
    }

    private function extractSongFromApi($current_song) {
        $result = array(
            'song' => '',
            'title' => '',
            'artist_name' => '',
            'artist_thumb' => '',
            'album' => '',
            'genre' => '',
            'ends_at' => 0,
            'starts_at' => 0,
        );

        if (!is_array($current_song)) {
            return $result;
        }

        $result['title'] = isset($current_song['title'])
            ? $this->normalizeSongValue($current_song['title'])
            : '';

        if (!empty($current_song['artist']) && is_array($current_song['artist'])) {
            $result['artist_name'] = isset($current_song['artist']['name'])
                ? $this->normalizeSongValue($current_song['artist']['name'])
                : '';

            if (!empty($current_song['artist']['image']) && is_string($current_song['artist']['image'])) {
                $result['artist_thumb'] = trim($current_song['artist']['image']);
            } elseif (!empty($current_song['artist']['thumb']) && is_string($current_song['artist']['thumb'])) {
                $result['artist_thumb'] = trim($current_song['artist']['thumb']);
            }
        }

        if (!empty($current_song['album']) && is_array($current_song['album']) && isset($current_song['album']['title'])) {
            $result['album'] = $this->normalizeSongValue($current_song['album']['title']);
        }

        if (!empty($current_song['genre'])) {
            $result['genre'] = $this->normalizeSongValue($current_song['genre']);
        }

        if (!empty($current_song['started_at'])) {
            $starts_at = strtotime($current_song['started_at']);
            if ($starts_at !== false) {
                $result['starts_at'] = (int)$starts_at;
            }
        }

        if (!empty($current_song['ends_at'])) {
            $ends_at = strtotime($current_song['ends_at']);
            if ($ends_at !== false) {
                $result['ends_at'] = (int)$ends_at;
            }
        }

        if ($result['artist_name'] !== '' && $result['title'] !== '') {
            $result['song'] = $result['artist_name'].' - '.$result['title'];
        } elseif ($result['title'] !== '') {
            $result['song'] = $result['title'];
        } else {
            $result['song'] = $result['artist_name'];
        }

        return $result;
    }

    private function getStreamTitle($stream_url) {
        $parts = @parse_url($stream_url);
        if (!is_array($parts) || empty($parts['host'])) {
            return '';
        }

        $scheme = isset($parts['scheme']) ? strtolower($parts['scheme']) : 'http';
        $host = $parts['host'];
        $port = isset($parts['port']) ? (int)$parts['port'] : ($scheme === 'https' ? 443 : 80);
        $path = isset($parts['path']) ? $parts['path'] : '/';
        if (!empty($parts['query'])) {
            $path .= '?'.$parts['query'];
        }

        $transport = $scheme === 'https' ? 'ssl://' : '';
        $errno = 0;
        $errstr = '';
        $fp = @fsockopen($transport.$host, $port, $errno, $errstr, 8);
        if (!$fp) {
            return '';
        }

        stream_set_timeout($fp, 8);
        $request = "GET ".$path." HTTP/1.0\r\n"
            ."Host: ".$host."\r\n"
            ."User-Agent: cxm_radiostatus_panel/1.0\r\n"
            ."Icy-MetaData: 1\r\n"
            ."Connection: close\r\n\r\n";
        fwrite($fp, $request);

        $metaint = 0;
        while (!feof($fp)) {
            $line = fgets($fp, 4096);
            if ($line === false || trim($line) === '') {
                break;
            }
            if (stripos($line, 'icy-metaint:') === 0) {
                $metaint = (int)trim(substr($line, 12));
            }
        }

        if ($metaint <= 0) {
            fclose($fp);
            return '';
        }

        $remaining = $metaint;
        while ($remaining > 0 && !feof($fp)) {
            $chunk = fread($fp, min(4096, $remaining));
            if ($chunk === false || $chunk === '') {
                break;
            }
            $remaining -= strlen($chunk);
        }

        $lengthByte = fread($fp, 1);
        if ($lengthByte === false || $lengthByte === '') {
            fclose($fp);
            return '';
        }

        $metadataLength = ord($lengthByte) * 16;
        if ($metadataLength <= 0) {
            fclose($fp);
            return '';
        }

        $metadata = '';
        while (strlen($metadata) < $metadataLength && !feof($fp)) {
            $chunk = fread($fp, $metadataLength - strlen($metadata));
            if ($chunk === false || $chunk === '') {
                break;
            }
            $metadata .= $chunk;
        }
        fclose($fp);

        if (preg_match("/StreamTitle='([^']*)';/i", $metadata, $matches)) {
            return trim($matches[1]);
        }

        return '';
    }

    public function makeCache($rs_id, $cache_id, $rs_stream_url_override = '') {
        $station_data = $this->getStation();
        $previous_cache = $this->loadExistingCache($rs_id, $cache_id);

        $res  = '<?php'."\n";
        $res .= 'if (!defined(\'IN_FUSION\')) { die(\'Access Denied\'); }'."\n";
        $res .= '$cache = array();'."\n";

        if ($station_data === false || !is_array($station_data)) {
            // Offline
            $res .= '$cache[\'status\'] = 0;'."\n";
            $res .= '$cache[\'error\'] = \''.$this->sanitizeForCache($this->error).'\';'."\n";
            $res .= '$cache[\'song\'] = \'\';'."\n";
            $res .= '$cache[\'title\'] = \'\';'."\n";
            $res .= '$cache[\'artist_name\'] = \'\';'."\n";
            $res .= '$cache[\'artist_thumb\'] = \'\';'."\n";
            $res .= '$cache[\'album\'] = \'\';'."\n";
            $res .= '$cache[\'ends_at\'] = 0;'."\n";
            $res .= '$cache[\'starts_at\'] = 0;'."\n";
            $res .= '$cache[\'listner\'] = 0;'."\n";
            $res .= '$cache[\'genre\'] = \'\';'."\n";
            $res .= '$cache[\'is_live\'] = 0;'."\n";
            $res .= '$cache[\'has_moderator\'] = 0;'."\n";
            $res .= '$cache[\'title_source\'] = \'fallback\';'."\n";
            $res .= '$cache[\'playlist\'] = \'\';'."\n";
            $res .= '$cache[\'station_img\'] = \''.$this->sanitizeForCache($this->getDefaultDisplayImage()).'\';'."\n";
            $res .= '$cache[\'onair_name\'] = \'AutoDJ\';'."\n";
            $res .= '$cache[\'stream_url\'] = \'\';'."\n";
            $res .= '$cache[\'history\'] = array();'."\n";
            $res .= '$cache[\'next_artists\'] = array();'."\n";
            $res .= '?>';
            return $res;
        }

        $title = '';
        $artist_name = '';
        $artist_thumb = '';
        $album = '';
        $genre = '';
        $song = '';
        $ends_at = 0;
        $starts_at = 0;
        $title_source = 'fallback';

        // Listener-Anzahl (eigener Request)
        $listeners_raw = $this->request('/station/'.urlencode($this->station).'/listeners');
        $listner = is_numeric($listeners_raw) ? (int)$listeners_raw : 0;

        // Station-Daten
        $stream_url  = '';
        $station_img = $this->getDefaultDisplayImage();
        $playlist    = '';
        if (is_array($station_data)) {
            if (!empty($rs_stream_url_override)) {
                $stream_url = $rs_stream_url_override;
            } elseif (!empty($station_data['stream_url'])) {
                $stream_url = $station_data['stream_url'];
            }
            if (isset($station_data['current_playlist']) && isset($station_data['current_playlist']['name'])) {
                $playlist = $station_data['current_playlist']['name'];
            }
            // Genre aus Station wenn nicht im Song
            if ($genre === '' && !empty($station_data['genres']) && is_array($station_data['genres'])) {
                $genre = implode(', ', $station_data['genres']);
            }
        }

        $current_song = $this->getCurrentSong();
        $api_song = $this->extractSongFromApi($current_song);
        if ($api_song['song'] !== '') {
            $song = $api_song['song'];
            $title = $api_song['title'];
            $artist_name = $api_song['artist_name'];
            $artist_thumb = $api_song['artist_thumb'];
            $album = $api_song['album'];
            if ($api_song['genre'] !== '') {
                $genre = $api_song['genre'];
            }
            if ($api_song['ends_at'] > 0) {
                $ends_at = $api_song['ends_at'];
            }
            $starts_at = $api_song['starts_at'];
            $title_source = 'api';
        } else {
            $stream_title = $stream_url !== '' ? $this->getStreamTitle($stream_url) : '';
            if ($stream_title !== '') {
            $song = $stream_title;
            $artist_thumb = '';
            $album = '';
            $title_source = 'stream';

            if (strpos($stream_title, ' - ') !== false) {
                list($meta_artist, $meta_title) = explode(' - ', $stream_title, 2);
                $artist_name = trim($meta_artist);
                $title = trim($meta_title);
            } else {
                $artist_name = '';
                $title = $stream_title;
            }
            } elseif (!empty($previous_cache['song']) && !empty($previous_cache['title_source'])
                && in_array($previous_cache['title_source'], array('api', 'stream', 'cache'), true)) {
                $song = isset($previous_cache['song']) ? (string)$previous_cache['song'] : '';
                $title = isset($previous_cache['title']) ? (string)$previous_cache['title'] : '';
                $artist_name = isset($previous_cache['artist_name']) ? (string)$previous_cache['artist_name'] : '';
                $artist_thumb = isset($previous_cache['artist_thumb']) ? (string)$previous_cache['artist_thumb'] : '';
                $album = isset($previous_cache['album']) ? (string)$previous_cache['album'] : '';
                $ends_at = isset($previous_cache['ends_at']) ? (int)$previous_cache['ends_at'] : 0;
                $starts_at = isset($previous_cache['starts_at']) ? (int)$previous_cache['starts_at'] : 0;
                $title_source = 'cache';
            }
        }

        if ($song === '') {
            if ($playlist !== '') {
                $song = $playlist;
            } else {
                $song = 'Keine aktuellen Titeldaten';
            }
        }

        // Letzte Songs (History)
        if (!isset($last_songs) || !is_array($last_songs)) {
            $last_songs = $this->getLastSongs();
        }
        $history     = array();
        foreach (array_slice($last_songs, 0, 10) as $ls) {
            $h_title  = isset($ls['title'])  ? $ls['title']  : '';
            $h_artist = (isset($ls['artist']) && isset($ls['artist']['name'])) ? $ls['artist']['name'] : '';
            $h_start  = isset($ls['started_at']) ? $ls['started_at'] : '';
            $history[] = array(
                'title'      => $h_title,
                'artist'     => $h_artist,
                'started_at' => $h_start,
            );
        }

        // Nächste Künstler
        $next_raw    = $this->getNextArtists();
        $next_artists = array();
        foreach (array_slice($next_raw, 0, 3) as $na) {
            $next_artists[] = array(
                'name'  => isset($na['name'])  ? $na['name']  : '',
                'thumb' => isset($na['thumb']) ? $na['thumb'] : '',
            );
        }

        // Live-Status
        $is_live = (!empty($station_data['active']) || $this->isLive()) ? 1 : 0;
        $onair_name = 'AutoDJ';
        $has_moderator = 0;
        if ($is_live) {
            $moderator_name = $this->extractModeratorName($title !== '' ? $title : $song);
            $moderator = $this->resolveModerator($moderator_name);
            $onair_name = $moderator['name'];
            $station_img = $moderator['image'];
            $has_moderator = ($moderator_name !== '') ? 1 : 0;
        }

        // Cache-Code zusammenbauen
        $res .= '$cache[\'status\'] = '.($is_live ? 1 : 0).';'."\n";
        $res .= '$cache[\'error\'] = \'\';'."\n";
        $res .= '$cache[\'song\'] = \''.$this->sanitizeForCache($song).'\';'."\n";
        $res .= '$cache[\'title\'] = \''.$this->sanitizeForCache($title).'\';'."\n";
        $res .= '$cache[\'artist_name\'] = \''.$this->sanitizeForCache($artist_name).'\';'."\n";
        $res .= '$cache[\'artist_thumb\'] = \''.$this->sanitizeForCache($artist_thumb).'\';'."\n";
        $res .= '$cache[\'album\'] = \''.$this->sanitizeForCache($album).'\';'."\n";
        $res .= '$cache[\'ends_at\'] = '.(int)$ends_at.';'."\n";
        $res .= '$cache[\'starts_at\'] = '.(int)$starts_at.';'."\n";
        $res .= '$cache[\'listner\'] = '.(int)$listner.';'."\n";
        $res .= '$cache[\'genre\'] = \''.$this->sanitizeForCache($genre).'\';'."\n";
        $res .= '$cache[\'is_live\'] = '.(int)$is_live.';'."\n";
        $res .= '$cache[\'has_moderator\'] = '.(int)$has_moderator.';'."\n";
        $res .= '$cache[\'title_source\'] = \''.$this->sanitizeForCache($title_source).'\';'."\n";
        $res .= '$cache[\'playlist\'] = \''.$this->sanitizeForCache($playlist).'\';'."\n";
        $res .= '$cache[\'station_img\'] = \''.$this->sanitizeForCache($station_img).'\';'."\n";
        $res .= '$cache[\'onair_name\'] = \''.$this->sanitizeForCache($onair_name).'\';'."\n";
        $res .= '$cache[\'stream_url\'] = \''.$this->sanitizeForCache($stream_url).'\';'."\n";

        // History-Array
        $res .= '$cache[\'history\'] = array();'."\n";
        foreach ($history as $h) {
            $res .= '$cache[\'history\'][] = array('
                .'\'title\' => \''.$this->sanitizeForCache($h['title']).'\', '
                .'\'artist\' => \''.$this->sanitizeForCache($h['artist']).'\', '
                .'\'started_at\' => \''.$this->sanitizeForCache($h['started_at']).'\''.
                ');'."\n";
        }

        // Next-Artists-Array
        $res .= '$cache[\'next_artists\'] = array();'."\n";
        foreach ($next_artists as $na) {
            $res .= '$cache[\'next_artists\'][] = array('
                .'\'name\' => \''.$this->sanitizeForCache($na['name']).'\', '
                .'\'thumb\' => \''.$this->sanitizeForCache($na['thumb']).'\''.
                ');'."\n";
        }

        $res .= '?>';
        return $res;
    }
}

/**
 * LexyCast (streaming.center-Panel), öffentliche API v2 ohne Login.
 * rs_station enthält die Panel-Adresse mit Port, z.B. https://c02.lexycast.de:4020
 * Optional mit Server-Nummer: https://c02.lexycast.de:4020#2 (sonst der Standard-Server des Panels).
 */
class LexyCastAPI extends LautFmAPI {
    const HISTORY_LIMIT = 11;

    private $panel_url;
    private $server_id;

    public function __construct($station) {
        list($this->panel_url, $this->server_id) = self::parseStation($station);
    }

    /**
     * Panel-Adresse normalisieren: ohne "/" am Ende, ohne "/api/v2", Schema https wenn keins angegeben
     * @return array array(panel_url, server_id) - server_id 0 = Standard-Server
     */
    public static function parseStation($value) {
        $value = trim((string)$value);
        $server_id = 0;
        if (preg_match('/#(\d+)$/', $value, $m)) {
            $server_id = (int)$m[1];
            $value = substr($value, 0, -strlen($m[0]));
        }
        $value = rtrim($value, '/');
        $value = preg_replace('#/api(/v\d+)?$#i', '', $value);
        if ($value !== '' && !preg_match('#^https?://#i', $value)) {
            $value = 'https://'.$value;
        }
        $parts = @parse_url($value);
        if (!is_array($parts) || empty($parts['host'])) {
            return array('', 0);
        }
        return array($value, $server_id);
    }

    /**
     * Wert für rs_station beim Speichern im Admin
     */
    public static function normalizeStation($value) {
        list($url, $server_id) = self::parseStation($value);
        return $url !== '' ? $url.($server_id > 0 ? '#'.$server_id : '') : '';
    }

    private function lcRequest($path, $query = array()) {
        $url = $this->panel_url.'/api/v2/'.$path.(!empty($query) ? '?'.http_build_query($query) : '');
        $ctx = stream_context_create(array(
            'http' => array(
                'timeout'       => 5,
                'ignore_errors' => true,
                'user_agent'    => 'cxm_radiostatus_panel/1.0',
                'header'        => "Accept: application/json\r\n",
            )
        ));
        $raw = @file_get_contents($url, false, $ctx);
        $headers = function_exists('http_get_last_response_headers')
            ? http_get_last_response_headers()
            : (isset($http_response_header) ? $http_response_header : array());
        $code = (is_array($headers) && isset($headers[0]) && preg_match('#\s(\d{3})\s?#', $headers[0], $m)) ? (int)$m[1] : 0;
        if ($raw === false || $code < 200 || $code >= 300) {
            $this->error = 'HTTP-Fehler '.$code.' beim Abruf von '.$path;
            return false;
        }
        $data = json_decode($raw, true);
        if (!is_array($data)) {
            $this->error = 'Ungültige JSON-Antwort von '.$path;
            return false;
        }
        return $data;
    }

    private function findServerId($previous_cache) {
        if ($this->server_id > 0) {
            return $this->server_id;
        }
        if (!empty($previous_cache['lc_server']) && isset($previous_cache['lc_panel']) && $previous_cache['lc_panel'] === $this->panel_url) {
            return (int)$previous_cache['lc_server'];
        }
        $servers = $this->lcRequest('servers/');
        if (!is_array($servers)) {
            return 0;
        }
        $first = 0;
        foreach ($servers as $server) {
            if (!is_array($server) || empty($server['id'])) {
                continue;
            }
            if (!empty($server['default'])) {
                return (int)$server['id'];
            }
            if ($first === 0) {
                $first = (int)$server['id'];
            }
        }
        return $first;
    }

    private static function text($item, $key) {
        return (isset($item[$key]) && is_scalar($item[$key])) ? trim((string)$item[$key]) : '';
    }

    /**
     * Moderator auf Sendung (nicht der AutoDJ mit cat "auto"), sonst ''
     */
    private static function liveDj($dj_name, $djs) {
        $dj_name = trim((string)$dj_name);
        $match = '';
        foreach ((array)$djs as $dj) {
            if (!is_array($dj) || (isset($dj['cat']) && $dj['cat'] === 'auto') || empty($dj['name'])) {
                continue;
            }
            $name = trim((string)$dj['name']);
            if (!empty($dj['on_air'])) {
                return $dj;
            }
            if ($match === '' && $dj_name !== '' && $name === $dj_name) {
                $match = $dj;
            }
        }
        return $match;
    }

    private function renderCache($cache) {
        return '<?php'."\n"
            .'if (!defined(\'IN_FUSION\')) { die(\'Access Denied\'); }'."\n"
            .'$cache = '.var_export($cache, true).';'."\n"
            .'?>';
    }

    public function makeCache($rs_id, $cache_id, $rs_stream_url_override = '') {
        $previous_cache = $this->loadExistingCache($rs_id, $cache_id);
        $cache = array(
            'status'        => 0,
            'error'         => '',
            'song'          => '',
            'title'         => '',
            'artist_name'   => '',
            'artist_thumb'  => '',
            'album'         => '',
            'ends_at'       => 0,
            'starts_at'     => 0,
            'listner'       => 0,
            'genre'         => '',
            'is_live'       => 0,
            'has_moderator' => 0,
            'title_source'  => 'fallback',
            'playlist'      => '',
            'station_img'   => $this->getDefaultDisplayImage(),
            'onair_name'    => 'AutoDJ',
            'stream_url'    => '',
            'history'       => array(),
            'next_artists'  => array(),
            'lc_panel'      => $this->panel_url,
            'lc_server'     => 0,
        );

        if ($this->panel_url === '') {
            $cache['error'] = 'Ungültige LexyCast-Adresse';
            return $this->renderCache($cache);
        }

        $server = $this->findServerId($previous_cache);
        $channels = $server > 0 ? $this->lcRequest('channels/', array('server' => $server)) : false;
        if (!is_array($channels)) {
            if ($cache['error'] === '') {
                $cache['error'] = $this->error !== '' ? $this->error : 'Kein Server im LexyCast-Panel gefunden';
            }
            return $this->renderCache($cache);
        }
        $cache['lc_server'] = $server;

        // Kanäle: Hörer zusammenzählen, Stream-Adresse des ersten laufenden Kanals (https bevorzugt)
        $online = false;
        $stream_url = '';
        foreach ($channels as $channel) {
            if (!is_array($channel) || (isset($channel['active']) && !$channel['active'])) {
                continue;
            }
            $is_online = isset($channel['state']) && (int)$channel['state'] === 2;
            if (!$is_online) {
                continue;
            }
            $online = true;
            $cache['listner'] += isset($channel['listeners_current']) ? (int)$channel['listeners_current'] : 0;
            if ($stream_url === '') {
                $stream_url = self::text($channel, 'secure_stream_url') !== ''
                    ? self::text($channel, 'secure_stream_url')
                    : self::text($channel, 'stream_url');
            }
        }
        $cache['stream_url'] = $rs_stream_url_override !== '' ? $rs_stream_url_override : $stream_url;

        if (!$online) {
            $cache['error'] = 'Kein Kanal auf Sendung';
            return $this->renderCache($cache);
        }
        $cache['status'] = 1;

        // Titel und Verlauf (neuester zuerst)
        $history = $this->lcRequest('history/', array('limit' => self::HISTORY_LIMIT, 'offset' => 0, 'server' => $server));
        $items = (is_array($history) && isset($history['results']) && is_array($history['results'])) ? $history['results'] : array();
        usort($items, function ($a, $b) {
            return (isset($b['ts']) ? $b['ts'] : 0) - (isset($a['ts']) ? $a['ts'] : 0);
        });
        $current = isset($items[0]) && is_array($items[0]) ? $items[0] : null;
        if ($current !== null) {
            $cache['title'] = self::text($current, 'title');
            $cache['artist_name'] = self::text($current, 'author');
            $cache['album'] = self::text($current, 'album');
            $cache['genre'] = self::text($current, 'genre');
            $cache['playlist'] = self::text($current, 'playlist_title');
            $cache['artist_thumb'] = self::text($current, 'img_medium_url') !== ''
                ? self::text($current, 'img_medium_url')
                : self::text($current, 'img_url');
            if ($cache['artist_name'] !== '' && $cache['title'] !== '') {
                $cache['song'] = $cache['artist_name'].' - '.$cache['title'];
            } else {
                $cache['song'] = $cache['title'] !== '' ? $cache['title'] : self::text($current, 'metadata');
            }
            if (!empty($current['ts']) && !empty($current['length'])) {
                $cache['starts_at'] = (int)floor($current['ts'] / 1000);
                $cache['ends_at'] = $cache['starts_at'] + (int)round($current['length'] / 1000);
            }
            $cache['title_source'] = 'api';
        } elseif (!empty($previous_cache['song']) && isset($previous_cache['title_source']) && $previous_cache['title_source'] !== 'fallback') {
            foreach (array('song', 'title', 'artist_name', 'artist_thumb', 'album', 'starts_at', 'ends_at') as $key) {
                if (isset($previous_cache[$key])) {
                    $cache[$key] = $previous_cache[$key];
                }
            }
            $cache['title_source'] = 'cache';
        }
        if ($cache['song'] === '') {
            $cache['song'] = 'Keine aktuellen Titeldaten';
        }

        foreach (array_slice($items, 1, 10) as $item) {
            if (!is_array($item)) {
                continue;
            }
            $cache['history'][] = array(
                'title'      => self::text($item, 'title') !== '' ? self::text($item, 'title') : self::text($item, 'metadata'),
                'artist'     => self::text($item, 'author'),
                'started_at' => !empty($item['ts']) ? date('c', (int)floor($item['ts'] / 1000)) : '',
            );
        }

        // Live-Moderator (nicht AutoDJ)
        $djs = $this->lcRequest('djs/', array('server' => $server));
        $dj = self::liveDj($current !== null ? self::text($current, 'dj_name') : '', is_array($djs) ? $djs : array());
        if (is_array($dj)) {
            $cache['is_live'] = 1;
            $cache['has_moderator'] = 1;
            $cache['onair_name'] = trim((string)$dj['name']);
            $image = !empty($dj['image_medium']) ? (string)$dj['image_medium'] : (!empty($dj['image_thumbnail']) ? (string)$dj['image_thumbnail'] : '');
            if ($image === '') {
                $moderator = $this->resolveModerator($cache['onair_name']);
                $image = $moderator['image'];
            }
            $cache['station_img'] = $image;
        }

        return $this->renderCache($cache);
    }
}

/**
 * API-Objekt passend zur Quelle des Streams (laut.fm oder LexyCast)
 */
function cxm_rs_create_api($stream) {
    global $cxm_radiostatus_settings;

    if (isset($stream['rs_source']) && $stream['rs_source'] === 'lexycast') {
        return new LexyCastAPI($stream['rs_station']);
    }
    return new LautFmAPI($stream['rs_station'], $cxm_radiostatus_settings['api_base']);
}
?>
