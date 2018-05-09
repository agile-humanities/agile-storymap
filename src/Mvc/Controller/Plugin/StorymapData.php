<?php

namespace Storymap\Mvc\Controller\Plugin;

use DateTime;
use Zend\Mvc\Controller\Plugin\AbstractPlugin;

class StorymapData extends AbstractPlugin {

  const RENDER_YEAR_JANUARY_1 = 'january_1';

  const RENDER_YEAR_JULY_1 = 'july_1';

  const RENDER_YEAR_DECEMBER_31 = 'december_31';

  const RENDER_YEAR_JUNE_30 = 'june_30';

  const RENDER_YEAR_FULL_YEAR = 'full_year';

  // Render a year as a range: use convertSingleDate().
  const RENDER_YEAR_SKIP = 'skip';

  const RENDER_YEAR_DEFAULT = self::RENDER_YEAR_JANUARY_1;

  protected $renderYear;

  /**
   * Extract titles, descriptions and dates from the storymap’s pool of items.
   *
   * @param array $itemPool
   * @param array $args
   *
   * @return array
   */
  public function __invoke(array $itemPool, array $args) {
    $slides = [];
    $is_gigapixel = FALSE;
    //determine property types.
    $propertyItemTitle = $args['item_title'];
    $propertyItemDescription = $args['item_description'];
    $propertyItemDate = $args['item_date'];
    $propertyItemLocation = $args['item_location'];
    $propertyItemType = $args['item_type'];
    $propertyItemContributor = $args['item_contributor'];
    $propertyItemOrder = $args['item_order'];
    $items = $this->getController()->api()
      ->search('items', $itemPool)
      ->getContent();
    // generate fake page ordering for items missing order number.
    $nullcounter = 1000;
    foreach ($items as $item) {
      $nullcounter++;
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
      $itemOrder = $item->value($propertyItemOrder);
      $item_order = $nullcounter;
      if ($itemOrder) {
        $item_order = $itemOrder->value();
      }
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
      $itemDescription = $item->value($propertyItemDescription, [
        'type' => 'literal',
        'default' => '',
      ]);
      if ($itemDescription) {
        $itemDescription = $this->snippet($itemDescription->value(), 200);
      }
      $media = $item->primaryMedia();
      //large, medium, square
      $mediaUrl = $media ? $media->thumbnailUrl('large') : NULL;

      $caption = $media->displayTitle();
      // Start building slides
      $slide = [];
      $is_overview = FALSE;
      if ($itemDate) {
        $itemDate = $itemDate->value();
      }


      if ($itemType && strtolower($itemType->value()) == 'overview') {
        $slide['type'] = strtolower($itemType->value());
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
      if ($is_overview) {
        array_unshift($slides, $slide);
      }
      else {
        if ($lat && $long) {
          $slides[$item_order] = $slide;
        }
      }

      if ($itemType && $itemType->value() == strtolower('gigapixel')) {
        $storage = $media->storageId();
        $is_gigapixel = TRUE;
        $info = getimagesize($mediaUrl);
        $height = $info[1];
        $width = $info[0];
      }
    }
    // create optional gigapixel
    $data = [];
    $data['storymap']['slides'] = array_values($slides);
    if (isset($args['map_type'])) {
      $data['storymap']['map_type'] = $args['map_type'];
    }
    if ($is_gigapixel) {
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
   * Returns a string for storymap_json 'classname' attribute for an item.
   *
   * Default fields included are: 'item', item type name, all DC:Type values.
   *
   * @return string
   */
  protected function itemClass($item) {
    $classes = ['item'];

    $type = $item->resourceClass() ? $item->resourceClass()->label() : NULL;

    if ($type) {
      $classes[] = $this->textToId($type);
    }
    $dcTypes = $item->value('dcterms:type', [
      'all' => TRUE,
      'type' => 'literal',
      'default' => [],
    ]);
    foreach ($dcTypes as $type) {
      $classes[] = $this->textToId($type->value());
    }

    $classAttribute = implode(' ', $classes);
    return $classAttribute;
  }

  /**
   * Generates an ISO-8601 date from a date string
   *
   * @param string $date
   * @param string renderYear Force the format of a single number as a year.
   *
   * @return string ISO-8601 date
   */
  protected function convertDate($date, $renderYear = NULL) {
    if (empty($renderYear)) {
      $renderYear = $this->renderYear;
    }

    // Check if the date is a single number.
    if (preg_match('/^-?\d{1,4}$/', $date)) {
      // Normalize the year.
      $date = $date < 0
        ? '-' . str_pad(substring($date, 1), 4, '0', STR_PAD_LEFT)
        : str_pad($date, 4, '0', STR_PAD_LEFT);
      switch ($renderYear) {
        case self::RENDER_YEAR_JANUARY_1:
          $date_out = $date . '-01-01' . 'T00:00:00+00:00';
          break;
        case self::RENDER_YEAR_JULY_1:
          $date_out = $date . '-07-01' . 'T00:00:00+00:00';
          break;
        case self::RENDER_YEAR_DECEMBER_31:
          $date_out = $date . '-12-31' . 'T00:00:00+00:00';
          break;
        case self::RENDER_YEAR_JUNE_30:
          $date_out = $date . '-06-30' . 'T00:00:00+00:00';
          break;
        case self::RENDER_YEAR_FULL_YEAR:
          // Render a year as a range: use storymap_convert_single_date().
        case self::RENDER_YEAR_SKIP:
        default:
          $date_out = FALSE;
          break;
      }
      return $date_out;
    }

    try {
      $dateTime = new DateTime($date);

      $date_out = $dateTime->format(DateTime::ISO8601);
      $date_out = preg_replace('/^(-?)(\d{3}-)/', '${1}0\2', $date_out);
      $date_out = preg_replace('/^(-?)(\d{2}-)/', '${1}00\2', $date_out);
      $date_out = preg_replace('/^(-?)(\d{1}-)/', '${1}000\2', $date_out);
    } catch (\Exception $e) {
      $date_out = NULL;
    }

    return $date_out;
  }

  /**
   * Generates an array of one or two ISO-8601 dates from a string.
   *
   * @todo manage the case where the start is empty and the end is set.
   *
   * @param string $date
   * @param string renderYear Force the format of a single number as a year.
   *
   * @return array Array of two dates.
   */
  protected function convertAnyDate($date, $renderYear = NULL) {
    return $this->convertTwoDates($date, NULL, $renderYear);
  }

  /**
   * Generates an array of one or two ISO-8601 dates from two strings.
   *
   * @todo manage the case where the start is empty and the end is set.
   *
   * @param string $date
   * @param string $dateEnd
   * @param string renderYear Force the format of a single number as a year.
   *
   * @return array Array of two dates.
   */
  protected function convertTwoDates($date, $dateEnd, $renderYear = NULL) {
    if (empty($renderYear)) {
      $renderYear = $this->renderYear;
    }

    // Manage a common issue (2016-2017).
    $dateArray = preg_match('/^\d{4}-\d{4}$/', $date)
      ? array_map('trim', explode('-', $date))
      : array_map('trim', explode('/', $date));

    // A range of dates.
    if (count($dateArray) == 2) {
      return $this->convertRangeDates($dateArray, $renderYear);
    }

    $dateEndArray = explode('/', $dateEnd);
    $dateEnd = trim(reset($dateEndArray));

    // A single date, or a range when the two dates are years and when the
    // render is "full_year".
    if (empty($dateEnd)) {
      return $this->convertSingleDate($dateArray[0], $renderYear);
    }

    return $this->convertRangeDates([$dateArray[0], $dateEnd], $renderYear);
  }

  /**
   * Generates an ISO-8601 date from a date string, with an exception for
   * "full_year" render, that returns two dates.
   *
   * @param string $date
   * @param string renderYear Force the format of a single number as a year.
   *
   * @return array Array of two dates.
   */
  protected function convertSingleDate($date, $renderYear = NULL) {
    if (empty($renderYear)) {
      $renderYear = $this->renderYear;
    }

    // Manage a special case for render "full_year" with a single number.
    if ($renderYear == self::RENDER_YEAR_FULL_YEAR && preg_match('/^-?\d{1,4}$/', $date)) {
      $dateStartValue = $this->convertDate($date, self::RENDER_YEAR_JANUARY_1);
      $dateEndValue = $this->convertDate($date, self::RENDER_YEAR_DECEMBER_31);
      return [$dateStartValue, $dateEndValue];
    }

    // Only one date.
    $dateStartValue = $this->convertDate($date, $renderYear);
    return [$dateStartValue, NULL];
  }

  /**
   * Generates two ISO-8601 dates from an array of two strings.
   *
   * By construction, no "full_year" is returned.
   *
   * @param array $dates
   * @param string renderYear Force the format of a single number as a year.
   *
   * @return array $dates
   */
  protected function convertRangeDates($dates, $renderYear = NULL) {
    if (!is_array($dates)) {
      return [NULL, NULL];
    }

    if (empty($renderYear)) {
      $renderYear = $this->renderYear;
    }

    $dateStart = $dates[0];
    $dateEnd = $dates[1];

    // Check if the date are two numbers (years).
    if ($renderYear == self::RENDER_YEAR_SKIP) {
      $dateStartValue = $this->convertDate($dateStart, $renderYear);
      $dateEndValue = $this->convertDate($dateEnd, $renderYear);
      return [$dateStartValue, $dateEndValue];
    }

    // Check if there is one number and one date.
    if (!preg_match('/^-?\d{1,4}$/', $dateStart)) {
      if (!preg_match('/^-?\d{1,4}$/', $dateEnd)) {
        // TODO Check order to force the start or the end.
        $dateStartValue = $this->convertDate($dateStart, $renderYear);
        $dateEndValue = $this->convertDate($dateEnd, $renderYear);
        return [$dateStartValue, $dateEndValue];
      }
      // Force the format for the end.
      $dateStartValue = $this->convertDate($dateStart, $renderYear);
      if ($renderYear == self::RENDER_YEAR_FULL_YEAR) {
        $renderYear = self::RENDER_YEAR_DECEMBER_31;
      }
      $dateEndValue = $this->convertDate($dateEnd, $renderYear);
      return [$dateStartValue, $dateEndValue];
    }
    // The start is a year.
    elseif (!preg_match('/^-?\d{1,4}$/', $dateEnd)) {
      // Force the format of the start.
      $dateEndValue = $this->convertDate($dateEnd, $renderYear);
      if ($renderYear == self::RENDER_YEAR_FULL_YEAR) {
        $renderYear = self::RENDER_YEAR_JANUARY_1;
      }
      $dateStartValue = $this->convertDate($dateStart, $renderYear);
      return [$dateStartValue, $dateEndValue];
    }

    $dateStart = (integer) $dateStart;
    $dateEnd = (integer) $dateEnd;

    // Same years.
    if ($dateStart == $dateEnd) {
      $dateStartValue = $this->convertDate($dateStart, self::RENDER_YEAR_JANUARY_1);
      $dateEndValue = $this->convertDate($dateEnd, self::RENDER_YEAR_DECEMBER_31);
      return [$dateStartValue, $dateEndValue];
    }

    // The start and the end are years, so reorder them (may be useless).
    if ($dateStart > $dateEnd) {
      $kdate = $dateEnd;
      $dateEnd = $dateStart;
      $dateStart = $kdate;
    }

    switch ($renderYear) {
      case self::RENDER_YEAR_JULY_1:
        $dateStartValue = $this->convertDate($dateStart, self::RENDER_YEAR_JULY_1);
        $dateEndValue = $this->convertDate($dateEnd, self::RENDER_YEAR_JUNE_30);
        return [$dateStartValue, $dateEndValue];
      case self::RENDER_YEAR_JANUARY_1:
        $dateStartValue = $this->convertDate($dateStart, self::RENDER_YEAR_JANUARY_1);
        $dateEndValue = $this->convertDate($dateEnd, self::RENDER_YEAR_JANUARY_1);
        return [$dateStartValue, $dateEndValue];
      case self::RENDER_YEAR_FULL_YEAR:
      default:
        $dateStartValue = $this->convertDate($dateStart, self::RENDER_YEAR_JANUARY_1);
        $dateEndValue = $this->convertDate($dateEnd, self::RENDER_YEAR_DECEMBER_31);
        return [$dateStartValue, $dateEndValue];
    }
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

  /**
   * Convert a word or phrase to a valid HTML ID.
   *
   * For example: 'Foo Bar' becomes 'foo-bar'.
   *
   * This function converts to lowercase, replaces whitespace with hyphens,
   * removes all non-alphanumerics, removes leading or trailing delimiters,
   * and optionally prepends a piece of text.
   *
   * @see Omeka Classic application/libraries/globals.php text_to_id()
   *
   * @package Omeka\Function\Text
   *
   * @param string $text The text to convert
   * @param string $prepend Another string to prepend to the ID
   * @param string $delimiter The delimiter to use (- by default)
   *
   * @return string
   */
  protected function textToId($text, $prepend = NULL, $delimiter = '-') {
    $text = strtolower($text);
    $id = preg_replace('/\s/', $delimiter, $text);
    $id = preg_replace('/[^\w\-]/', '', $id);
    $id = trim($id, $delimiter);
    return $prepend ? $prepend . $delimiter . $id : $id;
  }
}
