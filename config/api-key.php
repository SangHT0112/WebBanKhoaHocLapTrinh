<?php
// Định nghĩa nhiều API keys (thay bằng keys thực của bạn, phân cách bằng dấu phẩy hoặc mảng)
// Ví dụ: Các keys từ Google AI Studio (tạo nhiều account để có nhiều keys miễn phí)
$rawKeys = [
    "AIzaSyC2G-3OuJEQtoJDaFo5X_zdAvUpG1LSAGg",
    'AIzaSyBk7twdv6n450gZtjhbNN_ugriuqkut-UE',

    'AIzaSyCCcTfhbAFLpKHWBbmQj4gz2VzYMTXy4J8',  // Key 1
    'AIzaSyC53rYBDAMuxqV0h954Zag_Ea1BH494Lrg',  // Key 2
    'AIzaSyDyFqx1JcpDq7Jp5FtOucXFQZ9Cm15_Wes',
    "AIzaSyB0N08gzn_alTZ_yM5BWHJFiHDLFUO2vkY",
    'AIzaSyA0dxT-VrMs4VphmoztFk89aojjg28BwzM',
];

// Lọc keys hợp lệ (không rỗng và không phải 'xxx' placeholder)
$validKeys = array_filter($rawKeys, function($key) {
    return !empty($key) && $key !== 'xxx';
});

define('GEMINI_API_KEYS', $validKeys);  // Mảng keys đã lọc


define('GMAIL_USERNAME', 'huynhtsang2004@gmail.com');
define('GMAIL_PASSWORD', 'xtrgjliokmzruehr'); 
?>