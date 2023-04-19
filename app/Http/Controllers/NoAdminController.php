<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Routing\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Response;
use DB;
use \Firebase\JWT\JWT;
use App\Models\Admin;
use App\Models\Post;
use App\Models\Game;
use App\Models\Page;
use App\Models\PostComment;
use App\Models\Order;
use App\Models\Faq;
use App\Models\OurGuarantees;
use App\Models\OurWorks;
use App\Models\OurReviews;
use App\Models\OurStaff;
use App\Models\Slide;
use App\Models\Promocode;
use App\Models\Ads;

class NoAdminController extends Controller
{
    public $key = 'odpaipwdaodjidwa-!kkwd9_!410924890184AAXz';
    public $user = null;
    public $response = ['status'=>'failed', 'message'=>null, 'error'=>null ];
    public $nopriv = ['login','translate','tests','storeFile'];

    public function __construct(Request $request, Route $route)  {
        $controller = explode('@', $route->getActionName());
        $action = array_pop($controller);
        $token = $request->header('Authorization');
        $this->user = $this->token_to_user($token);
        if(!$this->user && !in_array($action, $this->nopriv)):
            return response('unauthorized', 401);
        endif;
    }

    public function tests() {
        $posts = DB::select("SELECT `id`, `text` FROM posts");
        foreach($posts as &$post):
            preg_match_all('/ src="data:image\/.*base64,([^"]+)"/', $post->text, $matches);
            if($matches[1]):
                $path = Storage::disk('posts')->path(null);
                foreach($matches[1] as $i => $image):
                    $filename = self::upload_base64_file($image, $path);
                    if($filename) $post->text = str_replace($matches[0][$i], ' src="'.url('/public/img/posts/'.$filename).'" ', $post->text);
                endforeach;
            endif;  
            DB::select("UPDATE posts SET `text` = '".str_replace("'", "\'", $post->text)."' WHERE id = $post->id");      
        endforeach;
        var_dump($posts);
        exit();
    }

    public function storeFile($disk, Request $request) {
        $image = $request->file('files');
        $disk = 'posts';
        if($image):
            if(self::sanitizefile($image)):
                $filename = $image->store('', $disk);
                $this->response['url'] = url("/public/img/$disk/$filename");
                $this->response['status'] = 'ok';
            endif;
        endif;
        return response($this->response, 200);
    }

    public function menu() {
        if($this->user->permissions == 'grant all'):
            $conditions = '';
        else:
            $permissions = is_array($this->user->permissions) ? (array)$this->user->permissions : [0];
            $allowed = array_map(function($e) { return (int)$e; }, array_values($permissions));
            $conditions = ' AND m.id IN ('.implode(',',$allowed).')';
        endif;
        $this->response['conditions'] = $this->user->permissions;
        $menus = DB::select("SELECT m.id, m.name, m.link, m.icon, m.shortcut  FROM landing_noadmin_menus as m WHERE display = 1 AND nested_in = 0 $conditions ORDER BY display_id");
        foreach($menus as &$item):
            $item->nestings = DB::select("SELECT m.id, m.name, m.link, m.icon, m.shortcut  FROM landing_noadmin_menus as m WHERE display = 1 AND nested_in = ? $conditions ORDER BY display_id", [$item->id]);
        endforeach;
        $this->response['status'] = 'ok';
        $this->response['list'] = $menus;
        return response($this->response, 200);
    }

    public function updateUser($id, request $request) {
        $id = $id == 'me' ? $this->user->id : intval($id);
        $params = $request->all();
        $image = $request->file('files');
        $user = DB::table('landing_noadmin_users')->where('id', $id)->first();
        if($user):
            if($image):
              if(self::sanitizefile($image)):
                $filename = $image->store('', 'users');
                $params['avatar'] = $filename;
                $avatar = Storage::disk('users')->path($filename);
                self::resize_image($avatar, 100, 100);
                Storage::disk('users')->delete($user->avatar);
              endif;
            else:
                unset($params['avatar']);
            endif;
            $data = [];
            if(array_key_exists('is_approved', $params)) $data['is_approved'] = boolint($params['is_approved']);
            if(array_key_exists('type', $params)) $data['type'] = boolint($params['type']);
            if(array_key_exists('is_blocked', $params)) $data['is_blocked'] = boolint($params['is_blocked']);
            if(array_key_exists('avatar', $params)) $data['avatar'] = str_replace("'","\'",$params['avatar']);
            if(array_key_exists('login', $params)) $data['login'] = str_replace("'","\'",$params['login']);
            if(array_key_exists('email', $params)) $data['email'] = str_replace("'","\'",$params['email']);
            if(array_key_exists('pwd', $params)) $data['password'] = md5($params['pwd']);
            if(array_key_exists('avatar', $params)) $data['avatar'] = str_replace("'","\'",$params['avatar']);
            if(array_key_exists('nick_name', $params)) $data['nick_name'] = str_replace("'","\'",$params['nick_name']);
            if(array_key_exists('language', $params)) $data['language'] = str_replace("'","\'",$params['language']);
            if(array_key_exists('country', $params)) $data['country'] = str_replace("'","\'",$params['country']);
            if(array_key_exists('city', $params)) $data['city'] = str_replace("'","\'",$params['city']);
            if(array_key_exists('zip', $params)) $data['zip'] = str_replace("'","\'",$params['zip']);
            if(array_key_exists('phone', $params)) $data['phone'] = str_replace("'","\'",$params['phone']);
            if(array_key_exists('skype', $params)) $data['skype'] = str_replace("'","\'",$params['skype']);
            if(array_key_exists('viber', $params)) $data['viber'] = str_replace("'","\'",$params['viber']);
            if(array_key_exists('discord', $params)) $data['discord'] = str_replace("'","\'",$params['discord']);
            if(array_key_exists('vkontakte', $params)) $data['vkontakte'] = str_replace("'","\'",$params['vkontakte']);
            if(array_key_exists('facebook', $params)) $data['facebook'] = str_replace("'","\'",$params['facebook']);
            if(array_key_exists('instagram', $params)) $data['instagram'] = str_replace("'","\'",$params['instagram']);
            if(array_key_exists('youtube', $params)) $data['youtube'] = str_replace("'","\'",$params['youtube']);
            if(array_key_exists('twitter', $params)) $data['twitter'] = str_replace("'","\'",$params['twitter']);
            if(array_key_exists('referrer_hash', $params)) $data['referrer_hash'] = str_replace("'","\'",$params['referrer_hash']);
            if(array_key_exists('recovery_hash', $params)) $data['recovery_hash'] = str_replace("'","\'",$params['recovery_hash']);
            if(array_key_exists('permissions', $params)) $data['permissions'] = str_replace("'","\'",(is_array($params['permissions']) ? json_encode($params['permissions']) : $params['permissions']));
            $data['updated_at'] = date('Y-m-d H:i:s');
            DB::table('landing_noadmin_users')->where('id', $id)->update($data);
            $this->response['status'] = 'ok';
            return response($this->response, 200);
        else:
            return response($this->response, 202);
        endif;
    }

    public function addUser(Request $request) {
        $login = $request->get('login') ?? '';
        if(!DB::table('landing_noadmin_users')->where(['login' => $login])->first()):
            $id = DB::table('landing_noadmin_users')->insertGetId(['login' => $login]);
            $this->response['status'] = 'ok';
            $this->response['user'] = DB::table('landing_noadmin_users')->where('id', $id)->first();
            return response($this->response, 200);
        else:
            return response($this->response, 202);
        endif;
    }

    public function removeUser($id) {
        $id = (int)$id;
        $item = DB::table('landing_noadmin_users')->where('id', $id)->first();
        if($item):
            Storage::disk('users')->delete($item->avatar);
            DB::select("DELETE FROM landing_noadmin_users WHERE id = $id");
            $this->response['status'] = 'ok';
            return response($this->response, 200);
        else:
            return response($this->response, 202);
        endif;
    }

    public function getUser($id) {
        $id = $id == 'me' ? $this->user->id : intval($id);
        $types = DB::select("SELECT id, name, permissions FROM landing_noadmin_users_types");
        foreach($types as &$e) $e->permissions = @json_decode($e->permissions, true);
        $menus = DB::table('landing_noadmin_menus')->where('nested_in', 0)->select('id','name','nested_in')->get();
        foreach($menus as &$e):
            $e->nestings = DB::table('landing_noadmin_menus')->where('nested_in', $e->id)->select('id','name','nested_in')->get();
        endforeach;
        $user = DB::table('landing_noadmin_users')->where('id', $id)->first();
        if($user):
            $user->permissions = $user->permissions == 'grant all' ? $user->permissions : @json_decode($user->permissions, true) ?? [];
        endif;
        $this->response['menus'] = $menus;
        $this->response['user'] = $user;
        $this->response['types'] = $types;
        $this->response['status'] = 'ok';
        return response($this->response, 200);
    }

    public function getUsers(Request $request) {
        $params = $request->all();
        $sortfields = ['id','type','created_at','login','email'];
        $page = $params['page'] ?? 1;
        $perpage = 15;
        $offset = $page * $perpage - $perpage;
        $asc = boolint($params['asc'] ?? true);
        $type = $params['type'] ?? null;
        $block = $params['block'] ?? null;
        $approved = $params['approved'] ?? null;
        $keyword = trim($params['keyword'] ?? '');
        $sort = $params['sort'] ?? null;
        $sortfield = in_array($params['sort'], $sortfields) ? $params['sort'] : $sortfields[0];
        $sortdir = $asc ? 'ASC' : 'DESC';
        $limits = "LIMIT $offset, $perpage";
        $conditions = '';
        if(is_numeric($approved)) $conditions .= " AND u.is_approved = " . boolint($approved);
        if(is_numeric($block)) $conditions .= " AND u.is_blocked = " . boolint($block);
        if(is_numeric($type)) $conditions .= " AND u.type = " . boolint($type);
        if(strlen($keyword)) $conditions .= " AND (u.login LIKE '%$keyword%' OR u.nick_name LIKE '%$keyword%')";
        $types = DB::select("SELECT id, name FROM landing_noadmin_users_types");
        $users = DB::select("SELECT u.*, (SELECT name FROM landing_noadmin_users_types WHERE id = u.type) as type_name FROM landing_noadmin_users as u WHERE 1 $conditions ORDER BY u.$sortfield $sortdir $limits");
        $total = DB::table('landing_noadmin_users')->selectRaw('count(id) as total')->first()->total;
        $this->response['list'] = $users;
        $this->response['types'] = $types;
        $this->response['total'] = $total;
        $this->response['pagination'] = self::paginate($page, $perpage, ceil($total / $perpage), 3);
        $this->response['status'] = 'ok';
        return response($this->response, 200);
    }

    public function getUserTypes(Request $request) {
        $params = $request->all();
        $sortfields = ['id','created_at','name','users_in'];
        $page = $params['page'] ?? 1;
        $perpage = 15;
        $offset = $page * $perpage - $perpage;
        $asc = boolint($params['asc'] ?? true);
        $keyword = trim($params['keyword'] ?? '');
        $sort = $params['sort'] ?? null;
        $sortfield = in_array($params['sort'], $sortfields) ? $params['sort'] : $sortfields[0];
        $sortdir = $asc ? 'ASC' : 'DESC';
        $limits = "LIMIT $offset, $perpage";
        $conditions = '';
        if(boolint($keyword)&&strlen($keyword)) $conditions .= " AND (u.name LIKE '%$keyword%')";
        $types = DB::select("SELECT u.*, (SELECT count(id) FROM landing_noadmin_users WHERE type = u.id) as users_in FROM landing_noadmin_users_types as u WHERE 1 $conditions ORDER BY u.$sortfield $sortdir $limits");
        $total = DB::table('landing_noadmin_users_types')->selectRaw('count(id) as total')->first()->total;
        $this->response['list'] = $types;
        $this->response['total'] = $total;
        $this->response['pagination'] = self::paginate($page, $perpage, ceil($total / $perpage), 3);
        $this->response['status'] = 'ok';
        return response($this->response, 200);
    }

    public function getUserType($id) {
        $id = intval($id);
        $users = [];
        $menus = DB::table('landing_noadmin_menus')->where('nested_in', 0)->where('display',1)->select('id','name','nested_in')->get();
        foreach($menus as &$e):
            $e->nestings = DB::table('landing_noadmin_menus')->where('nested_in', $e->id)->where('display',1)->select('id','name','nested_in')->get();
        endforeach;
        $type = DB::table('landing_noadmin_users_types')->where('id', $id)->first();
        if($type):
            $type->permissions = @json_decode($type->permissions, true) ?? [];
            $users = DB::table('landing_noadmin_users')->where('type', $type->id)->get();
        endif;
        $this->response['menus'] = $menus;
        $this->response['type'] = $type;
        $this->response['users'] = $users;
        $this->response['status'] = 'ok';
        return response($this->response, 200);
    }

    public function updateUserType($id, request $request) {
        $params = $request->all();
        $type = DB::table('landing_noadmin_users_types')->where('id', $id)->first();
        if($type):
            $data = [];
            if(array_key_exists('name', $params)) $data['name'] = str_replace("'","\'",$params['name']);
            if(array_key_exists('permissions', $params)) $data['permissions'] = str_replace("'","\'",(is_array($params['permissions']) ? json_encode($params['permissions']) : $params['permissions']));
            $data['updated_at'] = date('Y-m-d H:i:s');
            DB::table('landing_noadmin_users_types')->where('id', $id)->update($data);
            $this->response['status'] = 'ok';
            return response($this->response, 200);
        else:
            return response($this->response, 202);
        endif;
    }

    public function removeUserType($id) {
        $id = (int)$id;
        $item = DB::table('landing_noadmin_users_types')->where('id', $id)->first();
        if($item):
            DB::select("DELETE FROM landing_noadmin_users_types WHERE id = $id");
            $this->response['status'] = 'ok';
            return response($this->response, 200);
        else:
            return response($this->response, 202);
        endif;
    }

    public function createUserType(Request $request) {
        $name = $request->get('name') ?? '';
        $id = DB::table('landing_noadmin_users_types')->insertGetId(['name' => $name]);
        if($id):
            $this->response['status'] = 'ok';
            $this->response['type'] = DB::table('landing_noadmin_users_types')->where('id', $id)->first();
            return response($this->response, 200);
        else:
            return response($this->response, 202);
        endif;
    }

    public function getSettings() {
        $items = DB::select("SELECT * FROM landing_settings");
        $this->response['items'] = $items;
        $this->response['status'] = 'ok';
        return response($this->response, 200);
    }

    public function removeSettings($id) {
        $id = (int)$id;
        $page = DB::select("SELECT * FROM landing_settings WHERE id = $id LIMIT 1");
        if($page):
            DB::select("DELETE FROM landing_settings WHERE id = $id");
            $this->response['status'] = 'ok';
            return response($this->response, 200);
        else:
            return response($this->response, 202);
        endif;
    }

    public function saveSettings(Request $request) {
        $params = $request->all();
        foreach($params as $item):
            if($item['id'] > 0):
                $conditions = '';
                if($item['type']==3) $item['value'] = boolint($item['value']);
                if(array_key_exists('name', $item)) $conditions .= "name = '".str_replace("'","\'",$item['name'])."',";
                if(array_key_exists('title', $item)) $conditions .= "title = '".str_replace("'","\'",$item['title'])."',";
                if(array_key_exists('value', $item)) $conditions .= "value = '".str_replace("'","\'",$item['value'])."',";
                if(array_key_exists('type', $item)) $conditions .= "type = '".boolint($item['type'])."',";
                $conditions .= "deletable = '".boolint($item['deletable'] ?? 0)."'";
                DB::select("UPDATE landing_settings SET $conditions WHERE id = ?",[$item['id']]);
            else:
                $conditions = '';
                $conditions .= "'".str_replace("'","\'",$item['name'] ?? '')."',";
                $conditions .= "'".str_replace("'","\'",$item['title'] ?? '')."',";
                $conditions .= "'".str_replace("'","\'",$item['value'] ?? '')."',";
                $conditions .= "'".boolint($item['type'] ?? 1)."',";
                $conditions .= "'".boolint($item['deletable'] ?? 0)."'";                
                DB::select("INSERT INTO landing_settings (name,title,value,type,deletable) VALUES ($conditions)");
            endif;
        endforeach;
        $this->response['items'] = DB::select("SELECT * FROM landing_settings");
        $this->response['status'] = 'ok';
        return response($this->response, 200);
    }

    public function getPricelists() {
        $this->response['boosting_timings'] = DB::table('order_boosting_timings')->get(); 
        $this->response['boosting_settings'] = DB::table('order_boosting_settings')->first(); 
        $this->response['boosting_prices'] = DB::table('order_boosting_prices')->get(); 
        $this->response['boosting_types'] = DB::table('order_boosting_quality')->get(); 
        $this->response['medals_settings'] = DB::table('order_medals_settings')->first();
        $this->response['medals_prices'] = DB::table('order_medals_prices')->get();
        $this->response['cali_types'] = DB::table('order_calibration_quality')->get();
        $this->response['cali_settings'] = DB::table('order_calibration_settings')->first();
        $this->response['cali_prices'] = DB::table('order_calibration_prices')->get();
        $this->response['lp_settings'] = DB::table('order_lp_settings')->first();
        $this->response['training_prices'] = DB::table('order_training_prices')->get(); 
        $this->response['training_services'] = DB::table('order_training_services')->get(); 
        $this->response['training_settings'] = DB::table('order_training_settings')->first(); 
        foreach($this->response['cali_types'] as &$item):
            $item->labels = json_decode($item->labels, true) ?? [];
        endforeach;
        foreach($this->response['boosting_types'] as &$item):
            $item->options = array_filter(explode('|', $item->options));
        endforeach;
        $this->response['status'] = 'ok';
        return response($this->response, 200);
    }

    public function addMedalPrice(Request $request) {
        $title = $request->get('title') ?? '';
        $id = DB::table('order_medals_prices')->insertGetId(['title' => $title]);
        if($id):
            $this->response['status'] = 'ok';
            $this->response['item'] = DB::table('order_medals_prices')->where('i', $id)->first();
            return response($this->response, 200);
        else:
            return response($this->response, 202);
        endif;
    }

    public function removeMedalPrice($id) {
        $id = (int)$id;
        $item = DB::table('order_medals_prices')->where('i', $id)->first();
        if($item):
            Storage::disk('medals')->delete($item->image);
            DB::select("DELETE FROM order_medals_prices WHERE i = $id");
            $this->response['status'] = 'ok';
            return response($this->response, 200);
        else:
            return response($this->response, 202);
        endif;
    }

    public function updateMedalPrice($id, request $request) {
        $params = $request->all();
        $image = $request->file('file_medal');
        if($image):
          if(self::sanitizefile($image)):
            $filename = $image->store('', 'medals');
            $params['image'] = $filename;
          endif;
        endif;
        $conditions = '';  
        $item = DB::table('order_medals_prices')->where('i', $id)->first();
        if($item):
            if(array_key_exists('image', $params)) {
                $conditions .= "image = '".str_replace("'","\'",$params['image'])."',";
                Storage::disk('medals')->delete($item->image);
            }
            if(array_key_exists('title', $params)) $conditions .= "title = '".str_replace("'","\'",$params['title'])."',";
            if(array_key_exists('rank', $params)) $conditions .= "rank = '".str_replace("'","\'",$params['rank'])."',";
            if(array_key_exists('id', $params)) $conditions .= "id = '".boolint($params['id'])."',";
            if(array_key_exists('rub', $params)) $conditions .= "rub = '".floatval($params['rub'])."',";
            if(array_key_exists('usd', $params)) $conditions .= "usd = '".floatval($params['usd'])."',";
            $conditions .= "updated_at = '".date('Y-m-d H:i:s')."'";
            DB::select("UPDATE order_medals_prices SET $conditions WHERE i = $id");
            $this->response['status'] = 'ok';
            $this->response['item'] =  DB::table('order_medals_prices')->where('i', $id)->first();
            return response($this->response, 200);
        else:
            return response($this->response, 202);
        endif;
    }

    public function setMedalPrices(Request $request) {
        $params = $request->all();
        $prices = $params['prices'] ?? null;
        $settings = $params['settings'] ?? null;
        if($settings):
            $conditions = '';
            if(array_key_exists('ban_guard_price', $settings)) $conditions .= "ban_guard_price = '".boolint($settings['ban_guard_price'])."',";
            if(array_key_exists('min_hero_pick', $settings)) $conditions .= "min_hero_pick = '".boolint($settings['min_hero_pick'])."',";
            if(array_key_exists('max_hero_pick', $settings)) $conditions .= "max_hero_pick = '".boolint($settings['max_hero_pick'])."',";
            if(array_key_exists('min_hero_ban', $settings)) $conditions .= "min_hero_ban = '".boolint($settings['min_hero_ban'])."',";
            if(array_key_exists('max_hero_ban', $settings)) $conditions .= "max_hero_ban = '".boolint($settings['max_hero_ban'])."',";
            if(array_key_exists('time_margin', $settings)) $conditions .= "time_margin = '".boolint($settings['time_margin'])."',";
            $conditions .= "id = 1";
            DB::select("UPDATE order_medals_settings SET $conditions WHERE id = 1");
        endif;
        if($prices):
            foreach($prices as $e):
                if(!isset($e['i'])) continue;
                $data = [];
                if(array_key_exists('title', $e)) $data['title'] = str_replace("'","\'",$e['title']);
                if(array_key_exists('rank', $e)) $data['rank'] = str_replace("'","\'",$e['rank']);
                if(array_key_exists('id', $e)) $data['id'] = boolint($e['id']);
                if(array_key_exists('rub', $e)) $data['rub'] = floatval($e['rub']);
                if(array_key_exists('usd', $e)) $data['usd'] = floatval($e['usd']);
                if(array_key_exists('time', $e)) $data['time'] = floatval($e['time']);
                if(array_key_exists('formula_from', $e)) $data['formula_from'] = floatval($e['formula_from']);
                if(array_key_exists('formula_till', $e)) $data['formula_till'] = floatval($e['formula_till']);
                DB::table('order_medals_prices')->where('i', $e['i'])->update($data);
            endforeach;
        endif;
        if(sizeof($params)):
            $this->response['status'] = 'ok';
            return response($this->response, 200);
        else:
            return response($this->response, 202);
        endif;
    }

    public function setTrainingPrices(Request $request) {
        $params = $request->all();
        $prices = $params['prices'] ?? null;
        $services = $params['services'] ?? null;
        $settings = $params['settings'] ?? null;
        if($settings):
            $conditions = '';
            if(array_key_exists('min', $settings)) $conditions .= "min = '".boolint($settings['min'])."',";
            if(array_key_exists('max', $settings)) $conditions .= "max = '".boolint($settings['max'])."',";
            if(array_key_exists('start', $settings)) $conditions .= "start = '".boolint($settings['start'])."',";
            if(array_key_exists('hmin', $settings)) $conditions .= "hmin = '".boolint($settings['hmin'])."',";
            if(array_key_exists('hmax', $settings)) $conditions .= "hmax = '".boolint($settings['hmax'])."',";
            if(array_key_exists('hstart', $settings)) $conditions .= "hstart = '".boolint($settings['hstart'])."',";
            if(array_key_exists('max_hero_pick', $settings)) $conditions .= "max_hero_pick = '".boolint($settings['max_hero_pick'])."',";
            $conditions .= "id = 1";
            DB::select("UPDATE order_training_settings SET $conditions WHERE id = 1");
        endif;
        if($prices):
            $data = [];
            usort($prices, function($a, $b) { return $a['id'] > $b['id']; });
            foreach($prices as $e):
                $data[] = [
                    'hours' => $e['hours'] ?? 1, 
                    'rub' => $e['rub'] ?? 0, 
                    'usd' => $e['usd'] ?? 0,
                ];
            endforeach;
            DB::table('order_training_prices')->truncate();
            DB::table('order_training_prices')->insert($data);
        endif;
        if($services):
            $data = [];
            usort($services, function($a, $b) { return $a['id'] > $b['id']; });
            foreach($services as $e):
                $data[] = [
                    'name' => $e['name'] ?? '', 
                    'rub' => $e['rub'] ?? 0, 
                    'usd' => $e['usd'] ?? 0,
                ];
            endforeach;
            DB::table('order_training_services')->truncate();
            DB::table('order_training_services')->insert($data);
        endif;
        if(sizeof($params)):
            $this->response['status'] = 'ok';
            return response($this->response, 200);
        else:
            return response($this->response, 202);
        endif;
    }

    public function setBoostPrices(Request $request) {
        $params = $request->all();
        $prices = $params['prices'] ?? null;
        $settings = $params['settings'] ?? null;
        $types = $params['types'] ?? null;
        $timings = $params['timings'] ?? null;
        if($settings):
            $conditions = '';
            if(array_key_exists('min', $settings)) $conditions .= "min = '".boolint($settings['min'])."',";
            if(array_key_exists('max', $settings)) $conditions .= "max = '".boolint($settings['max'])."',";
            if(array_key_exists('start', $settings)) $conditions .= "start = '".boolint($settings['start'])."',";
            if(array_key_exists('end', $settings)) $conditions .= "end = '".boolint($settings['end'])."',";
            if(array_key_exists('step', $settings)) $conditions .= "step = '".boolint($settings['step'])."',";
            if(array_key_exists('ban_guard_price', $settings)) $conditions .= "ban_guard_price = '".boolint($settings['ban_guard_price'])."',";
            if(array_key_exists('min_hero_pick', $settings)) $conditions .= "min_hero_pick = '".boolint($settings['min_hero_pick'])."',";
            if(array_key_exists('max_hero_pick', $settings)) $conditions .= "max_hero_pick = '".boolint($settings['max_hero_pick'])."',";
            if(array_key_exists('min_hero_ban', $settings)) $conditions .= "min_hero_ban = '".boolint($settings['min_hero_ban'])."',";
            if(array_key_exists('max_hero_ban', $settings)) $conditions .= "max_hero_ban = '".boolint($settings['max_hero_ban'])."',";
            if(array_key_exists('time_margin', $settings)) $conditions .= "time_margin = '".boolint($settings['time_margin'])."',";
            $conditions .= "id = 1";
            DB::select("UPDATE order_boosting_settings SET $conditions WHERE id = 1");
        endif;        
        if($prices):
            $data = [];
            foreach($prices as $e):
                $data[] = [
                    'from' => $e['from'] ?? 0, 
                    'till' => $e['till'] ?? 0, 
                    'rub' => $e['rub'] ?? 0, 
                    'usd' => $e['usd'] ?? 0,
                    'volume' => $e['volume'] ?? 0, 
                ];
            endforeach;
            usort($data, function($a, $b) { return $a['from'] > $b['from']; });
            DB::table('order_boosting_prices')->truncate();
            DB::table('order_boosting_prices')->insert($data);
        endif; 
        if($timings):
            $data = [];
            foreach($timings as $e):
                $data[] = [
                    'from' => $e['from'] ?? 0, 
                    'till' => $e['till'] ?? 0, 
                    'volume' => $e['volume'] ?? 0, 
                    'hours' => $e['hours'] ?? 0,
                ];
            endforeach;
            usort($data, function($a, $b) { return $a['from'] > $b['from']; });
            DB::table('order_boosting_timings')->truncate();
            DB::table('order_boosting_timings')->insert($data);
        endif;
        if($types):
            foreach($types as $e):
                if(!isset($e['i'])) continue;
                $data = [];
                if(array_key_exists('title', $e)) $data['title'] = str_replace("'","\'",$e['title']);
                if(array_key_exists('currency', $e)) $data['currency'] = str_replace("'","\'",$e['currency']);
                if(array_key_exists('options', $e)) $data['options'] = str_replace("'","\'",implode('|',$e['options'] ?? []));
                if(array_key_exists('id', $e)) $data['id'] = boolint($e['id']);
                if(array_key_exists('display', $e)) $data['display'] = boolint($e['display']);
                if(array_key_exists('rub', $e)) $data['rub'] = floatval($e['rub']);
                if(array_key_exists('usd', $e)) $data['usd'] = floatval($e['usd']);    
                DB::table('order_boosting_quality')->where('i', $e['i'])->update($data);                            
            endforeach;
        endif;        
        if(sizeof($params)):
            $this->response['status'] = 'ok';
            return response($this->response, 200);
        else:
            return response($this->response, 202);
        endif;
    }

    public function addBoostType(Request $request) {
        $title = $request->get('title') ?? '';
        $id = DB::table('order_boosting_quality')->insertGetId(['title' => $title]);
        if($id):
            $this->response['status'] = 'ok';
            $this->response['item'] = DB::table('order_boosting_quality')->where('i', $id)->first();
            return response($this->response, 200);
        else:
            return response($this->response, 202);
        endif;
    }

    public function removeBoostType($id) {
        $id = (int)$id;
        $item = DB::table('order_boosting_quality')->where('i', $id)->first();
        if($item):
            Storage::disk('orders')->delete($item->cover);
            DB::select("DELETE FROM order_boosting_quality WHERE i = $id");
            $this->response['status'] = 'ok';
            return response($this->response, 200);
        else:
            return response($this->response, 202);
        endif;
    }

    public function updateBoostType($id, request $request) {
        $params = $request->all();
        $image = $request->file('file_cover');
        if($image):
          if(self::sanitizefile($image)):
            $filename = $image->store('', 'orders');
            $params['cover'] = $filename;
          endif;
        endif;
        $conditions = '';  
        $item = DB::table('order_boosting_quality')->where('i', $id)->first();
        if($item):
            if(array_key_exists('cover', $params)) {
                $conditions .= "cover = '".str_replace("'","\'",$params['cover'])."',";
                Storage::disk('orders')->delete($item->cover);
            }
            if(array_key_exists('title', $params)) $conditions .= "title = '".str_replace("'","\'",$params['title'])."',";
            if(array_key_exists('currency', $params)) $conditions .= "currency = '".str_replace("'","\'",$params['currency'])."',";
            if(array_key_exists('options', $params)) $conditions .= "options = '".str_replace("'","\'",implode('|',json_decode($params['options'], true) ?? []))."',";
            if(array_key_exists('id', $params)) $conditions .= "id = '".boolint($params['id'])."',";
            if(array_key_exists('display', $params)) $conditions .= "display = '".boolint($params['display'])."',";
            if(array_key_exists('rub', $params)) $conditions .= "rub = '".floatval($params['rub'])."',";
            if(array_key_exists('usd', $params)) $conditions .= "usd = '".floatval($params['usd'])."',";
            $conditions .= "updated_at = '".date('Y-m-d H:i:s')."'";
            DB::select("UPDATE order_boosting_quality SET $conditions WHERE i = $id");
            $this->response['status'] = 'ok';
            $this->response['item'] =  DB::table('order_boosting_quality')->where('i', $id)->first();
            return response($this->response, 200);
        else:
            return response($this->response, 202);
        endif;
    }

    public function setLPPrices(Request $request) {
        $params = $request->all();
        $settings = $params['settings'] ?? null;
        if($settings):
            $conditions = '';
            if(array_key_exists('min', $settings)) $conditions .= "min = '".boolint($settings['min'])."',";
            if(array_key_exists('max', $settings)) $conditions .= "max = '".boolint($settings['max'])."',";
            if(array_key_exists('start', $settings)) $conditions .= "start = '".boolint($settings['start'])."',";
            if(array_key_exists('price', $settings)) $conditions .= "price = '".floatval($settings['price'])."',";
            if(array_key_exists('time_volume', $settings)) $conditions .= "time_volume = '".floatval($settings['time_volume'])."',";
            if(array_key_exists('time_hours', $settings)) $conditions .= "time_hours = '".floatval($settings['time_hours'])."',";
            if(array_key_exists('time_margin', $settings)) $conditions .= "time_margin = '".floatval($settings['time_margin'])."',";
            $conditions .= "id = 1";
            DB::select("UPDATE order_lp_settings SET $conditions WHERE id = 1");
        endif;
        if(sizeof($params)):
            $this->response['status'] = 'ok';
            return response($this->response, 200);
        else:
            return response($this->response, 202);
        endif;
    }

    public function setCalibrationPrices(Request $request) {
        $params = $request->all();
        $prices = $params['prices'] ?? null;
        $types = $params['types'] ?? null;
        $settings = $params['settings'] ?? null;
        if($settings):
            $conditions = '';
            if(array_key_exists('min', $settings)) $conditions .= "min = '".boolint($settings['min'])."',";
            if(array_key_exists('max', $settings)) $conditions .= "max = '".boolint($settings['max'])."',";
            if(array_key_exists('start', $settings)) $conditions .= "start = '".boolint($settings['start'])."',";
            if(array_key_exists('warranty_price', $settings)) $conditions .= "warranty_price = '".floatval($settings['warranty_price'])."',";
            if(array_key_exists('warranty_text', $settings)) $conditions .= "warranty_text = '".str_replace("'","\'",$settings['warranty_text'])."',";
            if(array_key_exists('time_volume', $settings)) $conditions .= "time_volume = '".floatval($settings['time_volume'])."',";
            if(array_key_exists('time_hours', $settings)) $conditions .= "time_hours = '".floatval($settings['time_hours'])."',";
            if(array_key_exists('time_margin', $settings)) $conditions .= "time_margin = '".floatval($settings['time_margin'])."',";
            $conditions .= "id = 1";
            DB::select("UPDATE order_calibration_settings SET $conditions WHERE id = 1");
        endif;
        if(sizeof($types)):
            $data = [];
            foreach($types as $e):
                $data[] = [
                    'title' => $e['title'] ?? '',
                    'labels' => str_replace("'","\'",json_encode($e['labels'] ?? [], JSON_UNESCAPED_UNICODE)),
                    'description' => $e['description'] ?? '',
                    'currency' => $e['currency'] ?? '', 
                    'rub' => boolint($e['rub'] ?? 0), 
                    'usd' => boolint($e['usd'] ?? 0), 
                    'price' => boolint($e['price'] ?? 0), 
                    'display' => boolint($e['display'] ?? 0), 
                    'display_number' => boolint($e['id'] ?? 0),
                ];
            endforeach;
            DB::table('order_calibration_quality')->truncate();
            DB::table('order_calibration_quality')->insert($data);
        endif;
        if(sizeof($prices)):
            $data = [];
            foreach($prices as $e):
                $data[] = [
                    'from' => floatval($e['from'] ?? 0), 
                    'till' => floatval($e['till'] ?? 0), 
                    'rub' => boolint($e['rub'] ?? 0), 
                    'usd' => boolint($e['usd'] ?? 0), 
                ];
            endforeach;
            DB::table('order_calibration_prices')->truncate();
            DB::table('order_calibration_prices')->insert($data);
        endif;
        if(sizeof($params)):
            $this->response['status'] = 'ok';
            return response($this->response, 200);
        else:
            return response($this->response, 202);
        endif;
    }

    public function getStaffs(Request $request) {
        $this->response['pagination'] = [];
        $page = $request->get('page');
        $asc = boolint($request->get('asc'));
        $keyword = $request->get('keyword') !== 'null' ? $request->get('keyword') : null;
        $sortfield = $request->get('sort') ?? 'created_at';
        $conditions = "";
        $sortdir = $asc ? "ASC" : "DESC";
        if($keyword) $conditions .= " AND (our_staff.name LIKE '%$keyword%' OR our_staff.country LIKE '%$keyword%')";
        $items = DB::select("SELECT our_staff.*, cast(our_staff.rating AS UNSIGNED) as rating FROM our_staff WHERE 1 $conditions ORDER BY our_staff.$sortfield $sortdir");
        foreach($items as &$item):
            $item->heroes = json_decode($item->heroes, true);
            $item->lanes = json_decode($item->lanes, true);
        endforeach;
        $this->response['items'] = $items;
        $this->response['status'] = 'ok';
        return response($this->response, 200);
    }

    public function updateStaff($id, request $request) {
        $params = $request->all();
        $image = $request->file('file_avatar');
        if($image):
          if(self::sanitizefile($image)):
            $filename = $image->store('', 'users');
            $params['avatar'] = $filename;
          endif;
        endif;
        $conditions = '';  
        $page = DB::select("SELECT * FROM our_staff WHERE id = $id LIMIT 1");
        if($page):
            if(array_key_exists('avatar', $params)) $conditions .= "avatar = '".str_replace("'","\'",$params['avatar'])."',";
            if(array_key_exists('name', $params)) $conditions .= "name = '".str_replace("'","\'",$params['name'])."',";
            if(array_key_exists('country', $params)) $conditions .= "country = '".str_replace("'","\'",$params['country'])."',";
            if(array_key_exists('heroes', $params)) $conditions .= "heroes = '".str_replace("'","\'",$params['heroes'])."',";
            if(array_key_exists('lanes', $params)) $conditions .= "lanes = '".str_replace("'","\'",$params['lanes'])."',";
            if(array_key_exists('mmr', $params)) $conditions .= "mmr = '".boolint($params['mmr'])."',";
            if(array_key_exists('rating', $params)) $conditions .= "rating = '".boolint($params['rating'])."',";
            if(array_key_exists('display', $params)) $conditions .= "display = '".boolint($params['display'])."',";
            $conditions .= "created_at = '".date('Y-m-d H:i:s')."'";
            DB::select("UPDATE our_staff SET $conditions WHERE id = $id");
            $this->response['status'] = 'ok';
            return response($this->response, 200);
        else:
            return response($this->response, 202);
        endif;
    }

    public function addStaff(Request $request) {
        $user_id = $request->get('id') ?? 0;
        $name = $request->get('nick_name') ?? '';
        $avatar = $request->get('avatar');
        $country = $request->get('country');
        $heroes = $request->get('heroes');
        $lanes = $request->get('lanes');
        $mmr_solo = $request->get('mmr_solo');
        $rating = $request->get('rating');
        $data = ['name' => $name];
        $entity = null;
        if($user_id):
            $entity = OurStaff::where('user_id', $user_id)->first();
        endif;    
        if(!$entity):
            $entity = OurStaff::create($data);
        endif;

        if($heroes) $data['heroes'] = json_encode($heroes);
        if($user_id) $data['user_id'] = $user_id;
        if($mmr_solo) $data['mmr'] = $mmr_solo;
        if($country) $data['country'] = $country;
        if($rating):
            $data['rating'] = ceil(5 * $rating / 3);
        endif;
        if($avatar):
            if($entity) Storage::disk('users')->delete($entity->avatar); 
            $path = Storage::disk('users')->path(null);
            $filename = 'usrimp_' . pathinfo($avatar, PATHINFO_BASENAME);
            if(@copy($avatar, $path.$filename)) $data['avatar'] = $filename;
        endif;

        $entity->update($data);

        if($entity):
            $entity->heroes = json_decode($entity->heroes, true);
            $this->response['status'] = 'ok';
            $this->response['item'] = $entity;
            return response($this->response, 200);
        else:
            return response($this->response, 202);
        endif;
    }

    public function removeStaff($id) {
        $id = (int)$id;
        $entity = collect(DB::select("SELECT * FROM our_staff WHERE id = $id LIMIT 1"))->first();
        if($entity):
            Storage::disk('users')->delete($entity->avatar); 
            DB::select("DELETE FROM our_staff WHERE id = $id");
            $this->response['status'] = 'ok';
            return response($this->response, 200);
        else:
            return response($this->response, 202);
        endif;
    }

    public function getReviews(Request $request) {
        $this->response['pagination'] = [];
        $page = $request->get('page');
        $asc = boolint($request->get('asc'));
        $keyword = $request->get('keyword') !== 'null' ? $request->get('keyword') : null;
        $sortfield = $request->get('sort') ?? 'created_at';
        $conditions = "";
        $sortdir = $asc ? "ASC" : "DESC";
        if($keyword) $conditions .= " AND (our_reviews.author LIKE '%$keyword%' OR our_reviews.message LIKE '%$keyword%')";
        $items = DB::select("SELECT our_reviews.* FROM our_reviews WHERE 1 $conditions ORDER BY our_reviews.$sortfield $sortdir");
        $this->response['items'] = $items;
        $this->response['otypes'] = DB::select("SELECT name, id FROM order_types ORDER BY id");
        $this->response['status'] = 'ok';
        return response($this->response, 200);
    }

    public function updateReview($id, request $request) {
        $params = $request->all();
        $image = $request->file('file_avatar');
        if($image):
          if(self::sanitizefile($image)):
            $filename = $image->store('', 'users');
            $params['avatar'] = $filename;
          endif;
        endif;
        $conditions = '';  
        $page = DB::select("SELECT * FROM our_reviews WHERE id = $id LIMIT 1");
        if($page):
            if(array_key_exists('avatar', $params)) $conditions .= "avatar = '".str_replace("'","\'",$params['avatar'])."',";
            if(array_key_exists('author', $params)) $conditions .= "author = '".str_replace("'","\'",$params['author'])."',";
            if(array_key_exists('message', $params)) $conditions .= "message = '".str_replace("'","\'",$params['message'])."',";
            if(array_key_exists('mark', $params)) $conditions .= "mark = '".boolint($params['mark'])."',";
            if(array_key_exists('order_type', $params)) $conditions .= "order_type = '".boolint($params['order_type'])."',";
            if(array_key_exists('display', $params)) $conditions .= "display = '".boolint($params['display'])."',";
            $conditions .= "published_at = '".date('Y-m-d H:i:s')."'";
            DB::select("UPDATE our_reviews SET $conditions WHERE id = $id");
            $this->response['status'] = 'ok';
            return response($this->response, 200);
        else:
            return response($this->response, 202);
        endif;
    }

    public function addReview(Request $request) {
        $message = $request->get('message') ?? '';
        $item = OurReviews::create(['message' => $message]);
        if($item):
            $this->response['status'] = 'ok';
            $this->response['item'] = OurReviews::where('id',$item->id)->first();
            return response($this->response, 200);
        else:
            return response($this->response, 202);
        endif;
    }

    public function removeReview($id) {
        $id = (int)$id;
        $page = DB::select("SELECT * FROM our_reviews WHERE id = $id LIMIT 1");
        if($page):
            DB::select("DELETE FROM our_reviews WHERE id = $id");
            $this->response['status'] = 'ok';
            return response($this->response, 200);
        else:
            return response($this->response, 202);
        endif;
    }

    public function getWorks(Request $request) {
        $this->response['pagination'] = [];
        $page = $request->get('page');
        $asc = boolint($request->get('asc'));
        $keyword = $request->get('keyword') !== 'null' ? $request->get('keyword') : null;
        $sortfield = $request->get('sort') ?? 'created_at';
        $conditions = "";
        $sortdir = $asc ? "ASC" : "DESC";
        if($keyword) $conditions .= " AND (our_works.alt LIKE '%$keyword%' OR our_works.title LIKE '%$keyword%')";
        $items = DB::select("SELECT our_works.* FROM our_works WHERE 1 $conditions ORDER BY our_works.$sortfield $sortdir");
        $this->response['items'] = $items;
        $this->response['status'] = 'ok';
        return response($this->response, 200);
    }

    public function removeWork($id) {
        $id = (int)$id;
        $works = DB::select("SELECT * FROM our_works WHERE id = $id LIMIT 1");
        if($work = array_pop($works)):
            Storage::disk('works')->delete($work->image);
            DB::select("DELETE FROM our_works WHERE id = $id");
            $this->response['status'] = 'ok';
            return response($this->response, 200);
        else:
            return response($this->response, 202);
        endif;
    }

    public function updateWork($id, request $request) {
        $params = $request->all();
        $conditions = '';  
        $page = DB::select("SELECT * FROM our_works WHERE id = $id LIMIT 1");
        if($page):
            if(array_key_exists('alt', $params)) $conditions .= "alt = '".str_replace("'","\'",$params['alt'])."',";
            if(array_key_exists('title', $params)) $conditions .= "title = '".str_replace("'","\'",$params['title'])."',";
            if(array_key_exists('display', $params)) $conditions .= "display = '".boolint($params['display'])."',";
            $conditions .= "updated_at = '".date('Y-m-d H:i:s')."'";
            DB::select("UPDATE our_works SET $conditions WHERE id = $id");
            $this->response['status'] = 'ok';
            return response($this->response, 200);
        else:
            return response($this->response, 202);
        endif;
    }

    public function addWork(Request $request) {
        $results = []; 
        $files = $request->file('files');
        if($files):
            foreach($files as $image):
                if(self::sanitizeimage($image)):
                    $filename = $image->store('', 'works');
                    if($filename):
                        $work = OurWorks::create(['image'=>$filename]);
                        $results[] = $work->id;
                    endif;
                endif;
            endforeach;
        endif;
        if($results):
            $this->response['status'] = 'ok';
            $this->response['items'] = DB::select("SELECT * FROM our_works WHERE id IN (".implode(',',$results).")");
            return response($this->response, 200);
        else:
            return response($this->response, 202);
        endif;
    }

    public function getAds(Request $request) {
        $this->response['pagination'] = [];
        $page = $request->get('page');
        $asc = boolint($request->get('asc'));
        $keyword = $request->get('keyword') !== 'null' ? $request->get('keyword') : null;
        $sortfield = $request->get('sort') ?? 'created_at';
        $conditions = "";
        $sortdir = $asc ? "ASC" : "DESC";
        if($keyword) $conditions .= " AND (ads.title LIKE '%$keyword%' OR ads.text LIKE '%$keyword%')";
        $items = DB::select("SELECT ads.* FROM ads WHERE 1 $conditions ORDER BY ads.$sortfield $sortdir");
        $this->response['items'] = $items;
        $this->response['status'] = 'ok';
        return response($this->response, 200);
    }

    public function addAds(Request $request) {
        $title = $request->get('title') ?? '';
        $item = Ads::create(['title' => $title]);
        if($item):
            $this->response['status'] = 'ok';
            $this->response['item'] = Ads::where('id',$item->id)->first();
            return response($this->response, 200);
        else:
            return response($this->response, 202);
        endif;
    }

    public function updateAds($id, request $request) {
        $params = $request->all();
        $image = $request->file('file_cover');
        if($image):
          if(self::sanitizefile($image)):
            $filename = $image->store('', 'images');
            $params['cover'] = $filename;
          endif;
        endif;
        $conditions = '';  
        $page = DB::select("SELECT * FROM ads WHERE id = $id LIMIT 1");
        if($page):
            if(array_key_exists('cover', $params)) $conditions .= "cover = '".str_replace("'","\'",$params['cover'])."',";
            if(array_key_exists('title', $params)) $conditions .= "title = '".str_replace("'","\'",$params['title'])."',";
            if(array_key_exists('text', $params)) $conditions .= "text = '".str_replace("'","\'",$params['text'])."',";
            if(array_key_exists('button_text', $params)) $conditions .= "button_text = '".str_replace("'","\'",$params['button_text'])."',";
            if(array_key_exists('button_link', $params)) $conditions .= "button_link = '".str_replace("'","\'",$params['button_link'])."',";
            if(array_key_exists('display', $params)) $conditions .= "display = '".boolint($params['display'])."',";
            $conditions .= "updated_at = '".date('Y-m-d H:i:s')."'";
            DB::select("UPDATE ads SET $conditions WHERE id = $id");
            $this->response['status'] = 'ok';
            return response($this->response, 200);
        else:
            return response($this->response, 202);
        endif;
    }

    public function removeAds($id) {
        $id = (int)$id;
        $page = DB::select("SELECT * FROM ads WHERE id = $id LIMIT 1");
        if($page):
            DB::select("DELETE FROM ads WHERE id = $id");
            $this->response['status'] = 'ok';
            return response($this->response, 200);
        else:
            return response($this->response, 202);
        endif;
    }

    public function getPromocodes(Request $request) {
        $this->response['pagination'] = [];
        $page = $request->get('page');
        $asc = boolint($request->get('asc'));
        $keyword = $request->get('keyword') !== 'null' ? $request->get('keyword') : null;
        $sortfield = $request->get('sort') ?? 'created_at';
        $conditions = "";
        $sortdir = $asc ? "ASC" : "DESC";
        if($keyword) $conditions .= " AND (codes.code LIKE '%$keyword%')";
        $items = DB::select("SELECT codes.* FROM order_promocodes as codes WHERE 1 $conditions ORDER BY codes.$sortfield $sortdir");
        if($items):
            foreach($items as &$e):
                $e->services = explode('|', $e->services) ?? [];
                $e->services = array_map(function($e) { return (int)$e; }, $e->services);
            endforeach;
        endif;
        $this->response['items'] = $items;
        $this->response['otypes'] = DB::select("SELECT id, name FROM order_types");
        $this->response['types'] = DB::select("SELECT id, name FROM order_promocodes_types");
        $this->response['users'] = DB::select("SELECT id, login, nick_name FROM users");
        $this->response['vtypes'] = [['id'=>1,'name'=>'Сумма'],['id'=>2,'name'=>'Процент']];
        $this->response['status'] = 'ok';
        return response($this->response, 200);
    }

    public function updatePromocode($id, request $request) {
        $params = $request->all();
        $conditions = '';  
        $page = DB::table('order_promocodes')->where('id', $id)->first();
        if($page):
            $data = [];
            if(array_key_exists('code', $params)) $data['code'] = str_replace("'","\'",$params['code']);
            if(array_key_exists('services', $params)) $data['services'] = is_array($params['services']) ? implode('|', $params['services']) : null;
            if(array_key_exists('user_id', $params)) $data['user_id'] = boolint($params['user_id']);
            if(array_key_exists('from', $params)) $data['from'] = (float)$params['from'];
            if(array_key_exists('till', $params)) $data['till'] = (float)$params['till'];
            if(array_key_exists('type', $params)) $data['type'] = boolint($params['type']);
            if(array_key_exists('vtype', $params)) $data['vtype'] = boolint($params['vtype']);
            if(array_key_exists('expires_at', $params)) $data['expires_at'] = date('Y-m-d H:i:s', strtotime($params['expires_at']));
            $page = DB::table('order_promocodes')->where('id', $id)->update($data);
            $this->response['status'] = 'ok';
            return response($this->response, 200);
        else:
            return response($this->response, 202);
        endif;
    }

    public function addPromocode(Request $request) {
        $code = $request->get('code') ?? '';
        $item = Promocode::create(['code' => $code]);
        if($item):
            $this->response['status'] = 'ok';
            $this->response['item'] = Promocode::where('id',$item->id)->first();
            return response($this->response, 200);
        else:
            return response($this->response, 202);
        endif;
    }

    public function removePromocode($id) {
        $id = (int)$id;
        $page = DB::select("SELECT * FROM order_promocodes WHERE id = $id LIMIT 1");
        if($page):
            DB::select("DELETE FROM order_promocodes WHERE id = $id");
            $this->response['status'] = 'ok';
            return response($this->response, 200);
        else:
            return response($this->response, 202);
        endif;
    }

    public function getSlides(Request $request) {
        $this->response['pagination'] = [];
        $page = $request->get('page');
        $asc = boolint($request->get('asc'));
        $keyword = $request->get('keyword') !== 'null' ? $request->get('keyword') : null;
        $sortfield = $request->get('sort') ?? 'created_at';
        $conditions = "";
        $sortdir = $asc == 'true' ? "ASC" : "DESC";
        if($keyword) $conditions .= " AND (slides.title LIKE '%$keyword%' OR slides.text LIKE '%$keyword%')";
        $items = DB::select("SELECT slides.* FROM slides WHERE 1 $conditions ORDER BY slides.$sortfield $sortdir");
        if($items):
            foreach($items as &$e):
                $e->labels = @json_decode($e->labels, true) ?? [];
            endforeach;
        endif;
        $this->response['items'] = $items;
        $this->response['positions'] = DB::select("SELECT * FROM slides_positions");
        $this->response['status'] = 'ok';
        return response($this->response, 200);
    }

    public function updateSlide($id, request $request) {
        $params = $request->all();
        $image = $request->file('file_image');
        if($image):
          if(self::sanitizefile($image)):
            $filename = $image->store('', 'images');
            $params['image'] = $filename;
          endif;
        endif;
        $conditions = '';  
        $page = DB::table('slides')->where('id', $id)->first();
        if($page):
            $data = [];
            if(array_key_exists('title', $params)) $data['title'] = str_replace("'","\'",$params['title']);
            if(array_key_exists('text', $params)) $data['text'] = str_replace("'","\'",$params['text']);
            if(array_key_exists('video', $params)) $data['video'] = str_replace("'","\'",$params['video']);
            if(array_key_exists('button_text', $params)) $data['button_text'] = str_replace("'","\'",$params['button_text']);
            if(array_key_exists('image', $params)) $data['image'] = str_replace("'","\'",$params['image']);
            if(array_key_exists('button_link', $params)) $data['button_link'] = str_replace("'","\'",$params['button_link']);
            if(array_key_exists('labels', $params)) $data['labels'] = is_array($params['labels']) ? json_encode($params['labels'], JSON_UNESCAPED_UNICODE) : $params['labels'];
            if(array_key_exists('display', $params)) $data['display'] = boolint($params['display']);
            if(array_key_exists('display_number', $params)) $data['display_number'] = boolint($params['display_number']);
            $data['updated_at'] = date('Y-m-d H:i:s');
            $page = DB::table('slides')->where('id', $id)->update($data);
            $this->response['status'] = 'ok';
            return response($this->response, 200);
        else:
            return response($this->response, 202);
        endif;
    }

    public function addSlide(Request $request) {
        $title = $request->get('title') ?? '';
        $item = Slide::create(['title' => $title]);
        if($item):
            $this->response['status'] = 'ok';
            $this->response['item'] = Slide::where('id',$item->id)->first();
            return response($this->response, 200);
        else:
            return response($this->response, 202);
        endif;
    }

    public function removeSlide($id) {
        $id = (int)$id;
        $page = DB::select("SELECT * FROM slides WHERE id = $id LIMIT 1");
        if($page):
            DB::select("DELETE FROM slides WHERE id = $id");
            $this->response['status'] = 'ok';
            return response($this->response, 200);
        else:
            return response($this->response, 202);
        endif;
    }


    public function getAdvantages(Request $request) {
        $this->response['pagination'] = [];
        $page = $request->get('page');
        $asc = boolint($request->get('asc'));
        $keyword = $request->get('keyword') !== 'null' ? $request->get('keyword') : null;
        $sortfield = $request->get('sort') ?? 'created_at';
        $conditions = "";
        $sortdir = $asc == 'true' ? "ASC" : "DESC";
        if($keyword) $conditions .= " AND (landing_guarantees.caption LIKE '%$keyword%' OR landing_guarantees.description LIKE '%$keyword%')";
        $items = DB::select("SELECT landing_guarantees.* FROM landing_guarantees WHERE 1 $conditions ORDER BY landing_guarantees.$sortfield $sortdir");
        $this->response['items'] = $items;
        $this->response['status'] = 'ok';
        return response($this->response, 200);
    }

    public function updateAdvantage($id, request $request) {
        $params = $request->all();
        $image = $request->file('file_image');
        if($image):
          if(self::sanitizefile($image)):
            $filename = $image->store('', 'images');
            $params['image'] = $filename;
          endif;
        endif;
        $conditions = '';  
        $page = DB::select("SELECT * FROM landing_guarantees WHERE id = $id LIMIT 1");
        if($page):
            if(array_key_exists('image', $params)) $conditions .= "image = '".str_replace("'","\'",$params['image'])."',";
            if(array_key_exists('caption', $params)) $conditions .= "caption = '".str_replace("'","\'",$params['caption'])."',";
            if(array_key_exists('description', $params)) $conditions .= "description = '".str_replace("'","\'",$params['description'])."',";
            if(array_key_exists('display', $params)) $conditions .= "display = '".boolint($params['display'])."',";
            if(array_key_exists('type', $params)) $conditions .= "type = '".intval($params['type'])."',";
            $conditions .= "updated_at = '".date('Y-m-d H:i:s')."'";
            DB::select("UPDATE landing_guarantees SET $conditions WHERE id = $id");
            $this->response['status'] = 'ok';
            return response($this->response, 200);
        else:
            return response($this->response, 202);
        endif;
    }

    public function addAdvantage(Request $request) {
        $caption = $request->get('caption') ?? '';
        $item = OurGuarantees::create(['caption' => $caption]);
        if($item):
            $this->response['status'] = 'ok';
            $this->response['item'] = OurGuarantees::where('id',$item->id)->first();
            return response($this->response, 200);
        else:
            return response($this->response, 202);
        endif;
    }

    public function removeAdvantage($id) {
        $id = (int)$id;
        $page = DB::select("SELECT * FROM landing_guarantees WHERE id = $id LIMIT 1");
        if($page):
            DB::select("DELETE FROM landing_guarantees WHERE id = $id");
            $this->response['status'] = 'ok';
            return response($this->response, 200);
        else:
            return response($this->response, 202);
        endif;
    }

    public function getFAQs(Request $request) {
        $this->response['pagination'] = [];
        $page = $request->get('page');
        $asc = boolint($request->get('asc'));
        $keyword = $request->get('keyword') !== 'null' ? $request->get('keyword') : null;
        $sortfield = $request->get('sort') ?? 'created_at';
        $conditions = "";
        $sortdir = $asc == 'true' ? "ASC" : "DESC";
        if($keyword) $conditions .= " AND (landing_faq.ru_question LIKE '%$keyword%' OR landing_faq.ru_answer LIKE '%$keyword%' OR landing_faq.en_question LIKE '%$keyword%' OR landing_faq.en_answer LIKE '%$keyword%')";
        $faqs = DB::select("SELECT landing_faq.* FROM landing_faq WHERE 1 $conditions ORDER BY landing_faq.$sortfield $sortdir");
        $this->response['faqs'] = $faqs;
        $this->response['types'] = [['name'=>'Основные FAQ'],['name'=>'Проблема с оплатой']];
        $this->response['status'] = 'ok';
        return response($this->response, 200);
    }

    public function updateFAQ($id, request $request) {
        $params = $request->all();
        $conditions = '';  
        $page = DB::select("SELECT * FROM landing_faq WHERE id = $id LIMIT 1");
        if($page):
            if(array_key_exists('ru_question', $params)) $conditions .= "ru_question = '".str_replace("'","\'",$params['ru_question'])."',";
            if(array_key_exists('ru_answer', $params)) $conditions .= "ru_answer = '".str_replace("'","\'",$params['ru_answer'])."',";
            if(array_key_exists('en_question', $params)) $conditions .= "en_question = '".str_replace("'","\'",$params['en_question'])."',";
            if(array_key_exists('en_answer', $params)) $conditions .= "en_answer = '".str_replace("'","\'",$params['en_answer'])."',";
            if(array_key_exists('display', $params)) $conditions .= "display = '".boolint($params['display'])."',";
            if(array_key_exists('display_number', $params)) $conditions .= "display_number = '".boolint($params['display_number'])."',";
            if(array_key_exists('type', $params)) $conditions .= "type = '".boolint($params['type'])."',";
            $conditions .= "updated_at = '".date('Y-m-d H:i:s')."'";
            DB::select("UPDATE landing_faq SET $conditions WHERE id = $id");
            $this->response['status'] = 'ok';
            return response($this->response, 200);
        else:
            return response($this->response, 202);
        endif;
    }

    public function addFAQ(Request $request) {
        $type = boolint($request->get('type') ?? 1);
        $faq = Faq::create(['type' => $type]);
        if($faq):
            $this->response['status'] = 'ok';
            $this->response['faq'] = Faq::where('id',$faq->id)->first();
            return response($this->response, 200);
        else:
            return response($this->response, 202);
        endif;
    }

    public function removeFAQ($id) {
        $id = (int)$id;
        $page = DB::select("SELECT * FROM landing_faq WHERE id = $id LIMIT 1");
        if($page):
            DB::select("DELETE FROM landing_faq WHERE id = $id");
            $this->response['status'] = 'ok';
            return response($this->response, 200);
        else:
            return response($this->response, 202);
        endif;
    }

    public function getPages(Request $request) {
        $this->response['pagination'] = [];
        $page = $request->get('page');
        $asc = $request->get('asc');
        $keyword = $request->get('keyword') !== 'null' ? $request->get('keyword') : null;
        $sortfield = $request->get('sort') ?? 'created_at';
        $conditions = "";
        $sortdir = $asc && $asc == 'true' ? "ASC" : "DESC";
        if($keyword) $conditions .= " AND (landing_pages.tags LIKE '%$keyword%' OR landing_pages.title LIKE '%$keyword%' OR landing_pages.preview LIKE '%$keyword%' OR landing_pages.text LIKE '%$keyword%')";
        $pages = DB::select("
            SELECT landing_pages.*,
            (SELECT p.name FROM landing_pages as p WHERE p.id = landing_pages.nested_in) as parent_name
            FROM landing_pages WHERE 1 $conditions ORDER BY landing_pages.$sortfield $sortdir
        ");
        $this->response['pages'] = $pages;
        $this->response['status'] = 'ok';
        return response($this->response, 200);
    }

    public function getPage($id, Request $request) {
        $id = (int)$id;
        $page = DB::select("
            SELECT landing_pages.*,
            (SELECT p.name FROM landing_pages as p WHERE p.id = landing_pages.nested_in) as parent_name
            FROM landing_pages WHERE id = $id LIMIT 1
        ");
        $page = $page[0] ?? null;
        $this->response['page'] = $page;
        $this->response['status'] = 'ok';
        if(!$page):
            return response($this->response, 202);
        endif;
        $this->response['pages'] = DB::select("SELECT landing_pages.name, landing_pages.id, landing_pages.nested_in FROM landing_pages WHERE id <> $id ORDER BY landing_pages.nested_in");
        return response($this->response, 200);
    }

    public function updatePage($id, request $request) {
        $params = $request->all();
        $conditions = '';  
        $page = DB::select("SELECT * FROM landing_pages WHERE id = $id LIMIT 1");
        if($page):
            if(array_key_exists('slug', $params)) $conditions .= "slug = '".str_replace("'","\'",$params['slug'])."',";
            if(array_key_exists('name', $params)) $conditions .= "name = '".str_replace("'","\'",$params['name'])."',";
            if(array_key_exists('title', $params)) $conditions .= "title = '".str_replace("'","\'",$params['title'])."',";
            if(array_key_exists('seo_title', $params)) $conditions .= "seo_title = '".str_replace("'","\'",$params['seo_title'])."',";
            if(array_key_exists('seo_keywords', $params)) $conditions .= "seo_keywords = '".str_replace("'","\'",$params['seo_keywords'])."',";
            if(array_key_exists('seo_description', $params)) $conditions .= "seo_description = '".str_replace("'","\'",$params['seo_description'])."',";
            if(array_key_exists('seo_text', $params)) $conditions .= "seo_text = '".str_replace("'","\'",$params['seo_text'])."',";
            if(array_key_exists('display_header', $params)) $conditions .= "display_header = '".boolint($params['display_header'])."',";
            if(array_key_exists('display_footer', $params)) $conditions .= "display_footer = '".boolint($params['display_footer'])."',";
            if(array_key_exists('display', $params)) $conditions .= "display = '".boolint($params['display'])."',";
            if(array_key_exists('seo_indexing', $params)) $conditions .= "seo_indexing = '".boolint($params['seo_indexing'])."',";
            if(array_key_exists('display_number', $params)) $conditions .= "display_number = '".boolint($params['display_number'])."',";
            if(array_key_exists('order_type', $params)) $conditions .= "order_type = '".boolint($params['order_type'])."',";
            if(array_key_exists('nested_in', $params)) $conditions .= "nested_in = '".boolint($params['nested_in'])."',";
            $conditions .= "updated_at = '".date('Y-m-d H:i:s')."'";
            DB::select("UPDATE landing_pages SET $conditions WHERE id = $id");
            $this->response['status'] = 'ok';
            return response($this->response, 200);
        else:
            return response($this->response, 202);
        endif;
    }

    public function addPage(Request $request) {
        $name = $request->get('name');
        $title = $request->get('title');
        $slug = $request->get('slug');
        $page = Page::create([
            'title' => $title,
            'name' => $name,
            'slug' => $slug,
        ]);
        if($page):
            $this->response['status'] = 'ok';
            $this->response['id'] = $page->id;
            return response($this->response, 200);
        else:
            return response($this->response, 202);
        endif;
    }

    public function removePage($id) {
        $id = (int)$id;
        $page = DB::select("SELECT * FROM landing_pages WHERE id = $id LIMIT 1");
        if($page):
            DB::select("DELETE FROM landing_pages WHERE id = $id");
            $this->response['status'] = 'ok';
            return response($this->response, 200);
        else:
            return response($this->response, 202);
        endif;
    }

    public function getOrderTypes(Request $request) {
        $this->response['pagination'] = [];
        $page = $request->get('page');
        $asc = boolint($request->get('asc'));
        $keyword = $request->get('keyword') !== 'null' ? $request->get('keyword') : null;
        $sortfield = $request->get('sort') ?? 'created_at';
        $data = [];

        $conditions = "";
        $sortdir = $asc ? "ASC" : "DESC";
        if($keyword) $conditions .= " AND (order_types.name LIKE '%$keyword%')";
        $order_types = DB::select("
            SELECT order_types.*, games.name as game_name FROM order_types
            LEFT JOIN games ON games.id = order_types.game
            WHERE 1 $conditions ORDER BY order_types.$sortfield $sortdir
        ");
        $this->response['order_types'] = $order_types;
        $this->response['status'] = 'ok';
        return response($this->response, 200);
    }

    public function getOrderType($id, Request $request) {
        $id = (int)$id;
        $order_types = DB::select("SELECT order_types.* FROM order_types WHERE order_types.id = $id LIMIT 1");
        $order_type = $order_types ? array_pop($order_types) : null;
        if($order_type):
            $order_type->info_labels = json_decode($order_type->info_labels, true);
        endif;
        $this->response['order_type'] = $order_type;
        $this->response['games'] = DB::select("SELECT id, name FROM games");
        $this->response['status'] = 'ok';
        return response($this->response, 200);
    }

    public function addOrderType(Request $request) {
        $name = $request->get('name');
        $order = Order::create(['name' => $name]);
        if($order):
            $this->response['status'] = 'ok';
            $this->response['id'] = $order->id;
            return response($this->response, 200);
        else:
            return response($this->response, 202);
        endif;
    }

    public function updateOrderType($id, request $request) {
        $params = $request->all();
        $icon = $request->file('file_review_icon');
        $covers = [];
        $covers['cover'] = $request->file('file_cover');
        $covers['info_image'] = $request->file('file_info_image');
        $covers['info_finish_image'] = $request->file('file_info_finish_image');
        $covers['closed_image_1'] = $request->file('file_closed_image_1');
        $covers['closed_image_2'] = $request->file('file_closed_image_2');
        $covers['closed_image_3'] = $request->file('file_closed_image_3');
        $conditions = '';  
        $type = collect(DB::select("SELECT * FROM order_types WHERE id = $id LIMIT 1"))->first();
        if($type):
            if($icon):
              if(self::sanitizefile($icon)):
                $filename = $icon->storeAs($type->id.'.'.$icon->extension(),'','orders_types');
                $params['review_icon'] = $filename;
              endif;
            endif;
            foreach($covers as $field => $file):
                if($file):
                  if(self::sanitizefile($file)):
                    Storage::disk('orders')->delete($type->{$field});
                    $filename = $file->store('', 'orders');
                    $params[$field] = $filename;
                  endif;
                endif;
            endforeach;
            if(array_key_exists('review_icon', $params)) $conditions .= "review_icon = '".$params['review_icon']."',";
            if(array_key_exists('cover', $params)) $conditions .= "cover = '".$params['cover']."',";
            if(array_key_exists('info_image', $params)) $conditions .= "info_image = '".$params['info_image']."',";
            if(array_key_exists('info_finish_image', $params)) $conditions .= "info_finish_image = '".$params['info_finish_image']."',";
            if(array_key_exists('closed_image_1', $params)) $conditions .= "closed_image_1 = '".$params['closed_image_1']."',";
            if(array_key_exists('closed_image_2', $params)) $conditions .= "closed_image_2 = '".$params['closed_image_2']."',";
            if(array_key_exists('closed_image_3', $params)) $conditions .= "closed_image_3 = '".$params['closed_image_3']."',";
            if(array_key_exists('info_order', $params)) $conditions .= "info_order = '".str_replace("'","\'",$params['info_order'])."',";
            if(array_key_exists('info_payment', $params)) $conditions .= "info_payment = '".str_replace("'","\'",$params['info_payment'])."',";
            if(array_key_exists('info_description', $params)) $conditions .= "info_description = '".str_replace("'","\'",$params['info_description'])."',";
            if(array_key_exists('info_price', $params)) $conditions .= "info_price = '".str_replace("'","\'",$params['info_price'])."',";
            if(array_key_exists('info_labels', $params)) $conditions .= "info_labels = '".str_replace("'","\'",$params['info_labels'])."',";
            if(array_key_exists('info_finish_title', $params)) $conditions .= "info_finish_title = '".str_replace("'","\'",$params['info_finish_title'])."',";
            if(array_key_exists('info_finish_text', $params)) $conditions .= "info_finish_text = '".str_replace("'","\'",$params['info_finish_text'])."',";
            if(array_key_exists('closed_text_1', $params)) $conditions .= "closed_text_1 = '".str_replace("'","\'",$params['closed_text_1'])."',";
            if(array_key_exists('name', $params)) $conditions .= "name = '".str_replace("'","\'",$params['name'])."',";
            if(array_key_exists('game', $params)) $conditions .= "game = '".boolint($params['game'])."',";
            if(array_key_exists('display', $params)) $conditions .= "display = '".boolint($params['display'])."',";
            $conditions .= "updated_at = '".date('Y-m-d H:i:s')."'";   
            DB::select("UPDATE order_types SET $conditions WHERE id = $id");
            $this->response['status'] = 'ok';
            return response($this->response, 200);
        else:
            return response($this->response, 202);
        endif;
    }

    public function removeOrderType($id) {
        $id = (int)$id;
        $page = DB::select("SELECT * FROM order_types WHERE id = $id LIMIT 1");
        if($page):
            DB::select("DELETE FROM order_types WHERE id = $id");
            $this->response['status'] = 'ok';
            return response($this->response, 200);
        else:
            return response($this->response, 202);
        endif;
    }

    public function getGames(Request $request) {
        $this->response['pagination'] = [];
        $page = $request->get('page');
        $asc = boolint($request->get('asc'));
        $keyword = $request->get('keyword') !== 'null' ? $request->get('keyword') : null;
        $sortfield = $request->get('sort') ?? 'created_at';
        $data = [];

        $conditions = "";
        $sortdir = $asc ? "ASC" : "DESC";
        if($keyword) $conditions .= " AND (games.name LIKE '%$keyword%')";
        $games = DB::select("
            SELECT games.* FROM games
            WHERE 1 $conditions ORDER BY games.$sortfield $sortdir
        ");
        $this->response['games'] = $games;
        $this->response['status'] = 'ok';
        return response($this->response, 200);
    }

    public function updateGame($id, request $request) {
        $params = $request->all();
        $cover = $request->file('file_cover');
        $thumb = $request->file('file_thumb');
        if($cover):
          if(self::sanitizefile($cover)):
            $filename = $cover->store('', 'games');
            $params['cover'] = $filename;
          endif;
        endif;
        if($thumb):
          if(self::sanitizefile($thumb)):
            $filename = $thumb->store('', 'games_thumb');
            $params['thumb'] = $filename;
          endif;
        endif;
        $conditions = '';  
        $post = DB::select("SELECT * FROM games WHERE id = $id LIMIT 1");
        if($post):
            if(array_key_exists('cover', $params)) $conditions .= "cover = '".$params['cover']."',";
            if(array_key_exists('thumb', $params)) $conditions .= "thumb = '".$params['thumb']."',";
            if(array_key_exists('name', $params)) $conditions .= "name = '".str_replace("'","\'",$params['name'])."',";
            if(array_key_exists('display', $params)) $conditions .= "display = '".boolint($params['display'])."',";
            if(array_key_exists('display_number', $params)) $conditions .= "display_number = '".boolint($params['display_number'])."',";
            if(array_key_exists('stat_boosters', $params)) $conditions .= "stat_boosters = '".boolint($params['stat_boosters'])."',";
            if(array_key_exists('stat_orders', $params)) $conditions .= "stat_orders = '".boolint($params['stat_orders'])."',";
            if(array_key_exists('stat_weekly', $params)) $conditions .= "stat_weekly = '".boolint($params['stat_weekly'])."',";
            $conditions .= "updated_at = '".date('Y-m-d H:i:s')."'";   
            DB::select("UPDATE games SET $conditions WHERE id = $id");
            $this->response['status'] = 'ok';
            return response($this->response, 200);
        else:
            return response($this->response, 202);
        endif;
    }

    public function getGame($id, Request $request) {
        $id = (int)$id;
        $games = DB::select("SELECT games.* FROM games WHERE games.id = $id LIMIT 1");
        $game = $games ? array_pop($games) : null;
        $this->response['game'] = $game;
        $this->response['status'] = 'ok';
        return response($this->response, 200);
    }

    public function addGame(Request $request) {
        $name = $request->get('name');
        $game = Game::create(['name' => $name]);
        if($game):
            $this->response['status'] = 'ok';
            $this->response['id'] = $game->id;
            return response($this->response, 200);
        else:
            return response($this->response, 202);
        endif;
    }

    public function removeGame($id) {
        $id = (int)$id;
        $game = DB::select("SELECT * FROM games WHERE id = $id LIMIT 1");
        if($game):
            DB::select("DELETE FROM games WHERE id = $id");
            $this->response['status'] = 'ok';
            return response($this->response, 200);
        else:
            return response($this->response, 202);
        endif;
    }

    public function getConfSteps(Request $request) {
        $data = [];
        $steps = DB::table('order_configure_steps')->first();
        if($steps):
            $steps->step_0_texts = array_filter(explode('|', $steps->step_0_texts));
            $steps->step_1_texts = array_filter(explode('|', $steps->step_1_texts));
            $steps->step_2_texts = array_filter(explode('|', $steps->step_2_texts));
            $steps->step_3_texts = array_filter(explode('|', $steps->step_3_texts));
        endif;
        $this->response['steps'] = $steps;
        $this->response['status'] = 'ok';
        return response($this->response, 200);
    }

    public function updateConfSteps(request $request) {
        $params = $request->all();
        $data = [];  
        if(array_key_exists('step_0_checkbox', $params)) $data['step_0_checkbox'] = str_replace("'","\'",$params['step_0_checkbox']);
        if(array_key_exists('step_0_name', $params)) $data['step_0_name'] = str_replace("'","\'",$params['step_0_name']);
        if(array_key_exists('step_0_texts', $params)) $data['step_0_texts'] = str_replace("'","\'",implode('|',array_filter($params['step_0_texts'])));
        if(array_key_exists('step_0_title', $params)) $data['step_0_title'] = str_replace("'","\'",$params['step_0_title']);
        if(array_key_exists('step_0_video', $params)) $data['step_0_video'] = str_replace("'","\'",$params['step_0_video']);
        if(array_key_exists('step_1_checkbox', $params)) $data['step_1_checkbox'] = str_replace("'","\'",$params['step_1_checkbox']);
        if(array_key_exists('step_1_name', $params)) $data['step_1_name'] = str_replace("'","\'",$params['step_1_name']);
        if(array_key_exists('step_1_texts', $params)) $data['step_1_texts'] = str_replace("'","\'",implode('|',array_filter($params['step_1_texts'])));
        if(array_key_exists('step_1_title', $params)) $data['step_1_title'] = str_replace("'","\'",$params['step_1_title']);
        if(array_key_exists('step_1_video', $params)) $data['step_1_video'] = str_replace("'","\'",$params['step_1_video']);
        if(array_key_exists('step_2_checkbox', $params)) $data['step_2_checkbox'] = str_replace("'","\'",$params['step_2_checkbox']);
        if(array_key_exists('step_2_name', $params)) $data['step_2_name'] = str_replace("'","\'",$params['step_2_name']);
        if(array_key_exists('step_2_texts', $params)) $data['step_2_texts'] = str_replace("'","\'",implode('|',array_filter($params['step_2_texts'])));
        if(array_key_exists('step_2_title', $params)) $data['step_2_title'] = str_replace("'","\'",$params['step_2_title']);
        if(array_key_exists('step_2_video', $params)) $data['step_2_video'] = str_replace("'","\'",$params['step_2_video']);
        if(array_key_exists('step_3_checkbox', $params)) $data['step_3_checkbox'] = str_replace("'","\'",$params['step_3_checkbox']);
        if(array_key_exists('step_3_name', $params)) $data['step_3_name'] = str_replace("'","\'",$params['step_3_name']);
        if(array_key_exists('step_3_texts', $params)) $data['step_3_texts'] = str_replace("'","\'",implode('|',array_filter($params['step_3_texts'])));
        if(array_key_exists('step_3_title', $params)) $data['step_3_title'] = str_replace("'","\'",$params['step_3_title']);
        if(array_key_exists('step_3_video', $params)) $data['step_3_video'] = str_replace("'","\'",$params['step_3_video']);                
        DB::table('order_configure_steps')->where('id', 1)->update($data);
        $this->response['status'] = 'ok';
        return response($this->response, 200);
    }

    public function getPosts(Request $request) {
        $page = $request->get('page') ?? 1;
        $asc = $request->get('asc');
        $keyword = $request->get('keyword') !== 'null' ? $request->get('keyword') : null;
        $tags = $request->get('tags');
        $games = $request->get('games');
        $categories = $request->get('categories');
        $perpage = 2;
        $offset = $page * $perpage - $perpage;
        $limits = "LIMIT $offset, $perpage";
        $data = [];

        $conditions = "";
        $sortdir = $asc ? "ASC" : "DESC";
        if($tags) $conditions .= " AND (posts.tags LIKE '%".implode(" OR posts.tags LIKE %',", $tags)."%')";
        if($keyword) $conditions .= " AND (posts.tags LIKE '%$keyword%' OR posts.title LIKE '%$keyword%' OR posts.preview LIKE '%$keyword%')";
        if($games) $conditions .= " AND posts.game_id IN ('".implode("',", $games)."')";
        if($categories) $conditions .= " AND posts.category_id IN ('".implode("','", $categories)."')";
        $posts = DB::select("
            SELECT posts.*, (SELECT nick_name FROM landing_noadmin_users as users WHERE id = posts.user_id LIMIT 1) as author FROM posts
            WHERE 1 $conditions  ORDER BY posts.created_at $sortdir $limits
        ");
        $total = collect(DB::select("SELECT count(id) as total FROM posts WHERE 1 $conditions LIMIT 1"))->first()->total;
        foreach($posts as &$post):
            $post->tags = array_filter(explode('|', $post->tags));
            $post->comments = DB::select("
                SELECT com.id, com.text, com.created_at, com.marked, com.reply_to, com.user_name as author_name, com.user_avatar as author_avatar 
                FROM posts_comments as com
                WHERE com.post_id = $post->id ORDER BY com.created_at DESC
            ");
        endforeach;
        $tags = array_values(DB::table('posts')->select('tags')->pluck('tags')->toArray());
        $tags = explode('|', str_replace(' ', '', implode('|', $tags)));
        $this->response['news'] = $posts;
        $this->response['categories'] = DB::select("SELECT * FROM posts_categories");
        $this->response['games'] = DB::select("SELECT * FROM games");
        $this->response['tags'] = array_values(array_unique(array_filter($tags)));
        $this->response['pagination'] = self::paginate($page, $perpage, ceil($total / $perpage), 3);
        $this->response['status'] = 'ok';
        return response($this->response, 200);
    }

    public function getPost($id, Request $request) {
        $id = (int)$id;
        $posts = DB::select("
            SELECT posts.*, (SELECT nick_name FROM landing_noadmin_users as users WHERE id = posts.user_id LIMIT 1) as author FROM posts
            WHERE posts.id = $id LIMIT 1
        ");
        $post = $posts ? array_pop($posts) : null;
        if($post):
            $this->response['comments'] = DB::select("
                SELECT com.id, com.text, com.created_at, com.publish, com.marked, com.reply_to, com.user_name, com.user_avatar
                FROM posts_comments as com
                WHERE com.post_id = $post->id ORDER BY com.created_at DESC
            ");
        endif;
        $this->response['post'] = $post;
        $this->response['categories'] = DB::select("SELECT * FROM posts_categories");
        $this->response['games'] = DB::select("SELECT * FROM games");
        $this->response['status'] = 'ok';
        return response($this->response, 200);
    }

    public function addPost(Request $request) {
        $title = $request->get('title');
        $preview = $request->get('preview');
        $text = $request->get('text');
        $post = Post::create([
            'title' => $title,
            'preview' => $preview,
            'text' => $text,
            'user_id' => $this->user->id,
        ]);
        if($post):
            $this->response['status'] = 'ok';
            $this->response['id'] = $post->id;
            return response($this->response, 200);
        else:
            return response($this->response, 202);
        endif;
    }

    public function addPostComment(Request $request) {
        $text = $request->get('text');
        $user_name = $request->get('user_name');
        $user_avatar = $request->get('user_avatar');
        $post_id = $request->get('post_id') ?? 0;
        $user_id = $request->get('user_id') ?? 0;
        $reply_to = $request->get('reply_to') ?? 0;
        $comment = PostComment::create([
            'text' => $text,
            'user_name' => $user_name,
            'user_avatar' => $user_avatar,
            'post_id' => $post_id,
            'user_id' => $user_id,
            'reply_to' => $reply_to,
        ]);
        if($comment):
            $this->response['status'] = 'ok';
            $this->response['comment'] = $comment;
            return response($this->response, 200);
        else:
            return response($this->response, 202);
        endif;
    }

    public function removePost($id) {
        $id = (int)$id;
        $post = DB::select("SELECT * FROM posts WHERE id = $id LIMIT 1");
        if($post):
            DB::select("DELETE FROM posts WHERE id = $id");
            $this->response['status'] = 'ok';
            return response($this->response, 200);
        else:
            return response($this->response, 202);
        endif;
    }

    public function removePostComment($id) {
        $id = (int)$id;
        $post = DB::select("SELECT * FROM posts_comments WHERE id = $id LIMIT 1");
        if($post):
            DB::select("DELETE FROM posts_comments WHERE id = $id");
            $this->response['status'] = 'ok';
            return response($this->response, 200);
        else:
            return response($this->response, 202);
        endif;
    }

    public function updatePost($id, request $request) {
        $params = $request->all();
        $cover = $request->file('files');
        $conditions = '';  
        $post = collect(DB::select("SELECT * FROM posts WHERE id = $id LIMIT 1"))->first();
        if($post):
            if($cover):
              if(self::sanitizefile($cover)):
                $filename = $cover->store('', 'posts');
                $params['cover'] = $filename;
                Storage::disk('posts')->delete($post->cover);
              endif;
            endif;
            if(array_key_exists('tags', $params)):
                preg_match_all("/#[^\\s]+\\s?/", $params['tags'] ?? '', $tags);
                $conditions .= "tags = '".implode(' | ',array_pop($tags))."',";
            endif;
            if(array_key_exists('text', $params)):
                preg_match_all('/ src="data:image\/.*base64,([^"]+)"/', $params['text'], $matches);
                if($matches[1]):
                    $path = Storage::disk('posts')->path(null);
                    foreach($matches[1] as $i => $image):
                        $filename = self::upload_base64_file($image, $path);
                        if($filename) $params['text'] = str_replace($matches[0][$i], ' src="'.url('/public/img/posts/'.$filename).'" ', $params['text']);
                    endforeach;
                endif;
                $conditions .= "text = '".str_replace("'","\'",$params['text'])."',";
                $this->response['text'] = $params['text'];
            endif;
            if(array_key_exists('slug', $params)) $conditions .= "slug = '".ltrim($params['slug'], '/')."',";
            if(array_key_exists('cover', $params)) $conditions .= "cover = '".$params['cover']."',";
            if(array_key_exists('title', $params)) $conditions .= "title = '".str_replace("'","\'",$params['title'])."',";
            if(array_key_exists('preview', $params)) $conditions .= "preview = '".str_replace("'","\'",$params['preview'])."',";
            if(array_key_exists('video', $params)) $conditions .= "video = '".str_replace("'","\'",$params['video'])."',";
            if(array_key_exists('seo_title', $params)) $conditions .= "seo_title = '".str_replace("'","\'",$params['seo_title'])."',";
            if(array_key_exists('seo_keywords', $params)) $conditions .= "seo_keywords = '".str_replace("'","\'",$params['seo_keywords'])."',";
            if(array_key_exists('seo_description', $params)) $conditions .= "seo_description = '".str_replace("'","\'",$params['seo_description'])."',";
            if(array_key_exists('published_at', $params)) $conditions .= "published_at = '".date('Y-m-d H:i:s', strtotime($params['published_at']))."',";
            if(array_key_exists('seo_indexing', $params)) $conditions .= "seo_indexing = '".boolint($params['seo_indexing'])."',";
            if(array_key_exists('category_id', $params)) $conditions .= "category_id = '".boolint($params['category_id'])."',";
            if(array_key_exists('game_id', $params)) $conditions .= "game_id = '".boolint($params['game_id'])."',";
            if(array_key_exists('user_id', $params)) $conditions .= "user_id = '".boolint($params['user_id'])."',";
            if(array_key_exists('featured', $params)) $conditions .= "featured = '".boolint($params['featured'])."',";
            if(array_key_exists('show_home', $params)) $conditions .= "show_home = '".boolint($params['show_home'])."',";
            if(array_key_exists('publish', $params)) $conditions .= "publish = '".boolint($params['publish'])."',";
            $conditions .= "updated_at = '".date('Y-m-d H:i:s')."'";   
            DB::select("UPDATE posts SET $conditions WHERE id = $id");
            $this->response['status'] = 'ok';
            return response($this->response, 200);
        else:
            return response($this->response, 202);
        endif;
    }

    public function updatePostComment($id, request $request) {
        $params = $request->all();
        $conditions = '';  
        $post = DB::select("SELECT * FROM posts_comments WHERE id = $id LIMIT 1");
        if($post):
            if(array_key_exists('marked', $params)) $conditions .= "marked = '".boolint($params['marked'])."',";
            if(array_key_exists('publish', $params)) $conditions .= "publish = '".boolint($params['publish'])."',";
            $conditions .= "updated_at = '".date('Y-m-d H:i:s')."'";   
            DB::select("UPDATE posts_comments SET $conditions WHERE id = $id");
            $this->response['status'] = 'ok';
            return response($this->response, 200);
        else:
            return response($this->response, 202);
        endif;
    }

    public function getHeroes(Request $request) {
        $data = DB::select("SELECT id, localized_name FROM heroes ORDER BY localized_name");
        return response($data, 200);
    }  

    public function translate(Request $request) {
        $locale = $request->get('locale');
        $data = [];
        $translates = DB::select("SELECT `name`, `$locale` FROM landing_noadmin_translates");
        foreach($translates as &$item) $data[$item->name] = $item->{$locale};
        return response($data, 200);
    }

    public function getTranslations(Request $request) {
        $locale = $request->get('locale');
        $translates = DB::select("SELECT id, name, ru, us, zh FROM landing_noadmin_translates ORDER BY id DESC");
        return response($translates, 200);
    }    

    public function setTranslations(Request $request) {
        $data = $request->all();
        $to_delete = array_filter($data, function($e) { return $e['id'] && ($e['d'] ?? false); });
        $to_update = array_filter($data, function($e) { return $e['id'] && ($e['u'] ?? false); });
        $to_create = array_filter($data, function($e) { return !$e['id'] && !($e['d'] ?? false); });
        if($to_delete):
            DB::select("DELETE FROM landing_noadmin_translates WHERE id IN (".implode(',',array_map(function($e) { return intval($e['id']); }, $to_delete)).")");
        endif;
        if($to_update):
            foreach($to_update as $e):
                 DB::select("UPDATE landing_noadmin_translates SET 
                    name = '".str_replace("'", "\'", $e['name'])."', 
                    ru = '".str_replace("'", "\'", $e['ru'])."', 
                    us = '".str_replace("'", "\'", $e['us'])."',
                    zh = '".str_replace("'", "\'", $e['zh'])."' 
                    WHERE id = '".$e['id']."'");
            endforeach;
        endif;
        if($to_create):
            foreach($to_create as $e):
                DB::select("
                    INSERT INTO landing_noadmin_translates (name, ru, us, zh) 
                    VALUES ('".str_replace("'", "\'", $e['name'])."','".str_replace("'", "\'", $e['ru'])."','".str_replace("'", "\'", $e['us'])."','".str_replace("'", "\'", $e['zh'])."')
                ");
            endforeach;
        endif;
        $translates = DB::select("SELECT id, name, ru, us, zh FROM landing_noadmin_translates ORDER BY id DESC");
        $translates_ru = [];
        $translates_us = [];
        $translates_zh = [];
        foreach($translates as $e):
            $translates_ru[$e->name] = $e->ru;
            $translates_us[$e->name] = $e->us;
            $translates_zh[$e->name] = $e->zh;
        endforeach;
        @file_put_contents(base_path('resources/lang/ru.json'), str_replace("\'","'",json_encode($translates_ru, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)));
        @file_put_contents(base_path('resources/lang/en.json'), str_replace("\'","'",json_encode($translates_us, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)));
        @file_put_contents(base_path('resources/lang/zh.json'), str_replace("\'","'",json_encode($translates_zh, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)));
        return response($translates, 200);
    }

    public function home() {
        $tempvars['order_games'] = Game::where('display', 1)->get();
        $tempvars['order_types'] = Page::for_orders();
        $tempvars['orders'] = isset($this->api->last_orders(5)['orders']) ? $this->api->last_orders(5)['orders'] : [];
        $tempvars['posts'] = Post::for_home();
        $tempvars['slider'] = Slide::where('display', 1)->where('slider_id', 1)->orderBy('display_number')->get();
        $tempvars['our_guarantees'] = OurGuarantees::where('display', 1)->get();
        $tempvars['ads'] = Ads::where('display', 1)->where('page_id', $this->page->id)->limit(1)->first();
        $tempvars['settings'] = Settings::values('video_about_us');
        parent::beforeRender();
        return view('index', $tempvars);
    }

    public function login(Request $request) {
        $status = 202;
        $user = Admin::where([ 'login' => $request->get('login'), 'password' => md5($request->get('password')) ])->first();
        $this->response['user'] = [
            'id' => $user['id'],
            'nick_name' => $user['nick_name'],
            'avatar' => $user['avatar'],
        ];
        if($user):
          $user->permissions = json_decode($user->permissions) ?? $user->permissions;
          if($user->is_approved == 0):
            $this->response['message'] = $this->get_message(8);
          elseif($user->is_blocked == 1):
            $this->response['message'] = $this->get_message(9);
          else:
            $this->response['message'] = $this->get_message(7);
            $this->response['_token'] = $this->user_to_token($user);
            $this->response['status'] = 'ok';
            $status = 200;
          endif;
        else:
            $this->response['message'] = $this->get_message(6);
        endif;
        return response($this->response, $status);
    }

    private function user_to_token($user) {
        return JWT::encode($user, $this->key);
    }

    private function token_to_user($token) {
        try {
            $user = JWT::decode($token, $this->key, array('HS256'));
        } catch (\Exception $e) {
            $user = null;
        }
        return $user;
    }

    private function get_message($code = 1) {
        return self::get_response($code);
    }

    static function get_response($code = 1) {
        $responses = [
            1  => 'операция на данный момент недоступна',
            2  => 'ваш аккаунт создан, на email отправлено письмо с инструкцией по его активации',
            3  => 'ваш email определен как недействительный, попробуйте другой',
            4  => 'ваш пароль не может быть короче 5 символов',
            5  => 'пользователь с этим email уже зарегистрирован в системе',
            6  => 'пользователь не найден',
            7  => 'вы успешно авторизованы',
            8  => 'владелец этого аккаунта не подтвержден, инструкция по активации была выслана на Ваш email при регистрации',
            9  => 'ваш аккаунт заблокирован администрацией сайта',
            10 => 'На Ваш email отправлено письмо с инструкцией по восстановлению пароля',
        ];
        return isset($responses[$code]) ? $responses[$code] : $responses[1];
    }

    static function sanitizefile($file) {
      $accepted = ['doc','docx','xls','xlsx','csv','pdf','txt','jpg','svg','jpeg','png','gif','php','js','html','xml'];
      return in_array($file->extension(), $accepted);
    }

    static function sanitizeimage($file) {
      $accepted = ['jpg','jpeg','png','gif','svg'];
      return in_array($file->extension(), $accepted);
    }


    static function paginator($pages, $per_page, $pages_display, $page) {
        $pagi    = sizeof($pages) > 1 ? array_merge($pages, $pages, $pages) : $pages;
        $current = intval($page) - 1 + sizeof($pages);
        $offset  = ceil(($pages_display - 1) / 2);
        $from    = $current - $offset;
        $till    = $current + $offset;
        $prev    = array_slice($pagi, $current - 1, 1);
        $next    = array_slice($pagi, $current + 1, 1);
        return [
            'pages'      => array_slice($pagi, $from, $pages_display),
            'prev'       => array_pop($prev) ?? $current,
            'next'       => array_pop($next) ?? $current,
            'current'    => intval($page),
        ];
    }

    static function paginate($page, $per_page, $pages_count, $pages_display) {
        $pages_all = [];
        for($x = 1; $x <= $pages_count; $x++) $pages_all[] = $x;
        $paginate = self::paginator($pages_all, $per_page, $pages_display, $page);
        return $paginate;
    }

    static function resize_image($file, $w, $h, $crop = false) {
        $extension = pathinfo($file, PATHINFO_EXTENSION);
        if(in_array($extension, ['png','jpg','jpeg'])):
            list($width, $height) = getimagesize($file);
            $r = $width / $height;
            if ($crop) {
                if ($width > $height) {
                    $width = ceil($width-($width*abs($r-$w/$h)));
                } else {
                    $height = ceil($height-($height*abs($r-$w/$h)));
                }
                $newwidth = $w;
                $newheight = $h;
            } else {
                if ($w/$h > $r) {
                    $newwidth = $h*$r;
                    $newheight = $h;
                } else {
                    $newheight = $w/$r;
                    $newwidth = $w;
                }
            }
            $src = imagecreatefromjpeg($file);
            $dst = imagecreatetruecolor($newwidth, $newheight);
            imagecopyresampled($dst, $src, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);
            if($extension == 'png'):
                return imagepng($dst, $file); 
            else:
                return imagejpeg($dst, $file); 
            endif;
        endif;
        return true;
    }

    static function upload_base64_file($image, $path) {
        $filename = 'upie'.uniqid().'.png';
        $data = base64_decode($image);
        file_put_contents($path.$filename, $data);
        return $filename;
    }

}