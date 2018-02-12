<?php
namespace PRS\Model;

//use PRS\Model\DB;

////////////////////////////////////
   // Методи для роботи з Events //
  //////////////////////////////////////////////////////////////////////////////

abstract class Event
{   
    
//  ----------------------------------------------------------------------------
//  Добавлення події
//  ----------------------------------------------------------------------------
    public static function AddEventMain($event, $game_id)
    {
        $DB = DB::Instance()->GetConnect();
        
        $attributes = '-';
        foreach ($event->satisfiedEventsTypes as &$attr) {
            $attr = $attr + 1000;
            $attributes .= $attr . '-';
        }

        $extra_attributes = '-';
        foreach ($event->qualifiers as $q) {
            if (!isset($q->value)) {
                $extra_attributes .= $q->type->value . '-';
            }
        }

        if (!isset($event->eventId)) $event->eventId = null;
        if (!isset($event->second)) $event->second = null;
        if (!isset($event->playerId)) $event->playerId = null;

        $stmt = $DB->prepare("INSERT INTO events(
            ws_id,
            game_id,
            event_num,
            minute,
            second,
            team_id,
            player_id,
            ex_minute,
            period,
            event_type_id,
            outcome,
            extra_attributes,
            extra_attributes_ser,
            attributes,
            attributes_ser,
            is_touch

            )VALUES(
            :id,
            :game_id,
            :event_num,
            :minute,
            :second,
            :team_id,
            :player_id,
            :ex_minute,
            :period,
            :event_type_id,
            :outcome,
            :extra_attributes,
            :extra_attributes_ser,
            :attributes,
            :attributes_ser,
            :is_touch
            )");
        
        $stmt->execute(array(
            'id' => $event->id,
            'game_id' => $game_id,
            'event_num' => $event->eventId,
            'minute' => $event->minute,
            'second' => $event->second,
            'team_id' => $event->teamId,
            'player_id' => $event->playerId,
            'ex_minute' => $event->expandedMinute,
            'period' => $event->period->value,
            'event_type_id' => $event->type->value,
            'outcome' => $event->outcomeType->value,
            'extra_attributes' => $extra_attributes,
            'extra_attributes_ser' => serialize($event->qualifiers),
            'attributes' => $attributes,
            'attributes_ser' => serialize($event->satisfiedEventsTypes),
            'is_touch' => $event->isTouch
            ));
        return 'Подія: '.$event->id.' добавлена';
    }

//  ----------------------------------------------------------------------------
//  Добавлення події Goal
//  ----------------------------------------------------------------------------
    public static function AddEventGoal($event, $game_id)
    {
        $DB = DB::Instance()->GetConnect();
        
        $attributes = '-';
        foreach ($event->satisfiedEventsTypes as $attr) {
            $attributes .= $attr . '-';
        }

        $extra_attributes = '-';
        foreach ($event->qualifiers as $q) {
            if (!isset($q->value)) {
                $extra_attributes .= $q->type->value . '-';
            }
        }

        $stmt = $DB->prepare("INSERT INTO game_goals(
            ws_id,
            game_id,
            team_id,
            player_id,
            time,
            period,
            extra_attributes,
            attributes

            )VALUES(
            :ws_id,
            :game_id,
            :team_id,
            :player_id,
            :time,
            :period,
            :extra_attributes,
            :attributes
            )");
        
        $stmt->execute(array(
            'ws_id' => $event->id,
            'game_id' => $game_id,
            'team_id' => $event->teamId,
            'player_id' => $event->playerId,
            'time' => $event->true_minute,
            'period' => $event->period->value,
            'extra_attributes' => $extra_attributes,
            'attributes' => $attributes
            ));
        return 'Гол: '.$event->id.' добавлений';
    }

//  ----------------------------------------------------------------------------
//  Добавлення події Corner
//  ----------------------------------------------------------------------------
    public static function AddEventCorner($event, $game_id)
    {
        $DB = DB::Instance()->GetConnect();
        
        $attributes = '-';
        foreach ($event->satisfiedEventsTypes as $attr) {
            $attributes .= $attr . '-';
        }

        $extra_attributes = '-';
        foreach ($event->qualifiers as $q) {
            if (!isset($q->value)) {
                $extra_attributes .= $q->type->value . '-';
            }
        }

        $stmt = $DB->prepare("INSERT INTO game_corners(
            ws_id,
            game_id,
            team_id,
            player_id,
            time,
            period,
            extra_attributes,
            attributes

            )VALUES(
            :ws_id,
            :game_id,
            :team_id,
            :player_id,
            :time,
            :period,
            :extra_attributes,
            :attributes
            )");
        
        $stmt->execute(array(
            'ws_id' => $event->id,
            'game_id' => $game_id,
            'team_id' => $event->teamId,
            'player_id' => $event->playerId,
            'time' => $event->true_minute,
            'period' => $event->period->value,
            'extra_attributes' => $extra_attributes,
            'attributes' => $attributes
            ));
        return 'Кутовий: '.$event->id.' добавлений';
    }


//  ----------------------------------------------------------------------------
//  Добавлення події Card
//  ----------------------------------------------------------------------------
    public static function AddEventCard($event, $game_id)
    {
        $DB = DB::Instance()->GetConnect();
        
        $attributes = '-';
        foreach ($event->satisfiedEventsTypes as $attr) {
            $attributes .= $attr . '-';
        }

        $extra_attributes = '-';
        foreach ($event->qualifiers as $q) {
            if (!isset($q->value)) {
                $extra_attributes .= $q->type->value . '-';
            }
        }

        $stmt = $DB->prepare("INSERT INTO game_cards(
            ws_id,
            game_id,
            team_id,
            player_id,
            time,
            period,
            extra_attributes,
            attributes

            )VALUES(
            :ws_id,
            :game_id,
            :team_id,
            :player_id,
            :time,
            :period,
            :extra_attributes,
            :attributes
            )");
        
        $stmt->execute(array(
            'ws_id' => $event->id,
            'game_id' => $game_id,
            'team_id' => $event->teamId,
            'player_id' => $event->playerId,
            'time' => $event->true_minute,
            'period' => $event->period->value,
            'extra_attributes' => $extra_attributes,
            'attributes' => $attributes
            ));
        return 'картка: '.$event->id.' добавлений';
    }


//  ----------------------------------------------------------------------------
//  Добавлення події Card
//  ----------------------------------------------------------------------------
    public static function AddEventSubst($event, $game_id)
    {
        $DB = DB::Instance()->GetConnect();
        
        $attributes = '-';
        foreach ($event->satisfiedEventsTypes as $attr) {
            $attributes .= $attr . '-';
        }

        $extra_attributes = '-';
        foreach ($event->qualifiers as $q) {
            if (!isset($q->value)) {
                $extra_attributes .= $q->type->value . '-';
            }
        }

        $stmt = $DB->prepare("INSERT INTO game_substitution(
            game_id,
            team_id,
            player_id_off,
            player_id_on,
            time,
            period

            )VALUES(
            :game_id,
            :team_id,
            :player_id_off,
            :player_id_on,
            :time,
            :period
            )");
        
        $stmt->execute(array(
            'game_id' => $game_id,
            'team_id' => $event->teamId,
            'player_id_off' => $event->relatedPlayerId,
            'player_id_on' => $event->playerId,
            'time' => $event->true_minute,
            'period' => $event->period->value
            ));
        return 'картка: '.$event->id.' добавлений';
    }


}

