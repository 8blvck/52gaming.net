<?php
namespace App\Models;
use Mail;
use DB;

class MailTemplates {

    public static $lang_prefix;

    public static function build($name, $mailto, $vars = [], $lang_prefix = false) {
        if($name == 'raw') {
            $temp = (object) ['subject' => $vars['subject'] ?? '', 'body' => $vars['body'] ?? ''];
        } else if(!$temp = self::getTemplate($name)) {
            return false;
        }
        $sender     = 'no-reply@fastboosting.ru';
        $subject    = $temp->subject;
        $body       = $temp->body;
        self::$lang_prefix = $lang_prefix ? $lang_prefix : "";
        foreach($vars as $var => $content) {
            $body = str_replace(['${'.$var.'}'], $content, $body);
            $subject = str_replace(['${'.$var.'}'], $content, $subject);
        }
        return self::send($mailto, $subject, $body, $sender);
    }

    public static function getTemplate($name) {
        $temp = collect(DB::select("SELECT *, subject, body FROM email_templates WHERE `name`='$name' LIMIT 1"))->first();
        return ($temp ? $temp : false);
    }

    public static function send($mailto, $subject, $body, $sender) {
        try {
            $letter = Mail::send(['html'=>'emails.default'], ['body'=>$body], function ($message) use ($mailto, $subject, $sender) {
              $message->from($sender, $sender);
              $message->to($mailto, $mailto)->subject($subject);
            });
        } catch(Exception $e) {
            echo json_encode($e);
            return $e;
        }
        return $letter;
    }
}
