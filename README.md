
# 🦅 Video Field for Silverstripe

[![Silverstripe Version](https://img.shields.io/badge/Silverstripe-^5.1-005ae1.svg?labelColor=white&logoColor=ffffff&logo=data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAxMDEuMDkxIDU4LjU1NSIgZmlsbD0iIzAwNWFlMSIgeG1sbnM6dj0iaHR0cHM6Ly92ZWN0YS5pby9uYW5vIj48cGF0aCBkPSJNNTAuMDE1IDUuODU4bC0yMS4yODMgMTQuOWE2LjUgNi41IDAgMCAwIDcuNDQ4IDEwLjY1NGwyMS4yODMtMTQuOWM4LjgxMy02LjE3IDIwLjk2LTQuMDI4IDI3LjEzIDQuNzg2czQuMDI4IDIwLjk2LTQuNzg1IDI3LjEzbC02LjY5MSA0LjY3NmM1LjU0MiA5LjQxOCAxOC4wNzggNS40NTUgMjMuNzczLTQuNjU0QTMyLjQ3IDMyLjQ3IDAgMCAwIDUwLjAxNSA1Ljg2MnptMS4wNTggNDYuODI3bDIxLjI4NC0xNC45YTYuNSA2LjUgMCAxIDAtNy40NDktMTAuNjUzTDQzLjYyMyA0Mi4wMjhjLTguODEzIDYuMTctMjAuOTU5IDQuMDI5LTI3LjEyOS00Ljc4NHMtNC4wMjktMjAuOTU5IDQuNzg0LTI3LjEyOWw2LjY5MS00LjY3NkMyMi40My0zLjk3NiA5Ljg5NC0uMDEzIDQuMTk4IDEwLjA5NmEzMi40NyAzMi40NyAwIDAgMCA0Ni44NzUgNDIuNTkyeiIvPjwvc3ZnPg==)](https://packagist.org/packages/goldfinch/video-field)
[![Package Version](https://img.shields.io/packagist/v/goldfinch/video-field.svg?labelColor=333&color=F8C630&label=Version)](https://packagist.org/packages/goldfinch/video-field)
[![Total Downloads](https://img.shields.io/packagist/dt/goldfinch/video-field.svg?labelColor=333&color=F8C630&label=Downloads)](https://packagist.org/packages/goldfinch/video-field)
[![License](https://img.shields.io/packagist/l/goldfinch/video-field.svg?labelColor=333&color=F8C630&label=License)](https://packagist.org/packages/goldfinch/video-field) 

YouTube & Vimeo video field for Silverstripe. Store video data for further manipulation. Enchant links with parameters through friendly interface, display thumbnails, fetching video data like title, description and more with no extra actions.

## Install

```bash
composer require goldfinch/video-field
```

## Usage

```php
use Goldfinch\VideoField\Forms\VideoField;

private static $db = [
    'Video' => 'Video',
];

// ..

VideoField::create($this, 'Video')

```

```html
<!-- template.ss -->

<!-- General -->
$Video.url
$Video.embedUrl
$Video.plainUrl
$Video.plainEmbedUrl
$Video.iframe
$Video.iframe(300,200)
$Video.thumbnailUrl
$Video.thumbnailUrl(standard)
$Video.thumbnail
$Video.thumbnail(standard)
<%-- $Video.dumpAllThumbnails --%>

<!-- Youtube (API oembed data) -->
$Video.hostData.title
$Video.hostData.author_name
$Video.hostData.author_url
$Video.hostData.type
$Video.hostData.height
$Video.hostData.width
$Video.hostData.version
$Video.hostData.provider_name
$Video.hostData.provider_url
$Video.hostData.thumbnail_height
$Video.hostData.thumbnail_width
$Video.hostData.thumbnail_url
$Video.hostData.html

<!-- Vimeo (API oembed data) -->
$Video.hostData.type
$Video.hostData.version
$Video.hostData.provider_name
$Video.hostData.provider_url
$Video.hostData.title
$Video.hostData.author_name
$Video.hostData.author_url
$Video.hostData.is_plus
$Video.hostData.account_type
$Video.hostData.html
$Video.hostData.width
$Video.hostData.height
$Video.hostData.duration
$Video.hostData.description
$Video.hostData.thumbnail_url
$Video.hostData.thumbnail_width
$Video.hostData.thumbnail_height
$Video.hostData.thumbnail_url_with_play_button
$Video.hostData.upload_date
$Video.hostData.video_id
$Video.hostData.uri
```

## Previews

#### Video fields
![Video fields](screenshots/video-fields.png)
#### Video settings
![Video settings](screenshots/video-field-settings.png)

## License

The MIT License (MIT)
