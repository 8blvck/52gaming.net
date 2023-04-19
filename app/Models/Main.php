<?php
namespace App\Models;
use DB;

class Main
{
  static function randstr($length = 8) {
      $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
      $pass = [];
      $alphaLength = strlen($alphabet) - 1;
      for ($i = 0; $i < $length; $i++) {
          $n = rand(0, $alphaLength);
          $pass[] = $alphabet[$n];
      }
      return implode($pass);
  }

  static function header_menu() {
    $list = DB::select("SELECT id, slug, name, title, nested_in FROM pages WHERE display = 1 AND display_header = 1 ORDER BY display_number");
    return self::nest($list);
  }

  static function footer_menu() {
    return DB::select("SELECT id, slug, name, title FROM pages WHERE display = 1 AND display_footer = 1 ORDER BY display_number");
  }

  static function nest($list, $nested_in = 0) {
    $result = [];
    foreach($list as $item):
      if($item->nested_in == $nested_in):
        $item->nests = self::nest($list, $item->id);
        $result[] = $item;
      endif;
    endforeach;
    return $result;
  }

  static function seo_global_settings(string $dbp = '') {
    $result = collect(DB::select(
      "SELECT `index` as indexing, afterHead, afterBody,
      `".$dbp."meta_title` as meta_title,
      `".$dbp."meta_keys` as meta_keys,
      `".$dbp."meta_desc` as meta_desc
       FROM total_config LIMIT 1"))->first();
    return $result;
  }

  static function get_translations($action, $dbp) {
    $result = (object)[];
    if($action=='home'):
      $result = collect(DB::select(
        "SELECT `indexing`, `animation`,
        `".$dbp."meta_title` as meta_title,
        `".$dbp."meta_keys` as meta_keys,
        `".$dbp."meta_desc` as meta_desc,
        `".$dbp."text_1` as text_1,
        `".$dbp."text_2` as text_2,
        `".$dbp."text_3` as text_3
        FROM page_home LIMIT 1"
      ))->first();
    elseif($action=='footer'):
      $result = collect(DB::select(
        "SELECT
        `".$dbp."text_1` as text_1,
        `".$dbp."text_2` as text_2,
        `".$dbp."text_3` as text_3,
        `".$dbp."text_4` as text_4,
        `".$dbp."text_5` as text_5,
        `".$dbp."text_6` as text_6
        FROM page_footer LIMIT 1"
      ))->first();
    elseif($action=='elements'):
      $result = collect(DB::select(
        "SELECT 
        `".$dbp."text_1` as text_1,
        `".$dbp."text_2` as text_2,
        `".$dbp."text_3` as text_3,
        `".$dbp."text_4` as text_4,
        `".$dbp."text_5` as text_5,
        `".$dbp."text_6` as text_6,
        `".$dbp."text_7` as text_7,
        `".$dbp."text_8` as text_8,
        `".$dbp."text_9` as text_9,
        `".$dbp."text_10` as text_10
        FROM page_elements LIMIT 1"
      ))->first();
    elseif($action=='sitemap'):
      $result = collect(DB::select(
        "SELECT `indexing`,
        `".$dbp."meta_title` as meta_title,
        `".$dbp."meta_keys` as meta_keys,
        `".$dbp."meta_desc` as meta_desc,
        `".$dbp."text_1` as text_1,
        `".$dbp."text_2` as text_2,
        `".$dbp."text_3` as text_3,
        `".$dbp."text_4` as text_4,
        `".$dbp."text_5` as text_5,
        `".$dbp."text_6` as text_6
        FROM page_sitemap LIMIT 1"
      ))->first();
    elseif($action=='about'):
      $result = collect(DB::select(
        "SELECT `indexing`,
        `".$dbp."meta_title` as meta_title,
        `".$dbp."meta_keys` as meta_keys,
        `".$dbp."meta_desc` as meta_desc,
        `".$dbp."text_1` as text_1,
        `".$dbp."text_2` as text_2,
        `".$dbp."text_3` as text_3,
        `".$dbp."text_4` as text_4,
        `".$dbp."text_5` as text_5,
        `".$dbp."text_6` as text_6,
        `".$dbp."text_7` as text_7,
        `".$dbp."text_8` as text_8,
        `".$dbp."text_9` as text_9,
        `".$dbp."text_10` as text_10,
        `".$dbp."text_11` as text_11,
        `".$dbp."text_12` as text_12,
        `".$dbp."text_13` as text_13,
        `".$dbp."text_14` as text_14,
        `".$dbp."text_15` as text_15,
        `".$dbp."text_16` as text_16,
        `".$dbp."text_17` as text_17,
        `".$dbp."text_18` as text_18,
        `".$dbp."text_19` as text_19,
        `".$dbp."text_20` as text_20,
        `".$dbp."text_21` as text_21,
        `".$dbp."text_22` as text_22,
        `".$dbp."text_23` as text_23,
        `".$dbp."text_24` as text_24,
        `".$dbp."text_25` as text_25,
        `".$dbp."text_26` as text_26,
        `".$dbp."text_27` as text_27,
        `".$dbp."text_28` as text_28,
        `".$dbp."text_29` as text_29,
        `".$dbp."text_30` as text_30,
        `".$dbp."text_31` as text_31,
        `".$dbp."text_32` as text_32,
        `".$dbp."text_33` as text_33,
        `".$dbp."text_34` as text_34,
        `".$dbp."text_35` as text_35,
        `".$dbp."text_36` as text_36,
        `".$dbp."text_37` as text_37,
        `".$dbp."text_38` as text_38,
        `".$dbp."text_39` as text_39,
        `".$dbp."text_40` as text_40,
        `".$dbp."text_41` as text_41,
        `".$dbp."text_42` as text_42,
        `".$dbp."text_43` as text_43,
        `".$dbp."text_44` as text_44,
        `".$dbp."text_45` as text_45,
        `".$dbp."text_46` as text_46,
        `".$dbp."text_47` as text_47,
        `".$dbp."text_48` as text_48,
        `".$dbp."text_49` as text_49,
        `".$dbp."text_50` as text_50,
        `".$dbp."text_51` as text_51
        FROM page_about LIMIT 1"
      ))->first();
    elseif($action=='search'):
      $result = collect(DB::select(
        "SELECT `indexing`,
        `".$dbp."meta_title` as meta_title,
        `".$dbp."meta_keys` as meta_keys,
        `".$dbp."meta_desc` as meta_desc,
        `".$dbp."text_1` as text_1,
        `".$dbp."text_2` as text_2,
        `".$dbp."text_3` as text_3,
        `".$dbp."text_4` as text_4,
        `".$dbp."text_5` as text_5,
        `".$dbp."text_6` as text_6,
        `".$dbp."text_7` as text_7,
        `".$dbp."text_8` as text_8,
        `".$dbp."text_9` as text_9,
        `".$dbp."text_10` as text_10,
        `".$dbp."text_11` as text_11
        FROM page_search LIMIT 1"
      ))->first();
    elseif($action=='contacts'):
      $result = collect(DB::select(
        "SELECT `indexing`,
        `".$dbp."meta_title` as meta_title,
        `".$dbp."meta_keys` as meta_keys,
        `".$dbp."meta_desc` as meta_desc,
        `".$dbp."text_1` as text_1,
        `".$dbp."text_2` as text_2,
        `".$dbp."text_3` as text_3,
        `".$dbp."text_4` as text_4,
        `".$dbp."text_5` as text_5,
        `".$dbp."text_6` as text_6,
        `".$dbp."text_7` as text_7,
        `".$dbp."text_8` as text_8,
        `".$dbp."text_9` as text_9,
        `".$dbp."text_10` as text_10,
        `".$dbp."text_11` as text_11,
        `".$dbp."text_12` as text_12,
        `".$dbp."text_13` as text_13,
        `".$dbp."text_14` as text_14,
        `".$dbp."text_15` as text_15,
        `".$dbp."text_16` as text_16,
        `".$dbp."text_17` as text_17,
        `".$dbp."text_18` as text_18,
        `".$dbp."text_19` as text_19,
        `".$dbp."text_20` as text_20,
        `".$dbp."text_21` as text_21,
        `".$dbp."text_22` as text_22,
        `".$dbp."text_23` as text_23,
        `".$dbp."text_24` as text_24,
        `".$dbp."text_25` as text_25,
        `".$dbp."text_26` as text_26,
        `".$dbp."text_27` as text_27
        FROM page_contacts LIMIT 1"
      ))->first();
    elseif($action=='posts'):
      $result = collect(DB::select(
        "SELECT `indexing`,
        `".$dbp."meta_title` as meta_title,
        `".$dbp."meta_keys` as meta_keys,
        `".$dbp."meta_desc` as meta_desc,
        `".$dbp."text_1` as text_1,
        `".$dbp."text_2` as text_2,
        `".$dbp."text_3` as text_3,
        `".$dbp."text_4` as text_4,
        `".$dbp."text_5` as text_5
        FROM page_posts LIMIT 1"
      ))->first();
    elseif($action=='post'):
      $result = collect(DB::select(
        "SELECT `indexing`,
        `".$dbp."meta_title` as meta_title,
        `".$dbp."meta_keys` as meta_keys,
        `".$dbp."meta_desc` as meta_desc,
        `".$dbp."text_1` as text_1,
        `".$dbp."text_2` as text_2,
        `".$dbp."text_3` as text_3,
        `".$dbp."text_4` as text_4,
        `".$dbp."text_5` as text_5
        FROM page_post LIMIT 1"
      ))->first();
    elseif($action=='promotions'):
      $result = collect(DB::select(
        "SELECT `indexing`,
        `".$dbp."meta_title` as meta_title,
        `".$dbp."meta_keys` as meta_keys,
        `".$dbp."meta_desc` as meta_desc,
        `".$dbp."text_1` as text_1,
        `".$dbp."text_2` as text_2,
        `".$dbp."text_3` as text_3,
        `".$dbp."text_4` as text_4,
        `".$dbp."text_5` as text_5
        FROM page_promotions LIMIT 1"
      ))->first();
    elseif($action=='promotion'):
      $result = collect(DB::select(
        "SELECT `indexing`,
        `".$dbp."meta_title` as meta_title,
        `".$dbp."meta_keys` as meta_keys,
        `".$dbp."meta_desc` as meta_desc,
        `".$dbp."text_1` as text_1,
        `".$dbp."text_2` as text_2,
        `".$dbp."text_3` as text_3,
        `".$dbp."text_4` as text_4,
        `".$dbp."text_5` as text_5
        FROM page_promotion LIMIT 1"
      ))->first();
    elseif($action=='industries'):
      $result = collect(DB::select(
        "SELECT `indexing`,
        `".$dbp."meta_title` as meta_title,
        `".$dbp."meta_keys` as meta_keys,
        `".$dbp."meta_desc` as meta_desc,
        `".$dbp."text_1` as text_1,
        `".$dbp."text_2` as text_2,
        `".$dbp."text_3` as text_3,
        `".$dbp."text_4` as text_4,
        `".$dbp."text_5` as text_5
        FROM page_industries LIMIT 1"
      ))->first();
    elseif($action=='industry'):
      $result = collect(DB::select(
        "SELECT `indexing`,
        `".$dbp."meta_title` as meta_title,
        `".$dbp."meta_keys` as meta_keys,
        `".$dbp."meta_desc` as meta_desc,
        `".$dbp."text_1` as text_1,
        `".$dbp."text_2` as text_2,
        `".$dbp."text_3` as text_3,
        `".$dbp."text_4` as text_4,
        `".$dbp."text_5` as text_5
        FROM page_industry LIMIT 1"
      ))->first();
    elseif($action=='solution'):
      $result = collect(DB::select(
        "SELECT `indexing`,
        `".$dbp."meta_title` as meta_title,
        `".$dbp."meta_keys` as meta_keys,
        `".$dbp."meta_desc` as meta_desc,
        `".$dbp."text_1` as text_1,
        `".$dbp."text_2` as text_2,
        `".$dbp."text_3` as text_3,
        `".$dbp."text_4` as text_4,
        `".$dbp."text_5` as text_5
        FROM page_industry_solution LIMIT 1"
      ))->first();
    elseif($action=='team'):
      $result = collect(DB::select(
        "SELECT `indexing`,
        `".$dbp."meta_title` as meta_title,
        `".$dbp."meta_keys` as meta_keys,
        `".$dbp."meta_desc` as meta_desc,
        `".$dbp."text_1` as text_1,
        `".$dbp."text_2` as text_2,
        `".$dbp."text_3` as text_3,
        `".$dbp."text_4` as text_4,
        `".$dbp."text_5` as popup_text_5,
        `".$dbp."text_6` as popup_text_6,
        `".$dbp."text_7` as popup_text_7,
        `".$dbp."text_8` as popup_text_8,
        `".$dbp."text_9` as popup_text_9,
        `".$dbp."text_10` as popup_text_10,
        `".$dbp."text_11` as popup_text_11,
        `".$dbp."text_12` as popup_text_12,
        `".$dbp."text_13` as popup_text_13,
        `".$dbp."text_14` as popup_text_14
        FROM page_team LIMIT 1"
      ))->first();
    elseif($action=='team_popup'):
      $result = collect(DB::select(
        "SELECT 
        `".$dbp."text_5` as popup_text_5,
        `".$dbp."text_6` as popup_text_6,
        `".$dbp."text_7` as popup_text_7,
        `".$dbp."text_8` as popup_text_8,
        `".$dbp."text_9` as popup_text_9,
        `".$dbp."text_10` as popup_text_10,
        `".$dbp."text_11` as popup_text_11,
        `".$dbp."text_12` as popup_text_12,
        `".$dbp."text_13` as popup_text_13,
        `".$dbp."text_14` as popup_text_14
        FROM page_team LIMIT 1"
      ))->first();
    elseif($action=='vacancies' || $action=='vacancy'):
      $result = collect(DB::select(
        "SELECT `indexing`,
        `".$dbp."meta_title` as meta_title,
        `".$dbp."meta_keys` as meta_keys,
        `".$dbp."meta_desc` as meta_desc,
        `".$dbp."text_1` as text_1,
        `".$dbp."text_2` as text_2,
        `".$dbp."text_3` as text_3,
        `".$dbp."text_4` as text_4,
        `".$dbp."text_5` as text_5,
        `".$dbp."text_6` as text_6,
        `".$dbp."text_7` as text_7
        FROM page_career LIMIT 1"
      ))->first();
    elseif($action=='services'):
      $result = collect(DB::select(
        "SELECT `indexing`,
        `".$dbp."meta_title` as meta_title,
        `".$dbp."meta_keys` as meta_keys,
        `".$dbp."meta_desc` as meta_desc,
        `".$dbp."text_1` as text_1,
        `".$dbp."text_2` as text_2,
        `".$dbp."text_3` as text_3,
        `".$dbp."text_4` as text_4,
        `".$dbp."text_5` as text_5
        FROM page_services LIMIT 1"
      ))->first();
    elseif($action=='service'):
      $result = collect(DB::select(
        "SELECT `indexing`,
        `".$dbp."meta_title` as meta_title,
        `".$dbp."meta_keys` as meta_keys,
        `".$dbp."meta_desc` as meta_desc,
        `".$dbp."text_1` as text_1,
        `".$dbp."text_2` as text_2,
        `".$dbp."text_3` as text_3,
        `".$dbp."text_4` as text_4,
        `".$dbp."text_5` as text_5,
        `".$dbp."text_6` as text_6,
        `".$dbp."text_7` as text_7,
        `".$dbp."text_8` as text_8,
        `".$dbp."text_9` as text_9,
        `".$dbp."text_10` as text_10,
        `".$dbp."text_11` as text_11,
        `".$dbp."text_12` as text_12,
        `".$dbp."text_13` as text_13,
        `".$dbp."text_14` as text_14,
        `".$dbp."text_15` as text_15,
        `".$dbp."text_16` as text_16,
        `".$dbp."text_17` as text_17
        FROM page_service LIMIT 1"
      ))->first();
    elseif($action=='support'):
      $result = collect(DB::select(
        "SELECT `indexing`,
        `".$dbp."meta_title` as meta_title,
        `".$dbp."meta_keys` as meta_keys,
        `".$dbp."meta_desc` as meta_desc,
        `".$dbp."text_1` as text_1,
        `".$dbp."text_2` as text_2,
        `".$dbp."text_3` as text_3,
        `".$dbp."text_4` as text_4,
        `".$dbp."text_5` as text_5,
        `".$dbp."text_6` as text_6,
        `".$dbp."text_7` as text_7,
        `".$dbp."text_8` as text_8,
        `".$dbp."text_9` as text_9,
        `".$dbp."text_10` as text_10,
        `".$dbp."text_11` as text_11,
        `".$dbp."text_12` as text_12,
        `".$dbp."text_13` as text_13,
        `".$dbp."text_14` as text_14,
        `".$dbp."text_15` as text_15,
        `".$dbp."text_16` as text_16,
        `".$dbp."text_17` as text_17,
        `".$dbp."text_18` as text_18,
        `".$dbp."text_19` as text_19,
        `".$dbp."text_20` as text_20,
        `".$dbp."text_21` as text_21,
        `".$dbp."text_22` as text_22,
        `".$dbp."text_23` as text_23,
        `".$dbp."text_24` as text_24,
        `".$dbp."text_25` as text_25,
        `".$dbp."text_26` as text_26,
        `".$dbp."text_27` as text_27,
        `".$dbp."text_28` as text_28,
        `".$dbp."text_29` as text_29,
        `".$dbp."text_30` as text_30,
        `".$dbp."text_31` as text_31,
        `".$dbp."text_32` as text_32,
        `".$dbp."text_33` as text_33,
        `".$dbp."text_34` as text_34
        FROM page_support LIMIT 1"
      ))->first();
    elseif($action=='ticket'):
      $result = collect(DB::select(
        "SELECT `indexing`,
        `".$dbp."meta_title` as meta_title,
        `".$dbp."meta_keys` as meta_keys,
        `".$dbp."meta_desc` as meta_desc,
        `".$dbp."text_1` as text_1,
        `".$dbp."text_2` as text_2,
        `".$dbp."text_3` as text_3,
        `".$dbp."text_4` as text_4,
        `".$dbp."text_5` as text_5,
        `".$dbp."text_6` as text_6,
        `".$dbp."text_7` as text_7,
        `".$dbp."text_8` as text_8,
        `".$dbp."text_9` as text_9,
        `".$dbp."text_10` as text_10,
        `".$dbp."text_11` as text_11,
        `".$dbp."text_12` as text_12,
        `".$dbp."text_13` as text_13,
        `".$dbp."text_14` as text_14,
        `".$dbp."text_15` as text_15,
        `".$dbp."text_16` as text_16,
        `".$dbp."text_17` as text_17,
        `".$dbp."text_18` as text_18,
        `".$dbp."text_19` as text_19,
        `".$dbp."text_20` as text_20,
        `".$dbp."text_21` as text_21,
        `".$dbp."text_22` as text_22,
        `".$dbp."text_23` as text_23,
        `".$dbp."text_24` as text_24,
        `".$dbp."text_25` as text_25,
        `".$dbp."text_26` as text_26,
        `".$dbp."text_27` as text_27,
        `".$dbp."text_28` as text_28,
        `".$dbp."text_29` as text_29,
        `".$dbp."text_30` as text_30,
        `".$dbp."text_31` as text_31,
        `".$dbp."text_32` as text_32
        FROM page_support_ticket LIMIT 1"
      ))->first();
    elseif($action=='ajax'):
      $result = collect(DB::select(
        "SELECT
        `".$dbp."text_1` as '1',
        `".$dbp."text_2` as '2',
        `".$dbp."text_3` as '3',
        `".$dbp."text_4` as '4',
        `".$dbp."text_5` as '5',
        `".$dbp."text_6` as '6',
        `".$dbp."text_7` as '7',
        `".$dbp."text_8` as '8',
        `".$dbp."text_9` as '9',
        `".$dbp."text_10` as '10',
        `".$dbp."text_11` as '11',
        `".$dbp."text_12` as '12',
        `".$dbp."text_13` as '13',
        `".$dbp."text_14` as '14',
        `".$dbp."text_15` as '15',
        `".$dbp."text_16` as '16',
        `".$dbp."text_17` as '17',
        `".$dbp."text_18` as '18',
        `".$dbp."text_19` as '19',
        `".$dbp."text_20` as '20',
        `".$dbp."text_21` as '21',
        `".$dbp."text_22` as '22',
        `".$dbp."text_23` as '23',
        `".$dbp."text_24` as '24',
        `".$dbp."text_25` as '25',
        `".$dbp."text_26` as '26',
        `".$dbp."text_27` as '27',
        `".$dbp."text_28` as '28',
        `".$dbp."text_29` as '29',
        `".$dbp."text_30` as '30'
        FROM page_responses LIMIT 1"
      ))->first();
    else:
      $result = collect(DB::select(
        "SELECT `index` as indexing,
        `".$dbp."meta_title` as meta_title,
        `".$dbp."meta_keys` as meta_keys,
        `".$dbp."meta_desc` as meta_desc
        FROM total_config LIMIT 1"
      ))->first();
    endif;
    return $result;
  }
}
