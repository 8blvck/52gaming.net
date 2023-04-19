<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Response;
use Session;
use DB;
use App;
use App\Models\BlackList;
use App\Models\OurGuarantees;
use App\Models\Page;
use App\Models\MailTemplates;
use App\Models\OrderReport;

class AppController extends Controller
{

    public function error404() {
        $this->page_class = 'home';
        $tempvars = [];
        parent::beforeRender();
        return view('404', $tempvars);
    }

    public function home() {
        $this->page_class = 'home';
        $tempvars = [];
        $tempvars['pages'] = Page::where('display_header', 1)->orderBy('id','asc')->get() ?? [];
        parent::beforeRender();
        return view('index', $tempvars);
    }

    public function boosters() {
        $tempvars = [];
        $tempvars['advantages'] = OurGuarantees::where('type',0)->where('display',1)->orderBy('id','asc')->get() ?? [];
        $tempvars['games'] = json_decode(@file_get_contents($this->api.'/info/vacancies')) ?? [];
        parent::beforeRender();
        return view('boosters', $tempvars);
    }

    public function services() {
        $tempvars = [];
        $tempvars['advantages'] = OurGuarantees::where('type',1)->where('display',1)->orderBy('id','asc')->get() ?? [];
        $tempvars['games'] = json_decode(@file_get_contents($this->api.'/info/games')) ?? [];
        parent::beforeRender();
        return view('services', $tempvars);
    }

    public function blacklist() {
        $tempvars = [];
        parent::beforeRender();
        return view('blacklist', $tempvars);
    }

    public function statistics() {
        $context = stream_context_create(["http" => ["method" => "GET", "header" => "Accept-language: ru-RU,ru;q=0.9,en-US;q=0.8,en;q=0.7"]]);
        $data =  file_get_contents('https://52gaming.net/embed/statistics?locale='.$this->locale->name.'&order_page_url=https://52gaming.net/'.$this->locale->name.'/statistics/', false, $context);
        if(trim($data) == 'not found') return redirect('/404');
        parent::beforeRender();
        return view('statistics', ['data'=>$data]);
    }

    public function statisticsOrder(Request $request, $number) {
        $steamLogin = $request->input('steamLogin') ?? null;
        $context = stream_context_create(["http" => ["method" => "GET", "header" => "Accept-language: ru-RU,ru;q=0.9,en-US;q=0.8,en;q=0.7"]]);
        $data = file_get_contents('https://52gaming.net/embed/statistics/'.$number.'?locale='.$this->locale->name.'&steamLogin='.$steamLogin.'&order_page_url=https://52gaming.net/'.$this->locale->name.'/statistics/', false, $context);
        if(trim($data) == 'not found') return redirect('/404');
        parent::beforeRender();
        return view('statisticsOrder', ['data'=>$data,'number'=>$number]);
    }

    public function statisticsEmbedOrder(Request $request, $number) {
        $order_page_url = $request->input('order_page_url') ?? 'https://52gaming.net/statistics/';
        $locale = $request->input('locale') ?? 'en';
        App::setLocale($locale);
        $steamLogin = $request->input('steamLogin') ?? null;
        $user = null;
        $order = DB::table('orders')
        ->select('orders.id','orders.created_at','orders.system_number','orders.type','orders.status','orders.mmr_start','orders.cali_games_total','orders.cali_games_done','orders.training_hours','orders.training_hours_done','orders.mmr_boosted','orders.mmr_finish','orders_types.pub as type_name','orders_games.icon as type_icon','orders_statuses.pub as status_name','orders.heroes','orders.heroes_ban','orders.lanes','orders.servers','security_variant', 'orders.dotabuff','orders.account_login','orders.client_id','orders.give_account','orders.do_faster','orders.play_with_booster','orders.cali_warranty','orders.medal_start','orders.medal_current','orders.medal_finish','orders.training_services')
        ->leftJoin('orders_types', 'orders_types.id', '=', 'orders.type')
        ->leftJoin('orders_games', 'orders_games.id', '=', 'orders_types.game_id')
        ->leftJoin('orders_statuses', 'orders_statuses.id', '=', 'orders.status')
        ->selectRaw('if(orders.type = 6, (select title from users_pricelists_dota_cups_prices where id = orders.medal_current limit 1), null) as cup_current')
        ->selectRaw('if(orders.type = 6, (select title from users_pricelists_dota_cups_prices where id = orders.medal_finish limit 1), null) as cup_finish')
        ->selectRaw('if(orders.type = 9, (select concat(title," ",rank) as title from users_pricelists_dota_autochess_prices where id = orders.medal_start limit 1), null) as chess_start')
        ->selectRaw('if(orders.type = 9, (select concat(title," ",rank) as title from users_pricelists_dota_autochess_prices where id = orders.medal_current limit 1), null) as chess_current')
        ->selectRaw('if(orders.type = 9, (select concat(title," ",rank) as title from users_pricelists_dota_autochess_prices where id = orders.medal_finish limit 1), null) as chess_finish')
        ->selectRaw('if(orders.type = 3, (select concat(title," ",rank) as title from dota_medals where id = orders.medal_current limit 1), null) as rank_current')
        ->selectRaw('if(orders.type = 3, (select concat(title," ",rank) as title from dota_medals where id = orders.medal_start limit 1), null) as rank_start')
        ->selectRaw('if(orders.type = 3, (select concat(title," ",rank) as title from dota_medals where id = orders.medal_finish limit 1), null) as rank_finish')
        ->selectRaw('if(orders.type = 8, (select title from users_pricelists_dota_behavior_score_prices where orders.mmr_start >= `from` and orders.mmr_start <= `till` limit 1), null) as grade_start')
        ->selectRaw('if(orders.type = 8, (select title from users_pricelists_dota_behavior_score_prices where orders.mmr_start + orders.mmr_boosted >= `from` and orders.mmr_start <= `till` limit 1), null) as grade_current')
        ->selectRaw('if(orders.type = 8, (select title from users_pricelists_dota_behavior_score_prices where orders.mmr_finish >= `from` and orders.mmr_finish <= `till` limit 1), null) as grade_finish')
        ->selectRaw('if(orders.type = 11, (select svg from orders_types where orders.type = id limit 1), null) as svg')
        ->whereIn('orders.type',[1,2,3,4,5,6,7,8,9,10,11])
        ->whereIn('orders.status',[1,4,5,6,7,8])
        ->where('orders.system_number',$number)
        ->orderBy('orders.id','desc')
        ->limit(1)
        ->first();
        if($order):
            $order->heroes = json_decode($order->heroes, true) ?? [];
            $order->heroes_ban = json_decode($order->heroes_ban, true) ?? [];
            $order->lanes = json_decode($order->lanes, true) ?? [];
            $order->servers = json_decode($order->servers, true) ?? [];
            $order->wins = 0;
            $order->loses = 0;
            $order->reports = DB::table('orders_reports')
            ->select('created_at','mmr','games','hours','result','starter','medal')
            ->where('order_id', $order->id)
            ->orderBy('id','asc')
            ->get();
            $order->logs = DB::table('logs')
            ->select('logs.created_at','logs.message','logs.additional','logs_actions.details','logs.action_id')
            ->leftJoin('logs_actions', 'logs_actions.id', '=', 'logs.action_id')
            ->where('logs.order_id', $order->id)
            ->orderBy('logs.id','desc')
            ->limit(10)
            ->get();
            $order->datasets = [];
            foreach ($order->reports as $x => $report):
                $win = false;
                if($report->starter) continue;
                $prev = $order->reports[$x - 1] ?? $order->reports[0];
                if($order->type == 1 or $order->type == 8 or $order->type == 10):
                    $win = $report->mmr >= $prev->mmr; 
                    $order->datasets[] = ['date'=>strtotime($report->created_at),'value'=>$report->mmr];
                elseif($order->type == 2):
                    $win = !!$report->result; 
                    $order->datasets[] = ['date'=>strtotime($report->created_at),'value'=>$report->games];
                elseif($order->type == 7):
                    $win = !!$report->result; 
                    $order->datasets[] = ['date'=>strtotime($report->created_at),'value'=>$report->games];
                elseif($order->type == 4):
                    $win = true; 
                    $order->datasets[] = ['date'=>strtotime($report->created_at),'value'=>$report->hours];
                elseif($order->type == 5):
                    $win = true; 
                    $order->datasets[] = ['date'=>strtotime($report->created_at),'value'=>$report->games];
                elseif($order->type == 11):
                    if(!isset($order->services_done)) $order->services_done = [];
                    if($report->medal && !in_array($report->medal, $order->services_done)) $order->services_done[] = $report->medal;
                endif;
                if($win):
                    $order->wins += 1;
                else: 
                    $order->loses += 1;
                endif;
            endforeach;
            $order->winrate = round(100 / (($order->wins + $order->loses) ?: 1) * $order->wins);
            if($steamLogin && trim($steamLogin) == trim($order->account_login)):
                $user = DB::table('users')->select('id','nick_name','avatar')->where('id',$order->client_id)->limit(1)->first();
            endif;
            if($order->type == 3):
                $order->medals = DB::select("SELECT id, title, rank, image FROM dota_medals ORDER BY id ASC");
            elseif($order->type == 6):
                $order->medals = DB::select("SELECT id, title, rank, image FROM users_pricelists_dota_cups_prices ORDER BY id ASC");
            elseif($order->type == 9):
                $order->medals = DB::select("SELECT id, title, rank, image FROM users_pricelists_dota_autochess_prices ORDER BY id ASC");
            elseif($order->type == 11):
                $order->training_services = json_decode($order->training_services, true);
            endif;  
        endif;
        return response()->view('statisticsEmbedOrder', ['order'=>$order, 'locale'=>$locale, '_user'=>$user, '_api_ws'=>$this->api_ws, 'order_page_url'=>$order_page_url, 'steamLogin'=>$steamLogin, 'number'=>$number]);
    }

    public function statisticsEmbed(Request $request) {
        $order_page_url = $request->input('order_page_url') ?? 'https://52gaming.net/statistics/';
        $locale = $request->input('locale') ?? 'en';
        App::setLocale($locale);
        $reports = DB::select('
            SELECT 
            reports.id, reports.created_at, reports.mmr, reports.mmr_diff, reports.games, reports.hours, reports.medal, reports.result, reports.user_id, 
            orders.system_number, orders.cali_games_done, orders.cali_games_total,
            orders_games.icon, orders.mmr_start, orders.mmr_finish, orders.cali_games_total, orders.training_hours, orders.training_hours_done, 
            orders.training_services, orders.type as order_type,
            if(orders.type = 3, (select concat(title," ",rank) as title from dota_medals where id = reports.medal limit 1), null) as rank_current,
            if(orders.type = 6, (select title from users_pricelists_dota_cups_prices where id = reports.medal limit 1), null) as cup_current,
            if(orders.type = 9, (select concat(title," ",rank) as title from users_pricelists_dota_autochess_prices where id = reports.medal limit 1), null) as chess_current,
            if(orders.type = 8, (select title from users_pricelists_dota_behavior_score_prices where reports.mmr >= `from` and reports.mmr <= `till` limit 1), null) as grade_current
            FROM orders_reports as reports
            LEFT JOIN orders on orders.id = reports.order_id
            LEFT JOIN orders_types on orders_types.id = orders.type
            LEFT JOIN orders_games on orders_games.id = orders_types.game_id
            WHERE reports.starter = 0
            ORDER BY reports.id desc LIMIT 5');
        return view('statisticsEmbed', ['reports'=>$reports, 'order_page_url'=>$order_page_url, 'locale'=>$locale]);
    }
}