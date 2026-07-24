<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'atsedeteguhan_pos');

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

mysqli_set_charset($conn, "utf8mb4");

function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] == 'admin';
}

function isSeller() {
    return isset($_SESSION['role']) && $_SESSION['role'] == 'seller';
}

function redirect($url) {
    header("Location: $url");
    exit();
}

function getUserBranch($conn, $user_id) {
    $id = intval($user_id);
    $result = mysqli_query($conn, "SELECT branch_id FROM users WHERE id = $id");
    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        return $row['branch_id'] ?? 1;
    }
    return 1;
}

function getCurrentBranchId($conn, $user_id, $role) {
    if ($role == 'admin' || $role == 'super_admin') {
        return $_SESSION['branch_id'] ?? 1;
    }
    return getUserBranch($conn, $user_id);
}

function getCurrentBranchName($conn, $branch_id) {
    $id = intval($branch_id);
    $result = mysqli_query($conn, "SELECT name FROM branches WHERE id = $id");
    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        return $row['name'];
    }
    return 'ዋና ቅርንጫፍ';
}

function getEthiopianDateDisplay() {
    $ts = time();
    $gy = (int)date('Y', $ts);
    $gm = (int)date('n', $ts);
    $gd = (int)date('j', $ts);
    
    $months = [1=>'መስከረም',2=>'ጥቅምት',3=>'ህዳር',4=>'ታህሳስ',5=>'ጥር',6=>'የካቲት',7=>'መጋቢት',8=>'ሚያዝያ',9=>'ግንቦት',10=>'ሰኔ',11=>'ሐምሌ',12=>'ነሐሴ',13=>'ጳጉሜ'];
    
    $il = (($gy%4==0 && $gy%100!=0) || $gy%400==0);
    $nyd = ($il && $gm>9) ? 12 : 11;
    $ey = ($gm>9||($gm==9&&$gd>=$nyd)) ? $gy-7 : $gy-8;
    
    $md = [30,30,30,30,30,30,30,30,30,30,30,30,5];
    if(($ey%4)==3) $md[12]=6;
    
    $nygy = ($gm<9||($gm==9&&$gd<$nyd)) ? $gy-1 : $gy;
    $inl = (($nygy%4==0 && $nygy%100!=0) || $nygy%400==0);
    $nydf = $inl ? 12 : 11;
    
    $nyts = mktime(0,0,0,9,$nydf,$nygy);
    $ds = floor(($ts-$nyts)/86400);
    $rem = $ds;
    $em = 1;
    $ed = 1;
    
    for($m=0;$m<13;$m++){
        if($rem<$md[$m]){ $em=$m+1; $ed=$rem+1; break; }
        $rem-=$md[$m];
    }
    
    return $ed.' '.$months[$em].' '.$ey;
}

function getAllBranches($conn) {
    $branches = [];
    $result = mysqli_query($conn, "SELECT * FROM branches ORDER BY name");
    if ($result) {
        while($row = mysqli_fetch_assoc($result)) {
            $branches[] = $row;
        }
    }
    return $branches;
}

function setBranchSession($branch_id, $branch_name) {
    $_SESSION['branch_id'] = $branch_id;
    $_SESSION['branch_name'] = $branch_name;
}

if (!function_exists('gregorian_to_ethiopian')) {
    function gregorian_to_ethiopian($year, $month, $day) {
        $ethiopian_months = [
            1 => "መስከረም", 2 => "ጥቅምት", 3 => "ህዳር", 4 => "ታህሳስ", 
            5 => "ጥር", 6 => "የካቲት", 7 => "መጋቢት", 8 => "ሚያዝያ", 
            9 => "ግንቦት", 10 => "ሰኔ", 11 => "ሐምሌ", 12 => "ነሐሴ", 13 => "ጳጉሜ"
        ];
        
        $ethiopian_year = $year - 8;
        $is_gregorian_leap = ($year % 4 == 0 && $year % 100 != 0) || ($year % 400 == 0);
        $new_year_day = $is_gregorian_leap ? 12 : 11;
        
        if ($month > 9 || ($month == 9 && $day >= $new_year_day)) {
            $ethiopian_year = $year - 7;
        }
        
        $new_year_gregorian_year = ($month < 9 || ($month == 9 && $day < $new_year_day)) ? $year - 1 : $year;
        $is_new_year_leap = ($new_year_gregorian_year % 4 == 0 && $new_year_gregorian_year % 100 != 0) || ($new_year_gregorian_year % 400 == 0);
        $new_year_day_final = $is_new_year_leap ? 12 : 11;
        
        $jd_current = gregoriantojd($month, $day, $year);
        $jd_new_year = gregoriantojd(9, $new_year_day_final, $new_year_gregorian_year);
        $days_since_new_year = $jd_current - $jd_new_year;
        
        $ethiopian_month = floor($days_since_new_year / 30) + 1;
        $ethiopian_day = ($days_since_new_year % 30) + 1;
        
        if ($ethiopian_month == 13) {
            $is_ethiopian_leap = ($ethiopian_year % 4 == 3);
            $max_pagume_days = $is_ethiopian_leap ? 6 : 5;
            if ($ethiopian_day > $max_pagume_days) {
                $ethiopian_month = 1;
                $ethiopian_day -= $max_pagume_days;
                $ethiopian_year++;
            }
        }
        
        $ethiopian_month = max(1, min(13, $ethiopian_month));
        
        return [
            'year' => $ethiopian_year,
            'month' => $ethiopian_month,
            'month_name' => $ethiopian_months[$ethiopian_month] ?? "መስከረም",
            'day' => $ethiopian_day,
            'full_date' => sprintf("%04d-%02d-%02d", $ethiopian_year, $ethiopian_month, $ethiopian_day),
            'display_date' => $ethiopian_day . ' ' . ($ethiopian_months[$ethiopian_month] ?? "መስከረም") . ' ' . $ethiopian_year
        ];
    }
}

if (!function_exists('getEthiopianDate')) {
    function getEthiopianDate($datetime = null) {
        if ($datetime === null) {
            $datetime = date('Y-m-d H:i:s');
        }
        $timestamp = strtotime($datetime);
        $year = (int)date('Y', $timestamp);
        $month = (int)date('n', $timestamp);
        $day = (int)date('j', $timestamp);
        return gregorian_to_ethiopian($year, $month, $day);
    }
}

if (!function_exists('format_ethiopian_date_from_db')) {
    function format_ethiopian_date_from_db($db_datetime) {
        if (empty($db_datetime)) return ['display' => ''];
        $timestamp = strtotime($db_datetime);
        $year = (int)date('Y', $timestamp);
        $month = (int)date('n', $timestamp);
        $day = (int)date('j', $timestamp);
        $eth = gregorian_to_ethiopian($year, $month, $day);
        return [
            'display' => $eth['display_date'],
            'year' => $eth['year'],
            'month' => $eth['month'],
            'month_name' => $eth['month_name'],
            'day' => $eth['day']
        ];
    }
}

if (!function_exists('format_gregorian_time_12hr')) {
    function format_gregorian_time_12hr($datetime) {
        if (empty($datetime)) return '';
        $date = new DateTime($datetime, new DateTimeZone('Africa/Addis_Ababa'));
        return $date->format('h:i:s A');
    }
}
?>