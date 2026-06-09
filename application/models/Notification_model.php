<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Notification_model extends CI_Model
{
    public function getNotificationConfig($table_name = '')
    {
        if (empty($table_name)) {
            return [];
        }

        $data = $this->db
            ->select("
                user_notification_1,
                user_notification_2,
                user_notification_3,
                user_notification_4,
                user_notification_5
            ")
            ->where('table_name', $table_name)
            ->where('deleted', 0)
            ->where('status', 0)
            ->get('user_notifications')
            ->row_array();

        return $data ?: [];
    }

    public function getNotificationUsers($config = [])
    {
        if (empty($config)) {
            return [];
        }

        $users = [];
        foreach ($config as $value) {

            if (!empty($value)) {
                $users[] = trim($value);
            }
        }

        return array_unique($users);
    }

    public function getNotificationLevel($config = [], $username = '')
    {
        if (empty($config) || empty($username)) {
            return null;
        }

        foreach ($config as $key => $value) {

            if ($value == $username) {

                preg_match('/(\d+)/', $key, $match);

                return isset($match[1])
                    ? (int)$match[1]
                    : null;
            }
        }

        return null;
    }
}