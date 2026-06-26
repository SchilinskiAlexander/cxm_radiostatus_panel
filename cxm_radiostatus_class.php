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

    private function sanitizeForCache($str) {
        $str = (string)$str;
        $str = str_replace("\\", "\\\\", $str);
        $str = str_replace("'", "\\'", $str);
        return $str;
    }

    private function toPublicPath($path) {
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

    private function getDefaultDisplayImage() {
        return $this->toPublicPath(
            (defined('CXM_RADIOSTATUS') ? CXM_RADIOSTATUS : INFUSIONS.'cxm_radiostatus_panel/').'images/autodj.gif'
        );
    }

    private function loadExistingCache($rs_id, $cache_id) {
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

    private function resolveModerator($moderator_name) {
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
?>
