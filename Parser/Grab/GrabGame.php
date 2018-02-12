<?php
namespace PRS\Parser\Grab;

use PRS\Parser\Engine\Start as Engine;
use PRS\Parser\Report;
use PRS\Model;

///////////////////////////////
   // ПАРСЕР СТОРІНКИ МАТЧУ //
  //////////////////////////////////////////////////////////////////////////////

class GrabGame
{   
    use ParseStadium;

    private $parts;
    private $game;
    private $report;

    private $region;
    private $tournament;
    private $season;
    private $stage;
    private $group;
    private $stadium;

    private $t1_players = [];
    private $t2_players = [];

    private $t1_coaches = [];
    private $t2_coaches = [];
    
    private $goals = [];
    private $t1_goals = [];
    private $t2_goals = [];
    private $t1_pen = [];
    private $t2_pen = [];
    
    private $substitutions = [];
    private $cards = [];

    private $timer;


//  ----------------------------------------------------------------------------
//  Конструктор
//  ----------------------------------------------------------------------------
    public function __construct()
    {
        // Включаємо таймер
        $this->timer = microtime(true);

        // Моделі регіонів-турнірів-сезонів-етапів-груп
        $this->region = new Model\Region;
        $this->tournament = new Model\Tournament;
        $this->season = new Model\Season;
        $this->stage = new Model\Stage;
        $this->group = new Model\Group;

        // Модель стадіона
        $this->stadium = new Model\Stadium;
    }
    
//  ----------------------------------------------------------------------------
//  Запуск
//  ----------------------------------------------------------------------------
    public function grab($task)
    {
        // створення рапорта
        $this->report = new Report($task);

        // Модель матчу
        $this->game = new Model\Game;
        
        // $this->game = Model\Game::getById($task->id);
        // if (!$this->game) {
        //     $this->game = new Model\Game;
        //     $this->game->id = $task->id;
        //     $this->game->start_set = 0;
        // }

        $mode = $task->mode;

        // збереження потрібних частин дому
        if ($this->split($task)){
            //$this->$mode();
        } else {
            $this->report->addLog('Нема такого матчу');
        }

        // точка завершення 
        $this->report->setTimeParse(round(microtime(true) - $this->timer, 3));
    }
//  ----------------------------------------------------------------------------
//  Збір необхідних частин документу
//  ----------------------------------------------------------------------------
    private function split($task)
    {
        $parts = new \stdClass();

        // ---------------------------------------------------------------------
        // Завантаження сторінки
        // ---------------------------------------------------------------------
        $curl_timer = microtime(true);

        $url_show = TARGET_DOMAIN . 'Matches/' . $task->id . '/Show/';
        $url_live = TARGET_DOMAIN . 'Matches/' . $task->id . '/Live/';
        
        $html = Engine::curlGet($url_show);
        //$html = Engine::file_get_html($url_show);
        echo 'user_agent = ' . ini_get('user_agent') . '<br>';
        echo 'allow_url_fopen = ' . ini_get('allow_url_fopen') . '<br>';
        if (strpos($html, 'was not found on this server') > 0) {
            return false;
        }

        echo $html;

        $r = strpos($html, 'was not found');
        var_dump($r);
        echo '<br>';
        echo $url_show . '<br>';
        $regexp = '~matchHeaderJson.*?;~';
        preg_match($regexp, $html, $json_head);
        //$parts->json_head = 

        var_dump($json_head);

        /*

        if (!$html){
            $this->report->addLog('Не отриманий HTML :(');
            return false;
        }

        if (in_array($mode, ['anonse', 'anonseCorr1', 'full'])) {
            $html_stadium = Engine::curlGet(TARGET_DOMAIN . $url . 'venue/');
        }
        $this->report->setTimeCurl(round(microtime(true) - $curl_timer, 3));
        
        // ---------------------------------------------------------------------
        // Генеруємо дом
        // ---------------------------------------------------------------------
        $dom = Engine::str_get_html($html);
        if (!$dom) {
            $this->report->addLog('Не сформований DOM :(');
            return false;
        }
        
        // -----------------------------------------------------------------
        // основний верхній блок
        if (in_array($mode, ['anonse', 'anonseCorr1', 'preview', 'review', 'full'])) {
            if (!$parts->top_block = $dom->find('#page_match_1_block_match_info_4', 0)) {
                $this->report->addLog('Нема такого матчу :(');
                return false;
            }
        }
        
        // -----------------------------------------------------------------
        // назва регіону
        if (in_array($mode, ['anonse', 'anonseCorr1', 'preview', 'review', 'full'])) {
            if (!$parts->region_name = $dom->find('h2', 0)) {
                $this->report->addLog('невідоми регіон :(');
                return false;
            }
        }

        // -----------------------------------------------------------------
        // ліве меню
        if (in_array($mode, ['anonse', 'anonseCorr1', 'preview', 'review', 'full'])) {
            if (!$parts->left_menu = $dom->find('ul.left-tree', 0)) {
                $this->report->addLog('Не знайдене меню :(');
                return false;
            }
        }

        // ---------------------------------------------------------------------
        // Рефері
        if (in_array($mode, ['preview', 'review', 'full'])) {
            $parts->referee = $dom->find('div.block_match_additional_info dl.details', 0);
        }
        
        // ---------------------------------------------------------------------
        // гравці
        if (in_array($mode, ['preview', 'review', 'full'])) {
            // основні
            $parts->lineups = $dom->find('div.combined-lineups-container', 0);
            // запасні
            $parts->subst = $dom->find('div.combined-lineups-container', 1);
            // травмовані
            $parts->sideline = $dom->find('#page_match_1_block_match_sidelined_11', 0);
        }
        
        // ---------------------------------------------------------------------
        // Голи детально
        if (in_array($mode, ['review', 'full'])) {
            $parts->goals = $dom->find('div.block_match_goals', 0);
        }
        // ---------------------------------------------------------------------
        // Пенальті
        if (in_array($mode, ['review', 'full'])) {
            $parts->penalty = $dom->find('div.block_match_penalty_shootout', 0);
        }

        // ---------------------------------------------------------------------
        // Стадіон || має бути в кінці. Змінює змінну $dom
        // ---------------------------------------------------------------------
        if (in_array($mode, ['anonse', 'review', 'anonseCorr1', 'full'])) {
            // Перегенеровуємо дом
            if (isset($html_stadium)){
                unset($dom);
                $dom = Engine::str_get_html($html_stadium);
                if ($dom) {
                    $parts->stadium = $dom->find('div.block_venue_info-wrapper', 0);
                }
            }
        }

        // ---------------------------------------------------------------------
        // Вичищаємо дом
        // ---------------------------------------------------------------------
        unset($dom);

        */

        $this->parts = $parts;
        return true;
    }
//  ----------------------------------------------------------------------------
//  Парсер Анонс
//  ----------------------------------------------------------------------------
    private function anonse()
    {   
        // Запуск парсерів
        if ($this->parseMain()){
            $this->parseStadium();
            // ---------------------------------------------------------------------
            // збереження в БД
            $this->season->save();
            $this->stage->save();
            $this->group->save();
            $this->stadium->save();

            // МАТЧ 
            $this->game->content_type = 1;
            $this->game->status = 2;
            $this->game->stadium_id = $this->stadium->id;
            $this->game->save();

            if (strtotime($this->game->start) < time() + 60 * 60 * 24 && strtotime($this->game->start) > time())
                $this->report->nextDay = true;
            if (strtotime($this->game->start) < time() + 60 * 60 * 24 * 7  && strtotime($this->game->start) > time())
                $this->report->nextWeek = true;    
        } else {
            $this->report->error = 1;
        }

    }

//  ----------------------------------------------------------------------------
//  Парсер Анонс
//  ----------------------------------------------------------------------------
    private function anonseCorr1()
    {   
        // Запуск парсерів
        if ($this->parseMain()){
            $this->parseStadium();
            
            // ---------------------------------------------------------------------
            // збереження в БД
            $this->season->save();
            $this->stage->save();
            $this->group->save();
            $this->stadium->save();

            // МАТЧ 
            $this->game->content_type = 1;
            $this->game->status = 2;
            $this->game->stadium_id = $this->stadium->id;
            $this->game->upd = true;
            $this->game->save();

            if (strtotime($this->game->start) < time() + 60 * 60 * 24 && strtotime($this->game->start) > time())
                $this->report->nextDay = true;
            if (strtotime($this->game->start) < time() + 60 * 60 * 24 * 7 && strtotime($this->game->start) > time())
                $this->report->nextWeek = true;    

            // ---------------------------------------------------------------------
            // Добавлення задач
            $this->report->addTask("team", 'full', $this->game->team_1_id, $this->game->team_1_url);
            $this->report->addTask("team", 'full', $this->game->team_2_id, $this->game->team_2_url);
        } else {
            $this->report->error = 1;
        }
    }

//  ----------------------------------------------------------------------------
//  Парсер передматчевих складів
//  ----------------------------------------------------------------------------
    private function preview()
    {   
        // Запуск парсерів
        $this->parseReferee();
        $this->parsePlayers(false);

        // Збереження гравців
        if (!empty($this->t1_players) && !empty($this->t2_players)) {
            
            $this->report->has_players = true;
            
            $this->report->addLog('Гравці команди 1');
            foreach ($this->t1_players as $player) {
                if ($player->injury) {
                    $this->report->addLog('Статус = ' . $player->status . ' : : травма = ' . $player->injury . ' : : id = ' . $player->id . ' : : ' . $player->name);
                } else {
                    $this->report->addLog('Статус = ' . $player->status . ' : : номер = ' . $player->number . ' : : id = ' . $player->id . ' : : ' . $player->name);
                }
                $player->saveToGame($this->game->id, $this->game->team_1_id);
                $this->report->addTask("player", 'takeAll', $player->id, $player->href);
            }
            
            $this->report->addLog('Гравці команди 2');
            foreach ($this->t2_players as $player) {
                if ($player->injury) {
                    $this->report->addLog('Статус = ' . $player->status . ' : : травма = ' . $player->injury . ' : : id = ' . $player->id . ' : : ' . $player->name);
                } else {
                    $this->report->addLog('Статус = ' . $player->status . ' : : номер = ' . $player->number . ' : : id = ' . $player->id . ' : : ' . $player->name);
                }
                $player->saveToGame($this->game->id, $this->game->team_2_id);
                $this->report->addTask("player", 'takeAll', $player->id, $player->href);
            }
        }
        
        // Збереження тренерів
        if (!empty($this->t1_coaches) && !empty($this->t2_coaches)) {
            $this->report->has_coaches = true;
            $this->report->addLog('Тренери');
            foreach ($this->t1_coaches as $coach) {
                $this->report->addLog('Тренер команди 1 : : id = ' . $coach->id);
                $coach->saveToGame($this->game->id, $this->game->team_1_id);
                $this->report->addTask("coach", 'takeAll', $coach->id, $coach->href);
            }

            foreach ($this->t2_coaches as $coach) {
                $this->report->addLog('Тренер команди 2 : : id = ' . $coach->id);
                $coach->saveToGame($this->game->id, $this->game->team_2_id);
                $this->report->addTask("coach", 'takeAll', $coach->id, $coach->href);
            }
        }
        
        $this->game->status = 3;

        // if (strtotime($this->game->start) < time() + 60 * 60 * 24 && strtotime($this->game->start) > time())
        //     $this->report->nextDay = true;
        // if (strtotime($this->game->start) < time() + 60 * 60 * 24 * 7 && strtotime($this->game->start) > time())
        //     $this->report->nextWeek = true;    

        // МАТЧ 
        $this->game->save();
    }

//  ----------------------------------------------------------------------------
//  Парсер результатів
//  ----------------------------------------------------------------------------
    private function review($corr = false)
    {   
        // Запуск парсерів
        $this->parseMain();
        $this->parseReferee();
        
        // для корекції
        if ($corr) {
            $this->parseStadium();
            $this->game->clearGoals();
            $this->game->clearSubstitutions();
            $this->game->clearCards();
        }

        $this->game->clearPlayers();
        $this->game->clearCoaches();
        
        $this->game->team_1_red_cards = 0;
        $this->game->team_2_red_cards = 0;
        $this->game->team_1_yellow_cards = 0;
        $this->game->team_2_yellow_cards = 0;
        $this->game->team_1_substitutions = 0;
        $this->game->team_2_substitutions = 0;

        $this->parsePlayers(true);
        $this->parseGoals();

        // Збереження гравців
        if (!empty($this->t1_players) && !empty($this->t2_players)) {
            
            $this->report->has_players = true;
            
            $this->report->addLog('Гравці команди 1');
            foreach ($this->t1_players as $player) {
                if ($player->injury) {
                    $this->report->addLog('Статус = ' . $player->status . ' : : травма = ' . $player->injury . ' : : id = ' . $player->id . ' : : ' . $player->name);
                } else {
                    $this->report->addLog('Статус = ' . $player->status . ' : : номер = ' . $player->number . ' : : id = ' . $player->id . ' : : ' . $player->name);
                }
                $player->saveToGame($this->game->id, $this->game->team_1_id);
                $this->report->addTask("player", 'takeAll', $player->id, $player->href);
            }
            
            $this->report->addLog('Гравці команди 2');
            foreach ($this->t2_players as $player) {
                if ($player->injury) {
                    $this->report->addLog('Статус = ' . $player->status . ' : : травма = ' . $player->injury . ' : : id = ' . $player->id . ' : : ' . $player->name);
                } else {
                    $this->report->addLog('Статус = ' . $player->status . ' : : номер = ' . $player->number . ' : : id = ' . $player->id . ' : : ' . $player->name);
                }
                $player->saveToGame($this->game->id, $this->game->team_2_id);
                $this->report->addTask("player", 'takeAll', $player->id, $player->href);
            }
        }
        
        // Збереження тренерів
        if (!empty($this->t1_coaches) && !empty($this->t2_coaches)) {
            $this->report->has_coaches = true;
            $this->report->addLog('Тренери');
            foreach ($this->t1_coaches as $coach) {
                $this->report->addLog('Тренер команди 1 : : id = ' . $coach->id);
                $coach->saveToGame($this->game->id, $this->game->team_1_id);
                $this->report->addTask("coach", 'takeAll', $coach->id, $coach->href);
            }

            foreach ($this->t2_coaches as $coach) {
                $this->report->addLog('Тренер команди 2 : : id = ' . $coach->id);
                $coach->saveToGame($this->game->id, $this->game->team_2_id);
                $this->report->addTask("coach", 'takeAll', $coach->id, $coach->href);
            }
        }

        foreach ($this->goals as $goal) {
            if ($goal->time and (!$this->game->first_goal_time or $goal->time < $this->game->first_goal_time)){
                $this->game->first_goal_time = $goal->time;
                $this->game->first_goal_team_id = $goal->team_id;
                $this->game->first_goal_player_id = $goal->player_id;
            }
            $this->report->addLog('Гол : : team_id = ' . $goal->team_id . ' : : player_id = ' . $goal->player_id . ' : : time = ' . $goal->time . ' : : period = ' . $goal->period);
            $goal->save();
        }
        foreach ($this->substitutions as $subst) {
            if ($subst->time and (!$this->game->first_substitution_time or $subst->time < $this->game->first_substitution_time)){
                $this->game->first_substitution_time = $subst->time;
                $this->game->first_substitution_team_id = $subst->team_id;
                $this->game->first_substitution_player_id_on = $subst->player_id_on;
                $this->game->first_substitution_player_id_off = $subst->player_id_off;
            }
            $this->report->addLog('Заміна : : team_id = ' . $subst->team_id . ' : : player_id_off = ' . $subst->player_id_off . ' : : player_id_on = ' . $subst->player_id_on . ' : : time = ' . $subst->time);
            $subst->save();
        }

        if (!empty($this->cards)) {
            $this->report->has_cards = true;
            foreach ($this->cards as $card) {
                if ($card->attributes == 'RC'){
                    if ($card->time and (!$this->game->first_red_card_time or $card->time < $this->game->first_red_card_time)){
                        $this->game->first_red_card_time = $card->time;
                        $this->game->first_red_card_team_id = $card->team_id;
                        $this->game->first_red_card_player_id = $card->player_id;
                    }
                } else {
                    if ($card->time and (!$this->game->first_yellow_card_time or $card->time < $this->game->first_yellow_card_time)){
                        $this->game->first_yellow_card_time = $card->time;
                        $this->game->first_yellow_card_team_id = $card->team_id;
                        $this->game->first_yellow_card_player_id = $card->player_id;
                    }
                }
                $this->report->addLog('Картка : : type = ' . $card->attributes . ' : : team_id = ' . $card->team_id . ' : : player_id = ' . $card->player_id . ' : : time = ' . $card->time);
                $card->save();
            }
        }

        $this->game->status = 4;

        // МАТЧ
        $this->game->save();
    }



//  ----------------------------------------------------------------------------
//  Парсинг основних даних матчу
//  ----------------------------------------------------------------------------
    private function parseReferee()
    {
        if ($this->parts->referee) {

            $this->report->has_referee = true;

            // урли рефері
            @$ref_main_id_href = substr($this->parts->referee->find('a', 0)->href, 1);
            @$ref_assist_1_id_href = substr($this->parts->referee->find('a', 1)->href, 1);
            @$ref_assist_2_id_href = substr($this->parts->referee->find('a', 2)->href, 1);
            @$ref_fourth_id_href = substr($this->parts->referee->find('a', 3)->href, 1);

            // ID рефері
            @$this->game->ref_main_id = array_reverse(explode('/', $ref_main_id_href))[1];
            @$this->game->ref_assist_1_id = array_reverse(explode('/', $ref_assist_1_id_href))[1];
            @$this->game->ref_assist_2_id = array_reverse(explode('/', $ref_assist_2_id_href))[1];
            @$this->game->ref_fourth_id = array_reverse(explode('/', $ref_fourth_id_href))[1];
            
            // логи
            @$this->report->addLog('Рефері - головний : : id = ' . $this->game->ref_main_id . ' : : url = ' . $ref_main_id_href);
            @$this->report->addLog('Рефері - асистент 1 : : id = ' . $this->game->ref_assist_1_id . ' : : url = ' . $ref_assist_1_id_href);
            @$this->report->addLog('Рефері - асистент 2 : : id = ' . $this->game->ref_assist_2_id . ' : : url = ' . $ref_assist_2_id_href);
            @$this->report->addLog('Рефері - четвертий : : id = ' . $this->game->ref_fourth_id . ' : : url = ' . $ref_fourth_id_href);

            // таски по рефері
            if ($this->game->ref_main_id)
                $this->report->addTask("referee", 'takeAll', $this->game->ref_main_id, $ref_main_id_href);
            if ($this->game->ref_assist_1_id)
                $this->report->addTask("referee", 'takeAll', $this->game->ref_assist_1_id, $ref_assist_1_id_href);
            if ($this->game->ref_assist_2_id)
                $this->report->addTask("referee", 'takeAll', $this->game->ref_assist_2_id, $ref_assist_2_id_href);
            if ($this->game->ref_fourth_id)
                $this->report->addTask("referee", 'takeAll', $this->game->ref_fourth_id, $ref_fourth_id_href);
        } else {
            $this->report->addLog('Рефері не знайдено');
        }
    }

//  ----------------------------------------------------------------------------
//  Парсинг голів
//  ----------------------------------------------------------------------------
    private function parseGoals()
    {
        // ---------------------------------------------------------------------
        // Голи
        
        if ($this->parts->goals) {
            $this->report->has_goals = true;
            // гравці і тренери команди 1
            $tr_goal = $this->parts->goals->find('table.events tbody', 0)->find('tr');
            //$tr_goal = $this->parts->goals->find('table', 0);
            //if ($tr_goal) $tr_goal = $tr_goal->find('tr');
            //var_dump($tr_goal);
            foreach ($tr_goal as $tr) {
                $t1_div = $tr->find('td.player-a', 0)->find('div', 0);
                $t2_div = $tr->find('td.player-b', 0)->find('div', 0);
                if ($t1_div->innertext != '') {
                    $goal = array_shift($this->t1_goals);
                    if ($goal) {
                        $time_arr = explode('+', str_replace("'", '', $t1_div->find('span.minute', 0)->innertext));
                        foreach ($time_arr as $t_part) $goal->time += (int)$t_part;
                        $goal->player_id = array_reverse(explode('/', $t1_div->find('a', 0)->href))[1];
                        $this->goals[] = $goal;
                    }
                }
                if ($t2_div->innertext != '') {
                    $goal = array_shift($this->t2_goals);
                    if ($goal) {
                        $time_arr = explode('+', str_replace("'", '', $t2_div->find('span.minute', 0)->innertext));
                        foreach ($time_arr as $t_part) $goal->time += (int)$t_part;
                        $goal->player_id = array_reverse(explode('/', $t2_div->find('a', 0)->href))[1];
                        $this->goals[] = $goal;
                    }
                }
            }
        }

        if ($this->parts->penalty) {

            // пенальті
            $tr_pen = $this->parts->penalty->find('table.events tbody', 0)->find('tr');
            foreach ($tr_pen as $tr) {
                $t1_div = $tr->find('td.player-a', 0)->find('div', 0);
                $t2_div = $tr->find('td.player-b', 0)->find('div', 0);
                if ($t1_div->innertext != '') {
                    if (array_reverse(explode('/', $t1_div->find('img', 0)->src))[0] == 'PSG.png'){
                        //var_dump(array_reverse(explode('/', $t1_div->find('img', 0)->src))[0]);
                        $goal = array_shift($this->t1_pen);
                        $goal->player_id = array_reverse(explode('/', $t1_div->find('a', 0)->href))[1];
                        $this->goals[] = $goal;
                    }
                }
                if ($t2_div->innertext != '') {
                    if (array_reverse(explode('/', $t2_div->find('img', 0)->src))[0] == 'PSG.png'){
                        //var_dump(array_reverse(explode('/', $t2_div->find('img', 0)->src))[0]);
                        $goal = array_shift($this->t2_pen);
                        $goal->player_id = array_reverse(explode('/', $t2_div->find('a', 0)->href))[1];
                        $this->goals[] = $goal;
                    }
                }
            }
        }

        //$this->goals = array_merge($this->goals, $this->t1_goals, $this->t2_goals, $this->t1_pen, $this->t2_pen);


    }

//  ----------------------------------------------------------------------------
//  Парсинг гравців і тренерів
//  ----------------------------------------------------------------------------
    private function parsePlayers($review_mode)
    {
        // ---------------------------------------------------------------------
        // Основний склад
        
        if ($this->parts->lineups) {

            // гравці і тренери команди 1
            $tr_team_1 = $this->parts->lineups->find('table.playerstats tbody', 0)->find('tr');
            foreach ($tr_team_1 as $tr) {
                if ($tr->find('td.player', 0)) {
                    $player = new Model\Player;
                    $player->href = substr($tr->find('a', 0)->href, 1);
                    $player->name = trim($tr->find('a', 0)->innertext);
                    $player->id = array_reverse(explode('/', $player->href))[1];
                    @$player->number = $tr->find('td.shirtnumber', 0)->innertext;
                    $player->status = 1;
                    $this->t1_players[] = $player;
                    if ($review_mode) {
                        $spans = $tr->find('td.bookings span');
                        if ($spans){
                            foreach ($spans as $span) {
                                $ico = trim(explode('.', array_reverse(explode('/', $span->find('img', 0)->src))[0])[0]);
                                
                                if (in_array($ico, ['RC', 'YC', 'Y2C'])) {

                                    $card = new Model\Card;
                                    $card->game_id = $this->game->id;
                                    $card->team_id = $this->game->team_1_id;
                                    $card->player_id = $player->id;
                                    $card->attributes = $ico;
                                    
                                    $card->time = 0;
                                    $time_arr = explode('+', trim(str_replace("'", '', array_reverse(explode('>', $span->innertext))[0])));
                                    foreach ($time_arr as $t_part) $card->time += (int)$t_part;
                                    $this->cards[] = $card;

                                    if ($card->time === 0)
                                        $card->time = null;

                                    if ($ico = 'RC') {
                                        $this->game->team_1_red_cards++;
                                    } else {
                                        $this->game->team_1_yellow_cards++;
                                    }
                                }
                            }
                        }
                    }
                } else {
                    $coach = new Model\Coach;
                    @$coach->href = substr($tr->find('a', 0)->href, 1);
                    if (!$coach->href) continue;
                    $coach->id = array_reverse(explode('/', $coach->href))[1];

                    $this->t1_coaches[] = $coach;
                }
            }

            // гравці і тренери команди 2
            $tr_team_2 = $this->parts->lineups->find('table.playerstats tbody', 1)->find('tr');
            foreach ($tr_team_2 as $tr) {
                if ($tr->find('td.player', 0)) {
                    $player = new Model\Player;
                    $player->href = substr($tr->find('a', 0)->href, 1);
                    $player->name = trim($tr->find('a', 0)->innertext);
                    $player->id = array_reverse(explode('/', $player->href))[1];
                    @$player->number = $tr->find('td.shirtnumber', 0)->innertext;
                    $player->status = 1;
                    $this->t2_players[] = $player;
                    if ($review_mode) {
                        $spans = $tr->find('td.bookings span');
                        if ($spans){
                            foreach ($spans as $span) {
                                $ico = trim(explode('.', array_reverse(explode('/', $span->find('img', 0)->src))[0])[0]);
                                
                                if (in_array($ico, ['RC', 'YC', 'Y2C'])) {

                                    $card = new Model\Card;
                                    $card->game_id = $this->game->id;
                                    $card->team_id = $this->game->team_2_id;
                                    $card->player_id = $player->id;
                                    $card->attributes = $ico;

                                    $card->time = 0;
                                    $time_arr = explode('+', trim(str_replace("'", '', array_reverse(explode('>', $span->innertext))[0])));
                                    foreach ($time_arr as $t_part) $card->time += (int)$t_part;
                                    $this->cards[] = $card;

                                    if ($card->time === 0)
                                        $card->time = null;

                                    if ($ico = 'RC') {
                                        $this->game->team_2_red_cards++;
                                    } else {
                                        $this->game->team_2_yellow_cards++;
                                    }
                                }
                            }
                        }
                    }
                } else {
                    $coach = new Model\Coach;
                    @$coach->href = substr($tr->find('a', 0)->href, 1);
                    if (!$coach->href) continue;
                    $coach->id = array_reverse(explode('/', $coach->href))[1];
                    $this->t2_coaches[] = $coach;
                }
            }

        }
        
        // ---------------------------------------------------------------------
        // Запасні гравці
        
        if ($this->parts->subst) {
            
            $this->report->has_substitutions = true;

            // запасні гравці команди 1
            $tr_team_1 = $this->parts->subst->find('table.playerstats tbody', 0)->find('tr');
            foreach ($tr_team_1 as $tr) {
                $player = new Model\Player;
                @$player->href = substr($tr->find('a', 0)->href, 1);
                if (!$player->href) continue;
                $player->name = trim($tr->find('a', 0)->innertext);
                $player->id = array_reverse(explode('/', $player->href))[1];
                @$player->number = $tr->find('td.shirtnumber', 0)->innertext;
                $player->status = 2;
                $this->t1_players[] = $player;
                $p_subst = $tr->find('p.substitute-out', 0);
                if ($review_mode && $p_subst){
                    $subst = new Model\Substitution;
                    $subst->game_id = $this->game->id;
                    $subst->team_id = $this->game->team_1_id;
                    $subst->player_id_off = array_reverse(explode('/', substr($p_subst->find('a', 0)->href, 1)))[1];
                    $subst->player_id_on = $player->id;
                    $time_arr = explode('+', trim(str_replace("'", '', array_reverse(explode('</a>', $p_subst->innertext))[0])));
                    foreach ($time_arr as $t_part) $subst->time += (int)$t_part;
                    $this->substitutions[] = $subst;
                }
                if ($review_mode) {
                    $spans = $tr->find('td.bookings span');
                    if ($spans){
                        foreach ($spans as $span) {
                            $ico = trim(explode('.', array_reverse(explode('/', $span->find('img', 0)->src))[0])[0]);
                            
                            if (in_array($ico, ['RC', 'YC', 'Y2C'])) {

                                $card = new Model\Card;
                                $card->game_id = $this->game->id;
                                $card->team_id = $this->game->team_1_id;
                                $card->player_id = $player->id;
                                $card->attributes = $ico;

                                $card->time = 0;
                                $time_arr = explode('+', trim(str_replace("'", '', array_reverse(explode('>', $span->innertext))[0])));
                                foreach ($time_arr as $t_part) $card->time += (int)$t_part;
                                $this->cards[] = $card;

                                if ($card->time === 0)
                                    $card->time = null;

                                if ($ico = 'RC') {
                                    $this->game->team_1_red_cards++;
                                } else {
                                    $this->game->team_1_yellow_cards++;
                                }
                            }
                        }
                    }
                }

            }

            // запасні гравці команди 2
            $tr_team_2 = $this->parts->subst->find('table.playerstats tbody', 1)->find('tr');
            foreach ($tr_team_2 as $tr) {
                $player = new Model\Player;
                @$player->href = substr($tr->find('a', 0)->href, 1);
                if (!$player->href) continue;
                $player->name = trim($tr->find('a', 0)->innertext);
                $player->id = array_reverse(explode('/', $player->href))[1];
                @$player->number = $tr->find('td.shirtnumber', 0)->innertext;
                $player->status = 2;
                $this->t2_players[] = $player;
                $p_subst = $tr->find('p.substitute-out', 0);
                if ($review_mode && $p_subst){
                    $subst = new Model\Substitution;
                    $subst->game_id = $this->game->id;
                    $subst->team_id = $this->game->team_2_id;
                    $subst->player_id_off = array_reverse(explode('/', substr($p_subst->find('a', 0)->href, 1)))[1];
                    $subst->player_id_on = $player->id;
                    $time_arr = explode('+', trim(str_replace("'", '', array_reverse(explode('</a>', $p_subst->innertext))[0])));
                    foreach ($time_arr as $t_part) $subst->time += (int)$t_part;
                    $this->substitutions[] = $subst;
                }
                if ($review_mode) {
                    $spans = $tr->find('td.bookings span');
                    if ($spans){
                        foreach ($spans as $span) {
                            $ico = trim(explode('.', array_reverse(explode('/', $span->find('img', 0)->src))[0])[0]);
                            
                            if (in_array($ico, ['RC', 'YC', 'Y2C'])) {

                                $card = new Model\Card;
                                $card->game_id = $this->game->id;
                                $card->team_id = $this->game->team_2_id;
                                $card->player_id = $player->id;
                                $card->attributes = $ico;

                                $card->time = 0;
                                $time_arr = explode('+', trim(str_replace("'", '', array_reverse(explode('>', $span->innertext))[0])));
                                foreach ($time_arr as $t_part) $card->time += (int)$t_part;
                                $this->cards[] = $card;

                                if ($card->time === 0)
                                    $card->time = null;

                                if ($ico = 'RC') {
                                    $this->game->team_2_red_cards++;
                                } else {
                                    $this->game->team_2_yellow_cards++;
                                }
                            }
                        }
                    }
                }                
            }
        }

        // ---------------------------------------------------------------------
        // Травмовані гравці
        
        if ($this->parts->sideline) {

            // Травмовані гравці команди 1
            $node = $this->parts->sideline->find('table.left tbody', 0);
            
            if ($node){
                $node = $node->find('tr[class!=group-head]');
            }
            
            if ($node){
                foreach ($node as $tr) {
                    $player = new Model\Player;
                    @$player->href = substr($tr->find('a', 0)->href, 1);
                    if (!$player->href) continue;
                    $player->name = trim($tr->find('a', 0)->innertext);
                    $player->id = array_reverse(explode('/', $player->href))[1];
                    @$player->injury = $tr->find('td.injury', 0)->title;
                    $player->status = 3;
                    $this->t1_players[] = $player;
                }
            }

            // Травмовані гравці команди 2
            $node = $this->parts->sideline->find('table.right tbody', 0);
            if ($node){
                $node = $node->find('tr[class!=group-head]');
            }
            if ($node){
                foreach ($node as $tr) {
                    $player = new Model\Player;
                    @$player->href = substr($tr->find('a', 0)->href, 1);
                    $player->name = trim($tr->find('a', 0)->innertext);
                    if (!$player->href) continue;
                    $player->id = array_reverse(explode('/', $player->href))[1];
                    @$player->injury = $tr->find('td.injury', 0)->title;
                    $player->status = 3;
                    $this->t2_players[] = $player;
                }
            }
        }
    }

//  ----------------------------------------------------------------------------
//  Парсинг основних даних матчу
//  ----------------------------------------------------------------------------
    private function parseMain()
    {
        if (!$this->parts->top_block) {
            $this->report->addLog('Не грузиться');
            return false;
        }
        // ---------------------------------------------------------------------
        // Команда 1
        $team_1_link = $this->parts->top_block->find('h3.thick', 0)->find('a',0);
        $team_1_name = trim($team_1_link->innertext);
        $this->game->team_1_url = $team_1_link->href;
        $this->game->team_1_id = array_reverse(explode('/', $this->game->team_1_url))[1];
        // Логи
        $this->report->addLog('Команда 1 : : id = ' . $this->game->team_1_id . ' : : name = ' . $team_1_name);

        // ---------------------------------------------------------------------
        // Команда 2
        $team_2_link = $this->parts->top_block->find('h3.thick', 2)->find('a',0);
        $team_2_name = trim($team_2_link->innertext);
        $this->game->team_2_url = $team_2_link->href;
        $this->game->team_2_id = array_reverse(explode('/', $this->game->team_2_url))[1];
        // Логи
        $this->report->addLog('Команда 2 : : id = ' . $this->game->team_2_id . ' : : name = ' . $team_2_name);

        // ---------------------------------------------------------------------
        // Час проведення
        $timeStamp = $this->parts->top_block->find('a span.timestamp', 0)->attr["data-value"];
        $this->game->start = date("Y-m-d H:i:s", $timeStamp);

        $this->game->anonce = 0;
        
        // ---------------------------------------------------------------------
        // Info
        $i = 0;
        while ($table = $this->parts->top_block->find('div.middle', 1)->children($i)) {
            $table = $table->find('dl', 0);
            $j = 0;
            if (trim($table->innertext) != '') {
                $has_half = false;
                while (@$infoName = $table->find('dt', $j)->innertext) {
                    $paramNode = $table->find('dd', $j);
                    switch ($infoName) {
                        case 'Competition':
                            $this->stage->id = substr(array_reverse(explode('/', trim($paramNode->find('a', 0)->href)))[1], 1);
                            $this->stage->name = ucwords(str_replace('-', ' ', array_reverse(explode('/', trim($paramNode->find('a', 0)->href)))[2]));
                            $this->tournament->name = trim($paramNode->find('a', 0)->innertext);
                            $this->game->stage_id = $this->stage->id;
                            $infoName = 'Етап ' . $this->stage->name;
                            $infoParam = $this->stage->id;
                            break;
                        case 'Date':
                            $infoName = 'Дата';
                            $infoParam = date("d M Y", $timeStamp);
                            break;
                        case 'Game week':
                            $infoParam = trim($paramNode->innertext);
                            $this->game->week = $infoParam;
                            break;
                        case 'Kick-off':
                            $infoName = 'Час початку';
                            $this->game->start_set = 1;
                            //$infoParam = trim($paramNode->innertext);
                            $infoParam = date("H:i", $timeStamp);
                            break;
                        case 'Half-time':
                            $has_half = true;
                            $infoParam = trim($paramNode->innertext);
                            $goal_arr = explode('-', $infoParam);
                            // голи забиті командою 1 в першому таймі
                            $this->game->team_1_goals_p1 = (int)trim($goal_arr[0]);
                            // голи забиті командою 2 в першому таймі
                            $this->game->team_2_goals_p1 = (int)trim($goal_arr[1]);
                            
                            if ($this->game->team_1_goals_p1 > 0){
                                for ($i=0; $i < $this->game->team_1_goals_p1; $i++) { 
                                    $goal = new Model\Goal();
                                    $goal->game_id = $this->game->id;
                                    $goal->team_id = $this->game->team_1_id;
                                    $goal->period = 1;
                                    $this->t1_goals[] = $goal;
                                }
                            }

                            if ($this->game->team_2_goals_p1 > 0){
                                for ($i=0; $i < $this->game->team_2_goals_p1; $i++) { 
                                    $goal = new Model\Goal();
                                    $goal->game_id = $this->game->id;
                                    $goal->team_id = $this->game->team_2_id;
                                    $goal->period = 1;
                                    $this->t2_goals[] = $goal;
                                }
                            }
                            break;
                        case 'Full-time':
                            $infoParam = trim($paramNode->innertext);
                            $goal_arr = explode('-', $infoParam);
                            // голи забиті командою 1 в другому таймі
                            $this->game->team_1_goals_p2 = (int)trim($goal_arr[0]) - $this->game->team_1_goals_p1;
                            // голи забиті командою 2 в другому таймі
                            $this->game->team_2_goals_p2 = (int)trim($goal_arr[1]) - $this->game->team_2_goals_p1;
                            if ($this->game->team_1_goals_p34 || $this->game->team_1_goals_p34) {
                                 break;
                            }
                            $this->game->anonce = 1;
                            // голи забиті командою 1
                            $this->game->team_1_goals = (int)trim($goal_arr[0]);
                            // голи забиті командою 2
                            $this->game->team_2_goals = (int)trim($goal_arr[1]);
                            if ($this->game->team_1_goals_p2 > 0){
                                for ($i=0; $i < $this->game->team_1_goals_p2; $i++) { 
                                    $goal = new Model\Goal();
                                    $goal->game_id = $this->game->id;
                                    $goal->team_id = $this->game->team_1_id;
                                    if ($has_half) $goal->period = 2;
                                    $this->t1_goals[] = $goal;
                                }
                            }
                            if ($this->game->team_2_goals_p2 > 0){
                                for ($i=0; $i < $this->game->team_2_goals_p2; $i++) { 
                                    $goal = new Model\Goal();
                                    $goal->game_id = $this->game->id;
                                    $goal->team_id = $this->game->team_2_id;
                                    if ($has_half) $goal->period = 2;
                                    $this->t2_goals[] = $goal;
                                }
                            }
                            break;
                        case 'Extra-time':
                            $infoParam = trim($paramNode->innertext);
                            $goal_arr = explode('-', $infoParam);
                            $this->game->anonce = 2;
                            // голи забиті командою 1
                            $this->game->team_1_goals = (int)trim($goal_arr[0]);
                            // голи забиті командою 2
                            $this->game->team_2_goals = (int)trim($goal_arr[1]);
                            // голи забиті командою 1 в першому таймі
                            $this->game->team_1_goals_p34 = (int)trim($goal_arr[0]) - $this->game->team_1_goals_p2;
                            // голи забиті командою 2 в першому таймі
                            $this->game->team_2_goals_p34 = (int)trim($goal_arr[1]) - $this->game->team_2_goals_p2;
                            if ($this->game->team_1_goals_p34 > 0){
                                for ($i=0; $i < $this->game->team_1_goals_p34; $i++) { 
                                    $goal = new Model\Goal();
                                    $goal->game_id = $this->game->id;
                                    $goal->team_id = $this->game->team_1_id;
                                    $goal->period = 34;
                                    $this->t1_goals[] = $goal;
                                }
                            }
                            if ($this->game->team_2_goals_p34 > 0){
                                for ($i=0; $i < $this->game->team_2_goals_p34; $i++) { 
                                    $goal = new Model\Goal();
                                    $goal->game_id = $this->game->id;
                                    $goal->team_id = $this->game->team_2_id;
                                    $goal->period = 34;
                                    $this->t2_goals[] = $goal;
                                }
                            }
                            break;
                        case 'Penalties':
                            $infoParam = trim($paramNode->innertext);
                            $this->game->anonce = 3;
                            $goal_arr = explode('-', $infoParam);
                            // голи забиті командою 1 в післяматчевих пенальті
                            $this->game->team_1_goals_p = trim($goal_arr[0]);
                            // голи забиті командою 2 в післяматчевих пенальті
                            $this->game->team_2_goals_p = trim($goal_arr[1]);
                            if ($this->game->team_1_goals_p > 0){
                                for ($i=0; $i < $this->game->team_1_goals_p; $i++) { 
                                    $goal = new Model\Goal();
                                    $goal->game_id = $this->game->id;
                                    $goal->team_id = $this->game->team_1_id;
                                    $goal->period = 5;
                                    $this->t1_pen[] = $goal;
                                }
                            }
                            if ($this->game->team_2_goals_p > 0){
                                for ($i=0; $i < $this->game->team_2_goals_p; $i++) { 
                                    $goal = new Model\Goal();
                                    $goal->game_id = $this->game->id;
                                    $goal->team_id = $this->game->team_2_id;
                                    $goal->period = 5;
                                    $this->t2_pen[] = $goal;
                                }
                            }
                            break;
                        case 'Venue':
                            $infoName = 'Стадіон';
                            $infoParam = trim($paramNode->find('a', 0)->innertext);
                            break;
                        case 'Attendance':
                            $infoName = 'Кількість глядачів';
                            $infoParam = trim($paramNode->innertext);
                            break;
                        case 'On aggregate':
                            $infoParam = trim($paramNode->find('a', 0)->innertext);
                            break;
                    }
                    // Логи 
                    $this->report->addLog($infoName . ' = ' . $infoParam);
                    $j++;
                }
            }
            $i++;
        }

        // ---------------------------------------------------------------------
        // Регіон
        // ---------------------------------------------------------------------
        $this->region->name = $this->parts->region_name->innertext;
        $this->report->addLog('Регіон = ' . $this->region->name);

        // ---------------------------------------------------------------------
        // Парсинг лівого меню
        // ---------------------------------------------------------------------
        
        // Турнір
        $level_1 = $this->parts->left_menu->find('li.expanded', 0);
        if ($level_1) {
            $tourLink = $level_1->find('a',0);
            $this->tournament->id = substr(array_reverse(explode('/', $tourLink->href))[1], 1);
            //$this->tournament->name = $tourLink->title;
            $this->report->addLog('Турнір = ' . $this->tournament->id . ' : : ' . $this->tournament->name);
            
            // Сезон
            $level_2 = $level_1->find('ul.expanded', 0)->find('li.expanded', 0);
            if (!$level_2) $level_2 = $level_1->find('ul.expanded', 0)->find('li.current', 0);
            $seasonLink = $level_2->find('a',0);
            $this->season->id = substr(array_reverse(explode('/', $seasonLink->href))[1], 1); 
            $this->season->years = str_replace('/', '-', $seasonLink->innertext); 
            $this->report->addLog('Сезон = ' . $this->season->id . ' : : ' . $this->season->years);

            // Етап, Група
            $level_2_ul = $level_2->find('ul.expanded', 0);
            if ($level_2_ul) {
                $level_3 = $level_2_ul->find('li.expanded', 0);
                if (!$level_3) $level_3 = $level_2_ul->find('li.current', 0);
                if ($level_3) {
                    $currLink = $level_3->find('a',0);
                    switch (substr(array_reverse(explode('/', $currLink->href))[1], 0, 1)) {
                        case 'r':
                            $this->stage->id = substr(array_reverse(explode('/', $currLink->href))[1], 1); 
                            $this->stage->name = $currLink->innertext;
                            $this->report->addLog('Етап = ' . $this->stage->id . ' : : ' . $this->stage->name);
                            break;
                        case 'g':
                            $this->group->id = substr(array_reverse(explode('/', $currLink->href))[1], 1); 
                            $this->group->name = $currLink->innertext;
                            $this->report->addLog('Група = ' . $this->group->id . ' : : ' . $this->group->name);
                            break;
                    }

                    $level_3_ul = $level_3->find('ul.expanded', 0);
                    if ($level_3_ul) {
                        $level_4 = $level_3_ul->find('li.expanded', 0);
                        if (!$level_4) $level_4 = $level_3_ul->find('li.current', 0);
                        if ($level_4) {
                            $currLink = $level_4->find('a',0);
                            switch (substr(array_reverse(explode('/', $currLink->href))[1], 0, 1)) {
                                case 'r':
                                    $this->stage->id = substr(array_reverse(explode('/', $currLink->href))[1], 1); 
                                    $this->stage->name = $currLink->innertext;
                                    $this->report->addLog('Етап = ' . $this->stage->id . ' : : ' . $this->stage->name);
                                    break;
                                case 'g':
                                    $this->group->id = substr(array_reverse(explode('/', $currLink->href))[1], 1); 
                                    $this->group->name = $currLink->innertext;
                                    $this->report->addLog('Група = ' . $this->group->id . ' : : ' . $this->group->name);
                                    break;
                            }
                        }
                    }
                }
            }
        }
        
        // $this->report->addLog('Error = Неможливо визначити сезон, групу');
        // return false;
        //$this->region->is_international = (explode('/', $tourLink->href)[1] == 'international') ? 1 : 0;
        //$this->tournament->region_id = $this->region->id;
        $this->season->tournament_id = $this->tournament->id;
        $this->stage->season_id = $this->season->id;
        $this->group->stage_id = $this->stage->id;
        //$this->game->region_id = $this->region->id;
        //$this->game->tournament_id = $this->tournament->id;
        $this->game->season_id = $this->season->id;

        unset($this->parts->left_menu);
        unset($this->parts->top_block);
        return true;
    }

//  --------------------------------------------------------------------------
//  Повернути Репорт
//  --------------------------------------------------------------------------
    public function getReport()
    {
        return $this->report;
    }
}
