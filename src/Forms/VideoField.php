<?php

namespace Goldfinch\VideoField\Forms;

use InvalidArgumentException;
use SilverStripe\Assets\File;
use SilverStripe\Assets\Folder;
use SilverStripe\ORM\ArrayList;
use SilverStripe\View\ArrayData;
use SilverStripe\Forms\FormField;
use SilverStripe\Forms\TextField;
use SilverStripe\Forms\HiddenField;
use SilverStripe\View\Requirements;
use SilverStripe\Forms\LiteralField;
use Symfony\Component\Finder\Finder;
use SilverStripe\ORM\DataObjectInterface;
use SilverStripe\ORM\FieldType\DBHTMLText;
use Symfony\Component\Filesystem\Filesystem;
use Goldfinch\JSONEditor\Forms\JSONEditorField;
use Goldfinch\VideoField\ORM\FieldType\DBVideo;

class VideoField extends FormField
{
    protected $schemaDataType = 'VideoField';

    protected $videosSet = null;

    protected $videosSetConfig = null;

    protected $videosList = [];

    /**
     * @var HiddenField
     */
    protected $fieldData = null;

    /**
     * Gets field for the data input
     *
     * @return HiddenField
     */
    public function getDataField()
    {
        return $this->fieldData;
    }

    public function getPreviewField()
    {
        return LiteralField::create(
            $this->getName() . 'Video',
            '<div class="ggp__preview" data-goldfinch-video="preview"></div>',
        );
    }

    // public function getCurrentVideos()
    // {
    //     $cfg = $this->videosSetConfig;
    //     $value = $this->getKeyField()->dataValue();
    //     if ($value) {
    //         $values = explode(',', $value);
    //     }
    //     $videosList = $this->videosList;

    //     $html = '';

    //     $count = 0;

    //     if (isset($values)) {

    //         $count = count($values);

    //         foreach ($values as $v) {
    //             $video = $this->getVideoByKey($v);

    //             if (isset($video['admin_template'])) {
    //                 $html .= '<li data-value="'.$v.'">' . $video['admin_template'] . '</li>';
    //             }
    //         }
    //     }

    //     $return = DBHTMLText::create();
    //     $return->setValue('<ul data-count="'.$count.'">'.$html.'</ul>');

    //     return $return;
    // }

    public function __construct($parent, $name, $title = null, $value = '', $static = false)
    {
        $this->setName($name);

        $schema = file_get_contents(BASE_PATH . '/vendor/goldfinch/video-field/_schema/video.json');

        $this->fieldData = JSONEditorField::create(
            "{$name}[Data]",
            'Data',
            $parent,
            [],
            '{}',
            null,
            $schema,
        )->compact()->nolabel();

        // $this->fieldData->setAttribute('data-goldfinch-video', 'data');

        // $this->buildKeyField();

        $this->initSetsRequirements();

        if (!$static) {

            Requirements::css('goldfinch/video-field:client/dist/video-styles.css');
            Requirements::javascript('goldfinch/video-field:client/dist/video.js');

            $this->setVideosList();
        }

        parent::__construct($name, $title, $value);
    }

    public function getVideosConfigJSON()
    {
        return json_encode($this->videosSetConfig);
    }

    public function getVideosListJSON()
    {
        return json_encode($this->videosList);
    }

    public function getVideosList()
    {
        return ArrayList::create($this->videosList);
    }

    private function setVideosList(): void
    {
        $cfg = $this->videosSetConfig;

        /*
            $schemaList = [
                0 => [
                    'title' => '', // optional
                    'value' => 'value-video-prior', // optional (used prior the key)
                    'source' => '', // for display purpose (can be a full link, filename with extension etc.)
                    'template' => '', // added at the backend (not for customizations)
                ],
            ];
        */
        $schemaList = [];

        if (!isset($cfg['type'])) {
            return;
        }

        if ($cfg['type'] == 'font') {

            $fs = new Filesystem;

            $schema = BASE_PATH . '/app/_schema/video-' . $this->videosSet . '.json';

            if ($fs->exists($schema)) {
                $content = file_get_contents($schema);
                $content = json_decode($content, true);

                if ($content && is_array($content) && count($content)) {

                    $schemaList = $content;

                    foreach ($schemaList as $k => $sl) {
                        if (!isset($sl['value']) || $sl['value'] == '') {
                            $sl['value'] = $k;
                        }

                        $sl['admin_template'] = $this->renderVideoAdminTemplate($sl);

                        if (!isset($sl['template']) || $sl['template'] == '') {
                            $sl['template'] = $this->renderVideoTemplate($sl);
                        }


                        $schemaList[$k] = $sl;
                    }
                }
            }

        } else if ($cfg['type'] == 'dir') {

            $sourcePath = '/' . $cfg['source'];

            $finder = new Finder();
            $files = $finder->in(PUBLIC_PATH . $sourcePath)->files();

            foreach ($files as $file) {

                $filename = $file->getFilename();
                $ex = explode('.', $filename);

                $item = [
                    'title' => '',
                    'value' => $ex[0],
                    'source' => $sourcePath . '/' . $filename,
                ];

                $item['admin_template'] = $this->renderVideoAdminTemplate($item);
                $item['template'] = $this->renderVideoTemplate($item);
                $schemaList[] = $item;
            }

        } else if ($cfg['type'] == 'upload') {

            $targetFolder = File::get()->filter(['ClassName' => Folder::class, 'Name' => $cfg['source']])->first();

            if ($targetFolder) {

                $folder = File::get()->byID(1);

                if ($folder && $folder == Folder::class) {
                    foreach ($folder->myChildren() as $file) {

                        $item = [
                            'title' => $file->Title,
                            'value' => $file->ID,
                            'source' => $file->getURL(),
                        ];

                        $item['admin_template'] = $this->renderVideoAdminTemplate($item);
                        $item['template'] = $this->renderVideoTemplate($item);
                        $schemaList[] = $item;
                    }
                }
            } else {
                // specified folder in .yml is not found
            }

        } else if ($cfg['type'] == 'json') {

            $fs = new Filesystem;

            $schema = BASE_PATH . '/app/_schema/' . $cfg['source'];

            if ($fs->exists($schema)) {
                $content = file_get_contents($schema);
                $content = json_decode($content, true);

                if ($content && is_array($content) && count($content)) {

                    $schemaList = $content;

                    foreach ($schemaList as $k => $sl) {
                        if (!isset($sl['value']) || $sl['value'] == '') {
                            $sl['value'] = $k;
                        }

                        $sl['admin_template'] = $this->renderVideoAdminTemplate($sl);

                        if (!isset($sl['template']) || $sl['template'] == '') {
                            $sl['template'] = $this->renderVideoTemplate($sl);
                        }


                        $schemaList[$k] = $sl;
                    }
                }
            }

        }

        $this->videosList = $schemaList;
    }



    private function renderVideoAdminTemplate($item): string
    {
        return $this->renderVideoTemplate($item, true);
    }

    public function renderVideoTemplate($item, $admin = false, $set = null, $value = null): string
    {
        if (!$set) {
            $cfg = $this->videosSetConfig;
        } else {
            $cfg = $set;
        }

        $render = '';

        if ($admin) {
            $primaryPath = 'Goldfinch/VideoField/Types/Admin/';
        } else {
            $primaryPath = 'Goldfinch/VideoField/Types/';
        }

        if ($cfg['type'] == 'font') {

            $template = $primaryPath . 'FontItem';

        } else if ($cfg['type'] == 'dir') {

            $template = $primaryPath . 'DirItem';

        } else if ($cfg['type'] == 'upload') {

            $template = $primaryPath . 'UploadItem';

        } else if ($cfg['type'] == 'json') {

            $template = $primaryPath . 'JsonItem';

        }

        if ($value) {
            $item['value'] = $value;
        }

        if (!isset($item['title']) || !$item['title']) {
            $item['title'] = $item['value'];
        }

        if (isset($item['source']) && $item['source'] && $item['source'] != '') {
            $ext = explode('.', $item['source']);
            $ext = end($ext);
        } else {
            $ext = null;
        }

        if ($admin) {

            if ($cfg['type'] == 'upload' || $cfg['type'] == 'dir' || $cfg['type'] == 'json') {

                $inlineStyle = [
                    'display' => 'inline-block',
                    'width' => '32px',
                    'height' => '32px',
                    'mask-size' => 'contain',
                    'mask-repeat' => 'no-repeat',
                    'mask-position' => 'center',
                    'mask-image' => 'url(' . $item['source'] . ')',
                    'background-color' => '#43536d',
                ];
            }

        } else {

            $inlineStyle = [];

            // defaults
            if ($cfg['type'] == 'upload' || $cfg['type'] == 'dir' || $cfg['type'] == 'json') {

                $inlineStyle = [
                    'display' => 'inline-block',
                    'width' => '32px',
                    'height' => '32px',
                    'mask-size' => 'contain',
                    'mask-repeat' => 'no-repeat',
                    'mask-position' => 'center',
                    'mask-image' => 'url(' . $item['source'] . ')',
                    'background-color' => '#43536d',
                ];
            }

            // apply custom styles

            if (isset($item['color'])) {
                if ($cfg['type'] == 'font') {
                    $inlineStyle['color'] = $item['color'];
                } else if ($ext == 'svg') {
                    $inlineStyle['background-color'] = $item['color'];
                }
            }

            if (isset($item['size'])) {
                $size = (int) $item['size'];

                if ($size) {
                    if ($cfg['type'] == 'font') {
                        $inlineStyle['font-size'] = $item['size'] . 'px';
                    } else {
                        $inlineStyle['width'] = $item['size'] . 'px';
                        $inlineStyle['height'] = $item['size'] . 'px';
                    }
                }
            }
        }

        $inlineStyleStr = '';

        if (!empty($inlineStyle)) {
            foreach ($inlineStyle as $prop => $style) {
                $inlineStyleStr .= $prop . ':' . $style . ';';
            }
        }

        return $this->customise(ArrayData::create(['Video' => $item, 'InlineStyle' => $inlineStyleStr]))->renderWith($template)->RAW();
    }

    private function setVideosSet($set): void
    {
        $this->videosSet = $set;

        if ($sets = $this->config()->get('videos_sets')) {
            foreach ($sets as $type => $s) {
                if (isset($s['type']) && $set == $type) {
                    $this->videosSetConfig = $s;
                    break;
                }
            }
        }
    }

    private function initSetsRequirements(): void
    {
        $sets = $this->config()->get('videos_sets');

        $fonts = [];

        if ($sets) {
            foreach ($sets as $set) {
                if ($set['type'] == 'font' && isset($set['source'])) {
                    $fonts[] = $set['source'];
                }
            }
        }

        if ($fonts && is_array($fonts)) {
            foreach ($fonts as $include) {
                Requirements::css($include);
            }
        }
    }

    public function __clone()
    {
        $this->fieldData = clone $this->fieldData;
    }

    public function setSubmittedValue($value, $data = null)
    {
        if (empty($value)) {
            $this->value = null;
            $this->fieldData->setValue(null);
            return $this;
        }

        $value['Data'] = $value['Data'];

        // Update each field
        $this->fieldData->setValue($value['Data']);
        // $this->fieldData->setSubmittedValue($value['Data'], $value);

        // Get data value
        $this->value = $this->dataValue();
        return $this;
    }

    public function setValue($value, $data = null)
    {
        if (empty($value)) {
            $this->value = null;
            $this->fieldData->setValue(null);
            return $this;
        }

        if ($value instanceof DBVideo) {
            $stock = [
                'Data' => $value->getData(),
            ];
        } else {
            throw new InvalidArgumentException('Invalid video format');
        }

        // dump(2, $this->dataBundle($value->getKey()));
        // if (!isset($stock['Data']) || !$stock['Data']) {
        // $stock = $this->dataBundle($value->getKey());
        // }

        // Save value
        $this->fieldData->setValue($stock['Data']);
        $this->value = $this->dataValue();

        return $this;
    }

    // private function dataBundle($key)
    // {
    //     $set = $this->videosSetConfig;
    //     $item = $this->getVideoByKey($key);

    //     return [
    //         'Key' => $key,
    //         'Data' => [
    //             'set' => [
    //                 'name' => $this->videosSet,
    //                 'type' => isset($set['type']) ? $set['type'] : null,
    //                 // 'source' => $set['source'],
    //             ],
    //             'title' => $item && isset($item['title']) ? $item['title'] : '',
    //             // 'value' => $item && isset($item['value']) ? $item['value'] : null,
    //             'source' => $item && isset($item['source']) ? $item['source'] : '',
    //         ],
    //     ];
    // }

    /**
     * Get value as DBVideo object useful for formatting the number
     *
     * @return DBVideo
     */
    protected function getDBVideo()
    {
        return DBVideo::create_field('Video', [
            'Data' => $this->fieldData->dataValue(),
        ]);
    }

    public function dataValue()
    {
        // Non-localised
        return $this->getDBVideo()->getValue();
    }

    public function Value()
    {
        // Localised
        return $this->getDBVideo()->getValue(); // ->Nice();
    }

    /**
     * @param DataObjectInterface|Object $dataObject
     */
    public function saveInto(DataObjectInterface $dataObject)
    {
        $fieldName = $this->getName();
        if ($dataObject->hasMethod("set$fieldName")) {
            $dataObject->$fieldName = $this->getDBVideo();
        } else {
            $dataField = "{$fieldName}Data";

            $dataObject->$dataField = $this->fieldData->dataValue();
        }
    }

    /**
     * Returns a readonly version of this field.
     */
    // public function performReadonlyTransformation()
    // {
    //     $clone = clone $this;
    //     $clone->setReadonly(true);
    //     return $clone;
    // }

    // public function setReadonly($bool)
    // {
    //     parent::setReadonly($bool);

    //     $this->fieldData->setReadonly($bool);
    //     $this->fieldKey->setReadonly($bool);

    //     return $this;
    // }

    // public function setDisabled($bool)
    // {
    //     parent::setDisabled($bool);

    //     $this->fieldData->setDisabled($bool);
    //     $this->fieldKey->setDisabled($bool);

    //     return $this;
    // }

    // public function videoHidePreview()
    // {
    //     $this->addExtraClass('goldfinch-video-hide-preview');

    //     return $this;
    // }

    /**
     * Validate this field
     *
     * @param Validator $validator
     * @return bool
     */
    // public function validate($validator)
    // {
    //     // return $this->extendValidationResult($result, $validator);
    // }

    // public function setForm($form)
    // {
    //     $this->fieldKey->setForm($form);
    //     $this->fieldData->setForm($form);
    //     return parent::setForm($form);
    // }
}
