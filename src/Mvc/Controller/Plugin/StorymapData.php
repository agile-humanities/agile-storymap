<?php

namespace Storymap\Mvc\Controller\Plugin;

use Zend\Mvc\Controller\Plugin\AbstractPlugin;

/**
 * Class StorymapData
 *
 * @package Storymap\Mvc\Controller\Plugin
 */
class StorymapData extends AbstractPlugin {

  /**
   * Extract titles, descriptions and dates from the storymap’s pool of items.
   *
   * @param array $itemPool
   * @param array $args
   * @param \Omeka\Entity\SitePageBlock
   *
   * @return array
   */
  public function __invoke(array $itemPool, array $args, $block) {
    $slides = [];
    $gigapixel_item = NULL;
    $attachments = $block->getAttachments();
    $captions = [];
    // Captions are specific to the individual Storymap and are not stored in the item.
    foreach ($attachments as $attachment) {
      $attachment_item = $this->getController()->api()
        ->read('items', $attachment->getItem()->getId())
        ->getContent();
      $attachment_items[] = $attachment_item;
      $captions[$attachment_item->id()] = $attachment->getCaption();
    }

    if ($args['gigapixel_image']) {
      $gigapixel_item = $this->getController()->api()
        ->read('items', $args['gigapixel_image'])
        ->getContent();
      $media = $gigapixel_item->primaryMedia();
      $storage = $media->storageId();
      $mediaUrl = $media ? $media->thumbnailUrl('large') : NULL;
      $info = getimagesize($mediaUrl);
      $height = $info[1];
      $width = $info[0];
    }
    //determine property types.
    $propertyItemTitle = $args['item_title'];
    $propertyItemDescription = $args['item_description'];
    $propertyItemDate = $args['item_date'];
    $propertyItemLocation = $args['item_location'];
    $propertyItemType = $args['item_type'];
    $propertyItemContributor = $args['item_contributor'];
    $has_overview = FALSE;

    foreach ($attachment_items as $item) {
      // Get property values.
      $itemDate = $item->value($propertyItemDate, [
        'type' => 'literal',
        'default' => [],
      ]);
      $itemTitle = $item->value($propertyItemTitle, [
        'type' => 'literal',
        'default' => '',
      ]);
      $itemLocation = $item->value($propertyItemLocation, [
        'type' => 'literal',
        'default' => '',
      ]);
      $itemType = $item->value($propertyItemType, [
        'type' => 'literal',
        'default' => '',
      ]);

      $itemContributor = $item->value($propertyItemContributor, [
        'type' => 'literal',
        'default' => '',
      ]);

      $credit = ($itemContributor) ? $itemContributor->value() : 'Unknown';
      $lat = $long = NULL;
      if ($itemLocation) {
        $coordinates = explode(',', $itemLocation->value());
        $lat = (isset($coordinates[0]) && is_numeric($coordinates[0])) ? trim($coordinates[0]) : NULL;
        $long = (isset($coordinates[1]) && is_numeric($coordinates[0])) ? trim($coordinates[1]) : NULL;
      }
      if ($itemTitle) {
        $itemTitle = strip_tags($itemTitle->value());
      }
      // Use caption from Showcase if present, default to item description
      if (isset($captions[$item->id()])) {
        $itemDescription = $captions[$item->id()];
      }
      else {
        $itemDescription = $item->value($propertyItemDescription, [
          'type' => 'literal',
          'default' => '',
        ]);
        if ($itemDescription) {
          $itemDescription = $this->snippet($itemDescription->value(), 200);
        }
      }
      $media = $item->primaryMedia();
      //large, medium, square
      $mediaUrl = $media ? $media->thumbnailUrl('large') : NULL;

      $caption = $media->displayTitle();
      // Start building slides
      $is_overview = FALSE;
      $slide = [];
      if ($itemDate) {
        $itemDate = $itemDate->value();
      }

      if (!$has_overview) {
        $slide['type'] = 'overview';
        $has_overview = TRUE;
        $is_overview = TRUE;
      }


      $media_url = $media->siteUrl($args['site-slug']);
      $slide['date'] = $itemDate;
      $slide['text'] = [
        'headline' => "<a href='$media_url'>$itemTitle</a>",
        'text' => $itemDescription,
      ];
      if ($lat && $long) {
        $slide['location'] = [
          'lat' => trim($lat),
          'lon' => trim($long),
        ];
      }
      $slide['media'] = [
        'url' => $mediaUrl,
        'caption' => $caption,
        'credit' => $credit,
      ];
      if ($lat && $long || $is_overview) {
        $slides[] = $slide;
      }
    }

    $data = [];
    $data['storymap']['slides'] = array_values($slides);
    if (isset($args['map_type'])) {
      $data['storymap']['map_type'] = $args['map_type'];
    }
    // create optional gigapixel
    if ($gigapixel_item) {
      $multiplier = 3;
      if (isset($args['map_background'])) {
        $data['storymap']['map_background_color'] = $args['map_background'];
      }
      $attribution = $args['attribution'];
      $tolerance = $args['tolerance'] ? $args['tolerance'] : .9;
      $data['calculate_zoom'] = 'true';
      $data['storymap']['language'] = 'en';
      $data['storymap']['map_type'] = 'zoomify';
      $data['storymap']['map_as_image'] = 'true';
      $data['storymap']['zoomify'] = [
        'path' => "/files/tile/{$storage}_zdata/",
        'tolerance' => $tolerance,
        "width" => $width * $multiplier,
        "height" => $height * $multiplier,
        "attribution" => $attribution,
      ];
    }
    return $data;
  }

  /**
   * Remove html tags and truncate a string to the specified length.
   *
   * @param string $string
   * @param int $length
   *
   * @return string
   */
  protected function snippet($string, $length) {
    $str = strip_tags($string);
    return strlen($str) <= $length ? $str : substr($str, 0, $length - 1) . '&hellip;';
  }
}
