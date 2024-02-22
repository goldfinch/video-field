<?php

namespace Goldfinch\VideoField\ORM\FieldType;

use Goldfinch\VideoField\Forms\VideoField;
use SilverStripe\ORM\FieldType\DBComposite;
use Goldfinch\JSONEditor\ORM\FieldType\DBJSONText;

class DBVideo extends DBComposite
{
    /**
     * @var string $locale
     */
    protected $locale = null;

    protected $videoSize = null;
    protected $videoColor = null;

    /**
     * @var array<string,string>
     */
    private static $composite_db = [
        'Data' => DBJSONText::class,
    ];

    private static $casting = [
        // 'getTag' => 'HTMLFragment',
    ];

    public function forTemplate()
    {
        return $this->getTag();
    }

    public function getTag()
    {
        // $key = $this->getKey();

        // if ($key) {
        //     $data = json_decode($this->getData(), true);

        //     $field = $this->scaffoldFormField($this->getName(), ['static' => true]);

        //     if ($field) {
        //         return $field->renderVideoTemplate($data + [
        //             'color' => $this->videoColor,
        //             'size' => $this->videoSize,
        //         ], false, $data['set'], $key);
        //     }
        // }
    }

    public function URL()
    {
        // $key = $this->getKey();

        // if ($key) {
        //     $data = json_decode($this->getData(), true);

        //     if ($data && isset($data['source'])) {
        //         return $data['source'];
        //     }
        // }
    }

    public function Title()
    {
        // $key = $this->getKey();

        // if ($key) {
        //     $data = json_decode($this->getData(), true);

        //     if ($data && isset($data['title']) && $data['title'] && $data['title'] != '') {
        //         return $data['title'];
        //     } else {
        //         return $key;
        //     }
        // }
    }

    public function Size($size)
    {
        $this->videoSize = $size;

        return $this;
    }

    public function Color($color)
    {
        $this->videoColor = $color;

        return $this;
    }

    // public function getParse($key = null)
    // {
    //     $data = $this->getData();

    //     if (!$data) {
    //         return null;
    //     }

    //     $data = json_decode($data, true);

    //     $parse = [
    //         'set' => $data['set'],
    //     ];

    //     return $key ? (isset($parse[$key]) ? $parse[$key] : null) : $parse;
    // }

    public function getVideoSetName()
    {
        return $this->getParse('set')['name'];
    }

    public function getVideoType()
    {
        return $this->getParse('set')['type'];
    }

    /**
     *
     * @return string
     */
    public function getValue()
    {
        if (!$this->exists()) {
            return null;
        }

        $data = $this->getData();

        // $key = $this->getKey();

        // if (empty($key)) {
        //     return $data;
        // }

        return $data; // $data . ' ' . $key;
    }

    /**
     * @return float
     */
    public function getData()
    {
        return $this->getField('Data');
    }

    /**
     * @param mixed $data
     * @param bool $markChanged
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
     * @return boolean
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
        return !empty($a) && is_numeric($a);
    }

    /**
     * @param string $locale
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
     * @param string $title Optional. Localized title of the generated instance
     * @param array $params
     * @return FormField
     */
    public function scaffoldFormField($title = null, $params = null)
    {
        if ($params && isset($params['static'])) {
            $static = $params['static'];
        } else {
            $static = false;
        }

        if (!isset($params['set']['name']) && $data = $this->getData()) {
            $params = json_decode($data, true);
            if (isset($params['set']['name'])) {
                $set = $params['set']['name'];
            }
        }

        return isset($set) ? VideoField::create($set, $this->getName(), $title, '', $static) : null;
        // ->setLocale($this->getLocale());
    }
}
