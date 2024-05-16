<?php

namespace Goldfinch\VideoField\ORM\FieldType;

use Goldfinch\JSONEditor\ORM\FieldType\DBJSONText;
use Goldfinch\VideoField\Forms\VideoField;
use SilverStripe\ORM\FieldType\DBComposite;
use SilverStripe\ORM\FieldType\DBHTMLText;
use SilverStripe\View\ArrayData;

class DBVideo extends DBComposite
{
    /**
     * Vimeo API
     * https://help.vimeo.com/hc/en-us/articles/12426260232977-Player-parameters-overview
     *
     * YouTube API
     * https://developers.google.com/youtube/player_parameters
     */
    /**
     * @var string
     */
    protected $locale = null;

    protected $videoSize = null;

    protected $videoColor = null;

    protected $youtubeThumbs = [
        'default' => 'default',
        'media' => 'mqdefault',
        'high' => 'hqdefault',
        'standard' => 'sddefault',
        'max' => 'maxresdefault',
    ];

    protected $youtubeCasualThumbs = [
        'hq720',
        '0',
        '1',
        '2',
        '3',
        'sd1',
        'sd2',
        'sd3',
        'mq1',
        'mq2',
        'mq3',
        'hq1',
        'hq2',
        'hq3',
    ];

    /**
     * @var array<string,string>
     */
    private static $composite_db = [
        'Data' => DBJSONText::class,
    ];

    private static $casting = [
        'iframe' => 'HTMLFragment',
        'thumbnail' => 'HTMLFragment',
        'dumpAllThumbnails' => 'HTMLFragment',
    ];

    public function forTemplate()
    {
        return $this->url();
    }

    public function getVideoData()
    {
        if ($this->getData()) {
            $data = $this->getData();
        } else {
            // set empty data to escape errors
            $data = json_encode([
                'host' => '',
                'id' => '',
                'advanced_settings' => false,
            ]);
        }

        return json_decode($data, true);
    }

    public function url()
    {
        return $this->plainURL().$this->bundleParams();
    }

    public function plainUrl()
    {
        $data = $this->getVideoData();

        $str = '';

        if ($data['host'] == 'youtube') {
            $str = 'https://www.youtube.com/watch?v='.$data['id'];
        } elseif ($data['host'] == 'vimeo') {
            $str = 'https://vimeo.com/'.$data['id'];
        }

        return $str;
    }

    public function embedURL()
    {
        return $this->plainEmbedURL().$this->bundleParams(true);
    }

    public function plainEmbedURL()
    {
        $data = $this->getVideoData();

        $str = '';

        if ($data['host'] == 'youtube') {
            $str = 'https://www.youtube.com/embed/'.$data['id'];
        } elseif ($data['host'] == 'vimeo') {
            $str = 'https://player.vimeo.com/video/'.$data['id'];
        }

        return $str;
    }

    public function iframe($width = null, $height = null)
    {
        $data = $this->getVideoData();

        $str = '';

        if ($data['host'] == 'youtube') {
            if (! $width) {
                $width = 560;
            }
            if (! $height) {
                $height = 315;
            }
            $str =
                '<iframe src="'.
                $this->embedURL().
                '" width="'.
                $width.
                '" height="'.
                $height.
                '" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>';
        } elseif ($data['host'] == 'vimeo') {
            if (! $width) {
                $width = 640;
            }
            if (! $height) {
                $height = 360;
            }
            $str =
                '<iframe src="'.
                $this->embedURL().
                '" width="'.
                $width.
                '" height="'.
                $height.
                '" frameborder="0" allow="autoplay; fullscreen; picture-in-picture" allowfullscreen></iframe>';
        }

        return $str;
    }

    public function dumpAllThumbnails()
    {
        $data = $this->getVideoData();

        $str = '';

        if ($data['host'] == 'youtube') {
            foreach ($this->youtubeThumbs as $key => $thumb) {
                $str .=
                    '<div><strong>'.
                    $key.
                    '</strong><br><img src="'.
                    $this->thumbnailUrl($key).
                    '" alt="Thumbnail"></div>';
            }
            foreach ($this->youtubeCasualThumbs as $thumb) {
                $str .=
                    '<div><strong>'.
                    $thumb.
                    '</strong><br><img src="'.
                    $this->thumbnailUrl($thumb).
                    '" alt="Thumbnail"></div>';
            }
        } elseif ($data['host'] == 'vimeo') {
            $str = '';
        }

        return $str;
    }

    public function thumbnail($type = 'default')
    {
        $data = $this->getVideoData();

        $str = '';

        if ($data['host'] == 'youtube') {
            $str = $this->thumbnailUrl($type) ? '<img src="'.$this->thumbnailUrl($type).'" alt="Thumbnail">' : '';
        } elseif ($data['host'] == 'vimeo') {
            $str = $this->thumbnailUrl($type) ? '<img src="'.$this->thumbnailUrl($type).'" alt="Thumbnail">' : '';
        }

        return $str;
    }

    public function thumbnailUrl($type = 'default')
    {
        $data = $this->getVideoData();

        $str = '';

        // for ($x = 0; $x < sizeof($resolution); $x++) {
        //     $url = '//img.youtube.com/vi/' . $id . '/' . $resolution[$x] . '.jpg';
        //     if (get_headers($url)[0] == 'HTTP/1.0 200 OK') {
        //         break;
        //     }
        // }

        if ($data['host'] == 'youtube') {
            if (isset($this->youtubeThumbs[$type])) {
                $key = $this->youtubeThumbs[$type];
            } elseif (in_array($type, $this->youtubeCasualThumbs)) {
                $key = $type;
            }

            if (isset($key)) {
                $str = 'https://img.youtube.com/vi/'.$data['id'].'/'.$key.'.jpg';
            }
        } elseif ($data['host'] == 'vimeo') {
            $str = '';
            $hostdata = $this->hostData();

            if ($hostdata) {
                $hostdata = $this->hostData()->toMap();

                if (count($hostdata) && isset($hostdata['thumbnail_url'])) {
                    $str = $hostdata['thumbnail_url'];
                }
            }
        }

        return $str;
    }

    public function bundleParams($embed = false)
    {
        $data = $this->getVideoData();

        if ($data['advanced_settings']) {
            $params = [];

            if ($data['host'] == 'youtube') {
                if ($embed) {
                    // embed links

                    if ($this->getSetting('autoplay')) {
                        $params['autoplay'] = $this->getSetting('autoplay');
                    }

                    if (! $this->getSetting('rel')) {
                        $params['rel'] = $this->getSetting('rel');
                    }

                    if ($this->getSetting('loop')) {
                        $params['loop'] = $this->getSetting('loop');
                    }

                    if (! $this->getSetting('controls')) {
                        $params['controls'] = $this->getSetting('controls');
                    }

                    if ($this->getSetting('fs')) {
                        $params['fs'] = ! $this->getSetting('fs');
                    }

                    if ($start = $this->getSetting('start')) {
                        $params['start'] = $start;
                    }

                    if ($end = $this->getSetting('end')) {
                        $params['end'] = $end;
                    }

                    if ($cc_load_policy = $this->getSetting('cc_load_policy')) {
                        $params['cc_load_policy'] = $cc_load_policy;
                    }

                    return count($params) ? '?'.http_build_query($params) : '';
                } else {
                    // basic links

                    if ($start = $this->getSetting('start')) {
                        $params['t'] = $start.'s';
                    }

                    return count($params) ? '&'.http_build_query($params) : '';
                }
            } elseif ($data['host'] == 'vimeo') {
                if ($embed) {
                    // embed links

                    if ($this->getSetting('autoplay')) {
                        $params['autoplay'] = $this->getSetting('autoplay');
                    }

                    if (! $this->getSetting('controls')) {
                        $params['controls'] = $this->getSetting('controls');
                    }

                    if ($this->getSetting('loop')) {
                        $params['loop'] = $this->getSetting('loop');
                    }

                    if ($this->getSetting('muted')) {
                        $params['muted'] = $this->getSetting('muted');
                    }

                    return count($params) ? '?'.http_build_query($params) : '';
                } else {
                    // basic links

                    if ($start = $this->getSetting('start')) {
                        $params['t'] = $start;
                    }

                    return count($params) ? '#'.http_build_query($params) : '';
                }
            }
        }

        return '';
    }

    public function getSetting($name)
    {
        $data = $this->getVideoData();

        if ($data['advanced_settings']) {
            return isset($data['settings'][$name]) ? $data['settings'][$name] : null;
        }

        return null;
    }

    public function hostData($param = null)
    {
        $data = $this->getVideoData();

        if (isset($data['hostdata_json']) && $data['hostdata_json'] != '') {
            $data = json_decode($data['hostdata_json'], true);

            if (isset($data['html'])) {
                $html = DBHTMLText::create();
                $html->setValue($data['html']);
                $data['html'] = $html;
            }

            if ($param && isset($data[$param])) {
                return $data[$param];
            } elseif ($data) {
                return ArrayData::create($data);
            }
        }
    }

    public function fetchOembed($data)
    {
        if (
            strpos($data['id'], 'http:') === false &&
            strpos($data['id'], 'https:') === false &&
            strpos($data['id'], 'youtube.com') === false &&
            strpos($data['id'], 'vimeo.com') === false &&
            isset($data['id']) &&
            $data['id']
        ) {
            if ($data['host'] == 'youtube') {
                $str =
                    'https://www.youtube.com/oembed?url=http://www.youtube.com/watch?v='.$data['id'].'&format=json';
            } elseif ($data['host'] == 'vimeo') {
                $str = 'https://vimeo.com/api/oembed.json?url=https://player.vimeo.com/video/'.$data['id'];
            }

            return isset($str) ? file_get_contents($str) : null;
        } else {
            return null;
        }
    }

    /**
     * @return string
     */
    public function getValue()
    {
        if (! $this->exists()) {
            return null;
        }

        $data = $this->getData();

        return $data;
    }

    /**
     * @return float
     */
    public function getData()
    {
        return $this->getField('Data');
    }

    /**
     * @param  mixed  $data
     * @param  bool  $markChanged
     * @return $this
     */
    public function setData($data, $markChanged = true)
    {
        // Retain nullability to mark this field as empty
        if (isset($data)) {
            $data = (float) $data;
        }
        $this->setField('Data', $data, $markChanged);

        return $this;
    }

    /**
     * @return bool
     */
    public function exists()
    {
        return is_numeric($this->getData());
    }

    /**
     * Determine if this has a non-zero data
     *
     * @return bool
     */
    public function hasData()
    {
        $a = $this->getData();

        return ! empty($a) && is_numeric($a);
    }

    /**
     * @param  string  $locale
     * @return $this
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;

        return $this;
    }

    /**
     * @return string
     */
    public function getLocale()
    {
        return $this->locale ?: i18n::get_locale();
    }

    /**
     * Returns a CompositeField instance used as a default
     * for form scaffolding.
     *
     * Used by {@link SearchContext}, {@link ModelAdmin}, {@link DataObject::scaffoldFormFields()}
     *
     * @param  string  $title  Optional. Localized title of the generated instance
     * @param  array  $params
     * @return FormField
     */
    public function scaffoldFormField($title = null, $params = null)
    {
        if ($params && isset($params['static'])) {
            $static = $params['static'];
        } else {
            $static = false;
        }

        if (! isset($params['set']['name']) && ($data = $this->getData())) {
            $params = json_decode($data, true);
            if (isset($params['set']['name'])) {
                $set = $params['set']['name'];
            }
        }

        return isset($set) ? VideoField::create($set, $this->getName(), $title, '', $static) : null;
        // ->setLocale($this->getLocale());
    }
}
