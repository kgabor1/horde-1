<?php
/**
 * Copyright 2014 Horde LLC (http://www.horde.org/)
 *
 * See the enclosed file COPYING for license information (LGPL). If you
 * did not receive this file, see http://www.horde.org/licenses/lgpl21.
 *
 * @category  Horde
 * @copyright 2014 Horde LLC
 * @license   http://www.horde.org/licenses/lgpl21 LGPL 2.1
 * @package   Core
 */

/**
 * Filesystem backend for the javascript caching library.
 *
 * @author    Michael Slusarz <slusarz@horde.org>
 * @category  Horde
 * @copyright 2014 Horde LLC
 * @license   http://www.horde.org/licenses/lgpl21 LGPL 2.1
 * @package   Core
 * @since     2.12.0
 */
class Horde_Script_Cache_File extends Horde_Script_Cache
{
    /**
     * Cached javascript minification object.
     *
     * @var Horde_Script_Compress
     */
    protected $_jsmin;

    /**
     */
    public function gc()
    {
        global $registry;

        if (empty($this->_params['lifetime'])) {
            return;
        }

        /* Keep a file in the static directory that prevents us from doing
         * garbage collection more than once a day. */
        $curr_time = time();
        $static_dir = $registry->get('fileroot', 'horde') . '/static';
        $static_stat = $static_dir . '/gc_cachejs';

        $next_run = !is_readable($static_stat) ?: @file_get_contents($static_stat);

        if (!$next_run || ($curr_time > $next_run)) {
            file_put_contents($static_stat, $curr_time + 86400);
        }

        if (!$next_run || ($curr_time < $next_run)) {
            return;
        }

        $curr_time -= $this->_params['lifetime'];
        $removed = 0;

        foreach (glob($static_dir . '/*.js') as $file) {
            if ($curr_time > filemtime($file)) {
                @unlink($file);
                ++$removed;
            }
        }

        Horde::log(
            sprintf('Cleaned out static JS files (removed %d file(s)).', $removed),
            'DEBUG'
        );
    }

    /**
     */
    protected function _process($scripts)
    {
        global $registry;

        if (empty($scripts)) {
            return array();
        }

        $tmp = array();
        foreach ($scripts as $val) {
            $tmp[] = $val->modified;
        }
        $mtime = max($tmp);

        $hashes = array_keys($scripts);
        sort($hashes);

        $sig = hash('sha1', serialize($hashes) . $mtime);

        $js_filename = '/static/' . $sig . '.js';
        $js_path = $registry->get('fileroot', 'horde') . $js_filename;
        $js_url = $registry->get('webroot', 'horde') . $js_filename;

        $out = array($js_url);

        if (file_exists($js_path)) {
            return $out;
        }

        /* Check for existing process creating compressed file. Maximum 15
         * seconds wait time. */
        for ($i = 0; $i < 15; ++$i) {
            if (file_exists($js_path . '.lock')) {
                sleep(1);
            } elseif ($i) {
                return $out;
            } else {
                touch($js_path . '.lock');
                break;
            }
        }

        if (!isset($this->_compress)) {
            $this->_compress = new Horde_Script_Compress(
                $this->_params['compress'],
                $this->_params
            );
        }

        $sourcemap_url = $js_url . '.map';
        $jsmin = $this->_compress->getMinifier($scripts, $sourcemap_url);

        if (!file_put_contents($js_path, $jsmin->minify())) {
            Horde::log('Could not write cached JS file to disk.', Horde_Log::EMERG);
        } elseif ($this->_compress->sourcemap_support) {
            file_put_contents($js_path . '.map', $jsmin->sourcemap());
        }
        unlink($js_path . '.lock');

        return $out;
    }

}
